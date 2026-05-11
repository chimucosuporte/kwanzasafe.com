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

    public function __construct(User $user, string $code, int $expiryMinutes = 15)
    {
        $this->user = $user;
        $this->code = $code;
        $this->expiryMinutes = $expiryMinutes;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Código de Verificação KwanzaSafe — ' . $this->code,
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
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
