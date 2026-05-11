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
            // Dados Pessoais
            $table->date('birth_date')->nullable();
            $table->enum('gender', ['M', 'F', 'Outro'])->nullable();
            
            // Identidade
            $table->string('bi_number')->unique()->nullable();
            $table->date('bi_expiry')->nullable();
            
            // Localização (Angola Context)
            $table->string('province')->nullable();
            $table->string('municipality')->nullable();
            $table->string('address')->nullable();
            
            // Estado de Verificação de Dados (Diferente da verificação de ficheiro)
            $table->boolean('data_verified')->default(false);
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
