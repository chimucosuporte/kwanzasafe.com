<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentWalletResource;
use App\Models\PaymentWallet;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Carteiras de recepção (Bybit / Binance / RedotPay) via API mobile.
 *
 * Complementa o BeneficiaryController (IBAN). Aplica o mesmo anti-fraude de
 * titularidade (holder_name vs full_name do KYC).
 */
class PaymentWalletController extends Controller
{
    /** Lista as carteiras do utilizador (predefinida primeiro, depois recentes). */
    public function index(Request $request): AnonymousResourceCollection
    {
        $wallets = PaymentWallet::where('user_id', $request->user()->id)
            ->orderByDesc('is_default')
            ->orderByDesc('created_at')
            ->get();

        return PaymentWalletResource::collection($wallets);
    }

    /** Adiciona uma carteira (com anti-fraude e anti-duplicado). */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'provider'    => ['required', 'string', Rule::in(PaymentWallet::PROVIDERS)],
            'identifier'  => ['required', 'string', 'max:191'],
            'holder_name' => ['required', 'string', 'max:191'],
            'network'     => ['nullable', 'string', 'max:50'],
            'is_default'  => ['sometimes', 'boolean'],
        ]);

        $user = $request->user();

        // Anti-fraude: o titular tem de coincidir com o nome do KYC.
        $holderNormalized   = strtolower(trim(preg_replace('/\s+/', ' ', $data['holder_name'])));
        $userNameNormalized = strtolower(trim(preg_replace('/\s+/', ' ', $user->full_name ?? '')));

        if ($userNameNormalized && $holderNormalized !== $userNameNormalized) {
            AuditLogger::beneficiary('fraud_attempt',
                'Tentativa bloqueada: adicionar carteira com titular diferente do KYC (mobile)',
                null,
                [
                    'user_full_name'   => $user->full_name,
                    'attempted_holder' => $data['holder_name'],
                    'provider'         => $data['provider'],
                ]
            );

            throw ValidationException::withMessages([
                'holder_name' => 'O nome do titular não corresponde ao nome registado no teu KYC ("'.$user->full_name.'"). Por segurança, a carteira foi recusada.',
            ]);
        }

        $identifierClean = trim($data['identifier']);

        $exists = PaymentWallet::where('user_id', $user->id)
            ->where('provider', $data['provider'])
            ->where('identifier', $identifierClean)
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'identifier' => 'Esta carteira já está registada na tua conta.',
            ]);
        }

        $makeDefault = (bool) ($data['is_default'] ?? false);

        // Se for marcada como predefinida, limpa as outras.
        if ($makeDefault) {
            PaymentWallet::where('user_id', $user->id)->update(['is_default' => false]);
        }

        $wallet = PaymentWallet::create([
            'user_id'     => $user->id,
            'provider'    => $data['provider'],
            'identifier'  => $identifierClean,
            'holder_name' => $data['holder_name'],
            'network'     => $data['network'] ?? null,
            'is_default'  => $makeDefault,
        ]);

        AuditLogger::beneficiary('wallet_added',
            "Nova carteira adicionada: {$data['provider']} (mobile)",
            $wallet,
            ['provider' => $data['provider']]
        );

        return (new PaymentWalletResource($wallet))->response()->setStatusCode(201);
    }

    /** Marca uma carteira como predefinida (limpa as outras). */
    public function setDefault(Request $request, int $id): AnonymousResourceCollection
    {
        $user = $request->user();

        PaymentWallet::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        PaymentWallet::where('user_id', $user->id)->update(['is_default' => false]);
        PaymentWallet::where('id', $id)->where('user_id', $user->id)->update(['is_default' => true]);

        return $this->index($request);
    }

    /** Remove uma carteira do utilizador. */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $wallet = PaymentWallet::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $snapshot = $wallet->toArray();
        $wallet->delete();

        AuditLogger::beneficiary('wallet_removed',
            "Carteira removida: {$snapshot['provider']} (mobile)",
            null,
            $snapshot
        );

        return response()->json(['message' => 'Carteira removida.']);
    }
}
