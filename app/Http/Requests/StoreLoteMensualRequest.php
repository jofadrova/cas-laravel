<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLoteMensualRequest extends FormRequest
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
                Rule::unique('lotes_mensuales', 'mes')
                    ->where(
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
            'fecha_recepcion' => [
                'nullable',
                'date',
            ],
            'observaciones' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'mes.required' => 'Debe seleccionar el mes del lote.',
            'mes.integer' => 'El mes seleccionado no es válido.',
            'mes.between' => 'El mes debe estar comprendido entre enero y diciembre.',
            'mes.unique' => 'Ya existe un lote para el mes y la gestión seleccionados.',
            'gestion.required' => 'Debe ingresar la gestión del lote.',
            'gestion.integer' => 'La gestión debe ser un número entero.',
            'gestion.digits' => 'La gestión debe tener cuatro dígitos.',
            'gestion.min' => 'La gestión ingresada no es válida.',
            'gestion.max' => 'La gestión no puede ser posterior al próximo año.',
            'fecha_recepcion.date' => 'La fecha de recepción no es válida.',
            'observaciones.max' => 'Las observaciones no deben superar los 2000 caracteres.',
        ];
    }

    public function attributes(): array
    {
        return [
            'mes' => 'mes',
            'gestion' => 'gestión',
            'fecha_recepcion' => 'fecha de recepción',
            'observaciones' => 'observaciones',
        ];
    }
}