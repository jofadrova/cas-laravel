<?php

namespace App\Http\Requests;

use App\Models\LoteArchivo;
use App\Models\LoteMensual;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreCertificadoAporteArchivoRequest extends FormRequest
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
            'archivos' => ['required', 'array', 'min:1', 'max:10'],
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
                        'No se pueden cargar archivos de Certificados de Aportes '
                        . 'porque el lote se encuentra '
                        . strtolower($lote->estado) . '.'
                    );

                    return;
                }

                $existentes = LoteArchivo::query()
                    ->where('lote_mensual_id', $lote->id)
                    ->where('tipo', LoteArchivo::TIPO_CERTIFICADOS)
                    ->count();
                $nuevos = count($this->file('archivos', []));
                $total = $existentes + $nuevos;

                if ($total < 3) {
                    $validator->errors()->add(
                        'archivos',
                        'El lote debe contener entre 3 y 10 archivos de '
                        . "Certificados de Aportes. "
                        . "Con esta selección tendría {$total}."
                    );
                }

                if ($total > 10) {
                    $validator->errors()->add(
                        'archivos',
                        'El lote admite como máximo 10 archivos de '
                        . "Certificados de Aportes. "
                        . "Actualmente tiene {$existentes}."
                    );
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'archivos.required' => 'Seleccione los archivos Excel de Certificados de Aportes.',
            'archivos.array' => 'La selección de archivos no es válida.',
            'archivos.min' => 'Seleccione al menos un archivo Excel.',
            'archivos.max' => 'Puede seleccionar como máximo 10 archivos a la vez.',
            'archivos.*.required' => 'Uno de los archivos seleccionados no es válido.',
            'archivos.*.file' => 'Uno de los elementos seleccionados no es un archivo.',
            'archivos.*.mimes' => 'Solo se permiten archivos Excel .xlsx o .xls.',
            'archivos.*.max' => 'Cada archivo Excel debe pesar como máximo 10 MB.',
        ];
    }
}
