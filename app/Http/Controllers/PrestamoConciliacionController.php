<?php

namespace App\Http\Controllers;

use App\Models\LoteMensual;
use App\Models\LotePrestamoConciliacion;
use App\Models\LotePrestamoRegistro;
use App\Services\ProcesamientoMensual\PrestamoConciliacionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use LogicException;

class PrestamoConciliacionController extends Controller
{
    public function index(Request $request, LoteMensual $lote): View
    {
        $clasificacion = $request->string('clasificacion')->upper()->toString();
        $papeleta = $this->normalizarCodigoPersonal(
            $request->string('papeleta')->toString()
        );
        $nombre = trim($request->string('nombre')->toString());

        if (! in_array(
            $clasificacion,
            LotePrestamoConciliacion::CLASIFICACIONES,
            true
        )) {
            $clasificacion = '';
        }

        $consulta = LotePrestamoConciliacion::query()
            ->with([
                'registro:id,lote_archivo_id,fila_origen,codigo_personal,carnet,nombres,monto_descuento',
                'registro.archivo:id,nombre_original',
                'detalles',
            ])
            ->where('lote_mensual_id', $lote->id)
            ->when(
                $clasificacion !== '',
                fn ($query) => $query->where(
                    'clasificacion',
                    $clasificacion
                )
            )
            ->when(
                $papeleta !== '',
                fn ($query) => $query->whereHas(
                    'registro',
                    fn ($registro) => $registro->whereRaw(
                        <<<'SQL'
COALESCE(
    NULLIF(
        TRIM(
            LEADING '0'
            FROM TRIM(CAST(codigo_personal AS CHAR))
        ),
        ''
    ),
    '0'
) LIKE ?
SQL,
                        ["%{$papeleta}%"]
                    )
                )
            )
            ->when(
                $nombre !== '',
                fn ($query) => $query->whereHas(
                    'registro',
                    fn ($registro) => $registro->where(
                        'nombres',
                        'like',
                        "%{$nombre}%"
                    )
                )
            )
            ->orderBy('lote_prestamo_registro_id')
            ->orderBy('orden_operacion');

        $conciliaciones = $consulta
            ->paginate(50)
            ->withQueryString();

        $conteos = LotePrestamoConciliacion::query()
            ->where('lote_mensual_id', $lote->id)
            ->selectRaw('clasificacion, COUNT(*) AS total')
            ->groupBy('clasificacion')
            ->pluck('total', 'clasificacion');

        $resumen = collect(LotePrestamoConciliacion::CLASIFICACIONES)
            ->mapWithKeys(
                fn (string $estado): array => [
                    $estado => (int) ($conteos[$estado] ?? 0),
                ]
            );

        $totalImportados = LotePrestamoRegistro::query()
            ->where('lote_mensual_id', $lote->id)
            ->count();
        $totalOperacionesClasificadas = $resumen->sum();
        $totalRegistrosAtendidos = LotePrestamoConciliacion::query()
            ->where('lote_mensual_id', $lote->id)
            ->distinct()
            ->count('lote_prestamo_registro_id');

        return view(
            'procesamiento-mensual.lotes.archivos.prestamos.conciliacion',
            [
                'lote' => $lote,
                'conciliaciones' => $conciliaciones,
                'clasificaciones' =>
                    LotePrestamoConciliacion::CLASIFICACIONES,
                'clasificacionSeleccionada' => $clasificacion,
                'papeletaBuscada' => $papeleta,
                'nombreBuscado' => $nombre,
                'resumen' => $resumen,
                'totalImportados' => $totalImportados,
                'totalOperacionesClasificadas' =>
                    $totalOperacionesClasificadas,
                'totalRegistrosAtendidos' => $totalRegistrosAtendidos,
                'integridadCompleta' =>
                    $totalImportados === $totalRegistrosAtendidos,
            ]
        );
    }

    public function comparar(
        LoteMensual $lote,
        PrestamoConciliacionService $conciliador
    ): RedirectResponse {
        if (in_array($lote->estado, [
            LoteMensual::ESTADO_PROCESADO,
            LoteMensual::ESTADO_CERRADO,
            LoteMensual::ESTADO_ANULADO,
        ], true)) {
            return redirect()
                ->route(
                    'procesamiento-mensual.lotes.archivos.index',
                    $lote
                )
                ->with(
                    'error',
                    "No es posible comparar porque el lote se encuentra {$lote->estado}."
                );
        }

        try {
            $conciliador->ejecutar($lote, auth()->id());
        } catch (LogicException $exception) {
            return redirect()
                ->route(
                    'procesamiento-mensual.lotes.archivos.index',
                    $lote
                )
                ->with('error', $exception->getMessage());
        }

        return redirect()
            ->route(
                'procesamiento-mensual.lotes.archivos.prestamos.conciliacion.index',
                $lote
            )
            ->with(
                'success',
                'La comparación terminó correctamente. Todos los registros fueron clasificados.'
            );
    }

    private function normalizarCodigoPersonal(string $valor): string
    {
        $valor = trim($valor);

        if ($valor === '') {
            return '';
        }

        if (preg_match('/^\d+(?:\.0+)?$/', $valor)) {
            $valor = preg_replace('/\.0+$/', '', $valor);
            $valor = ltrim((string) $valor, '0');

            return $valor === '' ? '0' : $valor;
        }

        return $valor;
    }
}
