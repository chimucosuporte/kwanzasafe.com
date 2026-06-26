<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Feed de notificações do cliente (na view Notificações da app).
 *
 * Alimentado por eventos reais: novo acesso por IP (auditoria), transações,
 * recursos, etc. Cada notificação guarda detalhes em `data` (JSON).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type', 30);              // security | transaction | recourse | info
            $table->string('title', 191);
            $table->text('body');
            $table->json('data')->nullable();        // ex.: { reference_id, ip, device, location }
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['user_id', 'is_read']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_notifications');
    }
};
