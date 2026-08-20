<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEnvioMensualRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'mes' => [
                'required',
                'integer',
                'between:1,12',
                Rule::unique('envios_mensuales', 'mes')->where(
                    fn ($query) => $query->where(
                        'gestion',
                        $this->integer('gestion')
                    )
                ),
            ],
            'gestion' => [
                'required',
                'integer',
                'digits:4',
                'min:2000',
                'max:' . (now()->year + 1),
            ],
            'observaciones' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'mes.required' => 'Debe seleccionar el mes del envío.',
            'mes.between' => 'El mes seleccionado no es válido.',
            'mes.unique' => 'Ya existe un lote de envío para ese periodo.',
            'gestion.required' => 'Debe ingresar la gestión del envío.',
            'gestion.digits' => 'La gestión debe tener cuatro dígitos.',
            'gestion.max' => 'La gestión no puede ser posterior al próximo año.',
            'observaciones.max' => 'Las observaciones no deben superar los 2000 caracteres.',
        ];
    }
}
