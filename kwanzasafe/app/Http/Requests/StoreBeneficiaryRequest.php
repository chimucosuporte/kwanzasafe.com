<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBeneficiaryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'bank_name'   => 'required|string|max:100',
            'iban'        => 'required|string|min:10|max:34',
            'holder_name' => 'required|string|max:150',
        ];
    }

    public function messages(): array
    {
        return [
            'bank_name.required'   => 'Indica o nome do banco.',
            'iban.required'        => 'Introduz o IBAN da conta.',
            'iban.min'             => 'O IBAN deve ter pelo menos 10 caracteres.',
            'iban.max'             => 'O IBAN não pode exceder 34 caracteres.',
            'holder_name.required' => 'Indica o nome do titular da conta.',
        ];
    }
}
