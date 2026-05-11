<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone_number')->nullable();
            $table->timestamp('phone_verified_at')->nullable();
            $table->string('identity_document_path')->nullable(); // Caminho do BI/Passaporte
            $table->timestamp('identity_verified_at')->nullable();
            $table->string('profile_photo_path')->nullable();
            $table->boolean('is_fully_verified')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
