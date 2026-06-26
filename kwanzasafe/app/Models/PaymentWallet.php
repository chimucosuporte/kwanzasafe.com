<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Carteira de recepção (Bybit / Binance / RedotPay) de um utilizador.
 *
 * Colunas conforme migration 2026_06_21_100001_create_payment_wallets_table.
 */
class PaymentWallet extends Model
{
    use HasFactory, SoftDeletes;

    /** Provedores suportados. */
    public const PROVIDERS = ['bybit', 'binance', 'redotpay'];

    protected $fillable = [
        'user_id',
        'provider',
        'identifier',
        'holder_name',
        'network',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
