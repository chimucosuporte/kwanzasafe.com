<?php

namespace App\Http\Requests\Api;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules;

/**
 * Validação do registo via API mobile.
 * Espelha App\Http\Controllers\Auth\RegisteredUserController@store (web).
 */
class RegisterRequest extends FormRequest
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
            'name'        => ['required', 'string', 'max:120'],
            'email'       => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password'    => ['required', 'confirmed', Rules\Password::defaults()],
            'device_name' => ['nullable', 'string', 'max:120'],
        ];
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
