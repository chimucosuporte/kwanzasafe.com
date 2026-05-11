<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabela para guardar códigos OTP enviados
        Schema::create('otp_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Tipo: 'email' ou 'phone' (no futuro)
            $table->string('type', 20);

            // Destino do código (email ou número de telefone)
            $table->string('destination', 255);

            // Código de 6 dígitos (hashed para segurança extra)
            $table->string('code_hash', 255);

            // Quantas tentativas de validar este código
            $table->unsignedTinyInteger('attempts')->default(0);

            // Foi usado com sucesso?
            $table->timestamp('verified_at')->nullable();

            // Quando expira (15 min após criação)
            $table->timestamp('expires_at');

            // IP de quem solicitou (audit)
            $table->string('ip_address', 45)->nullable();

            $table->timestamps();

            // Indices para queries rápidas
            $table->index(['user_id', 'type']);
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('otp_codes');
    }
};
