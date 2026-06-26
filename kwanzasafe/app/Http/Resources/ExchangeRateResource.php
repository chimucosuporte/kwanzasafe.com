<?php

namespace App\Http\Resources;

use App\Models\ExchangeRate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Taxa de câmbio activa — alimenta a calculadora da app.
 *
 * @mixin ExchangeRate
 */
class ExchangeRateResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'currency_from' => $this->currency_from,
            'currency_to'   => $this->currency_to,
            // String para preservar precisão decimal (nunca float no dinheiro).
            'rate'          => (string) $this->rate,
            'is_active'     => (bool) $this->is_active,
        ];
    }
}
