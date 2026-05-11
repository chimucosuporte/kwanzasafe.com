<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exchange_rates', function (Blueprint $table) {
            $table->id();
            
            $table->string('currency_from', 3); // Ex: EUR, BRL
            $table->string('currency_to', 3)->default('AOA');
            
            // Decimal com 4 casas decimais para precisão de câmbio
            $table->decimal('rate', 15, 4); 
            
            $table->boolean('is_active')->default(true);
            
            // Quem atualizou a taxa
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exchange_rates');
    }
};