<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use App\Models\Recourse;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Arbitragem de recursos via app admin — super-admin.
 * Espelha o Admin\RecourseAdminController web. A conversa usa o canal 'recourse'.
 */
class RecourseController extends Controller
{
    public function index(): JsonResponse
    {
        $active = Recourse::with('transaction.user', 'openedBy')->active()->orderBy('created_at')->get()
            ->map(fn ($r) => $this->item($r));

        $resolved = Recourse::with('transaction.user')->whereIn('status', ['resolved', 'rejected'])
            ->orderByDesc('resolved_at')->limit(20)->get()
            ->map(fn ($r) => $this->item($r));

        return response()->json(['active' => $active->values(), 'resolved' => $resolved->values()]);
    }

    public function show(Request $request, $id): JsonResponse
    {
        $recourse = Recourse::with('transaction.user')->findOrFail($id);
        $adminId = $request->user()->id;

        $messages = ChatMessage::where('transaction_id', $recourse->transaction_id)
            ->where('channel', 'recourse')
            ->orderBy('created_at')
            ->get()
            ->map(fn ($m) => [
                'id'         => $m->id,
                'text'       => $m->message_text,
                'is_mine'    => ! is_null($m->sender_id) && $m->sender_id === $adminId,
                'is_system'  => is_null($m->sender_id),
                'created_at' => $m->created_at?->toIso8601String(),
            ]);

        return response()->json(['data' => array_merge($this->item($recourse), ['messages' => $messages])]);
    }

    public function reply(Request $request, $id): JsonResponse
    {
        $request->validate(['message_text' => ['required', 'string', 'max:2000']]);
        $recourse = Recourse::findOrFail($id);

        if ($recourse->status === 'open') {
            $recourse->update(['status' => 'in_review', 'assigned_super_admin' => $request->user()->id]);
        }

        ChatMessage::create([
            'transaction_id' => $recourse->transaction_id,
            'sender_id'      => $request->user()->id,
            'message_text'   => $request->message_text,
            'message_type'   => 'text',
            'channel'        => 'recourse',
            'is_read'        => false,
        ]);

        return response()->json(['message' => 'Resposta enviada ao cliente.', 'data' => $this->item($recourse->fresh(['transaction.user']))]);
    }

    public function resolve(Request $request, $id): JsonResponse
    {
        return $this->close($request, $id, 'resolved', '✅ Recurso resolvido');
    }

    public function reject(Request $request, $id): JsonResponse
    {
        return $this->close($request, $id, 'rejected', '❌ Recurso indeferido');
    }

    private function close(Request $request, $id, string $status, string $prefix): JsonResponse
    {
        $request->validate(['resolution' => ['required', 'string', 'max:2000']]);
        $recourse = Recourse::findOrFail($id);

        $recourse->update([
            'status'               => $status,
            'resolution'           => $request->resolution,
            'resolved_at'          => now(),
            'assigned_super_admin' => $request->user()->id,
        ]);

        ChatMessage::create([
            'transaction_id' => $recourse->transaction_id,
            'sender_id'      => null,
            'message_text'   => "{$prefix}: {$request->resolution}",
            'message_type'   => 'text',
            'channel'        => 'recourse',
            'is_read'        => false,
        ]);

        AuditLogger::admin("recourse_{$status}", "Recurso #{$recourse->id} marcado como {$status} (app)", $recourse->transaction, ['recourse_id' => $recourse->id, 'by' => $request->user()->id]);

        $label = $status === 'resolved' ? 'resolvido' : 'indeferido';
        return response()->json(['message' => "Recurso {$label}.", 'data' => $this->item($recourse->fresh(['transaction.user']))]);
    }

    private function item(Recourse $r): array
    {
        return [
            'id'            => $r->id,
            'transaction_id' => $r->transaction_id,
            'reference_id'  => $r->transaction->reference_id ?? null,
            'client_name'   => $r->transaction->user->full_name ?? '—',
            'client_email'  => $r->transaction->user->email ?? '—',
            'reason'        => $r->reason,
            'resolution'    => $r->resolution,
            'status'        => $r->status,
            'status_label'  => Recourse::STATUS_LABELS[$r->status] ?? $r->status,
            'created_at'    => $r->created_at?->toIso8601String(),
            'resolved_at'   => $r->resolved_at?->toIso8601String(),
        ];
    }
}
