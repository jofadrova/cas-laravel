<?php

namespace App\Http\Requests;

use App\Models\LoteMensual;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StorePrestamoArchivoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->hasFile('archivos') && ! is_array($this->file('archivos'))) {
            $this->files->set('archivos', [$this->file('archivos')]);
        }
    }

    public function rules(): array
    {
        return [
            'archivos' => ['required', 'array', 'min:1', 'max:15'],
            'archivos.*' => [
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
                        'archivos',
                        'No se pueden cargar archivos porque el lote se encuentra '
                        . strtolower($lote->estado) . '.'
                    );
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'archivos.required' => 'Seleccione al menos un archivo Excel.',
            'archivos.array' => 'La selección de archivos no es válida.',
            'archivos.min' => 'Seleccione al menos un archivo Excel.',
            'archivos.max' => 'Puede cargar como máximo 15 archivos a la vez.',
            'archivos.*.required' => 'Uno de los archivos seleccionados no es válido.',
            'archivos.*.file' => 'Uno de los elementos seleccionados no es un archivo.',
            'archivos.*.mimes' => 'Solo se permiten archivos Excel con extensión .xlsx o .xls.',
            'archivos.*.max' => 'Cada archivo Excel debe pesar como máximo 10 MB.',
        ];
    }

    public function attributes(): array
    {
        return [
            'archivos' => 'archivos Excel',
            'archivos.*' => 'archivo Excel',
        ];
    }
}

