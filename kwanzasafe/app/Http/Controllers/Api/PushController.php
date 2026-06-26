<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PushService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Registo do token de notificações push (Expo) da app mobile.
 */
class PushController extends Controller
{
    /** Guarda/actualiza o token Expo do dispositivo do utilizador. */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'token' => ['required', 'string', 'max:255'],
        ]);

        $user = $request->user();
        $user->expo_push_token = $data['token'];
        $user->save();

        return response()->json(['message' => 'Token registado.']);
    }

    /** Envia uma notificação de teste ao próprio utilizador. */
    public function test(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user->expo_push_token) {
            return response()->json([
                'message' => 'Este dispositivo ainda não registou notificações. Aceita a permissão e tenta de novo.',
            ], 422);
        }

        PushService::sendToUser(
            $user,
            'KwanzaSafe',
            'Notificação de teste — as notificações estão a funcionar! 🎉',
            ['type' => 'test'],
        );

        return response()->json(['message' => 'Notificação de teste enviada.']);
    }

    /** Remove o token (ex.: ao desactivar notificações ou terminar sessão). */
    public function destroy(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->expo_push_token = null;
        $user->save();

        return response()->json(['message' => 'Token removido.']);
    }
}
