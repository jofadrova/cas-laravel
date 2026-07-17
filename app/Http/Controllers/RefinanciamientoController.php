<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRefinanciamientoRequest;
use App\Models\Prestamo;
use App\Services\RefinanciamientoService;

class RefinanciamientoController extends Controller
{
    public function create(Prestamo $prestamo)
    {
        $prestamo->load([
            'socio.institucion.grado',
            'tipo',
            'garante1',
            'garante2',
        ]);
        $prestamo->loadCount([
            'cuotas as cuotas_pagadas_count' => fn ($query) => $query->where('estado', 'AC'),
            'cuotas as cuotas_pendientes_count' => fn ($query) => $query->where('estado', 'PE'),
        ]);

        abort_unless(
            $prestamo->estado === 'AC' && (int) $prestamo->tipo_prestamo === 1,
            403,
            'Solo los préstamos Regulares activos pueden refinanciarse.'
        );

        return view('prestamos.refinanciamiento', compact('prestamo'));
    }

    public function store(
        StoreRefinanciamientoRequest $request,
        Prestamo $prestamo,
        RefinanciamientoService $service
    ) {
        $nuevo = $service->aplicar($prestamo, $request->validated());

        return redirect()
            ->route('prestamos.index')
            ->with(
                'success',
                'El préstamo fue refinanciado correctamente. Nueva solicitud: '.$nuevo->nro_solicitud.'.'
            );
    }
}
