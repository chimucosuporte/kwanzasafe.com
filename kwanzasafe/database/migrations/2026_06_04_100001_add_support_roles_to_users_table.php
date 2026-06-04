<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Fase 0 — Fundação de papéis multi-equipa.
 *
 * Adiciona:
 *  - is_super_admin: distingue o "admin máximo" do suporte. super⇒admin.
 *  - is_active: permite ao super-admin desactivar contas de suporte sem as eliminar.
 *
 * Backfill: designa o super-admin inicial e garante is_active=true para todos.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'is_super_admin')) {
                $table->boolean('is_super_admin')->default(false)->after('is_admin');
            }
            if (!Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('is_super_admin');
            }
        });

        // Todos activos por defeito
        DB::table('users')->update(['is_active' => true]);

        // Designar o super-admin inicial (configurável via env, com fallback)
        $superEmail = env('KS_SUPER_ADMIN_EMAIL', 'chimucogeral@gmail.com');

        DB::table('users')->where('email', $superEmail)->update([
            'is_super_admin' => true,
            'is_admin'       => true,
            'role'           => 'super_admin',
        ]);

        // Espelhar role para os restantes admins já existentes
        DB::table('users')
            ->where('is_admin', true)
            ->where('is_super_admin', false)
            ->update(['role' => 'admin']);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'is_active')) {
                $table->dropColumn('is_active');
            }
            if (Schema::hasColumn('users', 'is_super_admin')) {
                $table->dropColumn('is_super_admin');
            }
        });
    }
};
