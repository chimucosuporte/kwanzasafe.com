<?php

namespace App\Services;

use App\Models\LedgerEntry;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LedgerService
{
    /**
     * Regista a conclusão de uma transação:
     * - debita o amount_sent do utilizador (saída de divisas)
     * - regista a fee como entrada para a plataforma
     */
    public static function recordTransactionCompleted(Transaction $transaction, ?User $actor = null): void
    {
        try {
            DB::transaction(function () use ($transaction, $actor) {
                $user = $transaction->user;

                // Debit: saída de divisas do utilizador
                if ((float) $transaction->amount_sent > 0) {
                    $before = (float) $user->balance;
                    $after  = $before; // balance em AOA; o envio é em moeda estrangeira — apenas registo informativo
                    LedgerEntry::create([
                        'user_id'        => $user->id,
                        'transaction_id' => $transaction->id,
                        'type'           => 'debit',
                        'amount'         => $transaction->amount_sent,
                        'balance_before' => $before,
                        'balance_after'  => $after,
                        'currency'       => $transaction->currency_from,
                        'description'    => "Transação #{$transaction->reference_id} concluída — {$transaction->amount_sent} {$transaction->currency_from} → {$transaction->amount_received} AOA",
                        'created_by'     => $actor?->id,
                    ]);
                }

                // Fee: comissão da plataforma (registada no user admin/plataforma como crédito)
                if ((float) $transaction->fee_amount > 0) {
                    LedgerEntry::create([
                        'user_id'        => $user->id,
                        'transaction_id' => $transaction->id,
                        'type'           => 'fee',
                        'amount'         => $transaction->fee_amount,
                        'balance_before' => 0,
                        'balance_after'  => 0,
                        'currency'       => $transaction->currency_from,
                        'description'    => "Comissão da transação #{$transaction->reference_id}",
                        'created_by'     => $actor?->id,
                    ]);
                }
            });
        } catch (\Exception $e) {
            // Ledger nunca quebra o fluxo principal
            Log::error('LedgerService::recordTransactionCompleted failed', [
                'transaction_id' => $transaction->id,
                'error'          => $e->getMessage(),
            ]);
        }
    }

    /**
     * Ajuste manual de balance pelo admin.
     */
    public static function adminAdjustment(User $user, float $amount, string $description, ?User $actor = null): void
    {
        try {
            DB::transaction(function () use ($user, $amount, $description, $actor) {
                $before = (float) $user->balance;
                $after  = round($before + $amount, 2);

                LedgerEntry::create([
                    'user_id'        => $user->id,
                    'transaction_id' => null,
                    'type'           => 'adjustment',
                    'amount'         => abs($amount),
                    'balance_before' => $before,
                    'balance_after'  => $after,
                    'currency'       => 'AOA',
                    'description'    => $description,
                    'created_by'     => $actor?->id,
                ]);

                $user->balance = $after;
                $user->saveQuietly();
            });
        } catch (\Exception $e) {
            Log::error('LedgerService::adminAdjustment failed', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);
        }
    }
}
