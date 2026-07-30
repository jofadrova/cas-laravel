<?php

namespace App\Http\Requests;

use App\Models\LoteMensual;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreGaranteArchivoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->hasFile('archivos_garantes')
            && ! is_array($this->file('archivos_garantes'))) {
            $this->files->set(
                'archivos_garantes',
                [$this->file('archivos_garantes')]
            );
        }
    }

    public function rules(): array
    {
        return [
            'archivos_garantes' => ['required', 'array', 'min:1', 'max:5'],
            'archivos_garantes.*' => [
                'required',
                'file',
                'mimes:xlsx,xls',
                'max:10240',
            ],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                /** @var LoteMensual|null $lote */
                $lote = $this->route('lote');

                if (! $lote) {
                    return;
                }

                if (in_array($lote->estado, [
                    LoteMensual::ESTADO_PROCESADO,
                    LoteMensual::ESTADO_CERRADO,
                    LoteMensual::ESTADO_ANULADO,
                ], true)) {
                    $validator->errors()->add(
                        'archivos_garantes',
                        'No se pueden cargar descuentos a garantes porque el lote se encuentra '
                        . strtolower($lote->estado) . '.'
                    );
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'archivos_garantes.required' =>
                'Seleccione el archivo Excel de descuentos a garantes.',
            'archivos_garantes.array' =>
                'La selección del archivo de garantes no es válida.',
            'archivos_garantes.min' =>
                'Seleccione al menos un archivo Excel de garantes.',
            'archivos_garantes.max' =>
                'Puede cargar como máximo 5 archivos de garantes a la vez.',
            'archivos_garantes.*.mimes' =>
                'Solo se permiten archivos de garantes .xlsx o .xls.',
            'archivos_garantes.*.max' =>
                'Cada archivo de garantes debe pesar como máximo 10 MB.',
        ];
    }
}
