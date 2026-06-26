<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\AdminController;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CreateTransactionRequest;
use App\Http\Requests\Api\UploadReceiptRequest;
use App\Http\Resources\TransactionResource;
use App\Models\Beneficiary;
use App\Models\ChatMessage;
use App\Models\ExchangeRate;
use App\Models\PaymentAccount;
use App\Models\PaymentWallet;
use App\Models\Transaction;
use App\Services\AuditLogger;
use App\Services\DestinationResolver;
use App\Services\NotificationService;
use App\Services\PushService;
use App\Services\TicketAssignment;
use App\Services\TransactionFlow;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Transações via API mobile — espelha App\Http\Controllers\TransactionController (web),
 * devolvendo JSON em vez de redirects/views. Mesma lógica de negócio e auditoria.
 */
class TransactionController extends Controller
{
    /**
     * Lista as transações do utilizador autenticado (mais recentes primeiro).
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $transactions = Transaction::where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->get();

        return TransactionResource::collection($transactions);
    }

    /**
     * Cria uma transação `pending` a partir da calculadora.
     */
    public function store(CreateTransactionRequest $request): JsonResponse
    {
        $user = $request->user();

        if (! $user->email_verified_at) {
            return response()->json([
                'message' => 'Verifica o teu email antes de criar uma transação.',
                'code'    => 'email_unverified',
            ], 403);
        }

        if (! $user->phone_verified_at || ! $user->identity_verified_at) {
            AuditLogger::transaction('blocked_unverified',
                'Tentativa de transação bloqueada — KYC incompleto (mobile)',
                null,
                [
                    'phone_verified'    => (bool) $user->phone_verified_at,
                    'identity_verified' => (bool) $user->identity_verified_at,
                ]
            );

            return response()->json([
                'message' => 'Conclui a verificação de identidade primeiro.',
                'code'    => 'kyc_incomplete',
            ], 403);
        }

        // Destino de recepção — validado DEPOIS das barreiras de email/KYC.
        $request->validate([
            'destino_tipo' => 'required|in:bank,bybit,binance,redotpay',
            'destino_id'   => 'required|integer',
        ], [
            'destino_tipo.required' => 'Escolhe onde queres receber os Kwanzas.',
            'destino_tipo.in'       => 'Destino de recepção inválido.',
            'destino_id.required'   => 'Escolhe onde queres receber os Kwanzas.',
        ]);

        $taxa         = ExchangeRate::findOrFail($request->moeda);
        $valorEnviar  = $request->valor_enviar;
        $valorReceber = round($valorEnviar * $taxa->rate, 2);

        // Destino de recepção: snapshot do beneficiário/carteira do utilizador.
        $destino = DestinationResolver::resolve($user, $request->destino_tipo, (int) $request->destino_id);

        // Garantir unicidade do reference_id com até 5 tentativas
        $referenceId = null;
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $candidate = 'KZ'.strtoupper(Str::random(6));
            if (! Transaction::withTrashed()->where('reference_id', $candidate)->exists()) {
                $referenceId = $candidate;
                break;
            }
        }
        if (! $referenceId) {
            return response()->json(['message' => 'Erro temporário ao criar transação. Tenta novamente.'], 503);
        }

        $transaction = Transaction::create([
            'reference_id'     => $referenceId,
            'user_id'          => $user->id,
            'exchange_rate_id' => $taxa->id,
            'currency_from'    => $taxa->currency_from,
            'currency_to'      => $taxa->currency_to,
            'amount_sent'      => $valorEnviar,
            'rate_applied'     => $taxa->rate,
            'amount_received'  => $valorReceber,
            'destination_type'       => $destino['type'],
            'destination_label'      => $destino['label'],
            'destination_identifier' => $destino['identifier'],
            'destination_holder'     => $destino['holder'],
            'destination_network'    => $destino['network'],
            'status'           => 'pending',
            // Prazo de validade da transação (countdown visível na app).
            'expires_at'       => now()->addHours(48),
        ]);

        TransactionFlow::systemMessage(
            $transaction,
            'A tua transação foi criada com sucesso. Um agente KwanzaSafe irá contactar-te aqui em breve para prosseguir com o envio.'
        );

        $agent = TicketAssignment::assign($transaction);
        if ($agent) {
            AuditLogger::transaction('auto_assigned',
                "Transação #{$referenceId} atribuída automaticamente ao agente {$agent->full_name}",
                $transaction,
                ['agent_id' => $agent->id]
            );
        }

        AuditLogger::transaction('created',
            "Nova transação #{$referenceId}: {$valorEnviar} {$taxa->currency_from} → {$valorReceber} AOA (mobile)",
            $transaction,
            [
                'amount_sent'     => $valorEnviar,
                'currency_from'   => $taxa->currency_from,
                'amount_received' => $valorReceber,
                'rate_applied'    => $taxa->rate,
                'channel'         => 'mobile',
            ]
        );

        AdminController::clearStatsCache();

        // Push + feed de "transação iniciada" (a criação não passa por TransactionFlow::transition).
        $createdBody = 'A tua transação foi criada. Um agente KwanzaSafe vai contactar-te em breve.';
        PushService::sendToUser($user, 'Transação '.$referenceId, $createdBody, ['reference_id' => $referenceId, 'status' => 'pending']);
        NotificationService::notify($user, 'transaction', 'Transação '.$referenceId, $createdBody, ['reference_id' => $referenceId, 'status' => 'pending']);

        return (new TransactionResource($this->withPaymentAccount($transaction)))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Detalhe de uma transação (inclui a conta de recepção activa).
     */
    public function show(Request $request, string $reference_id): TransactionResource
    {
        $transaction = $this->findOwned($request, $reference_id);

        return new TransactionResource($this->withPaymentAccount($transaction));
    }

    /**
     * Cliente confirma recepção dos AOA → transição para `completed`.
     */
    public function confirm(Request $request, string $reference_id): JsonResponse
    {
        $transaction = $this->findOwned($request, $reference_id);

        if ($transaction->status !== 'aoa_sent') {
            return response()->json(['message' => 'Esta acção não está disponível para o estado actual da transação.'], 422);
        }

        TransactionFlow::transition($transaction, 'completed', $request->user());

        AuditLogger::transaction('client_confirmed',
            "Cliente confirmou recepção dos AOA na transação #{$transaction->reference_id} (mobile)",
            $transaction,
            ['old_status' => 'aoa_sent']
        );

        AdminController::clearStatsCache();

        return (new TransactionResource($this->withPaymentAccount($transaction->fresh())))->response();
    }

    /**
     * Cliente cancela uma transação ainda em fase inicial.
     */
    public function cancel(Request $request, string $reference_id): JsonResponse
    {
        $transaction = $this->findOwned($request, $reference_id);

        if (! in_array($transaction->status, ['pending', 'negotiating'], true)) {
            return response()->json(['message' => 'Não é possível cancelar uma transação neste estado.'], 422);
        }

        $oldStatus = $transaction->status;
        $ok = TransactionFlow::transition($transaction, 'cancelled', $request->user());

        if (! $ok) {
            return response()->json(['message' => 'Não foi possível cancelar a transação.'], 422);
        }

        AuditLogger::transaction('client_cancelled',
            "Cliente cancelou a transação #{$transaction->reference_id} (mobile)",
            $transaction,
            ['old_status' => $oldStatus]
        );

        AdminController::clearStatsCache();

        return (new TransactionResource($this->withPaymentAccount($transaction->fresh())))->response();
    }

    /**
     * Cliente envia o comprovativo de pagamento → `awaiting_payment`.
     */
    public function uploadReceipt(UploadReceiptRequest $request, string $reference_id): JsonResponse
    {
        $transaction = $this->findOwned($request, $reference_id);

        $file      = $request->file('comprovativo');
        $oldStatus = $transaction->status;

        // Verificar MIME real (não apenas a extensão declarada pelo cliente)
        $allowedMimes = ['application/pdf', 'image/jpeg', 'image/png', 'image/webp'];
        if (function_exists('finfo_open')) {
            $finfo    = new \finfo(FILEINFO_MIME_TYPE);
            $realMime = $finfo->file($file->getRealPath());
            if (! in_array($realMime, $allowedMimes, true)) {
                return response()->json(['message' => 'Tipo de ficheiro não permitido. Usa PDF, JPG ou PNG.'], 422);
            }
        }

        $path = $file->store('receipts', 'local');

        ChatMessage::create([
            'transaction_id' => $transaction->id,
            'sender_id'      => $request->user()->id,
            'message_type'   => 'document',
            'channel'        => 'client',
            'file_path'      => $path,
            'message_text'   => 'Comprovativo de pagamento enviado pelo cliente.',
        ]);

        if ($transaction->status !== 'awaiting_payment') {
            TransactionFlow::transition($transaction, 'awaiting_payment', $request->user());
        }

        AuditLogger::transaction('receipt_uploaded',
            "Comprovativo enviado para transação #{$transaction->reference_id} (mobile)",
            $transaction,
            [
                'file_size'  => $file->getSize(),
                'file_mime'  => $file->getMimeType(),
                'old_status' => $oldStatus,
                'new_status' => 'awaiting_payment',
            ]
        );

        return (new TransactionResource($this->withPaymentAccount($transaction->fresh())))->response();
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

    /**
     * Anexa a conta de recepção activa (atributo dinâmico lido pelo Resource).
     */
    private function withPaymentAccount(Transaction $transaction): Transaction
    {
        $transaction->payment_account = PaymentAccount::activeFor($transaction->currency_from);

        return $transaction;
    }
}
