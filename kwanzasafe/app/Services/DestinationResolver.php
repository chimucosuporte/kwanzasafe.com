<?php

namespace App\Services;

use App\Models\Beneficiary;
use App\Models\PaymentWallet;
use App\Models\User;
use Illuminate\Validation\ValidationException;

/**
 * Resolve e valida o destino de recepção (conta bancária ou carteira) de um
 * utilizador, devolvendo um snapshot desnormalizado para gravar na transação.
 * Partilhado pela web e pela API mobile — fonte única da verdade.
 *
 * @return array{type:string,label:string,identifier:string,holder:?string,network:?string}
 */
class DestinationResolver
{
    public const WALLET_LABELS = [
        'bybit'    => 'Bybit',
        'binance'  => 'Binance',
        'redotpay' => 'RedotPay',
    ];

    /**
     * @return array{type:string,label:string,identifier:string,holder:?string,network:?string}
     */
    public static function resolve(User $user, string $tipo, int $id): array
    {
        if ($tipo === 'bank') {
            $b = Beneficiary::where('id', $id)->where('user_id', $user->id)->first();
            if (! $b) {
                throw ValidationException::withMessages(['destino_id' => 'Conta bancária inválida ou não encontrada.']);
            }

            return ['type' => 'bank', 'label' => $b->bank_name, 'identifier' => $b->iban, 'holder' => $b->holder_name, 'network' => null];
        }

        $w = PaymentWallet::where('id', $id)->where('user_id', $user->id)->where('provider', $tipo)->first();
        if (! $w) {
            throw ValidationException::withMessages(['destino_id' => 'Carteira inválida ou não encontrada.']);
        }

        return [
            'type'       => $tipo,
            'label'      => self::WALLET_LABELS[$tipo] ?? ucfirst($tipo),
            'identifier' => $w->identifier,
            'holder'     => $w->holder_name,
            'network'    => $w->network,
        ];
    }
}
