<?php

namespace App\Http\Controllers;

use App\Models\LoteFvsRegistro;
use App\Models\LoteMensual;
use App\Services\ProcesamientoMensual\FvsComparacionService;
use App\Services\ProcesamientoMensual\FvsFinalizacionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use LogicException;

class FvsComparacionController extends Controller
{
    public function index(Request $request, LoteMensual $lote): View
    {
        $estado = strtoupper(trim((string) $request->query('estado', '')));
        $papeleta = trim((string) $request->query('papeleta', ''));
        $nombre = trim((string) $request->query('nombre', ''));

        if (!in_array($estado, LoteFvsRegistro::ESTADOS_COMPARACION, true)) {
            $estado = '';
        }

        $base = LoteFvsRegistro::query()
            ->where('lote_mensual_id', $lote->id);

        $registros = (clone $base)
            ->with([
                'archivo:id,nombre_original',
                'socio:id,nombres,paterno,materno,nro_doc,estado',
                'socioInstitucion:id,id_socio,papeleta,estado',
            ])
            ->when($estado !== '', fn ($query) => $query->where('estado', $estado))
            ->when($papeleta !== '', fn ($query) => $query->where('codigo_personal', 'like', "%{$papeleta}%"))
            ->when($nombre !== '', fn ($query) => $query->where(function ($subconsulta) use ($nombre): void {
                $subconsulta
                    ->where('nombres', 'like', "%{$nombre}%")
                    ->orWhereHas('socio', fn ($socio) => $socio
                        ->where('nombres', 'like', "%{$nombre}%")
                        ->orWhere('paterno', 'like', "%{$nombre}%")
                        ->orWhere('materno', 'like', "%{$nombre}%"));
            }))
            ->orderBy('lote_archivo_id')
            ->orderBy('fila_origen')
            ->paginate(50)
            ->withQueryString();

        $conteos = (clone $base)
            ->selectRaw('estado, COUNT(*) AS total')
            ->groupBy('estado')
            ->pluck('total', 'estado');
        $montos = (clone $base)
            ->selectRaw('estado, COALESCE(SUM(monto_descuento), 0) AS total')
            ->groupBy('estado')
            ->pluck('total', 'estado');
        $total = (clone $base)->count();
        $validos = (int) ($conteos[LoteFvsRegistro::ESTADO_VALIDO] ?? 0);
        $noEncontrados = (int) ($conteos[LoteFvsRegistro::ESTADO_NO_ENCONTRADO] ?? 0);
        $procesamientoFvs = DB::table('lote_fvs_procesamientos')
            ->where('lote_mensual_id', $lote->id)
            ->first();
        $estadoPermiteProcesar = !in_array($lote->estado, [
            LoteMensual::ESTADO_PROCESADO,
            LoteMensual::ESTADO_CERRADO,
            LoteMensual::ESTADO_ANULADO,
        ], true);

        return view('procesamiento-mensual.lotes.fvs.comparacion', [
            'lote' => $lote,
            'registros' => $registros,
            'estadoSeleccionado' => $estado,
            'papeletaBuscada' => $papeleta,
            'nombreBuscado' => $nombre,
            'resumen' => [
                'total' => $total,
                'comparados' => $validos + $noEncontrados,
                'validos' => $validos,
                'no_encontrados' => $noEncontrados,
                'monto_total' => (float) (clone $base)->sum('monto_descuento'),
                'monto_valido' => (float) ($montos[LoteFvsRegistro::ESTADO_VALIDO] ?? 0),
                'monto_observado' => (float) ($montos[LoteFvsRegistro::ESTADO_NO_ENCONTRADO] ?? 0),
            ],
            'procesamientoFvs' => $procesamientoFvs,
            'puedeComparar' => $procesamientoFvs === null
                && $total > 0
                && $estadoPermiteProcesar,
            'puedeFinalizar' => $procesamientoFvs === null
                && $total > 0
                && $total === $validos + $noEncontrados
                && $estadoPermiteProcesar,
        ]);
    }

    public function comparar(
        LoteMensual $lote,
        FvsComparacionService $comparador
    ): RedirectResponse {
        if (DB::table('lote_fvs_procesamientos')
            ->where('lote_mensual_id', $lote->id)
            ->exists()) {
            return back()->with(
                'error',
                'FVS ya fue finalizado y está bloqueado mientras espera su asiento contable.'
            );
        }

        if (in_array($lote->estado, [
            LoteMensual::ESTADO_PROCESADO,
            LoteMensual::ESTADO_CERRADO,
            LoteMensual::ESTADO_ANULADO,
        ], true)) {
            return back()->with(
                'error',
                "No es posible comparar FVS porque el lote se encuentra {$lote->estado}."
            );
        }

        try {
            $resultado = $comparador->ejecutar($lote, auth()->id());
        } catch (LogicException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('procesamiento-mensual.lotes.fvs.comparacion.index', $lote)
            ->with(
                'success',
                "Comparación terminada: {$resultado['validos']} descuento(s) válido(s) y "
                ."{$resultado['no_encontrados']} observado(s)."
            );
    }

    public function finalizar(
        LoteMensual $lote,
        FvsFinalizacionService $finalizador
    ): RedirectResponse {
        try {
            $procesamiento = $finalizador->ejecutar($lote, auth()->id());
        } catch (LogicException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('procesamiento-mensual.lotes.fvs.comparacion.index', $lote)
            ->with(
                'success',
                'FVS finalizado. El monto de Bs '
                .number_format((float) $procesamiento->monto_total, 2, ',', '.')
                .' quedó PENDIENTE para generar el asiento contable.'
            );
    }
}
