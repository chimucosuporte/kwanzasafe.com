<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Alarga as colunas de código de moeda de VARCHAR(3) para VARCHAR(10)
 * para suportar stablecoins de 4 letras (USDT / USDC) além de EUR / BRL.
 * Usa ALTER cru (não precisa de doctrine/dbal).
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE exchange_rates MODIFY currency_from VARCHAR(10) NOT NULL");
        DB::statement("ALTER TABLE exchange_rates MODIFY currency_to VARCHAR(10) NOT NULL DEFAULT 'AOA'");
        DB::statement("ALTER TABLE transactions MODIFY currency_from VARCHAR(10) NOT NULL");
        DB::statement("ALTER TABLE transactions MODIFY currency_to VARCHAR(10) NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE exchange_rates MODIFY currency_from VARCHAR(3) NOT NULL");
        DB::statement("ALTER TABLE exchange_rates MODIFY currency_to VARCHAR(3) NOT NULL DEFAULT 'AOA'");
        DB::statement("ALTER TABLE transactions MODIFY currency_from VARCHAR(3) NOT NULL");
        DB::statement("ALTER TABLE transactions MODIFY currency_to VARCHAR(3) NOT NULL");
    }
};
