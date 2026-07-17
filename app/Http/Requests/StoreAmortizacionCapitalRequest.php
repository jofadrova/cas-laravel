<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreAmortizacionCapitalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tipo_recalculo' => ['required', Rule::in(['CUOTA', 'PLAZO'])],
            'monto_efectivo' => ['required', 'numeric', 'decimal:0,2', 'gt:0'],
            'tipo_cambio' => ['nullable', 'numeric', 'decimal:0,5', 'gt:0'],
            'autorizacion' => ['required', 'string', 'max:1000'],
            'observaciones' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'tipo_recalculo.required' => 'Debe seleccionar el tipo de recálculo.',
            'tipo_recalculo.in' => 'El tipo de recálculo seleccionado no es válido.',
            'monto_efectivo.required' => 'Debe ingresar el monto a amortizar.',
            'monto_efectivo.numeric' => 'El monto a amortizar debe ser válido.',
            'monto_efectivo.decimal' => 'El monto debe tener como máximo dos decimales.',
            'monto_efectivo.gt' => 'El monto debe ser mayor a cero.',
            'tipo_cambio.numeric' => 'El tipo de cambio debe ser válido.',
            'tipo_cambio.decimal' => 'El tipo de cambio debe tener como máximo cinco decimales.',
            'tipo_cambio.gt' => 'El tipo de cambio debe ser mayor a cero.',
            'autorizacion.required' => 'La autorización de Cartera es obligatoria.',
            'autorizacion.string' => 'La autorización debe ser un texto válido.',
            'autorizacion.max' => 'La autorización no puede superar los 1000 caracteres.',
            'observaciones.string' => 'Las observaciones deben ser un texto válido.',
            'observaciones.max' => 'Las observaciones no pueden superar los 1000 caracteres.',
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $prestamo = $this->route('prestamo');

                if ($prestamo->estado !== 'AC') {
                    $validator->errors()->add('monto_efectivo', 'Solo se puede amortizar un préstamo activo.');
                    return;
                }

                if (! $prestamo->cuotas()->where('estado', 'PE')->exists()) {
                    $validator->errors()->add('monto_efectivo', 'El préstamo no tiene cuotas pendientes.');
                    return;
                }

                $esDolares = $prestamo->tipo->tipo_moneda === 'SU';
                $tipoCambio = (float) $this->input('tipo_cambio');

                if ($esDolares && $tipoCambio <= 0) {
                    $validator->errors()->add('tipo_cambio', 'Debe ingresar un tipo de cambio válido.');
                    return;
                }

                $montoCapital = $esDolares
                    ? round((float) $this->input('monto_efectivo') / $tipoCambio, 2)
                    : round((float) $this->input('monto_efectivo'), 2);

                if ($montoCapital >= round((float) $prestamo->saldo_actual, 2)) {
                    $validator->errors()->add(
                        'monto_efectivo',
                        'La amortización debe ser menor al saldo actual. Para cancelar todo el saldo utilice Pago Total.'
                    );
                }
            },
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'autorizacion' => trim((string) $this->input('autorizacion')),
            'observaciones' => trim((string) $this->input('observaciones')),
        ]);
    }
}
