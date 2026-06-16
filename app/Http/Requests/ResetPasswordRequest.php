<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
{
    protected $errorBag = 'resetPassword';

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'password' => 'required|min:8|confirmed',
        ];
    }

    public function messages(): array
    {
        return [
            'password.min' =>
                'La contraseña debe tener mínimo 8 caracteres.',

            'password.confirmed' =>
                'Las contraseñas no coinciden.',
        ];
    }

    protected function prepareForValidation(): void
    {
        session([
            'reset_password_user' => [
                'id' => $this->route('usuario')->id,
                'username' => $this->route('usuario')->username,
            ]
        ]);
    }
}
