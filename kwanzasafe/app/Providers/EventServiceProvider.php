<?php

namespace App\Providers;

use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Services\AuditLogger;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     */
    protected $listen = [
        Registered::class => [
            \Illuminate\Auth\Listeners\SendEmailVerificationNotification::class,
        ],
    ];

    public function boot(): void
    {
        // ================================================================
        // AUDIT TRAIL — Eventos de Autenticação (Conformidade AML)
        // Estes listeners registam automaticamente toda a atividade de auth.
        // ================================================================

        // LOGIN bem-sucedido
        Event::listen(Login::class, function (Login $event) {
            AuditLogger::auth('login',
                "Utilizador '{$event->user->email}' entrou na plataforma",
                [
                    'user_id'    => $event->user->id,
                    'user_email' => $event->user->email,
                    'metadata'   => [
                        'guard'    => $event->guard,
                        'remember' => $event->remember,
                    ],
                ]
            );
        });

        // LOGOUT
        Event::listen(Logout::class, function (Logout $event) {
            if ($event->user) {
                AuditLogger::auth('logout',
                    "Utilizador '{$event->user->email}' terminou sessão",
                    [
                        'user_id'    => $event->user->id,
                        'user_email' => $event->user->email,
                    ]
                );
            }
        });

        // Tentativa de login FALHADA (crítico para deteção de ataques)
        Event::listen(Failed::class, function (Failed $event) {
            $email = $event->credentials['email'] ?? 'desconhecido';
            AuditLogger::auth('login_failed',
                "Tentativa de login falhada para '{$email}'",
                [
                    'user_id'    => $event->user?->id,
                    'user_email' => $email,
                    'severity'   => 'warning',
                    'metadata'   => [
                        'attempted_email' => $email,
                    ],
                ]
            );
        });

        // Bloqueio por demasiadas tentativas (possível ataque)
        Event::listen(Lockout::class, function (Lockout $event) {
            AuditLogger::auth('lockout',
                'Conta bloqueada por demasiadas tentativas de login',
                [
                    'severity' => 'critical',
                    'metadata' => [
                        'ip' => $event->request->ip(),
                    ],
                ]
            );
        });

        // REGISTO de novo utilizador
        Event::listen(Registered::class, function (Registered $event) {
            AuditLogger::auth('registered',
                "Nova conta criada: '{$event->user->email}'",
                [
                    'user_id'    => $event->user->id,
                    'user_email' => $event->user->email,
                    'severity'   => 'info',
                ]
            );
        });

        // RESET de password (ação sensível)
        Event::listen(PasswordReset::class, function (PasswordReset $event) {
            AuditLogger::auth('password_reset',
                "Password redefinida para '{$event->user->email}'",
                [
                    'user_id'    => $event->user->id,
                    'user_email' => $event->user->email,
                    'severity'   => 'warning',
                ]
            );
        });
    }

    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
