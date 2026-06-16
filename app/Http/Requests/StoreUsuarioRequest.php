<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreUsuarioRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [

            'username' => 'required|min:4|max:30|unique:users,username',

            'name' => 'required|min:5|max:255',

            'email' => 'required|email|unique:users,email',

            'estado' => 'required|in:ACTIVO,INACTIVO',

            'password' => 'required|min:8|confirmed',

        ];
    }

    public function messages(): array
    {
        return [

            'username.required' =>
                'Debe ingresar un usuario.',

            'username.unique' =>
                'El usuario ya existe.',

            'name.required' =>
                'Debe ingresar el nombre.',

            'name.min' =>
                'El nombre debe tener al menos 5 caracteres.',

            'email.required' =>
                'Debe ingresar un correo electrónico.',

            'email.email' =>
                'El correo no tiene un formato válido.',

            'email.unique' =>
                'El correo ya se encuentra registrado.',

            'password.min' =>
                'La contraseña debe tener mínimo 8 caracteres.',

            'password.confirmed' =>
                'Las contraseñas no coinciden.',

        ];
    }
}
