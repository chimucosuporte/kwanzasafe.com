<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Envio de mensagem (texto e/ou anexo) na sala de transação, via API mobile.
 * Espelha a validação de ChatController@sendMessage (web).
 */
class SendChatMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'message_text' => 'nullable|string|max:2000',
            'attachment'   => 'nullable|file|mimes:pdf,jpg,jpeg,png,webp|max:5120',
        ];
    }

    /**
     * Garante que há pelo menos texto OU anexo.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if (! $this->filled('message_text') && ! $this->hasFile('attachment')) {
                $validator->errors()->add('message_text', 'Escreve uma mensagem ou anexa um ficheiro.');
            }
        });
    }
}
