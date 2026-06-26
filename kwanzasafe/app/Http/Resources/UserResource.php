<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Forma canónica do utilizador em JSON para a API mobile.
 *
 * NUNCA expõe password, remember_token nem notas internas do KYC bot.
 *
 * @mixin User
 */
class UserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'full_name'    => $this->full_name,
            'email'        => $this->email,
            'phone_number' => $this->phone_number,
            'country'      => $this->country,
            'province'     => $this->province,
            'balance'      => $this->balance,
            'avatar_url'   => $this->display_photo_path ? url('api/v1/file/'.ltrim($this->display_photo_path, '/')) : null,

            // Papel (booleanos = fonte da verdade; label legível para a UI)
            'role'           => $this->isSuperAdmin() ? 'super_admin' : ($this->is_admin ? 'support' : 'client'),
            'role_label'     => $this->roleLabel(),
            'is_admin'       => (bool) $this->is_admin,
            'is_super_admin' => (bool) $this->is_super_admin,

            // Estado de verificação / KYC (para gating na app)
            'email_verified'     => ! is_null($this->email_verified_at),
            'phone_verified'     => ! is_null($this->phone_verified_at),
            'identity_verified'  => ! is_null($this->identity_verified_at),
            'data_verified'      => (bool) $this->data_verified,
            'is_fully_verified'  => (bool) $this->is_fully_verified,
            'kyc_bot_status'     => $this->kyc_bot_status,
            'kyc_score'          => $this->kyc_score,

            'two_factor_enabled' => $this->hasTwoFactorEnabled(),

            'created_at' => $this->created_at,
        ];
    }
}
