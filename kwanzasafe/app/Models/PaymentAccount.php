<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Conta de recepção (dados de pagamento) para uma moeda de origem.
 */
class PaymentAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'currency',
        'holder',
        'identifier',
        'network',
        'instructions',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /** Conta activa para uma dada moeda de origem (ou null). */
    public static function activeFor(string $currency): ?self
    {
        return static::where('currency', $currency)->where('is_active', true)->first();
    }
}
