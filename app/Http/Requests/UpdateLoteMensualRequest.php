<?php

namespace App\Http\Requests;

use App\Models\LoteMensual;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateLoteMensualRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var LoteMensual|null $lote */
        $lote = $this->route('lote');

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
                    )
                    ->ignore($lote?->getKey()),
            ],
            'gestion' => [
                'required',
                'integer',
                'digits:4',
                'min:2000',
                'max:' . (now()->year + 1),
            ],
            'tipo_cambio' => [
                'required',
                'numeric',
                'gt:0',
                'decimal:0,5',
                'max:99999.99999',
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

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                /** @var LoteMensual|null $lote */
                $lote = $this->route('lote');

                if ($lote && ! $lote->puedeEditar()) {
                    $validator->errors()->add(
                        'lote',
                        'El lote no puede editarse porque se encuentra '
                        . strtolower($lote->estado) . '.'
                    );
                }
            },
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
            'tipo_cambio.required' => 'Debe ingresar el tipo de cambio del lote.',
            'tipo_cambio.numeric' => 'El tipo de cambio debe ser un valor numérico.',
            'tipo_cambio.gt' => 'El tipo de cambio debe ser mayor que cero.',
            'tipo_cambio.decimal' => 'El tipo de cambio puede tener hasta cinco decimales.',
            'tipo_cambio.max' => 'El tipo de cambio ingresado excede el valor permitido.',
            'fecha_recepcion.date' => 'La fecha de recepción no es válida.',
            'observaciones.max' => 'Las observaciones no deben superar los 2000 caracteres.',
        ];
    }

    public function attributes(): array
    {
        return [
            'mes' => 'mes',
            'gestion' => 'gestión',
            'tipo_cambio' => 'tipo de cambio',
            'fecha_recepcion' => 'fecha de recepción',
            'observaciones' => 'observaciones',
        ];
    }
}
