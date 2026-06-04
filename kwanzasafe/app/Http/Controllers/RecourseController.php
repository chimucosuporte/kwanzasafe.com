<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use App\Models\Recourse;
use App\Models\Transaction;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Recursos do lado do cliente — abrir e acompanhar um recurso sobre a sua
 * transação. A conversa do recurso decorre no canal 'recourse'
 * (visível apenas ao cliente e ao super-admin).
 */
class RecourseController extends Controller
{
    public function open(Request $request, $reference_id)
    {
        $request->validate(['reason' => ['required', 'string', 'max:2000']]);

        $transaction = Transaction::where('reference_id', $reference_id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        // Apenas um recurso activo por transação.
        $active = Recourse::where('transaction_id', $transaction->id)->active()->exists();
        if ($active) {
            return back()->with('error', 'Já existe um recurso em aberto para esta transação.');
        }

        $recourse = Recourse::create([
            'transaction_id' => $transaction->id,
            'opened_by'      => Auth::id(),
            'reason'         => $request->reason,
            'status'         => 'open',
        ]);

        ChatMessage::create([
            'transaction_id' => $transaction->id,
            'sender_id'      => Auth::id(),
            'message_text'   => $request->reason,
            'message_type'   => 'text',
            'channel'        => 'recourse',
            'is_read'        => false,
        ]);

        AuditLogger::log('recourse.opened', 'transaction',
            "Cliente abriu um recurso na transação #{$transaction->reference_id}",
            ['target' => $transaction, 'metadata' => ['recourse_id' => $recourse->id]]
        );

        return back()->with('success', 'Recurso submetido. Um super-administrador irá analisar o teu caso.');
    }

    public function reply(Request $request, $reference_id)
    {
        $request->validate(['message_text' => ['required', 'string', 'max:2000']]);

        $transaction = Transaction::where('reference_id', $reference_id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $recourse = Recourse::where('transaction_id', $transaction->id)->active()->firstOrFail();

        ChatMessage::create([
            'transaction_id' => $transaction->id,
            'sender_id'      => Auth::id(),
            'message_text'   => $request->message_text,
            'message_type'   => 'text',
            'channel'        => 'recourse',
            'is_read'        => false,
        ]);

        return back()->with('success', 'Mensagem enviada ao super-administrador.');
    }
}
