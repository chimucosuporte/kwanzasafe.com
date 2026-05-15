<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RejectKycRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->is_admin;
    }

    public function rules(): array
    {
        return [
            'reason' => 'required|string|min:10|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'reason.required' => 'É obrigatório indicar o motivo de rejeição.',
            'reason.min'      => 'O motivo deve ter pelo menos 10 caracteres.',
            'reason.max'      => 'O motivo não pode exceder 500 caracteres.',
        ];
    }
}
