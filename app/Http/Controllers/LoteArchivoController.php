<?php

namespace App\Http\Controllers;

use App\Models\LoteArchivo;
use App\Models\LoteGaranteRegistro;
use App\Models\LoteMensual;
use App\Models\LotePrestamoRegistro;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LoteArchivoController extends Controller
{
    public function index(LoteMensual $lote): View
    {
        $lote->load(['envioMensual.archivoGarantes']);

        $archivos = LoteArchivo::query()
            ->where('lote_mensual_id', $lote->id)
            ->where('tipo', LoteArchivo::TIPO_PRESTAMOS)
            // Los archivos adicionales de MinDef se muestran al final.
            ->orderByRaw('CASE WHEN ruta LIKE ? THEN 1 ELSE 0 END', ['%/otros/%'])
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();

        $archivosGarantes = LoteArchivo::query()
            ->where('lote_mensual_id', $lote->id)
            ->where('tipo', LoteArchivo::TIPO_GARANTES)
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();

        $archivosOtros = $archivos
            ->filter(fn (LoteArchivo $archivo): bool => str_contains($archivo->ruta, '/otros/'))
            ->values();
        $archivosPrincipales = $archivos
            ->reject(fn (LoteArchivo $archivo): bool => str_contains($archivo->ruta, '/otros/'))
            ->values();

        $registrosGarantes = LoteGaranteRegistro::query()
            ->with('archivo:id,nombre_original')
            ->where('lote_mensual_id', $lote->id)
            ->orderBy('codigo_titular')
            ->orderBy('id')
            ->get();

        $registros = LotePrestamoRegistro::query()
            ->with('archivo:id,nombre_original')
            ->where('lote_mensual_id', $lote->id)
            ->whereHas('archivo', fn ($query) => $query->where('ruta', 'not like', '%/otros/%'))
            ->orderBy('id')
            ->paginate(50)
            ->withQueryString();

        $registrosOtros = LotePrestamoRegistro::query()
            ->with('archivo:id,nombre_original')
            ->where('lote_mensual_id', $lote->id)
            ->whereHas('archivo', fn ($query) => $query->where('ruta', 'like', '%/otros/%'))
            ->orderBy('id')
            ->get();

        $resumen = LotePrestamoRegistro::query()
            ->where('lote_mensual_id', $lote->id)
            ->selectRaw('COUNT(*) AS filas')
            ->selectRaw('COALESCE(SUM(monto_descuento), 0) AS monto_descuento')
            ->selectRaw('COALESCE(SUM(tot_2), 0) AS tot_2')
            ->selectRaw('COALESCE(SUM(comision), 0) AS comision')
            ->first();

        $prestamosProcesados = DB::table('lote_prestamo_procesamientos')
            ->where('lote_mensual_id', $lote->id)
            ->exists();

        $puedeCargar = ! $prestamosProcesados
            && ! in_array($lote->estado, [
                LoteMensual::ESTADO_PROCESADO,
                LoteMensual::ESTADO_CERRADO,
                LoteMensual::ESTADO_ANULADO,
            ], true);

        return view('procesamiento-mensual.lotes.archivos.index', [
            'lote' => $lote,
            'archivos' => $archivos,
            'archivosPrincipales' => $archivosPrincipales,
            'archivosOtros' => $archivosOtros,
            'archivosGarantes' => $archivosGarantes,
            'registrosGarantes' => $registrosGarantes,
            'registros' => $registros,
            'registrosOtros' => $registrosOtros,
            'resumen' => $resumen,
            'puedeCargar' => $puedeCargar,
            'prestamosProcesados' => $prestamosProcesados,
        ]);
    }
}
