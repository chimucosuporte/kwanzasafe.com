<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use App\Models\Recourse;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Arbitragem de recursos — exclusivo do super-admin.
 */
class RecourseAdminController extends Controller
{
    public function index()
    {
        $active = Recourse::with('transaction.user', 'openedBy')
            ->active()
            ->orderBy('created_at')
            ->get();

        $resolved = Recourse::with('transaction.user', 'superAdmin')
            ->whereIn('status', ['resolved', 'rejected'])
            ->orderByDesc('resolved_at')
            ->limit(20)
            ->get();

        // Mensagens do canal recourse, agrupadas por transação, para o painel.
        $threads = ChatMessage::with('sender')
            ->where('channel', 'recourse')
            ->whereIn('transaction_id', $active->pluck('transaction_id'))
            ->orderBy('created_at')
            ->get()
            ->groupBy('transaction_id');

        return view('admin.recourses.index', compact('active', 'resolved', 'threads'));
    }

    public function reply(Request $request, $id)
    {
        $request->validate(['message_text' => ['required', 'string', 'max:2000']]);

        $recourse = Recourse::findOrFail($id);

        if ($recourse->status === 'open') {
            $recourse->update(['status' => 'in_review', 'assigned_super_admin' => Auth::id()]);
        }

        ChatMessage::create([
            'transaction_id' => $recourse->transaction_id,
            'sender_id'      => Auth::id(),
            'message_text'   => $request->message_text,
            'message_type'   => 'text',
            'channel'        => 'recourse',
            'is_read'        => false,
        ]);

        return back()->with('success', 'Resposta enviada ao cliente.');
    }

    public function resolve(Request $request, $id)
    {
        return $this->close($request, $id, 'resolved', '✅ Recurso resolvido');
    }

    public function reject(Request $request, $id)
    {
        return $this->close($request, $id, 'rejected', '❌ Recurso indeferido');
    }

    private function close(Request $request, $id, string $status, string $prefix)
    {
        $request->validate(['resolution' => ['required', 'string', 'max:2000']]);

        $recourse = Recourse::findOrFail($id);

        $recourse->update([
            'status'               => $status,
            'resolution'           => $request->resolution,
            'resolved_at'          => now(),
            'assigned_super_admin' => Auth::id(),
        ]);

        // Mensagem de sistema no canal recourse com a decisão.
        ChatMessage::create([
            'transaction_id' => $recourse->transaction_id,
            'sender_id'      => null,
            'message_text'   => "{$prefix}: {$request->resolution}",
            'message_type'   => 'text',
            'channel'        => 'recourse',
            'is_read'        => false,
        ]);

        AuditLogger::admin("recourse_{$status}",
            "Recurso #{$recourse->id} marcado como {$status}",
            $recourse->transaction,
            ['recourse_id' => $recourse->id, 'by' => Auth::id()]
        );

        $label = $status === 'resolved' ? 'resolvido' : 'indeferido';

        return back()->with('success', "Recurso {$label} com sucesso.");
    }
}
