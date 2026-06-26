<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Upload de comprovativo de pagamento via API mobile.
 */
class UploadReceiptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'comprovativo' => 'required|file|mimes:pdf,jpg,jpeg,png,webp|max:5120',
        ];
    }

    public function messages(): array
    {
        return [
            'comprovativo.required' => 'Anexa o comprovativo de pagamento.',
            'comprovativo.mimes'    => 'Usa PDF, JPG, PNG ou WEBP.',
            'comprovativo.max'      => 'O ficheiro não pode exceder 5 MB.',
        ];
    }
}
