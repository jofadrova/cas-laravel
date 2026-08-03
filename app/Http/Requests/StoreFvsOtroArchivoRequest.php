<?php

namespace App\Http\Requests;

use App\Models\LoteArchivo;
use App\Models\LoteMensual;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreFvsOtroArchivoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'archivo' => ['required', 'file', 'mimes:xlsx,xls', 'max:10240'],
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
                        'archivo',
                        "El lote se encuentra {$lote->estado} y no admite nuevas cargas."
                    );
                }

                $principales = LoteArchivo::query()
                    ->where('lote_mensual_id', $lote->id)
                    ->where('tipo', LoteArchivo::TIPO_FVS)
                    ->where('ruta', 'not like', '%/otros/%')
                    ->count();

                if ($principales < 3) {
                    $validator->errors()->add(
                        'archivo',
                        'Primero debe cargar el grupo principal de al menos 3 archivos FVS.'
                    );
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'archivo.required' => 'Seleccione la planilla adicional de MinDef.',
            'archivo.file' => 'El elemento seleccionado no es un archivo válido.',
            'archivo.mimes' => 'La planilla debe ser un archivo .xlsx o .xls.',
            'archivo.max' => 'La planilla no puede superar 10 MB.',
        ];
    }
}
