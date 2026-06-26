<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use App\Models\UserNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Feed de notificações do cliente (view Notificações da app).
 */
class NotificationController extends Controller
{
    /** Lista as notificações do utilizador (mais recentes primeiro). */
    public function index(Request $request): JsonResponse
    {
        $items = UserNotification::where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->limit(100)
            ->get();

        return response()->json([
            'data'   => NotificationResource::collection($items),
            'unread' => $items->where('is_read', false)->count(),
        ]);
    }

    /** Número de notificações por ler (para o badge). */
    public function unreadCount(Request $request): JsonResponse
    {
        return response()->json([
            'unread' => UserNotification::where('user_id', $request->user()->id)->where('is_read', false)->count(),
        ]);
    }

    /** Marca uma notificação como lida. */
    public function markRead(Request $request, int $id): JsonResponse
    {
        UserNotification::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->update(['is_read' => true, 'read_at' => now()]);

        return response()->json(['message' => 'Marcada como lida.']);
    }

    /** Marca todas as notificações como lidas. */
    public function markAllRead(Request $request): JsonResponse
    {
        UserNotification::where('user_id', $request->user()->id)
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);

        return response()->json(['message' => 'Todas marcadas como lidas.']);
    }
}
