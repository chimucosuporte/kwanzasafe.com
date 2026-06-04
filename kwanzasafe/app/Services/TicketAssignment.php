<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\User;

/**
 * Atribuição automática de tíquetes (round-robin "menos ocupado").
 *
 * Distribui cada novo tíquete ao agente de suporte ACTIVO com menos tíquetes
 * abertos. O desempate estável por id aproxima um round-robin: quando a carga
 * está equilibrada, o próximo tíquete vai para o agente seguinte.
 *
 * Super-admins e contas inactivas/clientes nunca recebem atribuição automática.
 * Se não houver agentes de suporte activos, o tíquete fica sem atribuição
 * (fila do super-admin).
 */
class TicketAssignment
{
    private const OPEN_STATUSES = ['pending', 'negotiating', 'awaiting_payment', 'payment_received', 'processing', 'aoa_sent'];

    /**
     * Atribui o tíquete ao agente de suporte mais disponível.
     * Retorna o agente escolhido, ou null se não houver candidatos.
     */
    public static function assign(Transaction $transaction): ?User
    {
        $agent = self::pickAgent();

        if (! $agent) {
            return null;
        }

        $transaction->update(['assigned_admin' => $agent->id]);

        return $agent;
    }

    /**
     * Escolhe o agente de suporte activo com menos tíquetes abertos.
     */
    public static function pickAgent(): ?User
    {
        return User::query()
            ->where('is_admin', true)
            ->where('is_super_admin', false)
            ->where('is_active', true)
            ->withCount(['assignedTransactions as open_count' => fn ($q) =>
                $q->whereIn('status', self::OPEN_STATUSES)
            ])
            ->orderBy('open_count')
            ->orderBy('id')
            ->first();
    }
}
