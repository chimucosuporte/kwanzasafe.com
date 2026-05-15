<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBeneficiaryRequest;
use App\Models\Beneficiary;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BeneficiaryController extends Controller
{
    public function store(StoreBeneficiaryRequest $request)
    {

        $user = Auth::user();

        // Validação de Titularidade (Anti-Fraude)
        $holderNormalized   = strtolower(trim(preg_replace('/\s+/', ' ', $request->holder_name)));
        $userNameNormalized = strtolower(trim(preg_replace('/\s+/', ' ', $user->full_name ?? '')));

        if ($userNameNormalized && $holderNormalized !== $userNameNormalized) {
            // AUDIT CRÍTICO: tentativa de adicionar conta em nome de terceiros
            AuditLogger::beneficiary('fraud_attempt',
                "Tentativa bloqueada: adicionar IBAN com titular diferente do KYC",
                null,
                [
                    'user_full_name'     => $user->full_name,
                    'attempted_holder'   => $request->holder_name,
                    'bank_name'          => $request->bank_name,
                    'iban_masked'        => substr($request->iban, 0, 4) . '****' . substr($request->iban, -4),
                ]
            );

            return redirect()
                ->route('dashboard', ['tab' => 'iban'])
                ->with('error', 'O nome do titular "' . $request->holder_name . '" não corresponde ao nome registado no teu KYC ("' . $user->full_name . '"). Por segurança, a conta foi recusada.');
        }

        $ibanClean = strtoupper(str_replace(' ', '', $request->iban));

        $exists = Beneficiary::where('user_id', $user->id)
                              ->where('iban', $ibanClean)
                              ->exists();

        if ($exists) {
            return redirect()
                ->route('dashboard', ['tab' => 'iban'])
                ->with('error', 'Este IBAN já está registado na tua conta.');
        }

        $beneficiary = Beneficiary::create([
            'user_id'     => $user->id,
            'bank_name'   => $request->bank_name,
            'iban'        => $ibanClean,
            'holder_name' => $request->holder_name,
        ]);

        // AUDIT: IBAN adicionado com sucesso
        AuditLogger::beneficiary('added',
            "Nova conta bancária adicionada: {$request->bank_name}",
            $beneficiary,
            [
                'bank_name'   => $request->bank_name,
                'iban_masked' => substr($ibanClean, 0, 4) . '****' . substr($ibanClean, -4),
            ]
        );

        return redirect()
            ->route('dashboard', ['tab' => 'iban'])
            ->with('success', 'Conta bancária adicionada com sucesso ao teu Cofre IBAN.');
    }

    public function destroy($id)
    {
        $beneficiary = Beneficiary::where('id', $id)
                                  ->where('user_id', Auth::id())
                                  ->firstOrFail();

        // Snapshot antes de apagar
        $snapshot = $beneficiary->toArray();
        $snapshot['iban'] = substr($beneficiary->iban, 0, 4) . '****' . substr($beneficiary->iban, -4);

        $beneficiary->delete();

        // AUDIT: IBAN removido
        AuditLogger::beneficiary('removed',
            "Conta bancária removida: {$snapshot['bank_name']}",
            null,
            $snapshot
        );

        return redirect()
            ->route('dashboard', ['tab' => 'iban'])
            ->with('success', 'Conta bancária removida do teu Cofre IBAN.');
    }
}
