<?php

namespace App\Http\Resources;

use App\Models\UserNotification;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Notificação do feed do cliente, para a app mobile.
 *
 * @mixin UserNotification
 */
class NotificationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'type'       => $this->type,
            'title'      => $this->title,
            'body'       => $this->body,
            'data'       => $this->data,
            'is_read'    => (bool) $this->is_read,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
