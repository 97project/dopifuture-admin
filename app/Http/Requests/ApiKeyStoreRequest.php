<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApiKeyStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'abilities' => ['nullable', 'array'],
            'abilities.*' => ['string'],
            'ip_restrictions' => ['nullable', 'array'],
            'ip_restrictions.*' => ['ip'],
            'expires_at' => ['nullable', 'date', 'after:now'],
        ];
    }
}
