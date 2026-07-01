<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\HasTable;
use App\Support\ScasTable;
use App\Models\Prestamo;
use App\Models\Tasa;
use App\Services\Prestamos\CalculadoraPrestamo;

use App\Http\Requests\StorePrestamoRequest;
use App\Services\PrestamoService;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class PrestamoController extends Controller
{

    use HasTable;
    public function index()
    {
        $tipos = Tasa::where('estado', 'AC')
            ->orderBy('descripcion_tasa')
            ->get();


        $table = ScasTable::make(
            Prestamo::with(['socio', 'tipo'])
        )

       ->customSearch(function ($query, $buscar) {

            switch (request('campo')) {

                case 'solicitud':

                    $query->where(
                        'nro_solicitud',
                        'like',
                        "%{$buscar}%"
                    );

                    break;

                case 'papeleta':

                    $query->whereHas('socio', function ($q) use ($buscar) {

                        $q->where(
                            'nro_papeleta',
                            'like',
                            "%{$buscar}%"
                        );

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

                    $query->where(
                        'nro_solicitud',
                        'like',
                        "%{$buscar}%"
                    );
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
        ]);

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
            compact('prestamo', 'cuotas','qr')
        );

        $pdf->setPaper('letter');
        $dompdf = $pdf->getDomPDF();
        $dompdf->render();
        $canvas = $dompdf->getCanvas();
        $canvas->page_text(480,760,"Página {PAGE_NUM} de {PAGE_COUNT}",null,8);

        return $pdf->stream('Prestamo-'.$prestamo->nro_solicitud.'.pdf');


    }

    public function show()
    {

    }
}
