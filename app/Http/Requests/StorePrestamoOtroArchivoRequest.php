<?php

namespace App\Http\Requests;

use App\Models\LoteArchivo;
use App\Models\LoteMensual;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Validator;

class StorePrestamoOtroArchivoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'archivo' => [
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

                if (DB::table('lote_prestamo_procesamientos')
                    ->where('lote_mensual_id', $lote->id)
                    ->exists()) {
                    $validator->errors()->add(
                        'archivo',
                        'El pago mensual de Préstamos ya fue consolidado.'
                    );

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

                $tieneArchivosPrincipales = LoteArchivo::query()
                    ->where('lote_mensual_id', $lote->id)
                    ->where('tipo', LoteArchivo::TIPO_PRESTAMOS)
                    ->where('nombre_original', 'not like', 'planilla_mindef_%')
                    ->exists();

                if (! $tieneArchivosPrincipales) {
                    $validator->errors()->add(
                        'archivo',
                        'Primero debe cargar los archivos principales de Préstamos.'
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
