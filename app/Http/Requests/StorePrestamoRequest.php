<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePrestamoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Reglas de validación.
     */
    public function rules(): array
    {
        return [

            'id_socio' => [
                'required',
                'exists:socios,id',
            ],

            'tipo_prestamo' => [
                'required',
                'exists:tasa,id_tasa',
            ],

            'id_garante1' => [
                'nullable',
                'exists:socios,id',
            ],

            'id_garante2' => [
                'nullable',
                'exists:socios,id',
            ],

            'monto' => [
                'required',
                'numeric',
                'decimal:0,2',
                'gt:0',
            ],

            'plazo' => [
                'required',
                'integer',
                'gt:0',
            ],

            'asiento' => [
                'required',
                'string',
            ],

            'fechaPrestamo' => [
                'required',
                'date',
            ],

            'motivo' => [
                'required',
                'string',
            ],
            'cronograma' => [
                'required',
                'json',
            ],
            'tipo_cambio' => [
                'required',
                'numeric',
                'gt:0',
            ],

        ];
    }

    /**
     * Mensajes personalizados.
     */
    public function messages(): array
    {
        return [

            'id_socio.required'        => 'Debe seleccionar un socio.',
            'id_socio.exists'          => 'El socio seleccionado no es válido.',

            'tipo_prestamo.required'  => 'Debe seleccionar un tipo de préstamo.',
            'tipo_prestamo.exists'    => 'El tipo de préstamo seleccionado no es válido.',

            'id_garante1.exists'      => 'El primer garante seleccionado no es válido.',
            'id_garante2.exists'      => 'El segundo garante seleccionado no es válido.',

            'monto.required'          => 'Debe ingresar el monto del préstamo.',
            'monto.numeric'           => 'El monto debe ser un valor numérico.',
            'monto.gt'                => 'El monto debe ser mayor a cero.',

            'plazo.required'          => 'Debe ingresar el plazo del préstamo.',
            'plazo.integer'           => 'El plazo debe ser un número entero.',
            'plazo.gt'                => 'El plazo debe ser mayor a cero.',

            'asiento.required'        => 'Debe ingresar el número de asiento.',
            'asiento.max'             => 'El número de asiento no puede exceder los 20 caracteres.',

            'fechaPrestamo.required'  => 'Debe seleccionar la fecha del préstamo.',
            'fecha.date'              => 'La fecha del préstamo no es válida.',

            'motivo.required'              => 'El motivo no puede exceder los 255 caracteres.',
            'tipo_cambio.required'        => 'Debe introducir el Tipo de Cambio del Comprobante.',

        ];
    }

    /**
     * Nombres amigables de los campos.
     */
    public function attributes(): array
    {
        return [
            'id_socio'       => 'socio',
            'tipo_prestamo'  => 'tipo de préstamo',
            'id_garante1'    => 'garante 1',
            'id_garante2'    => 'garante 2',
            'monto'          => 'monto',
            'plazo'          => 'plazo',
            'asiento'        => 'asiento',
            'fecha'          => 'fecha del préstamo',
            'motivo'         => 'motivo',
            'tipo_cambio'    => 'tipo_cambio'
        ];
    }
}
