<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Mensagem direta entre membros da equipa (canal de staff geral).
 */
class StaffMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_id',
        'recipient_id',
        'body',
        'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function recipient()
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    /** Mensagens trocadas entre dois utilizadores (ambos os sentidos). */
    public function scopeBetween(Builder $q, int $a, int $b): Builder
    {
        return $q->where(function ($w) use ($a, $b) {
            $w->where(fn ($x) => $x->where('sender_id', $a)->where('recipient_id', $b))
              ->orWhere(fn ($x) => $x->where('sender_id', $b)->where('recipient_id', $a));
        });
    }

    /**
     * Regra de quem pode escrever a quem:
     *  - suporte → apenas super-admins (reporta para cima)
     *  - super-admin → qualquer membro do staff (excepto a si próprio)
     * O destinatário tem de ser staff activo.
     */
    public static function canMessage(User $sender, User $recipient): bool
    {
        if ($sender->id === $recipient->id) {
            return false;
        }
        if (! $recipient->isStaff() || ! $recipient->is_active) {
            return false;
        }
        if ($sender->isSuperAdmin()) {
            return true; // super-admin fala com qualquer staff
        }
        if ($sender->isSupport()) {
            return $recipient->isSuperAdmin(); // suporte só fala com super-admins
        }
        return false; // clientes não têm acesso
    }
}
