<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->is_admin;
    }

    public function rules(): array
    {
        return [
            'rate'      => 'required|numeric|min:0.01|max:9999999',
            'is_active' => 'required|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'rate.required' => 'Introduz a nova taxa de câmbio.',
            'rate.numeric'  => 'A taxa deve ser um valor numérico.',
            'rate.min'      => 'A taxa deve ser superior a 0.',
            'is_active.required' => 'Indica se a taxa está activa.',
        ];
    }
}
