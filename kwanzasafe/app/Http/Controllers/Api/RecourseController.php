<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\RecourseResource;
use App\Models\ChatMessage;
use App\Models\Recourse;
use App\Models\Transaction;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Recursos (appeal) do cliente via API mobile. Espelha o RecourseController web.
 * A conversa do recurso decorre no canal 'recourse' (já incluído no chat mobile).
 */
class RecourseController extends Controller
{
    /** Recurso mais recente da transação do utilizador (ou null). */
    public function show(Request $request, string $reference_id): JsonResponse
    {
        $transaction = $this->findOwned($request, $reference_id);

        $recourse = Recourse::where('transaction_id', $transaction->id)
            ->orderByDesc('created_at')
            ->first();

        return response()->json([
            'data' => $recourse ? new RecourseResource($recourse) : null,
        ]);
    }

    /** Abre um recurso (apenas um activo por transação). */
    public function open(Request $request, string $reference_id): JsonResponse
    {
        $data = $request->validate([
            'reason' => ['required', 'string', 'min:10', 'max:2000'],
        ], [
            'reason.required' => 'Explica o motivo do recurso.',
            'reason.min'      => 'Descreve o motivo com pelo menos 10 caracteres.',
        ]);

        $transaction = $this->findOwned($request, $reference_id);

        if (Recourse::where('transaction_id', $transaction->id)->active()->exists()) {
            throw ValidationException::withMessages(['reason' => 'Já tens um recurso em aberto para esta transação.']);
        }

        $recourse = Recourse::create([
            'transaction_id' => $transaction->id,
            'opened_by'      => $request->user()->id,
            'reason'         => $data['reason'],
            'status'         => 'open',
            'expires_at'     => now()->addDays(7),
        ]);

        ChatMessage::create([
            'transaction_id' => $transaction->id,
            'sender_id'      => $request->user()->id,
            'message_text'   => $data['reason'],
            'message_type'   => 'text',
            'channel'        => 'recourse',
            'is_read'        => false,
        ]);

        AuditLogger::log('recourse.opened', 'transaction',
            "Cliente abriu um recurso na transação #{$transaction->reference_id} (mobile)",
            ['target' => $transaction, 'metadata' => ['recourse_id' => $recourse->id, 'channel' => 'mobile']]
        );

        return (new RecourseResource($recourse))->response()->setStatusCode(201);
    }

    /** Cancela o recurso activo do cliente. */
    public function cancel(Request $request, string $reference_id): JsonResponse
    {
        $transaction = $this->findOwned($request, $reference_id);

        $recourse = Recourse::where('transaction_id', $transaction->id)->active()->firstOrFail();
        $recourse->update(['status' => 'cancelled']);

        ChatMessage::create([
            'transaction_id' => $transaction->id,
            'sender_id'      => null,
            'message_text'   => 'Recurso cancelado pelo cliente.',
            'message_type'   => 'text',
            'channel'        => 'recourse',
            'is_read'        => false,
        ]);

        AuditLogger::log('recourse.cancelled', 'transaction',
            "Cliente cancelou o recurso da transação #{$transaction->reference_id} (mobile)",
            ['target' => $transaction, 'metadata' => ['recourse_id' => $recourse->id, 'channel' => 'mobile']]
        );

        return response()->json([
            'message' => 'Recurso cancelado.',
            'data'    => new RecourseResource($recourse->fresh()),
        ]);
    }

    private function findOwned(Request $request, string $reference_id): Transaction
    {
        return Transaction::where('reference_id', $reference_id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();
    }
}
