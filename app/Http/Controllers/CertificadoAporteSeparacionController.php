<?php

namespace App\Http\Controllers;

use App\Models\LoteCertificadoAporteRegistro;
use App\Models\LoteCertificadoAporteSeparacion;
use App\Models\LoteMensual;
use App\Services\ProcesamientoMensual\CertificadoAporteSeparacionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use LogicException;

class CertificadoAporteSeparacionController extends Controller
{
    public function index(Request $request, LoteMensual $lote): View
    {
        $buscar = trim((string) $request->query('buscar', ''));
        $base = LoteCertificadoAporteSeparacion::query()
            ->where('lote_mensual_id', $lote->id);

        $separaciones = (clone $base)
            ->with([
                'registro:id,lote_archivo_id,fila_origen,codigo_concepto,codigo_personal,carnet,nombres,monto_descuento',
                'registro.archivo:id,nombre_original',
            ])
            ->when($buscar !== '', fn ($query) => $query->whereHas(
                'registro',
                fn ($registro) => $registro
                    ->where('codigo_personal', 'like', "%{$buscar}%")
                    ->orWhere('carnet', 'like', "%{$buscar}%")
                    ->orWhere('nombres', 'like', "%{$buscar}%")
            ))
            ->orderBy('lote_certificado_aporte_registro_id')
            ->paginate(50)
            ->withQueryString();

        $resumen = (clone $base)
            ->selectRaw('COUNT(*) AS registros')
            ->selectRaw('COALESCE(SUM(monto_total), 0) AS monto_total')
            ->selectRaw('COALESCE(SUM(monto_ao), 0) AS monto_ao')
            ->selectRaw('COALESCE(SUM(monto_av), 0) AS monto_av')
            ->selectRaw('COALESCE(SUM(monto_ai), 0) AS monto_ai')
            ->first();
        $totalRegistros = LoteCertificadoAporteRegistro::query()
            ->where('lote_mensual_id', $lote->id)
            ->count();
        $puedeSeparar = $totalRegistros > 0 && !in_array($lote->estado, [
            LoteMensual::ESTADO_PROCESADO,
            LoteMensual::ESTADO_CERRADO,
            LoteMensual::ESTADO_ANULADO,
        ], true);

        return view('procesamiento-mensual.lotes.certificados.separacion', compact(
            'lote',
            'separaciones',
            'resumen',
            'totalRegistros',
            'puedeSeparar',
            'buscar'
        ));
    }

    public function separar(
        LoteMensual $lote,
        CertificadoAporteSeparacionService $separador
    ): RedirectResponse {
        if (in_array($lote->estado, [
            LoteMensual::ESTADO_PROCESADO,
            LoteMensual::ESTADO_CERRADO,
            LoteMensual::ESTADO_ANULADO,
        ], true)) {
            return back()->with(
                'error',
                "No es posible separar aportes porque el lote se encuentra {$lote->estado}."
            );
        }

        try {
            $resultado = $separador->ejecutar($lote, auth()->id());
        } catch (LogicException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('procesamiento-mensual.lotes.certificados.separacion.index', $lote)
            ->with(
                'success',
                "Se separaron {$resultado['registros']} aporte(s) sin alterar el monto total."
            );
    }
}
