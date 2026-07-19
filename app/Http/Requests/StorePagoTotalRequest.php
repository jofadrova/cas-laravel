<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StorePagoTotalRequest extends FormRequest
{
    protected $errorBag = 'pagoTotal';

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'monto_efectivo_total' => ['required', 'numeric', 'decimal:0,2', 'gt:0'],
            'fecha_deposito_total' => ['required', 'date', 'before_or_equal:today'],
            'nop_total' => ['required', 'string', 'max:15', 'regex:/^[0-9]+$/'],
            'tipo_cambio_total' => ['nullable', 'numeric', 'decimal:0,5', 'gt:0'],
            'glosa_total' => ['required', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'monto_efectivo_total.required' => 'El pago efectivo es obligatorio.',
            'monto_efectivo_total.numeric' => 'El pago efectivo debe ser un monto válido.',
            'monto_efectivo_total.decimal' => 'El pago efectivo debe tener como máximo dos decimales.',
            'monto_efectivo_total.gt' => 'El pago efectivo debe ser mayor a cero.',
            'fecha_deposito_total.required' => 'La fecha de depósito es obligatoria.',
            'fecha_deposito_total.date' => 'La fecha de depósito no es válida.',
            'fecha_deposito_total.before_or_equal' => 'La fecha de depósito no puede ser posterior a la fecha actual.',
            'nop_total.required' => 'El NOP es obligatorio.',
            'nop_total.max' => 'El NOP no puede superar los 15 caracteres.',
            'nop_total.regex' => 'El NOP solamente puede contener números.',
            'tipo_cambio_total.numeric' => 'El tipo de cambio debe ser un valor válido.',
            'tipo_cambio_total.decimal' => 'El tipo de cambio debe tener como máximo cinco decimales.',
            'tipo_cambio_total.gt' => 'El tipo de cambio debe ser mayor a cero.',
            'glosa_total.required' => 'La glosa es obligatoria.',
            'glosa_total.string' => 'La glosa debe ser un texto válido.',
            'glosa_total.max' => 'La glosa no puede superar los 500 caracteres.',
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

                if ($prestamo->estado !== 'AC' || (float) $prestamo->saldo_actual <= 0) {
                    $validator->errors()->add(
                        'monto_efectivo_total',
                        'El préstamo ya no tiene un saldo pendiente para cancelar.'
                    );

                    return;
                }

                if (! $prestamo->cuotas()->where('estado', 'PE')->exists()) {
                    $validator->errors()->add(
                        'monto_efectivo_total',
                        'El préstamo no tiene cuotas pendientes para cancelar.'
                    );

                    return;
                }

                $esDolares = $prestamo->tipo->tipo_moneda === 'SU';
                $tipoCambio = (float) $this->input('tipo_cambio_total');

                if ($esDolares && $tipoCambio <= 0) {
                    $validator->errors()->add(
                        'tipo_cambio_total',
                        'Debe ingresar un tipo de cambio válido para el pago total.'
                    );

                    return;
                }

                $saldo = round((float) $prestamo->saldo_actual, 2);
                $totalExigido = $esDolares
                    ? round($saldo * $tipoCambio, 2)
                    : $saldo;
                $montoEfectivo = round((float) $this->input('monto_efectivo_total'), 2);

                if ($montoEfectivo < $totalExigido) {
                    $validator->errors()->add(
                        'monto_efectivo_total',
                        $esDolares
                            ? 'El pago efectivo en bolivianos no cubre el saldo total convertido a bolivianos.'
                            : 'El pago efectivo no puede ser menor al saldo total del préstamo.'
                    );
                }
            },
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('glosa_total')) {
            $this->merge([
                'glosa_total' => trim((string) $this->input('glosa_total')),
            ]);
        }
    }
}
