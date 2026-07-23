<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreReprogramacionPrestamoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cuotas_pendientes_nuevas' => ['required', 'integer', 'gt:0'],
            'autorizacion' => ['required', 'string', 'max:1000'],
            'observaciones' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'cuotas_pendientes_nuevas.required' => 'Debe ingresar la nueva cantidad de cuotas pendientes.',
            'cuotas_pendientes_nuevas.integer' => 'La cantidad de cuotas debe ser un número entero.',
            'cuotas_pendientes_nuevas.gt' => 'La cantidad de cuotas debe ser mayor a cero.',
            'autorizacion.required' => 'La autorización de Cartera es obligatoria.',
            'autorizacion.max' => 'La autorización no puede superar los 1000 caracteres.',
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
                $pendientes = $prestamo->cuotas()
                    ->where('estado', 'PE')
                    ->count();
                $pagadasHasta = (int) $prestamo->cuotas()
                    ->where('estado', 'AC')
                    ->max('nro_cuota');
                $nuevas = (int) $this->input('cuotas_pendientes_nuevas');

                if ($prestamo->estado !== 'AC' || (float) $prestamo->saldo_actual <= 0) {
                    $validator->errors()->add(
                        'cuotas_pendientes_nuevas',
                        'Solo se puede reprogramar un préstamo activo con saldo pendiente.'
                    );
                    return;
                }

                if ($pendientes === 0) {
                    $validator->errors()->add(
                        'cuotas_pendientes_nuevas',
                        'El préstamo no tiene cuotas pendientes para reprogramar.'
                    );
                    return;
                }

                if ($nuevas <= $pendientes) {
                    $validator->errors()->add(
                        'cuotas_pendientes_nuevas',
                        "La nueva cantidad debe ser mayor a las {$pendientes} cuotas pendientes actuales."
                    );
                }

                if ($pagadasHasta + $nuevas > (int) $prestamo->tipo->plazo_max) {
                    $validator->errors()->add(
                        'cuotas_pendientes_nuevas',
                        'El nuevo plazo total supera el máximo permitido para este tipo de préstamo.'
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
