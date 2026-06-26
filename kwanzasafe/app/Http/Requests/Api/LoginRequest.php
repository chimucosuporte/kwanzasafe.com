<?php

namespace App\Http\Requests\Api;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Autenticação via API mobile.
 * Espelha a lógica de throttle de App\Http\Requests\Auth\LoginRequest (web).
 */
class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'email'       => ['required', 'string', 'email'],
            'password'    => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:120'],
        ];
    }

    /**
     * Tenta autenticar as credenciais (sem iniciar sessão web — só validação).
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        // Valida as credenciais sem persistir sessão (login é stateless/token).
        if (! Auth::validate($this->only('email', 'password'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * O utilizador correspondente às credenciais validadas.
     */
    public function user($guard = null)
    {
        return Auth::getProvider()->retrieveByCredentials($this->only('email', 'password'));
    }

    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip()).'|api';
    }

    /**
     * Nome do dispositivo a associar ao token Sanctum.
     */
    public function deviceName(): string
    {
        return $this->filled('device_name')
            ? (string) $this->string('device_name')
            : 'mobile';
    }
}
