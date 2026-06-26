<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements \Illuminate\Contracts\Auth\MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'full_name', 'name', 'email', 'password', 'balance',
        'phone_number', 'phone_verified_at',
        'birth_date', 'gender', 'bi_number', 'bi_expiry',
        'province', 'municipality', 'address',
        'identity_document_path', 'identity_verified_at',
        'profile_photo_path', 'data_verified',
        'is_admin', 'is_super_admin', 'is_active', 'is_fully_verified',
        'kyc_score',
        'kyc_bot_status',
        'kyc_bot_analyzed_at',
        'kyc_bot_notes',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
    ];

    /**
     * CASTS — Essencial para usar ->format() e ->diffForHumans() nas views.
     * Sem isto, campos de data retornam strings brutas e causam erros.
     */
    protected $casts = [
        'email_verified_at'   => 'datetime',
        'phone_verified_at'   => 'datetime',
        'identity_verified_at'=> 'datetime',
        'birth_date'          => 'date',
        'bi_expiry'           => 'date',
        'data_verified'       => 'boolean',
        'is_admin'            => 'boolean',
        'is_super_admin'      => 'boolean',
        'is_active'           => 'boolean',
        'is_fully_verified'   => 'boolean',
        'balance'             => 'decimal:2',
        'kyc_bot_analyzed_at' => 'datetime',
        'kyc_bot_notes'       => 'array',
        'two_factor_secret'      => 'encrypted',
        'two_factor_confirmed_at'=> 'datetime',
    ];

    /** 2FA (TOTP) activo = segredo confirmado. */
    public function hasTwoFactorEnabled(): bool
    {
        return ! is_null($this->two_factor_confirmed_at);
    }

    // ============================================================
    // KYC helpers
    // ============================================================

    /**
     * Recalcula e persiste is_fully_verified com base nos campos KYC reais.
     * Chamar após qualquer alteração de estado KYC.
     */
    public function syncFullyVerified(): void
    {
        $verified = $this->email_verified_at
            && $this->phone_verified_at
            && $this->identity_verified_at
            && $this->data_verified
            && $this->kyc_bot_status === 'auto_approved';

        if ((bool) $this->is_fully_verified !== (bool) $verified) {
            $this->is_fully_verified = $verified;
            $this->saveQuietly();
        }
    }

    // ============================================================
    // Accessors / Mutators
    // ============================================================

    public function getNameAttribute(): ?string
    {
        return $this->full_name;
    }

    public function setNameAttribute($value): void
    {
        $this->attributes['full_name'] = $value;
    }

    /**
     * Caminho da foto a EXIBIR como avatar: o avatar de perfil escolhido pelo
     * utilizador (mobile) ou, em fallback, a selfie do KYC. Garante que web e
     * app mostram a MESMA foto. (A revisão de KYC usa profile_photo_path direto.)
     */
    public function getDisplayPhotoPathAttribute(): ?string
    {
        return $this->avatar_path ?: $this->profile_photo_path;
    }

    // ============================================================
    // Papéis (Fase 0 — multi-equipa)
    // ============================================================

    /**
     * Mantém is_admin / is_super_admin / role coerentes em qualquer save.
     * Fonte da verdade: os booleanos. role é espelho para leitura/audit.
     *   super_admin ⇒ admin.
     */
    protected static function booted(): void
    {
        static::saving(function (User $user) {
            if ($user->is_super_admin) {
                $user->is_admin = true;
                $user->role     = 'super_admin';
            } elseif ($user->is_admin) {
                $user->role = 'admin';
            } else {
                $user->role = 'client';
            }
        });
    }

    /** Admin máximo — acesso total, gere staff e arbitra recursos. */
    public function isSuperAdmin(): bool
    {
        return (bool) $this->is_super_admin;
    }

    /** Funcionário de suporte — admin de nível baixo (não super-admin). */
    public function isSupport(): bool
    {
        return $this->is_admin && ! $this->is_super_admin;
    }

    /** Qualquer membro da equipa (suporte ou super-admin). */
    public function isStaff(): bool
    {
        return (bool) $this->is_admin;
    }

    /** Cliente final. */
    public function isClient(): bool
    {
        return ! $this->is_admin;
    }

    /** Rótulo legível do papel. */
    public function roleLabel(): string
    {
        return match (true) {
            $this->isSuperAdmin() => 'Super-Admin',
            $this->isSupport()    => 'Suporte',
            default               => 'Cliente',
        };
    }

    // ============================================================
    // Relações
    // ============================================================
    public function beneficiaries()
    {
        return $this->hasMany(Beneficiary::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    /** Tíquetes/transações atribuídos a este membro do staff (suporte). */
    public function assignedTransactions()
    {
        return $this->hasMany(Transaction::class, 'assigned_admin');
    }

    /**
     * Códigos OTP relacionados (Sprint 4.1)
     */
    public function otpCodes()
    {
        return $this->hasMany(\App\Models\OtpCode::class);
    }
}