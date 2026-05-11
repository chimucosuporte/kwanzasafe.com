<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Score automático calculado pelo KycBot (0-100)
            $table->unsignedTinyInteger('kyc_score')->nullable()->after('identity_verified_at');

            // Estado do bot: 'auto_approved', 'pending_review', 'auto_rejected', null
            $table->string('kyc_bot_status', 30)->nullable()->after('kyc_score');

            // Data/hora da última análise do bot
            $table->timestamp('kyc_bot_analyzed_at')->nullable()->after('kyc_bot_status');

            // Razões/notas detalhadas em JSON (cada validação e pontos atribuídos)
            $table->json('kyc_bot_notes')->nullable()->after('kyc_bot_analyzed_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'kyc_score',
                'kyc_bot_status',
                'kyc_bot_analyzed_at',
                'kyc_bot_notes',
            ]);
        });
    }
};
