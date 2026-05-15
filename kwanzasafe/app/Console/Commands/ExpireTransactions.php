<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use App\Services\TransactionFlow;
use Illuminate\Console\Command;

class ExpireTransactions extends Command
{
    protected $signature   = 'transactions:expire';
    protected $description = 'Expira transações pendentes após 48 horas de inactividade';

    public function handle(): int
    {
        $stale = Transaction::whereIn('status', ['pending', 'negotiating', 'awaiting_payment'])
            ->where('updated_at', '<', now()->subHours(48))
            ->get();

        $count = 0;
        foreach ($stale as $tx) {
            if (TransactionFlow::transition($tx, 'expired')) {
                $count++;
                $this->line("  Expirada: #{$tx->reference_id}");
            }
        }

        $this->info("Total expiradas: {$count}");
        return self::SUCCESS;
    }
}
