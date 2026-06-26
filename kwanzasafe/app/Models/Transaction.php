<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'reference_id',
        'user_id',
        'assigned_admin',
        'exchange_rate_id',
        'currency_from',
        'currency_to',
        'amount_sent',
        'rate_applied',
        'amount_received',
        'fee_amount',
        'destination_type',
        'destination_label',
        'destination_identifier',
        'destination_holder',
        'destination_network',
        'status',
        'expires_at',
        'payment_received_at',
        'aoa_sent_at',
        'client_confirmed_at',
        'admin_notes',
    ];

    protected $casts = [
        'amount_sent'         => 'decimal:2',
        'rate_applied'        => 'decimal:4',
        'amount_received'     => 'decimal:2',
        'fee_amount'          => 'decimal:2',
        'expires_at'          => 'datetime',
        'payment_received_at' => 'datetime',
        'aoa_sent_at'         => 'datetime',
        'client_confirmed_at' => 'datetime',
    ];

    /**
     * Cliente dono da transação
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Admin atribuído (opcional)
     */
    public function admin()
    {
        return $this->belongsTo(User::class, 'assigned_admin');
    }

    /**
     * Taxa aplicada
     */
    public function exchangeRate()
    {
        return $this->belongsTo(ExchangeRate::class);
    }

    /**
     * Mensagens de chat associadas (CRÍTICO para Sprint 3B)
     */
    public function chatMessages()
    {
        return $this->hasMany(ChatMessage::class, 'transaction_id');
    }

    /**
     * Número de mensagens por ler do cliente
     */
    public function unreadFromClient()
    {
        return $this->chatMessages()
                    ->whereHas('sender', fn($q) => $q->where('is_admin', false))
                    ->where('is_read', false)
                    ->count();
    }

    /**
     * Última mensagem
     */
    public function lastMessage()
    {
        return $this->hasOne(ChatMessage::class, 'transaction_id')->latest();
    }
}
