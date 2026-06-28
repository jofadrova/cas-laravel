<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
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
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'id_socio' => ['required','exists:socios,id'],
            'tipo_prestamo' => ['required','exists:tasas,id_tasa'],
            'id_garante1' => ['nullable','exists:socios,id'],
            'id_garante2' => ['nullable','exists:socios,id'],
            'monto' => ['required','numeric','min:0.01'],
            'plazo' => ['required','integer','min:1'],
            'motivo' => ['nullable','string','max:250'],
            'asiento' => ['required','string','max:20'],
            'fecha' => ['required','date'],
        ];
    }

   
}
