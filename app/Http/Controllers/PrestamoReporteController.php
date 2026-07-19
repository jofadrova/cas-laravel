<?php

namespace App\Http\Controllers;

use App\Models\Tasa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PrestamoReporteController extends Controller
{
    public function tiposPrestamo(Request $request)
    {
        $gestionActual = now()->year;
        $gestionMinima = $gestionActual - 9;

        $gestion = $request->integer('gestion');

        if (
            $gestion < $gestionMinima ||
            $gestion > $gestionActual
        ) {
            $gestion = 0;
        }
        $estado = $request->string('estado')->toString();
        $tipoPrestamo = $request->integer('tipo_prestamo');

        $simboloMonedaIndicadores = '';

        $estadosPermitidos = ['AC', 'PA', 'CE'];

        if (!in_array($estado, $estadosPermitidos, true)) {
            $estado = '';
        }

        /*
        |--------------------------------------------------------------------------
        | Gestiones disponibles
        |--------------------------------------------------------------------------
        */

        $gestiones = collect(
            range($gestionActual, $gestionActual - 9)
        );

        /*
        |--------------------------------------------------------------------------
        | Consulta base
        |--------------------------------------------------------------------------
        */

        $consulta = DB::table('solicitudes as s')
            ->join('tasa as t', 't.id_tasa', '=', 's.tipo_prestamo')
            ->whereIn('s.estado', $estadosPermitidos);

        if ($gestion > 0) {
            $consulta->whereBetween('s.fecha_deposito', [
                "{$gestion}-01-01",
                "{$gestion}-12-31",
            ]);
        }

        if ($estado !== '') {
            $consulta->where('s.estado', $estado);
        }

        if ($tipoPrestamo > 0) {
            $consulta->where('s.tipo_prestamo', $tipoPrestamo);
        }

        /*
        |--------------------------------------------------------------------------
        | Indicadores generales
        |--------------------------------------------------------------------------
        */

        $indicadores = (clone $consulta)
            ->selectRaw('
                COUNT(*) AS total_prestamos,
                COALESCE(SUM(s.monto), 0) AS monto_colocado,
                COALESCE(SUM(s.saldo_actual), 0) AS saldo_actual,
                COALESCE(SUM(s.monto - s.saldo_actual), 0) AS capital_recuperado,
                SUM(CASE WHEN s.estado = "AC" THEN 1 ELSE 0 END) AS activos,
                SUM(CASE WHEN s.estado = "PA" THEN 1 ELSE 0 END) AS pagados,
                SUM(CASE WHEN s.estado = "CE" THEN 1 ELSE 0 END) AS refinanciados
            ')
            ->first();

        /*
        |--------------------------------------------------------------------------
        | Detalle agrupado por tipo
        |--------------------------------------------------------------------------
        */

        $tiposPrestamo = (clone $consulta)
            ->selectRaw('
                t.id_tasa,
                t.descripcion_tasa,
                t.tipo_moneda,
                t.porcentaje,

                COUNT(*) AS total_prestamos,

                SUM(CASE WHEN s.estado = "AC" THEN 1 ELSE 0 END) AS activos,
                SUM(CASE WHEN s.estado = "PA" THEN 1 ELSE 0 END) AS pagados,
                SUM(CASE WHEN s.estado = "CE" THEN 1 ELSE 0 END) AS refinanciados,

                COALESCE(SUM(s.monto), 0) AS monto_colocado,
                COALESCE(SUM(s.saldo_actual), 0) AS saldo_actual,
                COALESCE(SUM(s.monto - s.saldo_actual), 0) AS capital_recuperado
            ')
            ->groupBy(
                't.id_tasa',
                't.descripcion_tasa',
                't.tipo_moneda',
                't.porcentaje'
            )
            ->orderByDesc('monto_colocado')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Participación porcentual
        |--------------------------------------------------------------------------
        */

        $totalesPorMoneda = $tiposPrestamo
            ->groupBy(fn ($tipo) => strtoupper(trim((string) $tipo->tipo_moneda)))
            ->map(fn ($tipos) => (float) $tipos->sum('monto_colocado'));

        $tiposPrestamo->transform(function ($tipo) use ($totalesPorMoneda) {
            $codigoMoneda = strtoupper(trim((string) $tipo->tipo_moneda));
            $montoTotalMoneda = (float) $totalesPorMoneda->get($codigoMoneda, 0);

            $tipo->participacion = $montoTotalMoneda > 0
                ? round(($tipo->monto_colocado / $montoTotalMoneda) * 100, 2)
                : 0;

           $tipo->simbolo_moneda = match (
                strtoupper(trim((string) $tipo->tipo_moneda))
            ) {
                'B', 'BS', 'BOB', 'SB' => 'Bs',
                'D', 'U', 'USD', 'SU' => '$us',
                default => '',
            };

            $tipo->nombre_moneda = match ($codigoMoneda) {
                'B', 'BS', 'BOB', 'SB' => 'Bolivianos',
                'D', 'U', 'USD', 'SU' => 'Dólares estadounidenses',
                default => 'Moneda no definida',
            };

            return $tipo;
        });

        $monedasResultado = $tiposPrestamo
            ->pluck('tipo_moneda')
            ->map(fn ($moneda) => strtoupper(trim((string) $moneda)))
            ->filter()
            ->unique()
            ->values();

        $simboloMoneda = '';

        if ($monedasResultado->count() === 1) {
            $simboloMoneda = match ($monedasResultado->first()) {
                'B', 'BS', 'BOB', 'SB' => 'Bs',
                'D', 'U', 'USD', 'SU' => '$us',
                default => '',
            };
        }

        /*
        |--------------------------------------------------------------------------
        | Moneda de los indicadores generales
        |--------------------------------------------------------------------------
        | Cuando el filtro contiene una sola moneda, se muestra su símbolo.
        | Si existen préstamos en Bs y $us, no se coloca un símbolo único porque
        | los importes corresponden a monedas diferentes.
        */

        $monedasPresentes = $tiposPrestamo
            ->pluck('tipo_moneda')
            ->map(fn ($moneda) => strtoupper(trim((string) $moneda)))
            ->filter()
            ->unique()
            ->values();

        $monedaMixta = $monedasPresentes->count() > 1;

        $simboloMonedaIndicadores = $monedasPresentes->count() === 1
            ? match ($monedasPresentes->first()) {
                'B', 'BS', 'BOB', 'SB' => 'Bs',
                'D', 'U', 'USD', 'SU' => '$us',
                default => '',
            }
            : '';

        $indicadoresPorMoneda = $tiposPrestamo
            ->groupBy('simbolo_moneda')
            ->map(fn ($tipos, $simbolo) => [
                'simbolo' => $simbolo ?: 'Sin moneda',
                'monto_colocado' => (float) $tipos->sum('monto_colocado'),
                'saldo_actual' => (float) $tipos->sum('saldo_actual'),
                'capital_recuperado' => (float) $tipos->sum('capital_recuperado'),
            ])
            ->values();


        /*
        |--------------------------------------------------------------------------
        | Datos para gráficos
        |--------------------------------------------------------------------------
        */

        $graficoTipos = [
            'labels' => $tiposPrestamo
                ->pluck('descripcion_tasa')
                ->values(),

            'montos' => $tiposPrestamo
                ->pluck('monto_colocado')
                ->map(fn ($monto) => (float) $monto)
                ->values(),

            'saldos' => $tiposPrestamo
                ->pluck('saldo_actual')
                ->map(fn ($saldo) => (float) $saldo)
                ->values(),

            'simbolos' => $tiposPrestamo
                ->pluck('simbolo_moneda')
                ->values(),
        ];

        $graficoEstados = [
            'labels' => [
                'Activos',
                'Pagados',
                'Cerrados por refinanciamiento',
            ],

            'datos' => [
                (int) ($indicadores->activos ?? 0),
                (int) ($indicadores->pagados ?? 0),
                (int) ($indicadores->refinanciados ?? 0),
            ],
        ];

        /*
        |--------------------------------------------------------------------------
        | Catálogo para filtros
        |--------------------------------------------------------------------------
        */

        $catalogoTipos = Tasa::query()
            ->orderBy('descripcion_tasa')
            ->get([
                'id_tasa',
                'descripcion_tasa',
            ]);

        return view('prestamos.reportes.tipos-prestamo', [
            'indicadores' => $indicadores,
            'tiposPrestamo' => $tiposPrestamo,
            'catalogoTipos' => $catalogoTipos,
            'gestiones' => $gestiones,
            'graficoTipos' => $graficoTipos,
            'graficoEstados' => $graficoEstados,
            'simboloMoneda' => $simboloMoneda,
            'simboloMonedaIndicadores' => $simboloMonedaIndicadores,
            'monedaMixta' => $monedaMixta,
            'indicadoresPorMoneda' => $indicadoresPorMoneda,

            'gestionSeleccionada' => $gestion,
            'estadoSeleccionado' => $estado,
            'tipoSeleccionado' => $tipoPrestamo,
        ]);
    }
}
