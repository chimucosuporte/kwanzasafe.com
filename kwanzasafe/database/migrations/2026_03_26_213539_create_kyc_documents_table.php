<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kyc_documents', function (Blueprint $table) {
            $table->id();

            // Chave estrangeira para users
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict')->onUpdate('cascade');

            $table->enum('document_type', [
                'national_id',
                'passport',
                'driving_license',
                'selfie_with_id',
                'proof_of_address'
            ]);

            $table->string('document_number', 50)->nullable();
            $table->string('file_path', 500);
            $table->string('file_hash', 64)->unique();
            $table->string('file_mime', 100);
            $table->unsignedInteger('file_size_bytes');

            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('rejection_reason')->nullable();

            // Chave estrangeira para o admin que reviu
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null')->onUpdate('cascade');

            $table->timestamp('reviewed_at')->nullable();
            $table->date('expires_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kyc_documents');
    }
};