<?php

namespace App\Http\Resources;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Transação para a app mobile.
 *
 * Inclui rótulo de estado (PT) e flags de acção que a UI usa para decidir
 * que botões mostrar. A conta de recepção (`payment_account`) só é incluída
 * quando carregada pelo endpoint de detalhe.
 *
 * @mixin Transaction
 */
class TransactionResource extends JsonResource
{
    /** Rótulos legíveis (PT) por estado. */
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

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'reference_id'  => $this->reference_id,
            'status'        => $this->status,
            'status_label'  => self::STATUS_LABELS[$this->status] ?? $this->status,

            'currency_from' => $this->currency_from,
            'currency_to'   => $this->currency_to,
            // Strings (casts decimal) — precisão exacta, nunca float.
            'amount_sent'     => (string) $this->amount_sent,
            'rate_applied'    => (string) $this->rate_applied,
            'amount_received' => (string) $this->amount_received,
            'fee_amount'      => (string) $this->fee_amount,

            // Destino de recepção (PARA ONDE o cliente recebe os Kwanzas).
            'destination' => $this->destination_type ? [
                'type'       => $this->destination_type,
                'label'      => $this->destination_label,
                'identifier' => $this->destination_identifier,
                'holder'     => $this->destination_holder,
                'network'    => $this->destination_network,
            ] : null,

            // Flags de acção para a UI (espelham as regras dos controllers).
            'can_cancel'         => in_array($this->status, ['pending', 'negotiating'], true),
            'can_upload_receipt' => in_array($this->status, ['pending', 'negotiating', 'awaiting_payment'], true),
            'can_confirm'        => $this->status === 'aoa_sent',

            'created_at'          => $this->created_at?->toIso8601String(),
            'expires_at'          => $this->expires_at?->toIso8601String(),
            'payment_received_at' => $this->payment_received_at?->toIso8601String(),
            'aoa_sent_at'         => $this->aoa_sent_at?->toIso8601String(),
            'client_confirmed_at' => $this->client_confirmed_at?->toIso8601String(),

            // Presente apenas no detalhe (atribuído no controller via setAttribute).
            'payment_account' => $this->when(
                ! is_null($this->payment_account),
                fn () => new PaymentAccountResource($this->payment_account)
            ),
        ];
    }
}
