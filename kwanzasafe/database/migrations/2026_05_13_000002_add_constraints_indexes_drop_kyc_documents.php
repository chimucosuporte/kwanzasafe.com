<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // exchange_rates: único par de moedas por linha
        Schema::table('exchange_rates', function (Blueprint $table) {
            $table->unique(['currency_from', 'currency_to'], 'exchange_rates_pair_unique');
        });

        // beneficiaries: um IBAN por utilizador (duplicados bloqueados na BD)
        Schema::table('beneficiaries', function (Blueprint $table) {
            $table->unique(['user_id', 'iban'], 'beneficiaries_user_iban_unique');
        });

        // chat_messages: índices para queries de sala de transação
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->index('transaction_id', 'chat_messages_transaction_id_idx');
            $table->index('sender_id', 'chat_messages_sender_id_idx');
        });

        // kyc_documents: tabela criada mas nunca usada — remover
        Schema::dropIfExists('kyc_documents');
    }

    public function down(): void
    {
        Schema::table('exchange_rates', function (Blueprint $table) {
            $table->dropUnique('exchange_rates_pair_unique');
        });

        Schema::table('beneficiaries', function (Blueprint $table) {
            $table->dropUnique('beneficiaries_user_iban_unique');
        });

        Schema::table('chat_messages', function (Blueprint $table) {
            $table->dropIndex('chat_messages_transaction_id_idx');
            $table->dropIndex('chat_messages_sender_id_idx');
        });
    }
};
