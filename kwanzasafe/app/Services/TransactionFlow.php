<?php

namespace App\Services;

use App\Models\ChatMessage;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Services\LedgerService;

/**
 * TransactionFlow — Gere todas as transições de estado de uma transação.
 *
 * Centraliza: validação de transição, mensagem automática de sistema,
 * actualização de timestamps específicos, e registo no AuditLogger.
 *
 * USO:
 *   TransactionFlow::transition($transaction, 'payment_received', $admin);
 *   TransactionFlow::systemMessage($transaction, 'Texto da mensagem');
 */
class TransactionFlow
{
    // Mapa de transições válidas: estado_actual → [estados_permitidos]
    private const ALLOWED_TRANSITIONS = [
        'pending'          => ['negotiating', 'awaiting_payment', 'cancelled', 'expired'],
        'negotiating'      => ['awaiting_payment', 'cancelled', 'expired'],
        'awaiting_payment' => ['payment_received', 'cancelled', 'expired'],
        'payment_received' => ['aoa_sent', 'cancelled'],
        'processing'       => ['payment_received', 'aoa_sent', 'completed', 'cancelled'],
        'aoa_sent'         => ['completed'],
        'completed'        => [],
        'cancelled'        => [],
        'expired'          => [],
    ];

    // Mensagens automáticas de sistema por novo estado
    private const SYSTEM_MESSAGES = [
        'pending'          => 'A tua transação foi criada. Um agente KwanzaSafe irá contactar-te em breve.',
        'negotiating'      => 'O nosso agente está a analisar a tua transação. Aguarda instruções no chat.',
        'awaiting_payment' => '⏳ Comprovativo recebido. A verificar o teu pagamento — aguarda confirmação.',
        'payment_received' => '✅ Pagamento confirmado! Estamos a processar o envio dos Kwanzas para a tua conta.',
        'aoa_sent'         => '🏦 Os Kwanzas foram enviados para o IBAN indicado. Por favor confirma a recepção.',
        'completed'        => '🎉 Transação concluída com sucesso! Obrigado por usar a KwanzaSafe.',
        'cancelled'        => '❌ Transação cancelada. Contacta o suporte se tiveres alguma dúvida.',
        'expired'          => '⏰ Transação expirada por inactividade. Cria uma nova transação para continuar.',
    ];

    /**
     * Executa uma transição de estado com lock, mensagem automática e audit log.
     * Retorna false se a transição não for permitida.
     */
    public static function transition(Transaction $transaction, string $newStatus, ?User $actor = null): bool
    {
        $currentStatus = $transaction->status;

        if (!self::canTransition($currentStatus, $newStatus)) {
            return false;
        }

        DB::transaction(function () use ($transaction, $newStatus, $currentStatus, $actor) {
            $transaction = Transaction::lockForUpdate()->findOrFail($transaction->id);

            if ($transaction->status !== $currentStatus) {
                return;
            }

            $timestamps = self::timestampsFor($newStatus);
            $transaction->update(array_merge(['status' => $newStatus], $timestamps));

            self::systemMessage($transaction, self::SYSTEM_MESSAGES[$newStatus] ?? '');

            AuditLogger::transaction(
                "status_changed.{$newStatus}",
                "Transação #{$transaction->reference_id}: {$currentStatus} → {$newStatus}",
                $transaction,
                [
                    'old_status' => $currentStatus,
                    'new_status' => $newStatus,
                    'actor_id'   => $actor?->id,
                    'actor_role' => $actor?->is_admin ? 'admin' : 'client',
                ]
            );
        });

        Cache::forget('ks.admin.stats.today');
        Cache::forget('ks.admin.stats.week');
        Cache::forget('ks.admin.stats.all');
        Cache::forget('ks.admin.chart_data');

        // Registar no ledger quando transação é concluída
        if ($newStatus === 'completed') {
            LedgerService::recordTransactionCompleted($transaction->fresh(), $actor);
        }

        try {
            $client = $transaction->fresh()->user;
            $emailableStatuses = ['negotiating','awaiting_payment','payment_received','aoa_sent','completed','cancelled','expired'];
            if ($client && in_array($newStatus, $emailableStatuses, true)) {
                \Illuminate\Support\Facades\Mail::to($client->email)
                    ->send(new \App\Mail\TransactionStatusMail($transaction, $client, $newStatus));
            }
        } catch (\Throwable) {
            // Mail failure must never break a completed transition
        }

        return true;
    }

    /**
     * Cria uma mensagem de sistema (sem remetente) na sala de transação.
     */
    public static function systemMessage(Transaction $transaction, string $text): ChatMessage
    {
        return ChatMessage::create([
            'transaction_id' => $transaction->id,
            'sender_id'      => null,
            'message_type'   => 'text',
            'message_text'   => $text,
            'is_read'        => false,
        ]);
    }

    /**
     * Verifica se uma transição de estado é válida.
     */
    public static function canTransition(string $from, string $to): bool
    {
        return in_array($to, self::ALLOWED_TRANSITIONS[$from] ?? [], true);
    }

    /**
     * Retorna os estados permitidos a partir de um estado dado.
     */
    public static function allowedFrom(string $status): array
    {
        return self::ALLOWED_TRANSITIONS[$status] ?? [];
    }

    /**
     * Campos de timestamp a actualizar conforme o novo estado.
     */
    private static function timestampsFor(string $status): array
    {
        return match ($status) {
            'payment_received' => ['payment_received_at' => now()],
            'aoa_sent'         => ['aoa_sent_at' => now()],
            'completed'        => ['client_confirmed_at' => now()],
            default            => [],
        };
    }
}
