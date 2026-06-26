<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Recurso — escalonamento de uma transação do cliente para o super-admin.
 *
 * Estados: open → in_review → resolved | rejected
 */
class Recourse extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'opened_by',
        'assigned_super_admin',
        'reason',
        'status',
        'expires_at',
        'resolution',
        'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
        'expires_at'  => 'datetime',
    ];

    /** Rótulos legíveis (PT) por estado. */
    public const STATUS_LABELS = [
        'open'      => 'Em aberto',
        'in_review' => 'Em análise',
        'resolved'  => 'Resolvido',
        'rejected'  => 'Recusado',
        'cancelled' => 'Cancelado',
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function openedBy()
    {
        return $this->belongsTo(User::class, 'opened_by');
    }

    public function superAdmin()
    {
        return $this->belongsTo(User::class, 'assigned_super_admin');
    }

    /** Recursos ainda por resolver. */
    public function scopeActive(Builder $q): Builder
    {
        return $q->whereIn('status', ['open', 'in_review']);
    }

    public function isActive(): bool
    {
        return in_array($this->status, ['open', 'in_review'], true);
    }
}
