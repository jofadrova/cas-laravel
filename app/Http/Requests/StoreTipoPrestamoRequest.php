<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreTipoPrestamoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'descripcion_tasa' => 'required|string|max:25',
            'tipo_moneda' => 'required|in:BS,SU',
            'estado' => 'required|in:AC,IN',
            'porcentaje' => 'required|numeric|min:0|max:100',
            'monto_max' => 'required|numeric|min:0',
            'plazo_max' => 'required|integer|min:1|max:360',
            'garante' => 'required|integer|min:0|max:5',
            'itf' => 'required|numeric|min:0',
            'int_penal' => 'required|numeric|min:0',
            'papeleria' => 'required|numeric|min:0',
            'min_defensa' => 'required|numeric|min:0',
            'obs' => 'nullable|string|max:200',
        ];
    }
    public function messages(): array
    {
        return [
            'descripcion_tasa.required' => 'Debe ingresar la descripción.',
            'tipo_moneda.required' => 'Debe seleccionar la moneda.',
            'porcentaje.required' => 'Debe ingresar el interés.',
            'monto_max.required' => 'Debe ingresar el monto máximo.',
            'plazo_max.required' => 'Debe ingresar el plazo máximo.',
            'garante.required' => 'Debe indicar la cantidad de garantes.',
            'itf.required' => 'Debe indicar el itf del prestamo',
            'int_penal.required' => 'Debe indicar el interes penal del prestamo',
            'papeleria.required' => 'Debe indicar costo de papeleria del prestamo',
            'min_defensa.required' => 'Debe indicar la comision Min Defensa del prestamo',
        ];
    }
}
