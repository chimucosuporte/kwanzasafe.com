<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements \Illuminate\Contracts\Auth\MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'full_name', 'name', 'email', 'password', 'balance',
        'phone_number', 'phone_verified_at',
        'birth_date', 'gender', 'bi_number', 'bi_expiry',
        'province', 'municipality', 'address',
        'identity_document_path', 'identity_verified_at',
        'profile_photo_path', 'data_verified',
        'is_admin', 'is_fully_verified',
        'kyc_score',
        'kyc_bot_status',
        'kyc_bot_analyzed_at',
        'kyc_bot_notes',
    ];

    protected $hidden = [
        'password',
        'remember_token',
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
        'is_fully_verified'   => 'boolean',
        'balance'             => 'decimal:2',
        'kyc_bot_analyzed_at' => 'datetime',
        'kyc_bot_notes'       => 'array',
    ];

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

    /**
     * Códigos OTP relacionados (Sprint 4.1)
     */
    public function otpCodes()
    {
        return $this->hasMany(\App\Models\OtpCode::class);
    }
}