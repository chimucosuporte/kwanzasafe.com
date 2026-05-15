<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'moeda'        => 'required|exists:exchange_rates,id',
            'valor_enviar' => 'required|numeric|min:10|max:50000',
        ];
    }

    public function messages(): array
    {
        return [
            'moeda.required'        => 'Selecciona a moeda de envio.',
            'moeda.exists'          => 'Moeda inválida.',
            'valor_enviar.required' => 'Introduz o valor a enviar.',
            'valor_enviar.numeric'  => 'O valor deve ser um número.',
            'valor_enviar.min'      => 'O valor mínimo de envio é 10.',
            'valor_enviar.max'      => 'O valor máximo por transação é 50.000.',
        ];
    }
}
