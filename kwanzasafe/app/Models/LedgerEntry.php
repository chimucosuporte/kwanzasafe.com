<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LedgerEntry extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'transaction_id',
        'type',
        'amount',
        'balance_before',
        'balance_after',
        'currency',
        'description',
        'created_by',
    ];

    protected $casts = [
        'amount'         => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after'  => 'decimal:2',
        'created_at'     => 'datetime',
    ];

    protected static function booted(): void
    {
        // Ledger é imutável — igual ao AuditLog
        static::updating(fn() => throw new \RuntimeException('LedgerEntry é imutável.'));
        static::deleting(fn() => throw new \RuntimeException('LedgerEntry é imutável.'));
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
}
