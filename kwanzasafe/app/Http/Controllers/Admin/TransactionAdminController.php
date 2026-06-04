<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SendChatMessageRequest;
use App\Models\ChatMessage;
use App\Models\Transaction;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\TransactionFlow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TransactionAdminController extends Controller
{
    public function index(Request $request)
    {
        $user   = Auth::user();
        $status = $request->query('status', 'pending');
        $period = $request->query('period', 'all');
        $search = $request->query('q', '');
        // Suporte vê os seus tíquetes por defeito; super-admin vê todos.
        $assigned = $request->query('assigned', $user->isSupport() ? 'mine' : 'all');

        $query = Transaction::with('user', 'admin')
            ->withCount(['chatMessages as unread_count' => fn($q) =>
                $q->where('is_read', false)
                  ->whereNotNull('sender_id')
                  ->whereColumn('sender_id', 'transactions.user_id')
            ])
            ->orderBy('created_at', 'desc');

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

        if ($period === 'today') {
            $query->whereDate('created_at', today());
        } elseif ($period === 'week') {
            $query->where('created_at', '>=', now()->subDays(7));
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('reference_id', 'like', "%{$search}%")
                  ->orWhereHas('user', fn($qu) =>
                      $qu->where('email', 'like', "%{$search}%")
                         ->orWhere('full_name', 'like', "%{$search}%")
                  );
            });
        }

        $transactions = $query->paginate(20)->withQueryString();

        return view('admin.transactions.index', compact('transactions', 'status', 'period', 'search', 'assigned'));
    }

    public function show($id)
    {
        $transaction = Transaction::with('user', 'admin', 'chatMessages.sender')->findOrFail($id);

        $receipt = $transaction->chatMessages
            ->where('message_type', 'document')
            ->sortByDesc('created_at')
            ->first();

        // Agentes de suporte activos — para o super-admin reatribuir o tíquete.
        $agents = User::where('is_admin', true)
            ->where('is_super_admin', false)
            ->where('is_active', true)
            ->orderBy('full_name')
            ->get(['id', 'full_name']);

        return view('admin.transactions.show', compact('transaction', 'receipt', 'agents'));
    }

    /**
     * Reatribuir o tíquete a um agente de suporte específico (super-admin).
     */
    public function reassign(Request $request, $id)
    {
        $request->validate(['agent_id' => ['required', 'integer']]);

        $transaction = Transaction::findOrFail($id);

        $agent = User::where('is_admin', true)
            ->where('is_super_admin', false)
            ->where('is_active', true)
            ->findOrFail($request->agent_id);

        $transaction->update(['assigned_admin' => $agent->id]);

        AuditLogger::transaction('reassigned',
            "Transação #{$transaction->reference_id} reatribuída ao agente {$agent->full_name}",
            $transaction,
            ['agent_id' => $agent->id, 'by' => Auth::id()]
        );

        return back()->with('success', "Tíquete reatribuído a {$agent->full_name}.");
    }

    public function requestPayment($id)
    {
        $transaction = Transaction::findOrFail($id);
        $ref = $transaction->reference_id;

        $ok = TransactionFlow::transition($transaction, 'awaiting_payment', Auth::user());

        if (!$ok) {
            return back()->with('error', "Não é possível solicitar pagamento no estado actual ({$transaction->status}).");
        }

        return redirect()->route('admin.transaction.show', $id)
            ->with('success', "Transação #{$ref} — cliente notificado para efectuar o pagamento.");
    }

    public function markPaymentReceived($id)
    {
        $transaction = Transaction::findOrFail($id);
        $ref = $transaction->reference_id;

        $ok = TransactionFlow::transition($transaction, 'payment_received', Auth::user());

        if (!$ok) {
            return back()->with('error', "Não é possível confirmar pagamento no estado actual ({$transaction->status}).");
        }

        return redirect()->route('admin.transaction.show', $id)
            ->with('success', "Pagamento confirmado na transação #{$ref}.");
    }

    public function markAoaSent($id)
    {
        $transaction = Transaction::findOrFail($id);
        $ref = $transaction->reference_id;

        $ok = TransactionFlow::transition($transaction, 'aoa_sent', Auth::user());

        if (!$ok) {
            return back()->with('error', "Não é possível marcar AOA enviados no estado actual ({$transaction->status}).");
        }

        return redirect()->route('admin.transaction.show', $id)
            ->with('success', "AOA enviados — transação #{$ref} aguarda confirmação do cliente.");
    }

    public function approve(Request $request, $id)
    {
        $result = DB::transaction(function () use ($id) {
            $transaction = Transaction::lockForUpdate()->findOrFail($id);

            if ($transaction->status === 'completed') {
                return ['alreadyDone' => true, 'ref' => $transaction->reference_id, 'txId' => $id];
            }

            $oldStatus = $transaction->status;
            $transaction->update(['status' => 'completed', 'client_confirmed_at' => now()]);

            TransactionFlow::systemMessage(
                $transaction,
                '✅ Transação aprovada pelo administrador. Os Kwanzas foram libertados.'
            );

            AuditLogger::transaction('approved',
                "Transação #{$transaction->reference_id} aprovada ({$oldStatus} → completed)",
                $transaction,
                ['old_status' => $oldStatus, 'admin_id' => Auth::id()]
            );

            return ['alreadyDone' => false, 'ref' => $transaction->reference_id, 'txId' => $transaction->id];
        });

        if ($result['alreadyDone']) {
            return redirect()->route('admin.transaction.show', $result['txId'])
                ->with('error', 'Transação #' . $result['ref'] . ' já tinha sido aprovada.');
        }

        return redirect()->route('admin.transaction.show', $result['txId'])
            ->with('success', 'Transação #' . $result['ref'] . ' concluída com sucesso.');
    }

    public function receipt($id)
    {
        $transaction = Transaction::with('user')->findOrFail($id);

        if ($transaction->status !== 'completed') {
            return back()->with('error', 'O comprovativo só está disponível para transações concluídas.');
        }

        return view('transaction.receipt', compact('transaction'));
    }

    public function assign($id)
    {
        $transaction = Transaction::findOrFail($id);

        if (in_array($transaction->status, ['completed', 'cancelled', 'expired'])) {
            return back()->with('error', 'Não é possível assumir uma transação neste estado.');
        }

        $transaction->update(['assigned_admin' => Auth::id()]);

        AuditLogger::transaction('agent_assigned',
            "Agente assumiu a transação #{$transaction->reference_id}",
            $transaction,
            ['admin_id' => Auth::id()]
        );

        return back()->with('success', 'Transação assumida por ti.');
    }

    public function cancel($id)
    {
        $transaction = Transaction::findOrFail($id);
        $ref         = $transaction->reference_id;
        $oldStatus   = $transaction->status;

        $ok = TransactionFlow::transition($transaction, 'cancelled', Auth::user());

        if (!$ok) {
            return back()->with('error', "Não é possível cancelar a transação no estado actual ({$oldStatus}).");
        }

        return redirect()->route('admin.transaction.show', $id)
            ->with('success', "Transação #{$ref} cancelada.");
    }

    public function poll(Request $request, $id)
    {
        $transaction = Transaction::findOrFail($id);
        $afterId     = max(0, (int) $request->query('after', 0));
        $adminId     = Auth::id();

        // Filtra pelos canais que este membro do staff pode ver.
        $visible = ChatMessage::visibleChannelsFor(Auth::user());

        $messages = ChatMessage::where('transaction_id', $transaction->id)
            ->whereIn('channel', $visible)
            ->where('id', '>', $afterId)
            ->orderBy('created_at', 'asc')
            ->get();

        if ($messages->isNotEmpty()) {
            ChatMessage::where('transaction_id', $transaction->id)
                ->whereIn('channel', $visible)
                ->where('id', '>', $afterId)
                ->whereNotNull('sender_id')
                ->where('sender_id', '!=', $adminId)
                ->where('is_read', false)
                ->update(['is_read' => true]);
        }

        return response()->json([
            'messages' => $messages->map(fn($msg) => [
                'id'        => $msg->id,
                'text'      => $msg->message_text,
                'type'      => $msg->message_type,
                'is_mine'   => !is_null($msg->sender_id) && $msg->sender_id === $adminId,
                'is_system' => is_null($msg->sender_id),
                'file_url'  => $msg->file_path ? ks_file($msg->file_path) : null,
                'is_image'  => $msg->file_path ? ks_is_image($msg->file_path) : false,
                'time'      => $msg->created_at->format('d/m H:i'),
                'is_read'   => (bool) $msg->is_read,
            ]),
        ]);
    }

    public function sendChatMessage(SendChatMessageRequest $request, $id)
    {
        $transaction = Transaction::findOrFail($id);

        // Canal: 'client' (visível ao cliente) ou 'internal' (nota interna do staff).
        $channel = in_array($request->input('channel'), ['client', 'internal'], true)
            ? $request->input('channel')
            : 'client';

        $data = [
            'transaction_id' => $transaction->id,
            'sender_id'      => Auth::id(),
            'message_text'   => $request->message_text,
            'message_type'   => 'text',
            'channel'        => $channel,
            'is_read'        => false,
        ];

        $meta = ['transaction_ref' => $transaction->reference_id, 'channel' => $channel];

        if ($request->hasFile('attachment')) {
            $file         = $request->file('attachment');
            $allowedMimes = ['application/pdf', 'image/jpeg', 'image/png', 'image/webp'];
            if (function_exists('finfo_open')) {
                $finfo    = new \finfo(FILEINFO_MIME_TYPE);
                $realMime = $finfo->file($file->getRealPath());
                if (!in_array($realMime, $allowedMimes, true)) {
                    return back()->withErrors(['attachment' => 'Tipo de ficheiro não permitido.']);
                }
            }
            $data['file_path']    = $file->store('chat_attachments', 'local');
            $ext                  = strtolower($file->getClientOriginalExtension());
            $data['message_type'] = in_array($ext, ['jpg','jpeg','png','gif','webp']) ? 'image' : 'document';
            if (!$request->filled('message_text')) {
                $data['message_text'] = $data['message_type'] === 'image' ? '📷 Imagem' : '📎 Documento';
            }
            $meta['file_size'] = $file->getSize();
            $meta['file_type'] = $data['message_type'];
        }

        ChatMessage::create($data);

        AuditLogger::transaction('admin_message',
            "Admin enviou mensagem na transação #{$transaction->reference_id}",
            $transaction,
            $meta
        );

        return back()->with('success', 'Mensagem enviada.');
    }
}
