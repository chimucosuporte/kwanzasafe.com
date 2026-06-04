<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class ChatMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'sender_id',
        'message_text',
        'message_type',    // 'text' | 'image' | 'document'
        'channel',         // 'client' | 'internal' | 'recourse'
        'file_path',
        'is_read',
    ];

    /**
     * Canais que um determinado utilizador pode ver numa transação.
     *  - super-admin: tudo
     *  - suporte: cliente + notas internas (nunca recurso)
     *  - cliente: a sua conversa + o seu recurso
     */
    public static function visibleChannelsFor(User $viewer): array
    {
        if ($viewer->isSuperAdmin()) {
            return ['client', 'internal', 'recourse'];
        }
        if ($viewer->isStaff()) {
            return ['client', 'internal'];
        }
        return ['client', 'recourse'];
    }

    protected $casts = [
        'is_read'    => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ================================================================
    // Relações
    // ================================================================
    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    // ================================================================
    // Scopes
    // ================================================================
    public function scopeUnread(Builder $q): Builder
    {
        return $q->where('is_read', false);
    }

    public function scopeForTransaction(Builder $q, int $transactionId): Builder
    {
        return $q->where('transaction_id', $transactionId);
    }

    public function scopeFromAdmin(Builder $q): Builder
    {
        return $q->whereHas('sender', fn($s) => $s->where('is_admin', true));
    }

    public function scopeFromClient(Builder $q): Builder
    {
        return $q->whereHas('sender', fn($s) => $s->where('is_admin', false));
    }

    // ================================================================
    // Helpers
    // ================================================================

    /**
     * Verifica se esta mensagem foi enviada pelo admin.
     */
    public function getIsFromAdminAttribute(): bool
    {
        return $this->sender?->is_admin ?? false;
    }

    /**
     * Retorna o URL do ficheiro anexado (se houver).
     */
    public function getFileUrlAttribute(): ?string
    {
        return $this->file_path ? ks_file($this->file_path) : null;
    }

    /**
     * Verifica se o ficheiro anexado é imagem.
     */
    public function getIsImageAttribute(): bool
    {
        if (!$this->file_path) return false;
        $ext = strtolower(pathinfo($this->file_path, PATHINFO_EXTENSION));
        return in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
    }

    /**
     * Verifica se o ficheiro anexado é PDF.
     */
    public function getIsPdfAttribute(): bool
    {
        if (!$this->file_path) return false;
        return strtolower(pathinfo($this->file_path, PATHINFO_EXTENSION)) === 'pdf';
    }
}
