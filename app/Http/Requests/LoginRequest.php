<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:6'],
        ];

        if (\App\Models\Setting::getValue('security', 'recaptcha_enabled', false)) {
            $rules['recaptcha_token'] = ['required', 'string'];
        }

        return $rules;
    }
}
