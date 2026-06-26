<?php

namespace App\Http\Resources;

use App\Models\PaymentWallet;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Carteira de recepção do utilizador, para a app mobile.
 *
 * @mixin PaymentWallet
 */
class PaymentWalletResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'provider'    => $this->provider,
            'identifier'  => $this->identifier,
            'holder_name' => $this->holder_name,
            'network'     => $this->network,
            'is_default'  => (bool) $this->is_default,
            'created_at'  => $this->created_at?->toIso8601String(),
        ];
    }
}
