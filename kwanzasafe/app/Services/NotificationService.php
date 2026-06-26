<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserNotification;

/**
 * Cria entradas no feed de notificações do cliente. À semelhança do
 * AuditLogger, NUNCA lança exceção (uma falha aqui não quebra o fluxo).
 */
class NotificationService
{
    /**
     * Regista uma notificação no feed do utilizador. Opcionalmente envia push.
     */
    public static function notify(
        User $user,
        string $type,
        string $title,
        string $body,
        array $data = [],
        bool $alsoPush = false,
    ): void {
        try {
            UserNotification::create([
                'user_id' => $user->id,
                'type'    => $type,
                'title'   => $title,
                'body'    => $body,
                'data'    => $data ?: null,
                'is_read' => false,
            ]);

            if ($alsoPush) {
                PushService::sendToUser($user, $title, $body, $data);
            }
        } catch (\Throwable) {
            // Nunca quebra a aplicação.
        }
    }
}
