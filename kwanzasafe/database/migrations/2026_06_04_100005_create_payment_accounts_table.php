<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Contas de recepção — dados de pagamento por moeda de origem.
 *
 * Geridos pelo super-admin via formulário. A Sala de Transação mostra a conta
 * activa correspondente à currency_from da transação (EUR→IBAN, BRL→Pix/conta,
 * USDC→carteira+rede).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('currency', 10)->unique();   // EUR | BRL | USDC | USDT ...
            $table->string('holder', 191);               // titular
            $table->string('identifier', 255);           // IBAN / chave Pix / endereço de carteira
            $table->string('network', 100)->nullable();  // BIC/SWIFT ou rede cripto (TRC-20, ERC-20...)
            $table->text('instructions')->nullable();    // notas adicionais ao cliente
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_accounts');
    }
};
