<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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

    public function messages(): array
    {
        return [
            'message_text.max'  => 'A mensagem não pode exceder 2000 caracteres.',
            'attachment.file'   => 'O ficheiro anexado é inválido.',
            'attachment.mimes'  => 'Formatos permitidos: PDF, JPG, PNG, WEBP.',
            'attachment.max'    => 'O ficheiro não pode exceder 5 MB.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (!$this->filled('message_text') && !$this->hasFile('attachment')) {
                $validator->errors()->add('message_text', 'Escreve uma mensagem ou anexa um ficheiro.');
            }
        });
    }
}
