<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Destino de recepção da transação (PARA ONDE o admin envia os Kwanzas).
 *
 * Guardado como SNAPSHOT (não FK): mantém os dados exactos mesmo que o
 * beneficiário/carteira seja removido depois, e dá ao admin a informação
 * directa de para onde enviar. O titular já foi validado (= nome do KYC).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('destination_type', 20)->nullable()->after('amount_received');   // bank|bybit|binance|redotpay
            $table->string('destination_label', 191)->nullable()->after('destination_type'); // banco ou nome do provedor
            $table->string('destination_identifier', 191)->nullable()->after('destination_label'); // IBAN / UID / email
            $table->string('destination_holder', 191)->nullable()->after('destination_identifier'); // titular (= KYC)
            $table->string('destination_network', 50)->nullable()->after('destination_holder'); // rede (cripto)
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn([
                'destination_type', 'destination_label', 'destination_identifier',
                'destination_holder', 'destination_network',
            ]);
        });
    }
};
