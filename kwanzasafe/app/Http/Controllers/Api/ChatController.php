<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\SendChatMessageRequest;
use App\Http\Resources\ChatMessageResource;
use App\Models\ChatMessage;
use App\Models\Transaction;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Chat da sala de transação via API mobile.
 *
 * O cliente só acede às suas próprias transações e só vê os canais 'client'
 * e 'recourse' (nunca notas internas) — igual ao ChatController web.
 */
class ChatController extends Controller
{
    /** Canais visíveis ao cliente. */
    private const CLIENT_CHANNELS = ['client', 'recourse'];

    /**
     * Lista mensagens. Serve tanto a carga inicial como o polling:
     * passa-se `?after=<id>` para receber só as mensagens novas. Marca como
     * lidas as mensagens recebidas e devolvidas.
     */
    public function index(Request $request, string $reference_id): AnonymousResourceCollection
    {
        $transaction = $this->findOwned($request, $reference_id);

        $afterId = max(0, (int) $request->query('after', 0));
        $userId  = $request->user()->id;

        $messages = ChatMessage::where('transaction_id', $transaction->id)
            ->whereIn('channel', self::CLIENT_CHANNELS)
            ->where('id', '>', $afterId)
            ->orderBy('created_at', 'asc')
            ->get();

        if ($messages->isNotEmpty()) {
            ChatMessage::where('transaction_id', $transaction->id)
                ->whereIn('channel', self::CLIENT_CHANNELS)
                ->where('id', '>', $afterId)
                ->where(fn ($q) => $q->whereNull('sender_id')->orWhere('sender_id', '!=', $userId))
                ->where('is_read', false)
                ->update(['is_read' => true]);
        }

        return ChatMessageResource::collection($messages);
    }

    /**
     * Cliente envia mensagem de texto e/ou anexo.
     */
    public function store(SendChatMessageRequest $request, string $reference_id): JsonResponse
    {
        $transaction = $this->findOwned($request, $reference_id);

        $data = [
            'transaction_id' => $transaction->id,
            'sender_id'      => $request->user()->id,
            'message_text'   => $request->input('message_text'),
            'message_type'   => 'text',
            'channel'        => 'client',
            'is_read'        => false,
        ];

        if ($request->hasFile('attachment')) {
            $file         = $request->file('attachment');
            $allowedMimes = ['application/pdf', 'image/jpeg', 'image/png', 'image/webp'];
            if (function_exists('finfo_open')) {
                $finfo    = new \finfo(FILEINFO_MIME_TYPE);
                $realMime = $finfo->file($file->getRealPath());
                if (! in_array($realMime, $allowedMimes, true)) {
                    return response()->json(['message' => 'Tipo de ficheiro não permitido.'], 422);
                }
            }
            $path              = $file->store('chat_attachments', 'local');
            $data['file_path'] = $path;

            $ext                  = strtolower($file->getClientOriginalExtension());
            $data['message_type'] = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']) ? 'image' : 'document';

            if (! $request->filled('message_text')) {
                $data['message_text'] = $data['message_type'] === 'image'
                    ? '📷 Enviou uma imagem'
                    : '📎 Enviou um documento';
            }
        }

        $message = ChatMessage::create($data);

        AuditLogger::log('chat.message_sent', 'transaction',
            "Cliente enviou mensagem na transação #{$transaction->reference_id} (mobile)",
            [
                'target'   => $transaction,
                'metadata' => [
                    'message_type'   => $message->message_type,
                    'has_attachment' => (bool) $message->file_path,
                    'message_id'     => $message->id,
                    'channel'        => 'mobile',
                ],
            ]
        );

        return (new ChatMessageResource($message))->response()->setStatusCode(201);
    }

    /**
     * Marca como lidas as mensagens recebidas (não enviadas pelo cliente).
     */
    public function markAsRead(Request $request, string $reference_id): JsonResponse
    {
        $transaction = $this->findOwned($request, $reference_id);

        ChatMessage::where('transaction_id', $transaction->id)
            ->whereIn('channel', self::CLIENT_CHANNELS)
            ->where('sender_id', '!=', $request->user()->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['success' => true]);
    }

    /**
     * Encontra uma transação garantindo que pertence ao utilizador autenticado.
     */
    private function findOwned(Request $request, string $reference_id): Transaction
    {
        return Transaction::where('reference_id', $reference_id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();
    }
}
