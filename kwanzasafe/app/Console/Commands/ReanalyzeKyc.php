<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Services\KycBot;

/**
 * ============================================================================
 * KwanzaSafe — Comando para Reanalisar KYC com o Bot
 * ============================================================================
 *
 * Útil depois de instalar o Bot KYC pela primeira vez para analisar
 * todos os utilizadores existentes que ainda não têm score.
 *
 * Uso:
 *   php artisan kyc:reanalyze              (todos os utilizadores)
 *   php artisan kyc:reanalyze --pending    (só pendentes)
 *   php artisan kyc:reanalyze --user=42    (utilizador específico)
 *   php artisan kyc:reanalyze --dry-run    (não grava, só mostra)
 * ============================================================================
 */
class ReanalyzeKyc extends Command
{
    protected $signature = 'kyc:reanalyze
                            {--pending : Apenas utilizadores com KYC pendente}
                            {--user= : ID específico de utilizador}
                            {--dry-run : Apenas mostra resultados, não grava}';

    protected $description = 'Reanalisa KYC de utilizadores com o KycBot';

    public function handle(): int
    {
        $bot = new KycBot();

        // Construir query
        $query = User::query();

        if ($this->option('user')) {
            $query->where('id', $this->option('user'));
        } elseif ($this->option('pending')) {
            $query->whereNotNull('identity_document_path')
                  ->whereNull('identity_verified_at');
        }

        $users = $query->get();

        if ($users->isEmpty()) {
            $this->warn('Nenhum utilizador encontrado.');
            return self::SUCCESS;
        }

        $this->info("🤖 Analisando {$users->count()} utilizador(es)...");
        $this->newLine();

        $stats = [
            'auto_approved'  => 0,
            'pending_review' => 0,
            'auto_rejected'  => 0,
        ];

        $bar = $this->output->createProgressBar($users->count());
        $bar->start();

        foreach ($users as $user) {
            if ($this->option('dry-run')) {
                $result = $bot->analyze($user);
            } else {
                $result = $bot->analyzeAndApply($user);
            }

            $stats[$result['status']]++;

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // Tabela de resumo
        $this->info('📊 RESUMO');
        $this->table(
            ['Estado', 'Quantidade'],
            [
                ['✅ Auto-aprovados',   $stats['auto_approved']],
                ['⏳ Pendente revisão', $stats['pending_review']],
                ['❌ Auto-rejeitados',  $stats['auto_rejected']],
            ]
        );

        if ($this->option('dry-run')) {
            $this->warn('⚠️  DRY-RUN: nenhuma alteração foi gravada na BD.');
        } else {
            $this->info('✓ Análise concluída e gravada.');
        }

        return self::SUCCESS;
    }
}
