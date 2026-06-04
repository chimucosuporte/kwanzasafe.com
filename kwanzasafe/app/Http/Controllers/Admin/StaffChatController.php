<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StaffMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Canal de staff geral — mensagens diretas suporte ↔ super-admin (fora de tíquete).
 */
class StaffChatController extends Controller
{
    public function index(Request $request)
    {
        $me = Auth::user();

        $contacts = $this->contactsFor($me);

        // Contagem de não lidas por contacto
        $unread = StaffMessage::where('recipient_id', $me->id)
            ->where('is_read', false)
            ->selectRaw('sender_id, COUNT(*) as total')
            ->groupBy('sender_id')
            ->pluck('total', 'sender_id');

        // Conversa seleccionada (?with=ID)
        $activeId = (int) $request->query('with', 0);
        $active   = $contacts->firstWhere('id', $activeId);
        $messages = collect();

        if ($active) {
            $messages = StaffMessage::with('sender')
                ->between($me->id, $active->id)
                ->orderBy('created_at')
                ->get();

            // Marca como lidas as recebidas deste contacto
            StaffMessage::where('sender_id', $active->id)
                ->where('recipient_id', $me->id)
                ->where('is_read', false)
                ->update(['is_read' => true]);
        }

        return view('admin.staff-chat.index', compact('contacts', 'unread', 'active', 'messages'));
    }

    public function send(Request $request, $userId)
    {
        $request->validate(['body' => ['required', 'string', 'max:2000']]);

        $me        = Auth::user();
        $recipient = User::findOrFail($userId);

        if (! StaffMessage::canMessage($me, $recipient)) {
            return back()->with('error', 'Não tens permissão para enviar mensagens a este utilizador.');
        }

        StaffMessage::create([
            'sender_id'    => $me->id,
            'recipient_id' => $recipient->id,
            'body'         => $request->body,
            'is_read'      => false,
        ]);

        return redirect()->route('admin.staff_chat.index', ['with' => $recipient->id])
            ->with('success', 'Mensagem enviada.');
    }

    public function poll(Request $request, $userId)
    {
        $me      = Auth::user();
        $afterId = max(0, (int) $request->query('after', 0));

        $messages = StaffMessage::between($me->id, (int) $userId)
            ->where('id', '>', $afterId)
            ->orderBy('created_at')
            ->get();

        if ($messages->isNotEmpty()) {
            StaffMessage::where('sender_id', $userId)
                ->where('recipient_id', $me->id)
                ->where('id', '>', $afterId)
                ->where('is_read', false)
                ->update(['is_read' => true]);
        }

        return response()->json([
            'messages' => $messages->map(fn ($m) => [
                'id'      => $m->id,
                'body'    => $m->body,
                'is_mine' => $m->sender_id === $me->id,
                'time'    => $m->created_at->format('d/m H:i'),
            ]),
        ]);
    }

    /** Lista de contactos com quem o utilizador pode comunicar. */
    private function contactsFor(User $me)
    {
        $query = User::query()->where('is_active', true)->where('id', '!=', $me->id);

        if ($me->isSuperAdmin()) {
            $query->where('is_admin', true);             // qualquer membro do staff
        } else {
            $query->where('is_super_admin', true);       // suporte só vê super-admins
        }

        return $query->orderBy('full_name')->get();
    }
}
