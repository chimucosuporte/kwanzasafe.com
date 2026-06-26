<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;

/**
 * Notificações push para a app mobile (via Expo Push API).
 *
 * À semelhança do AuditLogger, NUNCA lança exceção: uma falha de push não pode
 * quebrar o fluxo (ex.: uma transição de transação).
 */
class PushService
{
    private const ENDPOINT = 'https://exp.host/--/api/v2/push/send';

    /** Envia uma notificação ao utilizador, se tiver token Expo registado. */
    public static function sendToUser(User $user, string $title, string $body, array $data = []): void
    {
        try {
            $token = $user->expo_push_token;

            if (! $token || ! str_starts_with($token, 'ExponentPushToken')) {
                return;
            }

            Http::acceptJson()
                ->timeout(5)
                ->post(self::ENDPOINT, [
                    'to'        => $token,
                    'title'     => $title,
                    'body'      => $body,
                    'data'      => $data,
                    'sound'     => 'default',
                    'channelId' => 'default',
                    'priority'  => 'high',
                ]);
        } catch (\Throwable) {
            // Push nunca quebra a aplicação.
        }
    }
}
