<?php

namespace App\Http\Requests;

use App\Models\ContaCuenta;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ContaCuentaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var ContaCuenta|null $cuenta */
        $cuenta = $this->route('cuenta');

        return [
            'cuenta_padre_id' => [
                'bail', 'nullable', 'integer',
                Rule::exists('conta_cuentas', 'id')->where('estado', true),
                Rule::notIn(array_filter([$cuenta?->id])),
            ],
            'codigo' => ['bail', 'required', 'string', 'min:1', 'max:30', 'regex:/^[0-9A-Z][0-9A-Z.\-]*$/', Rule::unique('conta_cuentas', 'codigo')->ignore($cuenta?->id)],
            'nombre' => ['bail', 'required', 'string', 'min:3', 'max:180'],
            'tipo' => ['bail', 'required', Rule::in(['ACTIVO', 'PASIVO', 'PATRIMONIO', 'INGRESO', 'GASTO', 'ORDEN'])],
            'naturaleza' => ['bail', 'required', Rule::in(['D', 'A'])],
            'moneda' => ['bail', 'required', Rule::in(['B', 'U', 'M'])],
            'acepta_movimientos' => ['nullable', 'boolean'],
            'estado' => ['nullable', 'boolean'],
            'vigente_desde' => [
                'bail', 'nullable', 'required_with:vigente_hasta', 'date_format:Y-m-d',
                Rule::when($this->filled('vigente_hasta'), 'before_or_equal:vigente_hasta'),
            ],
            'vigente_hasta' => [
                'bail', 'nullable', 'date_format:Y-m-d',
                Rule::when($this->filled('vigente_desde'), 'after_or_equal:vigente_desde'),
            ],
            'referencia_normativa' => ['bail', 'nullable', 'string', 'min:5', 'max:255'],
            'descripcion' => ['bail', 'nullable', 'string', 'min:10', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'El campo :attribute es obligatorio.',
            'required_with' => 'El campo :attribute es obligatorio cuando se indica :values.',
            'string' => 'El campo :attribute debe ser un texto válido.',
            'integer' => 'El campo :attribute debe ser un número entero válido.',
            'boolean' => 'El valor seleccionado para :attribute no es válido.',
            'min.string' => 'El campo :attribute debe tener al menos :min caracteres.',
            'max.string' => 'El campo :attribute no debe superar los :max caracteres.',
            'exists' => 'La cuenta superior seleccionada no existe o no está activa.',
            'unique' => 'El código ingresado ya está registrado.',
            'in' => 'El valor seleccionado para :attribute no es válido.',
            'regex' => 'El código solo puede contener números, letras mayúsculas, puntos y guiones, y debe comenzar con una letra o número.',
            'date_format' => 'El campo :attribute debe contener una fecha válida.',
            'before_or_equal' => 'La fecha :attribute debe ser anterior o igual a :date.',
            'after_or_equal' => 'La fecha :attribute debe ser posterior o igual a :date.',
            'not_in' => 'Una cuenta no puede depender de sí misma.',
        ];
    }

    public function attributes(): array
    {
        return [
            'cuenta_padre_id' => 'cuenta superior',
            'codigo' => 'código',
            'nombre' => 'nombre de la cuenta',
            'tipo' => 'rubro',
            'naturaleza' => 'naturaleza',
            'moneda' => 'moneda',
            'acepta_movimientos' => 'recibe movimientos',
            'estado' => 'estado',
            'vigente_desde' => 'vigente desde',
            'vigente_hasta' => 'vigente hasta',
            'referencia_normativa' => 'referencia normativa',
            'descripcion' => 'descripción y reglas de uso',
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator) {
            $padreId = $this->integer('cuenta_padre_id') ?: null;
            /** @var ContaCuenta|null $cuenta */
            $cuenta = $this->route('cuenta');

            if ($padreId && ContaCuenta::find($padreId)?->acepta_movimientos) {
                $validator->errors()->add('cuenta_padre_id', 'Una cuenta de movimiento no puede tener cuentas dependientes.');
            }

            if ($cuenta && $padreId && $this->esDescendiente($cuenta, $padreId)) {
                $validator->errors()->add('cuenta_padre_id', 'La cuenta padre no puede ser una descendiente de la cuenta editada.');
            }

            if ($cuenta && $this->boolean('acepta_movimientos') && $cuenta->hijas()->exists()) {
                $validator->errors()->add('acepta_movimientos', 'Una cuenta con dependientes no puede recibir movimientos.');
            }
        }];
    }

    private function esDescendiente(ContaCuenta $cuenta, int $posiblePadreId): bool
    {
        $actual = ContaCuenta::find($posiblePadreId);
        while ($actual) {
            if ($actual->id === $cuenta->id) {
                return true;
            }
            $actual = $actual->padre;
        }
        return false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'codigo' => strtoupper(trim((string) $this->codigo)),
            'nombre' => trim((string) $this->nombre),
            'referencia_normativa' => $this->textoOpcional('referencia_normativa'),
            'descripcion' => $this->textoOpcional('descripcion'),
            'acepta_movimientos' => $this->boolean('acepta_movimientos'),
            'estado' => $this->has('estado') ? $this->boolean('estado') : true,
        ]);
    }

    private function textoOpcional(string $campo): ?string
    {
        $valor = trim((string) $this->input($campo));

        return $valor === '' ? null : $valor;
    }
}
