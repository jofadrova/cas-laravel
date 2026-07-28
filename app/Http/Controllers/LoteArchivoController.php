<?php

namespace App\Http\Controllers;

use App\Models\LoteArchivo;
use App\Models\LoteMensual;
use App\Models\LotePrestamoRegistro;
use Illuminate\View\View;

class LoteArchivoController extends Controller
{
    public function index(LoteMensual $lote): View
    {
        $archivos = LoteArchivo::query()
            ->where('lote_mensual_id', $lote->id)
            ->where('tipo', LoteArchivo::TIPO_PRESTAMOS)
            ->latest()
            ->get();

        $registros = LotePrestamoRegistro::query()
            ->with('archivo:id,nombre_original')
            ->where('lote_mensual_id', $lote->id)
            ->orderBy('id')
            ->paginate(50)
            ->withQueryString();

        $resumen = LotePrestamoRegistro::query()
            ->where('lote_mensual_id', $lote->id)
            ->selectRaw('COUNT(*) AS filas')
            ->selectRaw('COALESCE(SUM(monto_descuento), 0) AS monto_descuento')
            ->selectRaw('COALESCE(SUM(tot_2), 0) AS tot_2')
            ->selectRaw('COALESCE(SUM(comision), 0) AS comision')
            ->first();

        $puedeCargar = ! in_array($lote->estado, [
            LoteMensual::ESTADO_PROCESADO,
            LoteMensual::ESTADO_CERRADO,
            LoteMensual::ESTADO_ANULADO,
        ], true);

        return view('procesamiento-mensual.lotes.archivos.index', [
            'lote' => $lote,
            'archivos' => $archivos,
            'registros' => $registros,
            'resumen' => $resumen,
            'puedeCargar' => $puedeCargar,
        ]);
    }
}
