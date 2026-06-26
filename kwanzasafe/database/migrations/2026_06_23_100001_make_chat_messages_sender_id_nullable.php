<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Garante que chat_messages.sender_id é NULLABLE.
 *
 * As mensagens de sistema (TransactionFlow::systemMessage) têm sender_id NULL.
 * Em alguns ambientes a coluna ficou NOT NULL (a migração original foi editada
 * depois de já ter corrido), partindo a criação de transações com:
 * "Column 'sender_id' cannot be null".
 *
 * Usa SQL directo para não depender de doctrine/dbal. A foreign key mantém-se.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE `chat_messages` MODIFY `sender_id` BIGINT UNSIGNED NULL');
    }

    public function down(): void
    {
        // Não revertemos para NOT NULL: quebraria as mensagens de sistema.
    }
};
