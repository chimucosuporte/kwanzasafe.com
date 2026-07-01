<?php

namespace App\Http\Controllers;

use App\Models\UserNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Central de notificações do cliente (web).
 * Reutiliza o modelo UserNotification alimentado pelo NotificationService
 * (o mesmo feed que a app mobile consome pela API).
 */
class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /** Página com o feed de notificações (mais recentes primeiro). */
    public function index(Request $request)
    {
        $notifications = UserNotification::where('user_id', Auth::id())
            ->orderByDesc('created_at')
            ->limit(100)
            ->get();

        $unread = $notifications->where('is_read', false)->count();

        return view('notifications', compact('notifications', 'unread'));
    }

    /** Marca uma notificação como lida e encaminha para o destino relevante. */
    public function markRead(Request $request, int $id)
    {
        $notification = UserNotification::where('id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if ($notification) {
            if (! $notification->is_read) {
                $notification->update(['is_read' => true, 'read_at' => now()]);
            }

            // Se estiver ligada a uma transação, abre-a diretamente.
            $ref = $notification->data['reference_id'] ?? null;
            if ($notification->type === 'transaction' && $ref) {
                return redirect()->route('transaction.show', $ref);
            }
        }

        return redirect()->route('notifications.index');
    }

    /** Marca todas as notificações como lidas. */
    public function markAllRead(Request $request)
    {
        UserNotification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);

        return redirect()->route('notifications.index')->with('success', 'Todas as notificações foram marcadas como lidas.');
    }
}
