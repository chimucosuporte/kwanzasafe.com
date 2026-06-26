<?php

namespace App\Http\Resources;

use App\Models\ChatMessage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Mensagem de chat para a app mobile.
 *
 * Espelha a forma devolvida por ChatController@poll (web), mas os anexos
 * apontam para a rota de ficheiros autenticada por token (/api/v1/file/...).
 *
 * @mixin ChatMessage
 */
class ChatMessageResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $userId   = $request->user()?->id;
        $filePath = $this->file_path;

        return [
            'id'         => $this->id,
            'text'       => $this->message_text,
            'type'       => $this->message_type,
            'is_mine'    => ! is_null($this->sender_id) && $this->sender_id === $userId,
            'is_system'  => is_null($this->sender_id),
            'is_read'    => (bool) $this->is_read,
            'file_url'   => $filePath ? url('api/v1/file/'.ltrim($filePath, '/')) : null,
            'is_image'   => $filePath ? ks_is_image($filePath) : false,
            'is_pdf'     => $filePath ? ks_is_pdf($filePath) : false,
            'created_at' => $this->created_at?->toIso8601String(),
            'time'       => $this->created_at?->format('d/m H:i'),
        ];
    }
}
