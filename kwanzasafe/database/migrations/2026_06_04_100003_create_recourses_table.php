<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fase 3 — Recursos (escalonamento cliente → super-admin).
 *
 * Um recurso é um caso aberto pelo cliente quando sente injustiça/falha.
 * É arbitrado pelo super-admin (resolver/indeferir). A conversa associada
 * usa chat_messages com channel='recourse'.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recourses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained()->cascadeOnDelete();
            $table->foreignId('opened_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('assigned_super_admin')->nullable()->constrained('users')->nullOnDelete();
            $table->text('reason');
            $table->enum('status', ['open', 'in_review', 'resolved', 'rejected'])->default('open')->index();
            $table->text('resolution')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recourses');
    }
};
