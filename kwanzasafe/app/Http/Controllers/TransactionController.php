<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\ExchangeRate;
use App\Models\ChatMessage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Services\AuditLogger;

class TransactionController extends Controller
{
    public function store(Request $request)
    {
        $user = Auth::user();

        // Bloquear se email não verificado
        if (!$user->email_verified_at) {
            return redirect()->route('otp.email.verify')
                ->withErrors(['email' => 'Verifica o teu email antes de criar uma transação.']);
        }
        
        // Regra de Ouro: Bloqueio se não estiver verificado
        if (!$user->phone_verified_at || !$user->identity_verified_at) {
            // AUDIT: tentativa de transação sem KYC completo
            AuditLogger::transaction('blocked_unverified',
                "Tentativa de transação bloqueada — KYC incompleto",
                null,
                [
                    'phone_verified'    => (bool) $user->phone_verified_at,
                    'identity_verified' => (bool) $user->identity_verified_at,
                ]
            );

            return redirect()->route('dashboard')
                ->with('error', 'Acesso negado. Conclua a verificação de identidade primeiro.');
        }

        $request->validate([
            'moeda'        => 'required|exists:exchange_rates,id',
            'valor_enviar' => 'required|numeric|min:10',
        ]);

        $taxa = ExchangeRate::findOrFail($request->moeda);
        $valorEnviar  = $request->valor_enviar;
        $valorReceber = $valorEnviar * $taxa->rate;

        $referenceId = 'KZ' . strtoupper(Str::random(5));

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

        // AUDIT: transação criada
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

        return redirect()->route('transaction.show', $transaction->reference_id);
    }

    public function show($reference_id)
    {
        $transaction = Transaction::where('reference_id', $reference_id)
                                  ->where('user_id', Auth::id())
                                  ->firstOrFail();

        return view('transaction.show', compact('transaction'));
    }

    public function uploadReceipt(Request $request, $reference_id)
    {
        $request->validate([
            'comprovativo' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $transaction = Transaction::where('reference_id', $reference_id)
                                  ->where('user_id', Auth::id())
                                  ->firstOrFail();

        if ($request->hasFile('comprovativo')) {
            $file = $request->file('comprovativo');
            $path = $file->store('receipts', 'public');

            ChatMessage::create([
                'transaction_id' => $transaction->id,
                'sender_id'      => Auth::id(),
                'message_type'   => 'document',
                'file_path'      => $path,
                'message_text'   => 'Comprovativo de pagamento enviado pelo cliente.',
            ]);

            $oldStatus = $transaction->status;
            $transaction->update(['status' => 'processing']);

            // AUDIT: comprovativo enviado
            AuditLogger::transaction('receipt_uploaded',
                "Comprovativo enviado para transação #{$transaction->reference_id}",
                $transaction,
                [
                    'file_size'   => $file->getSize(),
                    'file_mime'   => $file->getMimeType(),
                    'old_status'  => $oldStatus,
                    'new_status'  => 'processing',
                ]
            );

            return back()->with('success', 'Comprovativo enviado com sucesso! A nossa equipa está a analisar.');
        }

        return back()->withErrors(['comprovativo' => 'Erro ao enviar o ficheiro.']);
    }
}
