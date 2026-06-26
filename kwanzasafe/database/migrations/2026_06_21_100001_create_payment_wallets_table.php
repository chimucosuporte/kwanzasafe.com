<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Carteiras de recepção do beneficiário (Bybit / Binance / RedotPay).
 *
 * Complementa o Cofre IBAN (beneficiaries): além de contas bancárias, o cliente
 * pode receber os Kwanzas numa carteira. Anti-fraude espelha os beneficiários
 * (titular = nome do KYC).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('provider', 20);            // bybit | binance | redotpay
            $table->string('identifier', 191);         // UID / Pay ID / email / endereço
            $table->string('holder_name', 191);        // titular (= full_name do KYC)
            $table->string('network', 50)->nullable(); // ex.: TRC20, BEP20
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'provider']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_wallets');
    }
};
