<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProyeccionPrestamoRequest;
use App\Models\Tasa;
use App\Services\Prestamos\CalculadoraPrestamo;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;

class ProyeccionPrestamoController extends Controller
{
    public function index()
    {
        $tipos = Tasa::where('estado', 'AC')
            ->orderBy('descripcion_tasa')
            ->get();

        return view('prestamos.proyeccion', compact('tipos'));
    }

    public function calcular(
        ProyeccionPrestamoRequest $request,
        CalculadoraPrestamo $calculadora
    ): JsonResponse {
        return response()->json(
            $this->prepararResultado($request->validated(), $calculadora)
        );
    }

    public function reporte(
        ProyeccionPrestamoRequest $request,
        CalculadoraPrestamo $calculadora
    ) {
        $resultado = $this->prepararResultado($request->validated(), $calculadora);

        $pdf = Pdf::loadView('prestamos.pdf.proyeccion', $resultado)
            ->setPaper('letter');

        return $pdf->stream('Proyeccion-prestamo-'.now()->format('Ymd-His').'.pdf');
    }

    private function prepararResultado(array $datos, CalculadoraPrestamo $calculadora): array
    {
        $tipo = Tasa::where('estado', 'AC')->findOrFail($datos['tipo_prestamo']);
        $moneda = $tipo->tipo_moneda === 'SU' ? '$us' : 'Bs';
        $tipoCambio = $tipo->tipo_moneda === 'SU' ? (float) $datos['tipo_cambio'] : null;

        $simulacion = $calculadora->simular([
            'monto' => (float) $datos['monto'],
            'plazo' => (int) $datos['plazo'],
            'porcentaje' => (float) $tipo->porcentaje,
            'fecha' => $datos['fecha'],
            'tipo_moneda' => $tipo->tipo_moneda,
            'tipo' => (int) $tipo->id_tasa,
            'itf' => (float) $tipo->itf,
            'papeleria' => (float) $tipo->papeleria,
            'min_defensa' => (float) $tipo->min_defensa,
        ]);
        $simulacion['minDefensaTotal'] = round(
            array_sum(array_column($simulacion['cronograma'], 'min_defensa')),
            2,
            PHP_ROUND_HALF_UP
        );

        return [
            'tipo' => $tipo,
            'moneda' => $moneda,
            'tipoCambio' => $tipoCambio,
            'fechaProyeccion' => $datos['fecha'],
            'plazo' => (int) $datos['plazo'],
            'simulacion' => $simulacion,
        ];
    }
}
