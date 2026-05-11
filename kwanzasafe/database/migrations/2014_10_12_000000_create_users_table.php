<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('full_name', 120);
            $table->string('email', 191)->unique();
            $table->string('phone', 30)->nullable()->unique();
            $table->string('password'); // O Laravel gere o hash automaticamente

            // Papel do utilizador e Estados
            $table->enum('role', ['client', 'admin', 'super_admin'])->default('client');
            $table->enum('status', ['active', 'suspended', 'banned'])->default('active');
            $table->enum('kyc_status', [
                'not_submitted', 
                'pending_review', 
                'approved', 
                'rejected', 
                'expired'
            ])->default('not_submitted');

            $table->string('country', 60)->nullable();
            $table->string('avatar_path', 255)->nullable();
            
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip', 45)->nullable();
            
            $table->rememberToken();
            $table->timestamps(); // Cria created_at e updated_at
            $table->softDeletes(); // Cria deleted_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
