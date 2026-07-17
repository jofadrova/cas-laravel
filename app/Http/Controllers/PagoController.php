<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Http\Requests\StorePagoCuotaRequest;
use App\Models\Pago;
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

    public function store(StorePagoCuotaRequest $request, Prestamo $prestamo)
    {
        $datos = $request->validated();

        DB::transaction(function () use ($datos, $prestamo) {
            $cuotas = $prestamo->cuotas()
                ->whereIn('id', $datos['cuotas'])
                ->lockForUpdate()
                ->get();

            if ($cuotas->count() !== count($datos['cuotas'])) {
                throw ValidationException::withMessages([
                    'cuotas' => 'Una o más cuotas seleccionadas no pertenecen al préstamo.',
                ]);
            }

            if ($cuotas->contains(fn ($cuota) => $cuota->estado !== 'PE')) {
                throw ValidationException::withMessages([
                    'cuotas' => 'Una o más cuotas seleccionadas ya fueron pagadas.',
                ]);
            }

            $totalCuotas = round((float) $cuotas->sum('cuota_fija'), 2);
            $montoEfectivo = round((float) $datos['monto_efectivo'], 2);

            if (round((float) $datos['monto_efectivo'], 2) < $totalCuotas) {
                throw ValidationException::withMessages([
                    'monto_efectivo' => 'El pago efectivo no puede ser menor al total de las cuotas seleccionadas.',
                ]);
            }

            $diferencia = round($montoEfectivo - $totalCuotas, 2);

            $pago = Pago::create([
                'monto' => $totalCuotas,
                'diferencia' => $diferencia,
                'tipo_moneda' => 'B',
                'fecha' => now()->toDateString(),
                'tipo_pago' => 'PC',
                'anexo' => trim($datos['glosa']),
                'estado' => 'AC',
            ]);

            foreach ($cuotas as $cuota) {
                $pago->pagosCuotas()->create([
                    'id_cuota_solicitud' => $cuota->id,
                    'nro_cuota' => $cuota->nro_cuota,
                    'monto' => $cuota->cuota_fija,
                    'fecha' => now()->toDateString(),
                    'estado' => 'AC',
                ]);

                $cuota->update(['estado' => 'AC']);
            }

            $prestamoBloqueado = Prestamo::query()
                ->whereKey($prestamo->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $capitalPagado = round((float) $cuotas->sum('amortizacion_cap'), 2);
            $saldoActual = max(0, round((float) $prestamoBloqueado->saldo_actual - $capitalPagado, 2));
            $quedanCuotas = $prestamoBloqueado->cuotas()->where('estado', 'PE')->exists();

            $prestamoBloqueado->update([
                'saldo_actual' => $saldoActual,
                'ultima_cuota' => max((int) $prestamoBloqueado->ultima_cuota, (int) $cuotas->max('nro_cuota')),
                'estado' => $quedanCuotas ? $prestamoBloqueado->estado : 'CA',
            ]);
        });

        return redirect()
            ->route('prestamos.pagos', $prestamo)
            ->with('success', 'El pago de las cuotas fue guardado correctamente.');
    }
}
