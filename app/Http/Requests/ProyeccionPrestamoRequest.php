<?php

namespace App\Http\Requests;

use App\Models\Tasa;
use Illuminate\Foundation\Http\FormRequest;

class ProyeccionPrestamoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tipo_prestamo' => ['required', 'integer', 'exists:tasa,id_tasa'],
            'monto' => ['required', 'numeric', 'decimal:0,2', 'gt:0'],
            'plazo' => ['required', 'integer', 'gt:0'],
            'fecha' => ['required', 'date'],
            'tipo_cambio' => ['nullable', 'numeric', 'gt:0'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $tipo = Tasa::find($this->integer('tipo_prestamo'));

            if (!$tipo) {
                return;
            }

            if ($tipo->estado !== 'AC') {
                $validator->errors()->add('tipo_prestamo', 'El tipo de préstamo seleccionado no está activo.');
            }

            if ($this->filled('monto') && (float) $this->input('monto') > (float) $tipo->monto_max) {
                $validator->errors()->add(
                    'monto',
                    'El monto supera el máximo permitido de '.number_format((float) $tipo->monto_max, 2).'.'
                );
            }

            if ($this->filled('plazo') && $this->integer('plazo') > (int) $tipo->plazo_max) {
                $validator->errors()->add(
                    'plazo',
                    'El plazo supera el máximo permitido de '.$tipo->plazo_max.' meses.'
                );
            }

            if ($tipo->tipo_moneda === 'SU' && !$this->filled('tipo_cambio')) {
                $validator->errors()->add('tipo_cambio', 'Debe ingresar el tipo de cambio para préstamos en dólares.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'tipo_prestamo.required' => 'Debe seleccionar un tipo de préstamo.',
            'tipo_prestamo.exists' => 'El tipo de préstamo seleccionado no es válido.',
            'monto.required' => 'Debe ingresar el monto que desea proyectar.',
            'monto.numeric' => 'El monto debe ser un valor numérico.',
            'monto.decimal' => 'El monto admite hasta dos decimales.',
            'monto.gt' => 'El monto debe ser mayor a cero.',
            'plazo.required' => 'Debe ingresar el plazo de la proyección.',
            'plazo.integer' => 'El plazo debe expresarse en meses enteros.',
            'plazo.gt' => 'El plazo debe ser mayor a cero.',
            'fecha.required' => 'Debe seleccionar la fecha de la proyección.',
            'fecha.date' => 'La fecha ingresada no es válida.',
            'tipo_cambio.numeric' => 'El tipo de cambio debe ser un valor numérico.',
            'tipo_cambio.gt' => 'El tipo de cambio debe ser mayor a cero.',
        ];
    }
}
