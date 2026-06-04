<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\ChatMessage;
use Illuminate\Support\Facades\Auth;
use App\Services\AuditLogger;

/**
 * ChatController — Cliente comunica com Admin dentro da Sala de Transação.
 *
 * O cliente só pode enviar mensagens sobre as suas próprias transações.
 * Cada mensagem é registada no Audit Trail.
 */
class ChatController extends Controller
{
    /**
     * Cliente envia mensagem de texto ou ficheiro sobre uma transação específica.
     */
    public function sendMessage(Request $request, $reference_id)
    {
        // Validação: ou texto OU ficheiro (não pode ser vazio)
        $request->validate([
            'message_text' => 'nullable|string|max:2000',
            'attachment'   => 'nullable|file|mimes:pdf,jpg,jpeg,png,webp|max:5120',
        ]);

        // Garantir que há pelo menos um dos dois
        if (!$request->filled('message_text') && !$request->hasFile('attachment')) {
            return back()->withErrors(['message_text' => 'Escreve uma mensagem ou anexa um ficheiro.']);
        }

        // Segurança: garantir que a transação pertence ao cliente autenticado
        $transaction = Transaction::where('reference_id', $reference_id)
                                  ->where('user_id', Auth::id())
                                  ->firstOrFail();

        $data = [
            'transaction_id' => $transaction->id,
            'sender_id'      => Auth::id(),
            'message_text'   => $request->message_text,
            'message_type'   => 'text',
            'channel'        => 'client',
            'is_read'        => false,
        ];

        // Processar ficheiro anexado se existir
        if ($request->hasFile('attachment')) {
            $file          = $request->file('attachment');
            $allowedMimes  = ['application/pdf', 'image/jpeg', 'image/png', 'image/webp'];
            if (function_exists('finfo_open')) {
                $finfo    = new \finfo(FILEINFO_MIME_TYPE);
                $realMime = $finfo->file($file->getRealPath());
                if (!in_array($realMime, $allowedMimes, true)) {
                    return back()->withErrors(['attachment' => 'Tipo de ficheiro não permitido.']);
                }
            }
            $path = $file->store('chat_attachments', 'public');
            $data['file_path']    = $path;

            $ext = strtolower($file->getClientOriginalExtension());
            $data['message_type'] = in_array($ext, ['jpg','jpeg','png','gif','webp']) ? 'image' : 'document';

            // Se não houver texto, colocar descrição padrão
            if (!$request->filled('message_text')) {
                $data['message_text'] = $data['message_type'] === 'image'
                    ? '📷 Enviou uma imagem'
                    : '📎 Enviou um documento';
            }
        }

        $message = ChatMessage::create($data);

        // AUDIT: mensagem enviada
        AuditLogger::log('chat.message_sent', 'transaction',
            "Cliente enviou mensagem na transação #{$transaction->reference_id}",
            [
                'target'   => $transaction,
                'metadata' => [
                    'message_type'   => $message->message_type,
                    'has_attachment' => (bool) $message->file_path,
                    'message_id'     => $message->id,
                ],
            ]
        );

        return back()->with('success', 'Mensagem enviada com sucesso.');
    }

    /**
     * Polling AJAX — devolve mensagens novas desde after_id e marca-as como lidas.
     */
    public function poll(Request $request, $reference_id)
    {
        $transaction = Transaction::where('reference_id', $reference_id)
                                  ->where('user_id', Auth::id())
                                  ->firstOrFail();

        $afterId = max(0, (int) $request->query('after', 0));
        $userId  = Auth::id();

        // O cliente só vê os canais 'client' e 'recourse' — nunca notas internas.
        $visible = ['client', 'recourse'];

        $messages = ChatMessage::where('transaction_id', $transaction->id)
            ->whereIn('channel', $visible)
            ->where('id', '>', $afterId)
            ->orderBy('created_at', 'asc')
            ->get();

        if ($messages->isNotEmpty()) {
            ChatMessage::where('transaction_id', $transaction->id)
                ->whereIn('channel', $visible)
                ->where('id', '>', $afterId)
                ->where(fn($q) => $q->whereNull('sender_id')->orWhere('sender_id', '!=', $userId))
                ->where('is_read', false)
                ->update(['is_read' => true]);
        }

        return response()->json([
            'messages' => $messages->map(fn($msg) => [
                'id'        => $msg->id,
                'text'      => $msg->message_text,
                'type'      => $msg->message_type,
                'is_mine'   => !is_null($msg->sender_id) && $msg->sender_id === $userId,
                'is_system' => is_null($msg->sender_id),
                'file_url'  => $msg->file_path ? ks_file($msg->file_path) : null,
                'is_image'  => $msg->file_path ? ks_is_image($msg->file_path) : false,
                'time'      => $msg->created_at->format('d/m H:i'),
                'is_read'   => (bool) $msg->is_read,
            ]),
        ]);
    }

    /**
     * Cliente marca as mensagens do admin como lidas ao abrir a Sala de Transação.
     */
    public function markAsRead($reference_id)
    {
        $transaction = Transaction::where('reference_id', $reference_id)
                                  ->where('user_id', Auth::id())
                                  ->firstOrFail();

        // Marcar como lidas apenas mensagens recebidas (não enviadas pelo cliente),
        // e apenas dos canais que o cliente vê (nunca notas internas).
        ChatMessage::where('transaction_id', $transaction->id)
                   ->whereIn('channel', ['client', 'recourse'])
                   ->where('sender_id', '!=', Auth::id())
                   ->where('is_read', false)
                   ->update(['is_read' => true]);

        return response()->json(['success' => true]);
    }
}
