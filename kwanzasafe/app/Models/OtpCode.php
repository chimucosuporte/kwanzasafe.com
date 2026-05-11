<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OtpCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'destination',
        'code_hash',
        'attempts',
        'verified_at',
        'expires_at',
        'ip_address',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
        'expires_at'  => 'datetime',
    ];

    /**
     * Relação com utilizador
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Verifica se o código ainda é válido (não expirou e não foi usado)
     */
    public function isValid(): bool
    {
        return $this->verified_at === null
            && $this->expires_at->isFuture()
            && $this->attempts < 5;
    }

    /**
     * Marca código como usado
     */
    public function markAsVerified(): void
    {
        $this->verified_at = now();
        $this->save();
    }
}
