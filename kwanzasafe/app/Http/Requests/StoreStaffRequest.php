<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStaffRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Rota já protegida pelo middleware is_super_admin.
        return true;
    }

    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:120'],
            'email'     => ['required', 'string', 'lowercase', 'email', 'max:191', Rule::unique(User::class)],
            'password'  => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function messages(): array
    {
        return [
            'full_name.required' => 'O nome do funcionário é obrigatório.',
            'email.unique'       => 'Já existe uma conta com este email.',
            'password.min'       => 'A password deve ter pelo menos 8 caracteres.',
            'password.confirmed' => 'A confirmação da password não coincide.',
        ];
    }
}
