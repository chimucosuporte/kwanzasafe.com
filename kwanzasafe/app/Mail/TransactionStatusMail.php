<?php

namespace App\Mail;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TransactionStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    private const SUBJECTS = [
        'negotiating'      => 'O teu agente está disponível — Transação #',
        'awaiting_payment' => 'Instrução de Pagamento — Transação #',
        'payment_received' => 'Pagamento Confirmado ✓ — Transação #',
        'aoa_sent'         => 'Kwanzas Enviados — Confirma a Recepção #',
        'completed'        => 'Transação Concluída com Sucesso #',
        'cancelled'        => 'Transação Cancelada — #',
        'expired'          => 'Transação Expirada — #',
    ];

    public function __construct(
        public Transaction $transaction,
        public User $client,
        public string $newStatus,
    ) {}

    public function envelope(): Envelope
    {
        $prefix = self::SUBJECTS[$this->newStatus] ?? 'Actualização da Transação #';

        return new Envelope(
            subject: $prefix . $this->transaction->reference_id,
            from: new Address(
                config('mail.from.address', 'geral@kwanzasafe.com'),
                'KwanzaSafe'
            ),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.transaction_status',
            with: [
                'client'      => $this->client,
                'transaction' => $this->transaction,
                'newStatus'   => $this->newStatus,
                'txUrl'       => route('transaction.show', $this->transaction->reference_id),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
