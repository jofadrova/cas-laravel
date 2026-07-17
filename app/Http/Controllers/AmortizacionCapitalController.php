<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAmortizacionCapitalRequest;
use App\Models\Prestamo;
use App\Services\AmortizacionCapitalService;

class AmortizacionCapitalController extends Controller
{
    public function create(Prestamo $prestamo)
    {
        $prestamo->load([
            'socio.institucion.grado',
            'tipo',
            'cuotas',
        ]);

        abort_unless($prestamo->estado === 'AC', 403, 'Solo se puede amortizar un préstamo activo.');

        $cuotasPendientes = $prestamo->cuotas
            ->where('estado', 'PE')
            ->sortBy('nro_cuota')
            ->values();

        abort_if($cuotasPendientes->isEmpty(), 422, 'El préstamo no tiene cuotas pendientes.');

        $primeraCuota = $cuotasPendientes->first();

        return view('prestamos.amortizacion-capital', compact(
            'prestamo',
            'cuotasPendientes',
            'primeraCuota'
        ));
    }

    public function store(
        StoreAmortizacionCapitalRequest $request,
        Prestamo $prestamo,
        AmortizacionCapitalService $service
    ) {
        $service->aplicar($prestamo, $request->validated());

        return redirect()
            ->route('prestamos.index')
            ->with('success', 'La amortización de capital fue aplicada correctamente.');
    }
}
