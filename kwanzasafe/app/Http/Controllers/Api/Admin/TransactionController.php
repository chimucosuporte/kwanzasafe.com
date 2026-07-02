<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use App\Models\Transaction;
use App\Services\AuditLogger;
use App\Services\PushService;
use App\Services\TransactionFlow;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Gestão de transações via app admin — espelha o Admin\TransactionAdminController web.
 * Reutiliza TransactionFlow para as transições de estado.
 */
class TransactionController extends Controller
{
    private const STATUS_LABELS = [
        'pending'          => 'Pendente',
        'negotiating'      => 'Em negociação',
        'awaiting_payment' => 'A aguardar pagamento',
        'payment_received' => 'Pagamento recebido',
        'processing'       => 'Em processamento',
        'aoa_sent'         => 'Kwanzas enviados',
        'completed'        => 'Concluída',
        'cancelled'        => 'Cancelada',
        'expired'          => 'Expirada',
    ];

    /** Lista de tíquetes com filtros (status, pesquisa, atribuição). */
    public function index(Request $request): JsonResponse
    {
        $user     = $request->user();
        $status   = $request->query('status', 'pending');
        $search   = $request->query('q', '');
        $assigned = $request->query('assigned', $user->isSupport() ? 'mine' : 'all');

        $query = Transaction::with('user', 'admin')->orderByDesc('created_at');

        if ($assigned === 'mine') {
            $query->where('assigned_admin', $user->id);
        } elseif ($assigned === 'unassigned') {
            $query->whereNull('assigned_admin');
        }

        if ($status === 'pending') {
            $query->whereIn('status', ['pending', 'negotiating', 'awaiting_payment', 'payment_received', 'processing', 'aoa_sent']);
        } elseif ($status === 'completed') {
            $query->where('status', 'completed');
        } elseif ($status === 'cancelled') {
            $query->whereIn('status', ['cancelled', 'expired']);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('reference_id', 'like', "%{$search}%")
                  ->orWhereHas('user', fn ($qu) => $qu->where('email', 'like', "%{$search}%")->orWhere('full_name', 'like', "%{$search}%"));
            });
        }

        $query->withCount(['chatMessages as unread_count' => fn ($q) =>
            $q->where('is_read', false)->whereNotNull('sender_id')->whereColumn('sender_id', 'transactions.user_id')
        ]);

        $page = $query->paginate(20);

        return response()->json([
            'data' => collect($page->items())->map(fn ($t) => $this->listItem($t)),
            'meta' => [
                'current_page' => $page->currentPage(),
                'last_page'    => $page->lastPage(),
                'total'        => $page->total(),
            ],
        ]);
    }

    /** Detalhe de uma transação. */
    public function show($id): JsonResponse
    {
        $tx = Transaction::with('user', 'admin')->findOrFail($id);
        return response()->json(['data' => $this->detail($tx)]);
    }

    public function requestPayment($id): JsonResponse   { return $this->doTransition($id, 'awaiting_payment'); }
    public function paymentReceived($id): JsonResponse  { return $this->doTransition($id, 'payment_received'); }
    public function aoaSent($id): JsonResponse          { return $this->doTransition($id, 'aoa_sent'); }
    public function cancel($id): JsonResponse           { return $this->doTransition($id, 'cancelled'); }

    /** Assume o tíquete (atribui a si próprio). */
    public function assign(Request $request, $id): JsonResponse
    {
        $tx = Transaction::findOrFail($id);

        if (in_array($tx->status, ['completed', 'cancelled', 'expired'])) {
            return response()->json(['message' => 'Não é possível assumir uma transação neste estado.'], 422);
        }

        $tx->update(['assigned_admin' => $request->user()->id]);
        AuditLogger::transaction('agent_assigned', "Agente assumiu a transação #{$tx->reference_id} (app)", $tx, ['admin_id' => $request->user()->id]);

        return response()->json(['data' => $this->detail($tx->fresh(['user', 'admin']))]);
    }

    /** Aprova/conclui a transação (força completed) — espelha o web. */
    public function approve(Request $request, $id): JsonResponse
    {
        $admin = $request->user();

        $result = DB::transaction(function () use ($id, $admin) {
            $tx = Transaction::lockForUpdate()->findOrFail($id);
            if ($tx->status === 'completed') {
                return ['already' => true, 'tx' => $tx];
            }
            $old = $tx->status;
            $tx->update(['status' => 'completed', 'client_confirmed_at' => now()]);
            TransactionFlow::systemMessage($tx, '✅ Transação aprovada pelo administrador. Os Kwanzas foram libertados.');
            AuditLogger::transaction('approved', "Transação #{$tx->reference_id} aprovada ({$old} → completed) (app)", $tx, ['old_status' => $old, 'admin_id' => $admin->id]);
            return ['already' => false, 'tx' => $tx];
        });

        if ($result['already']) {
            return response()->json(['message' => 'Transação já tinha sido aprovada.'], 422);
        }

        $tx = Transaction::with('user', 'admin')->find($id);
        if ($tx->user) {
            PushService::sendToUser($tx->user, 'Transação ' . $tx->reference_id, '🎉 Transação concluída com sucesso! Obrigado por usar a KwanzaSafe.', ['reference_id' => $tx->reference_id, 'status' => 'completed']);
        }

        return response()->json(['data' => $this->detail($tx)]);
    }

    /** Executa uma transição via TransactionFlow. */
    private function doTransition($id, string $to): JsonResponse
    {
        $tx = Transaction::findOrFail($id);
        $ok = TransactionFlow::transition($tx, $to, request()->user());

        if (! $ok) {
            return response()->json(['message' => "Transição inválida a partir do estado actual ({$tx->status})."], 422);
        }

        return response()->json(['data' => $this->detail($tx->fresh(['user', 'admin']))]);
    }

    /** Mensagens do chat (polling admin) — canais visíveis ao staff. */
    public function messages(Request $request, $id): JsonResponse
    {
        $tx      = Transaction::findOrFail($id);
        $afterId = max(0, (int) $request->query('after', 0));
        $adminId = $request->user()->id;
        $visible = ChatMessage::visibleChannelsFor($request->user());

        $messages = ChatMessage::where('transaction_id', $tx->id)
            ->whereIn('channel', $visible)
            ->where('id', '>', $afterId)
            ->orderBy('created_at', 'asc')
            ->get();

        if ($messages->isNotEmpty()) {
            ChatMessage::where('transaction_id', $tx->id)
                ->whereIn('channel', $visible)
                ->where('id', '>', $afterId)
                ->whereNotNull('sender_id')
                ->where('sender_id', '!=', $adminId)
                ->where('is_read', false)
                ->update(['is_read' => true]);
        }

        return response()->json([
            'data' => $messages->map(fn ($m) => [
                'id'        => $m->id,
                'text'      => $m->message_text,
                'type'      => $m->message_type,
                'channel'   => $m->channel,
                'is_mine'   => ! is_null($m->sender_id) && $m->sender_id === $adminId,
                'is_system' => is_null($m->sender_id),
                'file_url'  => $m->file_path ? url('/api/v1/file/' . $m->file_path) : null,
                'is_image'  => $m->file_path ? ks_is_image($m->file_path) : false,
                'created_at' => $m->created_at?->toIso8601String(),
            ]),
        ]);
    }

    /** Envia mensagem no chat (canal client ou internal) — texto e/ou anexo. */
    public function sendMessage(Request $request, $id): JsonResponse
    {
        $tx = Transaction::findOrFail($id);

        $request->validate([
            'message_text' => ['nullable', 'string', 'max:2000'],
            'channel'      => ['nullable', 'in:client,internal'],
            'attachment'   => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
        ]);

        if (! $request->filled('message_text') && ! $request->hasFile('attachment')) {
            return response()->json(['message' => 'Escreve uma mensagem ou anexa um ficheiro.'], 422);
        }

        $channel = in_array($request->input('channel'), ['client', 'internal'], true) ? $request->input('channel') : 'client';

        $data = [
            'transaction_id' => $tx->id,
            'sender_id'      => $request->user()->id,
            'message_text'   => $request->message_text,
            'message_type'   => 'text',
            'channel'        => $channel,
            'is_read'        => false,
        ];

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $data['file_path']    = $file->store('chat_attachments', 'local');
            $ext                  = strtolower($file->getClientOriginalExtension());
            $data['message_type'] = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']) ? 'image' : 'document';
            if (! $request->filled('message_text')) {
                $data['message_text'] = $data['message_type'] === 'image' ? '📷 Imagem' : '📎 Documento';
            }
        }

        $msg = ChatMessage::create($data);
        AuditLogger::transaction('admin_message', "Admin enviou mensagem na transação #{$tx->reference_id} (app)", $tx, ['channel' => $channel]);

        return response()->json([
            'data' => [
                'id'        => $msg->id,
                'text'      => $msg->message_text,
                'type'      => $msg->message_type,
                'channel'   => $msg->channel,
                'is_mine'   => true,
                'is_system' => false,
                'file_url'  => $msg->file_path ? url('/api/v1/file/' . $msg->file_path) : null,
                'is_image'  => $msg->file_path ? ks_is_image($msg->file_path) : false,
                'created_at' => $msg->created_at?->toIso8601String(),
            ],
        ], 201);
    }

    // ---- serialização ----

    private function listItem(Transaction $t): array
    {
        return [
            'id'              => $t->id,
            'reference_id'    => $t->reference_id,
            'status'          => $t->status,
            'status_label'    => self::STATUS_LABELS[$t->status] ?? $t->status,
            'currency_from'   => $t->currency_from,
            'amount_sent'     => (string) $t->amount_sent,
            'amount_received' => (string) $t->amount_received,
            'client_name'     => $t->user->full_name ?? '—',
            'client_email'    => $t->user->email ?? '—',
            'agent_name'      => $t->admin->full_name ?? null,
            'unread_count'    => (int) ($t->unread_count ?? 0),
            'created_at'      => $t->created_at?->toIso8601String(),
        ];
    }

    private function detail(Transaction $t): array
    {
        $terminal = in_array($t->status, ['completed', 'cancelled', 'expired']);

        return array_merge($this->listItem($t), [
            'rate_applied'        => (string) $t->rate_applied,
            'fee_amount'          => (string) $t->fee_amount,
            'client_phone'        => $t->user->phone_number ?? null,
            'assigned_admin'      => $t->assigned_admin,
            'payment_received_at' => $t->payment_received_at?->toIso8601String(),
            'aoa_sent_at'         => $t->aoa_sent_at?->toIso8601String(),
            'client_confirmed_at' => $t->client_confirmed_at?->toIso8601String(),
            'expires_at'          => $t->expires_at?->toIso8601String(),
            'actions' => [
                'can_request_payment' => in_array($t->status, ['pending', 'negotiating']),
                'can_payment_received' => in_array($t->status, ['awaiting_payment', 'processing']),
                'can_aoa_sent'         => $t->status === 'payment_received',
                'can_approve'          => $t->status === 'aoa_sent',
                'can_cancel'           => ! $terminal,
                'can_assign'           => ! $terminal,
            ],
        ]);
    }
}
