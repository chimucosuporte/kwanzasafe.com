<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            
            // Liga a mensagem a uma transação específica
            $table->foreignId('transaction_id')->constrained('transactions')->onDelete('cascade');
            
            // Quem enviou a mensagem (pode ser o cliente ou o admin)
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
            
            $table->text('message_text')->nullable();
            
            // Tipo de mensagem para sabermos se é texto ou um comprovativo (imagem/pdf)
            $table->enum('message_type', ['text', 'image', 'document'])->default('text');
            $table->string('file_path', 500)->nullable();
            
            // Para sabermos se a outra parte já leu
            $table->boolean('is_read')->default(false);
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
    }
};