<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Pagos</title>
    <style>
        @page {
            margin: 14mm 10mm 15mm;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 8px;
            color: #1f2937;
            margin: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .header {
            border-bottom: 3px solid #198754;
            margin-bottom: 10px;
            padding-bottom: 7px;
        }

        .header td {
            vertical-align: middle;
        }

        .institution {
            color: #14532d;
            font-size: 13px;
            font-weight: bold;
            line-height: 1.35;
        }

        .report-title {
            color: #198754;
            font-size: 18px;
            font-weight: bold;
            text-align: right;
        }

        .report-meta {
            color: #4b5563;
            font-size: 8px;
            text-align: right;
            margin-top: 4px;
        }

        .section-title {
            background: #198754;
            color: #fff;
            font-size: 10px;
            font-weight: bold;
            padding: 6px 8px;
            margin-top: 9px;
        }

        .summary {
            border: 1px solid #b7c8bd;
        }

        .summary td {
            width: 25%;
            padding: 5px 7px;
            border: 1px solid #d6e0d9;
            vertical-align: top;
        }

        .label {
            color: #6b7280;
            font-size: 7px;
            text-transform: uppercase;
        }

        .value {
            font-size: 9px;
            font-weight: bold;
            margin-top: 2px;
        }

        .payments {
            margin-top: 0;
        }

        .payments thead {
            display: table-header-group;
        }

        .payments tr {
            page-break-inside: avoid;
        }

        .payments th {
            background: #e8f3ec;
            color: #14532d;
            border: 1px solid #9fb5a6;
            font-size: 7px;
            padding: 5px 3px;
            text-align: center;
        }

        .payments td {
            border: 1px solid #cfd8d2;
            padding: 4px 3px;
            vertical-align: top;
        }

        .payments tbody tr:nth-child(even) {
            background: #f7faf8;
        }

        .right {
            text-align: right;
        }

        .center {
            text-align: center;
        }

        .type {
            font-size: 7px;
            font-weight: bold;
            color: #14532d;
        }

        .totals td {
            background: #e8f3ec;
            color: #14532d;
            font-weight: bold;
        }

        .empty {
            padding: 18px !important;
            color: #6b7280;
            text-align: center;
        }

        .footer {
            position: fixed;
            left: 0;
            right: 0;
            bottom: -10mm;
            border-top: 1px solid #cfd8d2;
            color: #6b7280;
            font-size: 7px;
            padding-top: 4px;
            text-align: center;
        }
    </style>
</head>
<body>
    @php
        $monedaPrestamo = $esPrestamoDolares ? '$us' : 'Bs';
    @endphp

    <div class="footer">
        Sistema SCAS - Reporte generado el {{ now()->format('d/m/Y H:i') }}
    </div>
    <script type="text/php">
        if (isset($pdf)) {
            $font = $fontMetrics->get_font("DejaVu Sans", "normal");
            $pdf->page_text(700, 590, "Página {PAGE_NUM} de {PAGE_COUNT}", $font, 7, array(107, 114, 128));
        }
    </script>

    <table class="header">
        <tr>
            <td width="12%">
                <img src="{{ public_path('images/cas_sidebar.png') }}" width="68" alt="CAS">
            </td>
            <td width="53%">
                <div class="institution">
                    COOPERATIVA DE AHORRO Y CRÉDITO DE VÍNCULO LABORAL<br>
                    "OFICIALES DE CABALLERÍA APÓSTOL SANTIAGO" R.L.
                </div>
                <div>La Paz - Bolivia</div>
            </td>
            <td width="35%">
                <div class="report-title">REPORTE DE PAGOS</div>
                <div class="report-meta">
                    Solicitud N.º {{ str_pad($prestamo->nro_solicitud, 8, '0', STR_PAD_LEFT) }}
                </div>
            </td>
        </tr>
    </table>

    <div class="section-title">RESUMEN DEL PRÉSTAMO</div>
    <table class="summary">
        <tr>
            <td>
                <div class="label">Socio</div>
                <div class="value">
                    {{ trim($prestamo->socio->paterno.' '.$prestamo->socio->materno.' '.$prestamo->socio->nombres) }}
                </div>
            </td>
            <td>
                <div class="label">Papeleta</div>
                <div class="value">{{ $prestamo->socio->institucion->papeleta }}</div>
            </td>
            <td>
                <div class="label">Grado</div>
                <div class="value">{{ $prestamo->socio->institucion->grado->grado }}</div>
            </td>
            <td>
                <div class="label">Estado</div>
                <div class="value">{{ $prestamo->estado === 'PA' ? 'CANCELADO' : 'ACTIVO' }}</div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="label">Tipo de préstamo</div>
                <div class="value">{{ $prestamo->tipo->descripcion_tasa }}</div>
            </td>
            <td>
                <div class="label">Monto original</div>
                <div class="value">{{ $monedaPrestamo }} {{ number_format($prestamo->monto, 2) }}</div>
            </td>
            <td>
                <div class="label">Saldo actual</div>
                <div class="value">{{ $monedaPrestamo }} {{ number_format($prestamo->saldo_actual, 2) }}</div>
            </td>
            <td>
                <div class="label">Cuotas</div>
                <div class="value">{{ $cuotasPagadas }} pagadas / {{ $cuotasPendientes }} pendientes</div>
            </td>
        </tr>
    </table>

    <div class="section-title">DETALLE DE PAGOS REALIZADOS</div>
    <table class="payments">
        <thead>
            <tr>
                <th width="3%">N.º</th>
                <th width="7%">Fecha</th>
                <th width="9%">Tipo</th>
                <th width="12%">Cuotas</th>
                <th width="10%">Monto aplicado</th>
                <th width="10%">Efectivo Bs</th>
                <th width="7%">T.C.</th>
                <th width="9%">Diferencia Bs</th>
                <th width="27%">Glosa</th>
                <th width="6%">Estado</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pagos as $pago)
                @php
                    $esAmortizacion = $pago->tipo_pago === 'AM' && $pago->amortizacionCapital;
                    $cuotas = $esAmortizacion
                        ? 'No aplica'
                        : $pago->pagosCuotas->pluck('nro_cuota')->implode(', ');
                    $montoAplicado = $esAmortizacion
                        ? (float) $pago->amortizacionCapital->monto_capital
                        : (float) $pago->pagosCuotas->sum('monto');
                @endphp
                <tr>
                    <td class="center">{{ $loop->iteration }}</td>
                    <td class="center">{{ $pago->fecha->format('d/m/Y') }}</td>
                    <td class="center type">
                        @if($esAmortizacion)
                            AMORTIZACIÓN
                        @elseif($pago->tipo_pago === 'PT')
                            PAGO TOTAL
                        @else
                            POR CUOTAS
                        @endif
                    </td>
                    <td>{{ $cuotas ?: '-' }}</td>
                    <td class="right">{{ $monedaPrestamo }} {{ number_format($montoAplicado, 2) }}</td>
                    <td class="right">{{ number_format($pago->monto, 2) }}</td>
                    <td class="right">{{ $pago->tipo_cambio ? number_format($pago->tipo_cambio, 5) : '-' }}</td>
                    <td class="right">{{ number_format($pago->diferencia, 2) }}</td>
                    <td>
                        @if($esAmortizacion)
                            Aut.: {{ $pago->amortizacionCapital->autorizacion }}
                            @if($pago->amortizacionCapital->observaciones)
                                / Obs.: {{ $pago->amortizacionCapital->observaciones }}
                            @endif
                        @else
                            {{ $pago->anexo ?: '-' }}
                        @endif
                    </td>
                    <td class="center">{{ $pago->estado === 'AC' ? 'REG.' : $pago->estado }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="empty">No existen pagos registrados para este préstamo.</td>
                </tr>
            @endforelse
        </tbody>
        @if($pagos->isNotEmpty())
            <tfoot>
                <tr class="totals">
                    <td colspan="5" class="right">TOTALES</td>
                    <td class="right">{{ number_format($totalEfectivo, 2) }}</td>
                    <td></td>
                    <td class="right">{{ number_format($totalDiferencia, 2) }}</td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
        @endif
    </table>
</body>
</html>
