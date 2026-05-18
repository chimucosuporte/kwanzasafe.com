<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class OtpEmail extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;
    public string $code;
    public int $expiryMinutes;
    public string $context;
    public ?string $contextData;

    public function __construct(User $user, string $code, int $expiryMinutes = 15, string $context = 'email', ?string $contextData = null)
    {
        $this->user        = $user;
        $this->code        = $code;
        $this->expiryMinutes = $expiryMinutes;
        $this->context     = $context;      // 'email' | 'phone'
        $this->contextData = $contextData;  // número de telefone quando context='phone'
    }

    public function envelope(): Envelope
    {
        $subject = $this->context === 'phone'
            ? 'Confirmação de Telefone KwanzaSafe — ' . $this->code
            : 'Código de Verificação KwanzaSafe — ' . $this->code;

        return new Envelope(
            subject: $subject,
            from: new \Illuminate\Mail\Mailables\Address(
                config('mail.from.address', 'geral@kwanzasafe.com'),
                'KwanzaSafe'
            ),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.otp',
            with: [
                'user'          => $this->user,
                'code'          => $this->code,
                'expiryMinutes' => $this->expiryMinutes,
                'context'       => $this->context,
                'contextData'   => $this->contextData,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
