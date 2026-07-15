<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Prestamo;

class PagoController extends Controller
{
    public function index(Prestamo $prestamo)
    {
        $prestamo->load([
            'socio.institucion.grado',
            'tipo',
            'cuotas',
        ]);

        $cuotasPagadas = $prestamo->cuotas
            ->where('estado', 'CA')
            ->count();

        $mesActual = now()->month;
        $gestionActual = now()->year;

        $cuotasPendientes = $prestamo->cuotas()
            ->where('estado', 'PE')
            ->where(function ($q) use ($mesActual, $gestionActual) {

                $q->where('gestion', '<', $gestionActual)

                ->orWhere(function ($q) use ($mesActual, $gestionActual) {

                    $q->where('gestion', $gestionActual)
                        ->where('mes', '<=', $mesActual);

                });

            })
            ->orderBy('gestion')
            ->orderBy('mes')
            ->orderBy('nro_cuota')
            ->get();

        $cantidadCuotasPendientes = $prestamo->cuotas()
            ->where('estado', 'PE')
            ->count();

        return view('pagos.index', compact(
            'prestamo',
            'cuotasPagadas',
            'cuotasPendientes',
            'cantidadCuotasPendientes'
        ));
    }
}
