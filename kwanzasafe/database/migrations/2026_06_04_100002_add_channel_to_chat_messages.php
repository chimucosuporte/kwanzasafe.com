<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fase 3 — Canais de conversa sobre o tíquete.
 *
 * channel:
 *  - client   : conversa cliente ↔ suporte (visível ao cliente)
 *  - internal : notas internas suporte ↔ super-admin (invisíveis ao cliente)
 *  - recourse : recurso cliente ↔ super-admin (invisível ao agente de suporte)
 *
 * As linhas existentes recebem 'client' (default da coluna).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chat_messages', function (Blueprint $table) {
            if (!Schema::hasColumn('chat_messages', 'channel')) {
                $table->enum('channel', ['client', 'internal', 'recourse'])
                    ->default('client')
                    ->after('message_type')
                    ->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('chat_messages', function (Blueprint $table) {
            if (Schema::hasColumn('chat_messages', 'channel')) {
                $table->dropColumn('channel');
            }
        });
    }
};
