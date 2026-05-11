<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * AuditLog — Registo imutável de ação sensível na plataforma.
 *
 * REGRA DE OURO: Este modelo NUNCA deve ser atualizado ou apagado.
 * Qualquer tentativa de update() ou delete() deve ser bloqueada.
 */
class AuditLog extends Model
{
    use HasFactory;

    // Sem updated_at — logs são imutáveis
    public $timestamps = false;

    protected $fillable = [
        'user_id', 'user_email', 'user_role',
        'action', 'category', 'severity',
        'target_type', 'target_id', 'target_reference',
        'description', 'metadata',
        'ip_address', 'user_agent', 'session_id',
        'request_method', 'request_url',
        'created_at',
    ];

    protected $casts = [
        'metadata'   => 'array',
        'created_at' => 'datetime',
    ];

    // ================================================================
    // IMUTABILIDADE — Bloquear update e delete no nível do modelo
    // ================================================================
    protected static function booted(): void
    {
        static::updating(function () {
            throw new \RuntimeException('Audit logs are immutable and cannot be updated.');
        });
        static::deleting(function () {
            throw new \RuntimeException('Audit logs are immutable and cannot be deleted.');
        });
    }

    // ================================================================
    // Relações
    // ================================================================
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ================================================================
    // Scopes — Queries pré-construídas para a UI de auditoria
    // ================================================================
    public function scopeForUser(Builder $q, int $userId): Builder
    {
        return $q->where('user_id', $userId);
    }

    public function scopeCategory(Builder $q, string $category): Builder
    {
        return $q->where('category', $category);
    }

    public function scopeAction(Builder $q, string $action): Builder
    {
        return $q->where('action', $action);
    }

    public function scopeCritical(Builder $q): Builder
    {
        return $q->where('severity', 'critical');
    }

    public function scopeRecent(Builder $q, int $days = 30): Builder
    {
        return $q->where('created_at', '>=', now()->subDays($days));
    }

    public function scopeFromIp(Builder $q, string $ip): Builder
    {
        return $q->where('ip_address', $ip);
    }

    // ================================================================
    // Helpers de apresentação
    // ================================================================
    public function getSeverityColorAttribute(): string
    {
        return match($this->severity) {
            'critical' => 'red',
            'warning'  => 'amber',
            default    => 'slate',
        };
    }

    public function getCategoryIconAttribute(): string
    {
        return match($this->category) {
            'auth'        => '🔐',
            'kyc'         => '🪪',
            'transaction' => '💸',
            'admin'       => '⚙️',
            'profile'     => '👤',
            'beneficiary' => '🏦',
            default       => '📋',
        };
    }
}
