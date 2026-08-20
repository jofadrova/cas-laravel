<?php

namespace App\Http\Requests;

use App\Models\EnvioMensual;
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
            'envio_mensual_id' => [
                'required',
                'integer',
                Rule::exists('envios_mensuales', 'id')->where(
                    fn ($query) => $query->where(
                        'estado',
                        EnvioMensual::ESTADO_ENVIADO
                    )
                ),
                Rule::unique('lotes_mensuales', 'envio_mensual_id'),
            ],
            'fecha_recepcion' => [
                'required',
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
            'envio_mensual_id.required' => 'Debe seleccionar el lote enviado que está recibiendo.',
            'envio_mensual_id.exists' => 'El lote seleccionado no existe o aún no fue enviado.',
            'envio_mensual_id.unique' => 'El lote enviado ya tiene una recepción registrada.',
            'fecha_recepcion.required' => 'Debe registrar la fecha de recepción.',
            'fecha_recepcion.date' => 'La fecha de recepción no es válida.',
            'observaciones.max' => 'Las observaciones no deben superar los 2000 caracteres.',
        ];
    }

    public function attributes(): array
    {
        return [
            'envio_mensual_id' => 'lote enviado',
            'fecha_recepcion' => 'fecha de recepción',
            'observaciones' => 'observaciones',
        ];
    }
}
