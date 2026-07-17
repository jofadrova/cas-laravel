<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreRefinanciamientoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nuevo_monto' => ['required', 'numeric', 'decimal:0,2', 'gt:0'],
            'plazo' => ['required', 'integer', 'gt:0'],
            'tipo_cambio' => ['required', 'numeric', 'decimal:0,5', 'gt:0'],
            'fechaPrestamo' => ['required', 'date'],
            'asiento' => ['required', 'string', 'max:20'],
            'motivo' => ['required', 'string', 'max:255'],
            'id_garante1' => ['required', 'integer', 'exists:socios,id'],
            'id_garante2' => ['required', 'integer', 'different:id_garante1', 'exists:socios,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'nuevo_monto.required' => 'Debe ingresar el nuevo monto del préstamo.',
            'nuevo_monto.numeric' => 'El nuevo monto debe ser un valor válido.',
            'nuevo_monto.decimal' => 'El nuevo monto debe tener como máximo dos decimales.',
            'nuevo_monto.gt' => 'El nuevo monto debe ser mayor a cero.',
            'plazo.required' => 'Debe ingresar el nuevo plazo.',
            'plazo.integer' => 'El plazo debe ser un número entero.',
            'plazo.gt' => 'El plazo debe ser mayor a cero.',
            'tipo_cambio.required' => 'Debe ingresar el tipo de cambio.',
            'tipo_cambio.numeric' => 'El tipo de cambio debe ser válido.',
            'tipo_cambio.decimal' => 'El tipo de cambio debe tener como máximo cinco decimales.',
            'tipo_cambio.gt' => 'El tipo de cambio debe ser mayor a cero.',
            'fechaPrestamo.required' => 'Debe seleccionar la fecha del refinanciamiento.',
            'fechaPrestamo.date' => 'La fecha del refinanciamiento no es válida.',
            'asiento.required' => 'Debe ingresar el número de asiento.',
            'asiento.max' => 'El número de asiento no puede superar los 20 caracteres.',
            'motivo.required' => 'Debe ingresar el motivo del refinanciamiento.',
            'motivo.max' => 'El motivo no puede superar los 255 caracteres.',
            'id_garante1.required' => 'Debe seleccionar el primer garante mediante su código de papeleta.',
            'id_garante1.exists' => 'El primer garante seleccionado no es válido.',
            'id_garante2.required' => 'Debe seleccionar el segundo garante mediante su código de papeleta.',
            'id_garante2.different' => 'Los garantes deben ser personas diferentes.',
            'id_garante2.exists' => 'El segundo garante seleccionado no es válido.',
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
                    $validator->errors()->add('nuevo_monto', 'Solo se puede refinanciar un préstamo activo.');
                    return;
                }

                if ((int) $prestamo->tipo_prestamo !== 1) {
                    $validator->errors()->add('nuevo_monto', 'Solo los préstamos Regulares pueden refinanciarse.');
                    return;
                }

                if ((int) $this->input('id_garante1') === (int) $prestamo->ide_per) {
                    $validator->errors()->add('id_garante1', 'El solicitante no puede ser su propio garante.');
                }

                if ((int) $this->input('id_garante2') === (int) $prestamo->ide_per) {
                    $validator->errors()->add('id_garante2', 'El solicitante no puede ser su propio garante.');
                }

                $nuevoMonto = round((float) $this->input('nuevo_monto'), 2);
                $saldoActual = round((float) $prestamo->saldo_actual, 2);

                if ($nuevoMonto <= $saldoActual) {
                    $validator->errors()->add(
                        'nuevo_monto',
                        'El nuevo monto debe ser mayor al saldo actual para generar un desembolso.'
                    );
                }

                if ($nuevoMonto > (float) $prestamo->tipo->monto_max) {
                    $validator->errors()->add('nuevo_monto', 'El monto supera el máximo permitido para el préstamo Regular.');
                }

                if ((int) $this->input('plazo') > (int) $prestamo->tipo->plazo_max) {
                    $validator->errors()->add('plazo', 'El plazo supera el máximo permitido para el préstamo Regular.');
                }

                if ((float) $this->input('tipo_cambio') <= 0) {
                    $validator->errors()->add('tipo_cambio', 'Debe ingresar un tipo de cambio válido.');
                }
            },
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'asiento' => trim((string) $this->input('asiento')),
            'motivo' => trim((string) $this->input('motivo')),
        ]);
    }
}
