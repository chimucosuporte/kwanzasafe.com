<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Autenticação em 2 passos (TOTP / Google Authenticator) para a app mobile.
 * `two_factor_secret` é guardado encriptado (cast no model); só conta como
 * activo quando `two_factor_confirmed_at` está preenchido.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('two_factor_secret')->nullable()->after('password');
            $table->timestamp('two_factor_confirmed_at')->nullable()->after('two_factor_secret');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['two_factor_secret', 'two_factor_confirmed_at']);
        });
    }
};
