<?php

namespace App\Http\Resources;

use App\Models\Beneficiary;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Beneficiário (conta bancária / IBAN) do utilizador, para a app mobile.
 *
 * @mixin Beneficiary
 */
class BeneficiaryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'bank_name'   => $this->bank_name,
            'iban'        => $this->iban,
            'holder_name' => $this->holder_name,
            'created_at'  => $this->created_at?->toIso8601String(),
        ];
    }
}
