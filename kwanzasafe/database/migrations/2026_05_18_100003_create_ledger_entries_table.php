<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ledger_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('transaction_id')->nullable()->constrained()->nullOnDelete();
            // credit = entrada de dinheiro; debit = saída; fee = comissão KwanzaSafe; adjustment = admin manual
            $table->enum('type', ['credit', 'debit', 'fee', 'adjustment']);
            $table->decimal('amount', 15, 2);
            $table->decimal('balance_before', 15, 2);
            $table->decimal('balance_after', 15, 2);
            $table->string('currency', 10)->default('AOA');
            $table->string('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();
            // Imutável: sem updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ledger_entries');
    }
};
