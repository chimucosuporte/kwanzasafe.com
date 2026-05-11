<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Audit Trail — Sistema de Logs Indeléveis para Conformidade AML
 *
 * Esta tabela regista TODAS as ações sensíveis da plataforma:
 * - Autenticação (logins, logouts, tentativas falhadas)
 * - Operações KYC (upload, aprovação, rejeição)
 * - Transações financeiras (criação, upload comprovativo, aprovação)
 * - Ações administrativas (alteração taxas, suspensão clientes)
 * - Alterações de dados críticos (IBAN, dados pessoais)
 *
 * Características forenses:
 * - Apenas INSERT (sem UPDATE, sem DELETE no código da aplicação)
 * - Captura IP, User-Agent, User, timestamp preciso
 * - Payload JSON com antes/depois do estado
 * - Indexável para auditorias do BNA / UIF
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();

            // Quem fez a ação (nullable para ações anónimas tipo tentativas de login)
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('user_email', 191)->nullable();        // snapshot imutável — mesmo se o user for apagado
            $table->string('user_role', 20)->nullable();          // 'client' | 'admin'

            // O QUE aconteceu
            $table->string('action', 80)->index();                // ex: 'kyc.approved', 'transaction.created', 'auth.login'
            $table->string('category', 40)->index();              // 'auth' | 'kyc' | 'transaction' | 'admin' | 'profile' | 'beneficiary'
            $table->string('severity', 20)->default('info')->index(); // 'info' | 'warning' | 'critical'

            // SOBRE o quê (modelo alvo polimórfico)
            $table->string('target_type', 80)->nullable()->index(); // 'App\Models\Transaction' etc.
            $table->unsignedBigInteger('target_id')->nullable()->index();
            $table->string('target_reference', 100)->nullable();    // ex: 'KZTREHI', BI number mascarado

            // Descrição legível para humanos (mostrada na UI do admin)
            $table->string('description', 255);

            // Payload forense (estado antes/depois em JSON)
            $table->json('metadata')->nullable();

            // Contexto técnico (essencial para AML)
            $table->string('ip_address', 45)->nullable()->index();  // IPv4 ou IPv6
            $table->string('user_agent', 500)->nullable();
            $table->string('session_id', 100)->nullable();
            $table->string('request_method', 10)->nullable();       // GET | POST | PUT | DELETE
            $table->string('request_url', 500)->nullable();

            // Timestamp imutável (sem updated_at — logs nunca se alteram)
            $table->timestamp('created_at')->useCurrent();

            // Índices compostos para queries de auditoria frequentes
            $table->index(['user_id', 'created_at']);
            $table->index(['category', 'created_at']);
            $table->index(['action', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
