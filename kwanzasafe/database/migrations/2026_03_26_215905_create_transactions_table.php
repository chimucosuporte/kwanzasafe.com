<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('reference_id', 20)->unique(); // Código único da transação
            
            // Relacionamentos
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict');
            $table->foreignId('assigned_admin')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('exchange_rate_id')->nullable()->constrained('exchange_rates')->onDelete('set null');

            // Valores
            $table->string('currency_from', 3);
            $table->string('currency_to', 3);
            $table->decimal('amount_sent', 15, 2);
            $table->decimal('rate_applied', 15, 4); // Snapshot da taxa no momento
            $table->decimal('amount_received', 15, 2);
            $table->decimal('fee_amount', 15, 2)->default(0);

            // Status da Transação
            $table->enum('status', [
                'pending',
                'awaiting_payment',
                'processing',
                'completed',
                'cancelled',
                'expired'
            ])->default('pending');

            $table->timestamp('expires_at')->nullable(); // Para cancelar transações não pagas a tempo
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};