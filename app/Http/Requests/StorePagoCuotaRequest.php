<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StorePagoCuotaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cuotas' => ['required', 'array', 'min:1'],
            'cuotas.*' => ['required', 'integer', 'distinct'],
            'monto_efectivo' => ['required', 'numeric', 'decimal:0,2', 'gt:0'],
            'fecha_deposito' => ['required', 'date', 'before_or_equal:today'],
            'nop' => ['required', 'string', 'max:15', 'regex:/^[0-9]+$/'],
            'tipo_cambio' => ['nullable', 'numeric', 'decimal:0,5', 'gt:0'],
            'glosa' => ['required', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'cuotas.required' => 'Debe seleccionar al menos una cuota.',
            'cuotas.array' => 'Las cuotas seleccionadas no son válidas.',
            'cuotas.min' => 'Debe seleccionar al menos una cuota.',
            'cuotas.*.required' => 'La cuota seleccionada no es válida.',
            'cuotas.*.integer' => 'La cuota seleccionada no es válida.',
            'cuotas.*.distinct' => 'No se puede registrar una cuota más de una vez.',
            'monto_efectivo.required' => 'El pago efectivo es obligatorio.',
            'monto_efectivo.numeric' => 'El pago efectivo debe ser un monto válido.',
            'monto_efectivo.decimal' => 'El pago efectivo debe tener como máximo dos decimales.',
            'monto_efectivo.gt' => 'El pago efectivo debe ser mayor a cero.',
            'fecha_deposito.required' => 'La fecha de depósito es obligatoria.',
            'fecha_deposito.date' => 'La fecha de depósito no es válida.',
            'fecha_deposito.before_or_equal' => 'La fecha de depósito no puede ser posterior a la fecha actual.',
            'nop.required' => 'El NOP es obligatorio.',
            'nop.max' => 'El NOP no puede superar los 15 caracteres.',
            'nop.regex' => 'El NOP solamente puede contener números.',
            'tipo_cambio.numeric' => 'El tipo de cambio debe ser un valor válido.',
            'tipo_cambio.decimal' => 'El tipo de cambio debe tener como máximo cinco decimales.',
            'tipo_cambio.gt' => 'El tipo de cambio debe ser mayor a cero.',
            'glosa.required' => 'La glosa es obligatoria.',
            'glosa.string' => 'La glosa debe ser un texto válido.',
            'glosa.max' => 'La glosa no puede superar los 500 caracteres.',
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
                $idsCuotas = $this->input('cuotas', []);
                $cuotas = $prestamo->cuotas()
                    ->whereIn('id', $idsCuotas)
                    ->get();

                if ($cuotas->count() !== count($idsCuotas)) {
                    $validator->errors()->add(
                        'cuotas',
                        'Una o más cuotas seleccionadas no pertenecen al préstamo.'
                    );

                    return;
                }

                if ($cuotas->contains(fn ($cuota) => $cuota->estado !== 'PE')) {
                    $validator->errors()->add(
                        'cuotas',
                        'Una o más cuotas seleccionadas ya fueron pagadas.'
                    );
                }

                $totalCuotas = round((float) $cuotas->sum('cuota_fija'), 2);
                $montoEfectivo = round((float) $this->input('monto_efectivo'), 2);

                $esDolares = $prestamo->tipo->tipo_moneda === 'SU';
                $tipoCambio = (float) $this->input('tipo_cambio');

                if ($esDolares && $tipoCambio <= 0) {
                    $validator->errors()->add(
                        'monto_efectivo',
                        'El préstamo en dólares no tiene un tipo de cambio válido registrado.'
                    );

                    return;
                }

                $totalExigidoEnBolivianos = $esDolares
                    ? round($totalCuotas * $tipoCambio, 2)
                    : $totalCuotas;

                if ($montoEfectivo < $totalExigidoEnBolivianos) {
                    $validator->errors()->add(
                        'monto_efectivo',
                        $esDolares
                            ? 'El pago efectivo en bolivianos no cubre el equivalente de las cuotas seleccionadas en dólares.'
                            : 'El pago efectivo no puede ser menor al total de las cuotas seleccionadas.'
                    );
                }
            },
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('glosa')) {
            $this->merge([
                'glosa' => trim((string) $this->input('glosa')),
            ]);
        }
    }
}
