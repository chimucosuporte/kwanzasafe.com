<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBeneficiaryRequest;
use App\Http\Resources\BeneficiaryResource;
use App\Models\Beneficiary;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\ValidationException;

/**
 * Beneficiários (Cofre IBAN) via API mobile.
 * Espelha App\Http\Controllers\BeneficiaryController (web), incluindo o
 * anti-fraude de titularidade (holder_name vs full_name do KYC).
 */
class BeneficiaryController extends Controller
{
    /** Lista as contas bancárias do utilizador (mais recentes primeiro). */
    public function index(Request $request): AnonymousResourceCollection
    {
        $beneficiaries = Beneficiary::where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->get();

        return BeneficiaryResource::collection($beneficiaries);
    }

    /** Adiciona uma conta bancária (com validação anti-fraude). */
    public function store(StoreBeneficiaryRequest $request): JsonResponse
    {
        $user = $request->user();

        // Anti-fraude: o titular tem de coincidir com o nome do KYC.
        $holderNormalized   = strtolower(trim(preg_replace('/\s+/', ' ', $request->holder_name)));
        $userNameNormalized = strtolower(trim(preg_replace('/\s+/', ' ', $user->full_name ?? '')));

        if ($userNameNormalized && $holderNormalized !== $userNameNormalized) {
            AuditLogger::beneficiary('fraud_attempt',
                'Tentativa bloqueada: adicionar IBAN com titular diferente do KYC (mobile)',
                null,
                [
                    'user_full_name'   => $user->full_name,
                    'attempted_holder' => $request->holder_name,
                    'bank_name'        => $request->bank_name,
                    'iban_masked'      => substr($request->iban, 0, 4).'****'.substr($request->iban, -4),
                ]
            );

            throw ValidationException::withMessages([
                'holder_name' => 'O nome do titular não corresponde ao nome registado no teu KYC ("'.$user->full_name.'"). Por segurança, a conta foi recusada.',
            ]);
        }

        $ibanClean = strtoupper(str_replace(' ', '', $request->iban));

        $exists = Beneficiary::where('user_id', $user->id)
            ->where('iban', $ibanClean)
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'iban' => 'Este IBAN já está registado na tua conta.',
            ]);
        }

        $beneficiary = Beneficiary::create([
            'user_id'     => $user->id,
            'bank_name'   => $request->bank_name,
            'iban'        => $ibanClean,
            'holder_name' => $request->holder_name,
        ]);

        AuditLogger::beneficiary('added',
            "Nova conta bancária adicionada: {$request->bank_name} (mobile)",
            $beneficiary,
            [
                'bank_name'   => $request->bank_name,
                'iban_masked' => substr($ibanClean, 0, 4).'****'.substr($ibanClean, -4),
            ]
        );

        return (new BeneficiaryResource($beneficiary))->response()->setStatusCode(201);
    }

    /** Remove uma conta bancária do utilizador. */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $beneficiary = Beneficiary::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $snapshot = $beneficiary->toArray();
        $snapshot['iban'] = substr($beneficiary->iban, 0, 4).'****'.substr($beneficiary->iban, -4);

        $beneficiary->delete();

        AuditLogger::beneficiary('removed',
            "Conta bancária removida: {$snapshot['bank_name']} (mobile)",
            null,
            $snapshot
        );

        return response()->json(['message' => 'Conta bancária removida.']);
    }
}
