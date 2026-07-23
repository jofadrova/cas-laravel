<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReprogramacionPrestamoRequest;
use App\Models\Prestamo;
use App\Services\ReprogramacionPrestamoService;

class ReprogramacionPrestamoController extends Controller
{
    public function create(Prestamo $prestamo)
    {
        $prestamo->load([
            'socio.institucion.grado',
            'tipo',
            'cuotas' => fn ($query) => $query->orderBy('nro_cuota'),
        ]);

        $cuotasPagadas = $prestamo->cuotas
            ->where('estado', 'AC')
            ->values();
        $cuotasPendientes = $prestamo->cuotas
            ->where('estado', 'PE')
            ->values();

        abort_unless(
            $prestamo->estado === 'AC'
                && (float) $prestamo->saldo_actual > 0
                && $cuotasPendientes->isNotEmpty()
                && (int) $prestamo->periodo < (int) $prestamo->tipo->plazo_max,
            403,
            'El préstamo no está habilitado para reprogramación.'
        );

        return view('prestamos.reprogramacion', compact(
            'prestamo',
            'cuotasPagadas',
            'cuotasPendientes'
        ));
    }

    public function store(
        StoreReprogramacionPrestamoRequest $request,
        Prestamo $prestamo,
        ReprogramacionPrestamoService $service
    ) {
        $reprogramacion = $service->aplicar($prestamo, $request->validated());

        return redirect()
            ->route('prestamos.index')
            ->with(
                'success',
                "El préstamo fue reprogramado a {$reprogramacion->cuotas_pendientes_nuevo} cuotas pendientes."
            );
    }
}
