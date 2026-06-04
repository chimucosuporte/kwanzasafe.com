<?php

namespace App\Http\Controllers;

use App\Http\Controllers\AdminController;
use App\Http\Requests\CreateTransactionRequest;
use App\Models\ChatMessage;
use App\Models\ExchangeRate;
use App\Models\Transaction;
use App\Services\AuditLogger;
use App\Services\TicketAssignment;
use App\Services\TransactionFlow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class TransactionController extends Controller
{
    public function store(CreateTransactionRequest $request)
    {
        $user = Auth::user();

        if (!$user->email_verified_at) {
            return redirect()->route('otp.email.verify')
                ->withErrors(['email' => 'Verifica o teu email antes de criar uma transação.']);
        }

        if (!$user->phone_verified_at || !$user->identity_verified_at) {
            AuditLogger::transaction('blocked_unverified',
                "Tentativa de transação bloqueada — KYC incompleto",
                null,
                [
                    'phone_verified'    => (bool) $user->phone_verified_at,
                    'identity_verified' => (bool) $user->identity_verified_at,
                ]
            );

            return redirect()->route('dashboard')
                ->with('error', 'Acesso negado. Conclui a verificação de identidade primeiro.');
        }

        $taxa         = ExchangeRate::findOrFail($request->moeda);
        $valorEnviar  = $request->valor_enviar;
        $valorReceber = round($valorEnviar * $taxa->rate, 2);

        // Garantir unicidade do reference_id com até 5 tentativas
        $referenceId = null;
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $candidate = 'KZ' . strtoupper(Str::random(6));
            if (!Transaction::withTrashed()->where('reference_id', $candidate)->exists()) {
                $referenceId = $candidate;
                break;
            }
        }
        if (!$referenceId) {
            return back()->with('error', 'Erro temporário ao criar transação. Tenta novamente.');
        }

        $transaction = Transaction::create([
            'reference_id'     => $referenceId,
            'user_id'          => Auth::id(),
            'exchange_rate_id' => $taxa->id,
            'currency_from'    => $taxa->currency_from,
            'currency_to'      => $taxa->currency_to,
            'amount_sent'      => $valorEnviar,
            'rate_applied'     => $taxa->rate,
            'amount_received'  => $valorReceber,
            'status'           => 'pending',
        ]);

        // Mensagem automática de boas-vindas na sala de transação
        TransactionFlow::systemMessage(
            $transaction,
            'A tua transação foi criada com sucesso. Um agente KwanzaSafe irá contactar-te aqui em breve para prosseguir com o envio.'
        );

        // Atribuição automática a um agente de suporte (round-robin menos ocupado)
        $agent = TicketAssignment::assign($transaction);
        if ($agent) {
            AuditLogger::transaction('auto_assigned',
                "Transação #{$referenceId} atribuída automaticamente ao agente {$agent->full_name}",
                $transaction,
                ['agent_id' => $agent->id]
            );
        }

        AuditLogger::transaction('created',
            "Nova transação #{$referenceId}: {$valorEnviar} {$taxa->currency_from} → {$valorReceber} AOA",
            $transaction,
            [
                'amount_sent'     => $valorEnviar,
                'currency_from'   => $taxa->currency_from,
                'amount_received' => $valorReceber,
                'rate_applied'    => $taxa->rate,
            ]
        );

        AdminController::clearStatsCache();

        return redirect()->route('transaction.show', $transaction->reference_id);
    }

    public function show($reference_id)
    {
        $transaction = Transaction::where('reference_id', $reference_id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return view('transaction.show', compact('transaction'));
    }

    public function confirmReceipt($reference_id)
    {
        $transaction = Transaction::where('reference_id', $reference_id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        if ($transaction->status !== 'aoa_sent') {
            return back()->with('error', 'Esta acção não está disponível para o estado actual da transação.');
        }

        TransactionFlow::transition($transaction, 'completed');

        AuditLogger::transaction('client_confirmed',
            "Cliente confirmou recepção dos AOA na transação #{$transaction->reference_id}",
            $transaction,
            ['old_status' => 'aoa_sent']
        );

        AdminController::clearStatsCache();

        return back()->with('success', 'Obrigado! A tua transação foi marcada como concluída.');
    }

    public function receipt($reference_id)
    {
        $transaction = Transaction::where('reference_id', $reference_id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        if ($transaction->status !== 'completed') {
            return back()->with('error', 'O comprovativo só está disponível para transações concluídas.');
        }

        return view('transaction.receipt', compact('transaction'));
    }

    public function cancel($reference_id)
    {
        $transaction = Transaction::where('reference_id', $reference_id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        if (!in_array($transaction->status, ['pending', 'negotiating'], true)) {
            return back()->with('error', 'Não é possível cancelar uma transação neste estado.');
        }

        $oldStatus = $transaction->status;
        $ok = TransactionFlow::transition($transaction, 'cancelled', Auth::user());

        if (!$ok) {
            return back()->with('error', 'Não foi possível cancelar a transação.');
        }

        AuditLogger::transaction('client_cancelled',
            "Cliente cancelou a transação #{$transaction->reference_id}",
            $transaction,
            ['old_status' => $oldStatus]
        );

        AdminController::clearStatsCache();

        return redirect()->route('dashboard')
            ->with('success', "Transação #{$transaction->reference_id} cancelada.");
    }

    public function uploadReceipt(Request $request, $reference_id)
    {
        $request->validate([
            'comprovativo' => 'required|file|mimes:pdf,jpg,jpeg,png,webp|max:5120',
        ]);

        $transaction = Transaction::where('reference_id', $reference_id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $file      = $request->file('comprovativo');
        $oldStatus = $transaction->status;

        // Verificar MIME real (não apenas a extensão declarada pelo cliente)
        $allowedMimes = ['application/pdf', 'image/jpeg', 'image/png', 'image/webp'];
        if (function_exists('finfo_open')) {
            $finfo    = new \finfo(FILEINFO_MIME_TYPE);
            $realMime = $finfo->file($file->getRealPath());
            if (!in_array($realMime, $allowedMimes, true)) {
                return back()->withErrors(['comprovativo' => 'Tipo de ficheiro não permitido. Usa PDF, JPG ou PNG.']);
            }
        }

        $path = $file->store('receipts', 'public');

        ChatMessage::create([
            'transaction_id' => $transaction->id,
            'sender_id'      => Auth::id(),
            'message_type'   => 'document',
            'file_path'      => $path,
            'message_text'   => 'Comprovativo de pagamento enviado pelo cliente.',
        ]);

        if ($transaction->status !== 'awaiting_payment') {
            TransactionFlow::transition($transaction, 'awaiting_payment', Auth::user());
        }

        AuditLogger::transaction('receipt_uploaded',
            "Comprovativo enviado para transação #{$transaction->reference_id}",
            $transaction,
            [
                'file_size'  => $file->getSize(),
                'file_mime'  => $file->getMimeType(),
                'old_status' => $oldStatus,
                'new_status' => 'awaiting_payment',
            ]
        );

        return back()->with('success', 'Comprovativo enviado com sucesso! A nossa equipa está a verificar o pagamento.');
    }
}
