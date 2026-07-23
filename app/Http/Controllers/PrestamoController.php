<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\HasTable;
use App\Support\ScasTable;
use App\Models\Prestamo;
use App\Models\Tasa;

use App\Services\Prestamos\CalculadoraPrestamo;

use App\Http\Requests\StorePrestamoRequest;
use App\Http\Requests\UpdatePrestamoRequest;
use App\Services\PrestamoService;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Services\ExchangeRateService;
use App\Http\Requests\UpdateGarantesRequest;
use App\Models\HistorialGarante;

class PrestamoController extends Controller
{

    use HasTable;
    public function index()
    {
        $tipos = Tasa::where('estado', 'AC')
            ->orderBy('descripcion_tasa')
            ->get();


        $table = ScasTable::make(
            Prestamo::with(['socio.institucion', 'tipo'])
                ->withCount([
                    'cuotas as cuotas_pagadas_count' => fn ($query) => $query->where('estado', 'AC'),
                    'cuotas as cuotas_pendientes_count' => fn ($query) => $query->where('estado', 'PE'),
                ])
        )
       ->customSearch(function ($query, $buscar) {
            switch (request('campo')) {
                case 'solicitud':
                    $query->where('nro_solicitud','like',"%{$buscar}%");
                    break;
                case 'papeleta':
                    $query->whereHas('socio.institucion', function ($q) use ($buscar) {
                        $q->where('papeleta', 'like', "%{$buscar}%");
                    });
                    break;
                case 'asociado':
                    $query->whereHas('socio', function ($q) use ($buscar) {

                        $q->where('nombres', 'like', "%{$buscar}%")
                        ->orWhere('paterno', 'like', "%{$buscar}%")
                        ->orWhere('materno', 'like', "%{$buscar}%");
                    });
                    break;
                default:
                    $query->where('nro_solicitud','like',"%{$buscar}%");
                    break;
            }
        })
        ->filters([
            'estado',
            'tipo_prestamo'
        ])
        ->sortable([
            'nro_solicitud',
            'monto',
            'saldo_actual',
            'estado'
        ])

        ->defaultSort('nro_solicitud', 'desc');

        $prestamos = $table->paginate();

        return view('prestamos.index', compact('prestamos', 'table', 'tipos')
        );
    }

    public function create()
    {
        $tipos = Tasa::where('estado', 'AC')
        ->orderBy('descripcion_tasa')
        ->get();

        return view('prestamos.create', compact('tipos'));
    }

    public function validarSolicitud(Request $request)
    {
        $request->validate([
            'id_socio' => 'required|integer',
            'id_tasa'  => 'required|integer',
        ]);

        $prestamo = Prestamo::where('ide_per', $request->id_socio)
            ->where('tipo_prestamo', $request->id_tasa)
            ->whereIn('estado', ['AC','PE'])
            ->first();

        if ($prestamo) {

            return response()->json([
                'ok' => false,
                'mensaje' => 'El asociado ya tiene un préstamo ACTIVO de este tipo.',
                'prestamo' => [
                    'solicitud' => $prestamo->nro_solicitud,
                    'monto'     => $prestamo->monto,
                    'saldo'     => $prestamo->saldo_actual,
                ]
            ]);

        }
        return response()->json([
            'ok' => true
        ]);
    }

    public function simular(Request $request)
    {
        $request->validate([
            'monto'       => 'required|numeric|min:1',
            'plazo'       => 'required|integer|min:1',
            'porcentaje'  => 'required|numeric|min:0',
            'fecha'       => 'required|date',
            'tipo_moneda' => 'required',
            'itf'         => 'nullable|numeric',
            'papeleria'   => 'nullable|numeric',
            'tipo'        => 'nullable|integer',
        ]);

        $calculadora = new CalculadoraPrestamo();

        return response()->json(
            $calculadora->simular($request->all())
        );
    }

    public function store(StorePrestamoRequest $request, PrestamoService $service)
    {
        $service->consolidar(
            $request->validated()
        );
        return redirect()
            ->route('prestamos.index')
            ->with('success','Préstamo guardado y consolidado correctamente.');
    }

    public function reporte(Prestamo $prestamo)
    {
        $prestamo->load([
            'socio',
            'tipo',
            'garante1',
            'garante2',
            'reprogramaciones' => fn ($query) => $query
                ->where('estado', 'AC')
                ->orderBy('fecha')
                ->orderBy('id'),
        ]);
        $reprogramaciones = $prestamo->reprogramaciones;

        $cuotas = \App\Models\CuotaSolicitud::where(
                'id_solicitud',
                $prestamo->id_solicitud
            )
            ->orderBy('nro_cuota')
            ->get();

        $contenidoQr ="Socio: ".$prestamo->socio->paterno." ".
        $prestamo->socio->materno." ".
        $prestamo->socio->nombres."\n".
        "CI: ".$prestamo->socio->nro_doc."\n".
        "Papeleta: ".optional($prestamo->socio->institucion)->papeleta."\n".
        "Prestamo: ".$prestamo->nro_solicitud;

         $qr = base64_encode(QrCode::format('svg')
            ->size(180)
            ->margin(0)
            ->generate($contenidoQr)
        );

        $pdf = Pdf::loadView(
            'prestamos.pdf.cronograma',
            compact('prestamo', 'cuotas', 'reprogramaciones', 'qr')
        );

        $pdf->setPaper('letter');
        $dompdf = $pdf->getDomPDF();
        $dompdf->render();
        $canvas = $dompdf->getCanvas();
        $canvas->page_text(480,760,"Página {PAGE_NUM} de {PAGE_COUNT}",null,8);

        return $pdf->stream('Prestamo-'.$prestamo->nro_solicitud.'.pdf');


    }

    public function buscarTipoCambio(string $fecha, ExchangeRateService $exchangeRateService)
    {
        $cotizacion = $exchangeRateService->getByDate($fecha);
        if (!$cotizacion) {
            return response()->json([
                'ok' => false,
                'tipo_cambio' => null,
                'message' => 'No existe una cotización registrada para la fecha seleccionada.',
            ]);
        }
        return response()->json([
            'ok' => true,
            'tipo_cambio' => $cotizacion->usd_bob,
            'fecha' => $cotizacion->rate_date,
        ]);
    }

    public function edit(Prestamo $prestamo)
    {
        abort_if($prestamo->estado !== 'AC', 403, 'El préstamo cerrado no admite operaciones.');

        if ($prestamo->cuotas()->where('estado', 'AC')->exists()) {
            return redirect()
                ->route('prestamos.index')
                ->with('error', 'El préstamo no puede editarse porque ya tiene cuotas pagadas.');
        }

        if (!$prestamo->editable) {
            return redirect()
                ->route('prestamos.index')
                ->with(
                    'error',
                    'La edición de este préstamo se encuentra bloqueada.'
                );
        }
         $tipos = Tasa::where('estado', 'AC')
        ->orderBy('descripcion_tasa')->get();
        return view('prestamos.edit', compact('prestamo','tipos'));
    }

    public function update(UpdatePrestamoRequest $request, Prestamo $prestamo, PrestamoService $service)
    {
        abort_if($prestamo->estado !== 'AC', 403, 'El préstamo cerrado no admite operaciones.');
        abort_if(
            $prestamo->cuotas()->where('estado', 'AC')->exists(),
            403,
            'El préstamo no puede editarse porque ya tiene cuotas pagadas.'
        );

        if (!$prestamo->editable) {
            abort(403, 'La edición de este préstamo está bloqueada.');
        }
        $service->actualizar($prestamo, $request->validated());

        return redirect()
            ->route('prestamos.index')
            ->with(
                'success',
                'Préstamo actualizado correctamente.'
            );
    }

    public function bloquearEdicion(Prestamo $prestamo)
    {
        abort_if($prestamo->estado !== 'AC', 403, 'El préstamo cerrado no admite operaciones.');

        $prestamo->update([
            'editable' => false
        ]);

        return back()->with(
            'success',
            'La edición del préstamo fue bloqueada correctamente.'
        );
    }

    public function habilitarEdicion(Prestamo $prestamo)
    {
        abort_if($prestamo->estado !== 'AC', 403, 'El préstamo cerrado no admite operaciones.');

        $prestamo->update([
            'editable' => true
        ]);

        return back()->with(
            'success',
            'La edición del préstamo fue habilitada nuevamente.'
        );
    }

    public function garantes(Prestamo $prestamo)
    {
        abort_if($prestamo->estado !== 'AC', 403, 'El préstamo cerrado no admite operaciones.');

        if ($prestamo->tipo->id_tasa != 1) {
            abort(403, 'El cambio de garantes solo está permitido para préstamos REGULARES.');
        }
        $prestamo->load([
            'garante1.institucion',
            'garante2.institucion',
            'historialGarantes.usuario',
            'historialGarantes.garante1Old',
            'historialGarantes.garante2Old',
            'historialGarantes.garante1New',
            'historialGarantes.garante2New',
        ]);
        return view('prestamos.garantes', compact('prestamo'));
    }

    public function actualizarGarantes(UpdateGarantesRequest $request, Prestamo $prestamo, PrestamoService $service)
    {
        abort_if($prestamo->estado !== 'AC', 403, 'El préstamo cerrado no admite operaciones.');

        if ($prestamo->tipo->id_tasa != 1) {

            abort(403, 'El cambio de garantes solo está permitido para préstamos REGULARES.');

        }
        $service->actualizarGarantes(
            $prestamo,
            $request->validated()
        );

        return redirect()
            ->route('prestamos.index')
            ->with('success', 'El cambio de garantes fue registrado correctamente.');

    }

    public function pdfCambioGarantes(HistorialGarante $historial)
    {
        $historial->load([
            'prestamo.socio.institucion',
            'prestamo.tipo',
            'usuario',
            'garante1Old.institucion',
            'garante2Old.institucion',
            'garante1New.institucion',
            'garante2New.institucion',
        ]);

        $pdf = Pdf::loadView(
            'prestamos.pdf.cambio-garantes',
            compact('historial')
        );

        return $pdf->stream(
            'Cambio_Garantes_'.$historial->id.'.pdf'
        );

    }

    public function detalle(Prestamo $prestamo)
    {
        try {

            return view(
                'prestamos.partials.detalle',
                $this->datosDetallePrestamo($prestamo)
            );

        } catch (\Throwable $e) {
            report($e);

            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No fue posible obtener la información del préstamo.',
                ], 500);
            }
            throw $e;
        }
    }

    public function detallePdf(Prestamo $prestamo)
    {
        $pdf = Pdf::loadView(
            'prestamos.pdf.detalle',
            $this->datosDetallePrestamo($prestamo)
        )
            ->setPaper('letter', 'landscape');

        $dompdf = $pdf->getDomPDF();
        $dompdf->render();
        $dompdf->getCanvas()->page_text(
            650,
            585,
            'Página {PAGE_NUM} de {PAGE_COUNT}',
            null,
            8,
            [0.12, 0.16, 0.22]
        );

        return $pdf->stream('Detalle_Prestamo_'.$prestamo->nro_solicitud.'.pdf');
    }

    private function datosDetallePrestamo(Prestamo $prestamo): array
    {
        $prestamo->load([
            'socio.institucion.grado',
            'tipo',
            'garante1',
            'garante2',
            'prestamoOrigen',
            'refinanciamientos',
            'cuotas' => fn ($query) => $query
            ->with('pagosCuotas.pago')
            ->orderBy('nro_cuota'),
            'amortizacionesCapital' => fn ($query) => $query
                ->where('estado', 'AC')
                ->orderBy('fecha')
                ->orderBy('id'),
            'reprogramaciones' => fn ($query) => $query
                ->where('estado', 'AC')
                ->orderBy('fecha')
                ->orderBy('id'),
        ]);

        return [
            'prestamo' => $prestamo,
            'cuotasPagadas' => $prestamo->cuotas
                ->where('estado', 'AC')
                ->values(),
            'cuotasPendientes' => $prestamo->cuotas
                ->where('estado', 'PE')
                ->values(),
            'amortizaciones' => $prestamo->amortizacionesCapital,
            'reprogramaciones' => $prestamo->reprogramaciones,
            'esPrestamoDolares' => $prestamo->tipo?->tipo_moneda === 'SU',
        ];
    }

    public function show()
    {

    }

    //////////////////////////////////////

}
