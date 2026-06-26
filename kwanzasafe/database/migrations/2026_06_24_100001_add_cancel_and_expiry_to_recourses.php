<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Recursos: estado 'cancelled' (o cliente pode cancelar) + prazo de validade.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE `recourses` MODIFY `status` ENUM('open','in_review','resolved','rejected','cancelled') NOT NULL DEFAULT 'open'");

        Schema::table('recourses', function (Blueprint $table) {
            $table->timestamp('expires_at')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('recourses', function (Blueprint $table) {
            $table->dropColumn('expires_at');
        });

        DB::statement("ALTER TABLE `recourses` MODIFY `status` ENUM('open','in_review','resolved','rejected') NOT NULL DEFAULT 'open'");
    }
};
