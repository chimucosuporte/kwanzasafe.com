<?php

namespace App\Http\Resources;

use App\Models\PaymentAccount;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Conta de recepção (onde o cliente paga) para a moeda da transação.
 *
 * @mixin PaymentAccount
 */
class PaymentAccountResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'currency'     => $this->currency,
            'holder'       => $this->holder,
            'identifier'   => $this->identifier,
            'network'      => $this->network,
            'instructions' => $this->instructions,
        ];
    }
}
