<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Adicionar novos estados ao ENUM (MySQL requer raw statement para alterar ENUM)
        DB::statement("
            ALTER TABLE transactions
            MODIFY COLUMN status ENUM(
                'pending',
                'negotiating',
                'awaiting_payment',
                'payment_received',
                'processing',
                'aoa_sent',
                'completed',
                'cancelled',
                'expired'
            ) NOT NULL DEFAULT 'pending'
        ");

        Schema::table('transactions', function (Blueprint $table) {
            $table->timestamp('payment_received_at')->nullable()->after('expires_at');
            $table->timestamp('aoa_sent_at')->nullable()->after('payment_received_at');
            $table->timestamp('client_confirmed_at')->nullable()->after('aoa_sent_at');
            $table->text('admin_notes')->nullable()->after('client_confirmed_at');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['payment_received_at', 'aoa_sent_at', 'client_confirmed_at', 'admin_notes']);
        });

        DB::statement("
            ALTER TABLE transactions
            MODIFY COLUMN status ENUM(
                'pending',
                'awaiting_payment',
                'processing',
                'completed',
                'cancelled',
                'expired'
            ) NOT NULL DEFAULT 'pending'
        ");
    }
};
