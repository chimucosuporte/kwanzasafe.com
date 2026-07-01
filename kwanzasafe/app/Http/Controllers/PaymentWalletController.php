<?php

namespace App\Http\Controllers;

use App\Models\PaymentWallet;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Carteiras de recepção (Bybit / Binance / RedotPay) — versão web.
 * Espelha o Api\PaymentWalletController: mesmo anti-fraude de titularidade
 * (holder_name vs full_name do KYC) e anti-duplicado.
 */
class PaymentWalletController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /** Adiciona uma carteira (com anti-fraude e anti-duplicado). */
    public function store(Request $request)
    {
        $data = $request->validate([
            'provider'    => ['required', 'string', Rule::in(PaymentWallet::PROVIDERS)],
            'identifier'  => ['required', 'string', 'max:191'],
            'holder_name' => ['required', 'string', 'max:191'],
            'network'     => ['nullable', 'string', 'max:50'],
            'is_default'  => ['sometimes', 'boolean'],
        ]);

        $user = Auth::user();

        // Anti-fraude: o titular tem de coincidir com o nome do KYC.
        $holderNormalized   = strtolower(trim(preg_replace('/\s+/', ' ', $data['holder_name'])));
        $userNameNormalized = strtolower(trim(preg_replace('/\s+/', ' ', $user->full_name ?? '')));

        if ($userNameNormalized && $holderNormalized !== $userNameNormalized) {
            AuditLogger::beneficiary('fraud_attempt',
                'Tentativa bloqueada: adicionar carteira com titular diferente do KYC (web)',
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
            "Nova carteira adicionada: {$data['provider']} (web)",
            $wallet,
            ['provider' => $data['provider']]
        );

        return back()->with('success', 'Carteira adicionada com sucesso.');
    }

    /** Remove uma carteira do utilizador. */
    public function destroy(Request $request, int $id)
    {
        $wallet = PaymentWallet::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $snapshot = $wallet->toArray();
        $wallet->delete();

        AuditLogger::beneficiary('wallet_removed',
            "Carteira removida: {$snapshot['provider']} (web)",
            null,
            $snapshot
        );

        return back()->with('success', 'Carteira removida.');
    }
}
