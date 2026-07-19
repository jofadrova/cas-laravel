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
                    {{ trim(
                        ($prestamo->socio?->paterno ?? '').' '.
                        ($prestamo->socio?->materno ?? '').' '.
                        ($prestamo->socio?->nombres ?? '')
                    ) ?: 'No registrado' }}
                </div>
            </td>
            <td>
                <div class="label">Papeleta</div>
                <div class="value">{{ $prestamo->socio?->institucion?->papeleta ?? 'No registrada' }}</div>
            </td>
            <td>
                <div class="label">Grado</div>
                <div class="value">{{ $prestamo->socio?->institucion?->grado?->grado ?? 'No registrado' }}</div>
            </td>
            <td>
                <div class="label">Estado</div>
                <div class="value">{{ $prestamo->estado === 'PA' ? 'CANCELADO' : 'ACTIVO' }}</div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="label">Tipo de préstamo</div>
                <div class="value">{{ $prestamo->tipo?->descripcion_tasa ?? 'Tipo no registrado' }}</div>
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
                <th width="4%">Cuota</th>
                <th width="9%">Periodo</th>
                <th width="9%">Cuota fija</th>
                <th width="10%">Amort. capital</th>
                <th width="8%">Interés</th>
                <th width="7%">Min. Def.</th>
                <th width="9%">Contingencias</th>
                <th width="8%">Interés días</th>
                <th width="9%">Saldo capital</th>
                <th width="10%">Situación</th>
                <th width="17%">Observaciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($cuotasDetalle as $cuota)
                <tr>
                    <td class="center">{{ $cuota->nro_cuota }}</td>
                    <td class="center">{{ sprintf('%02d', $cuota->mes) }}/{{ $cuota->gestion }}</td>
                    <td class="right">{{ number_format($cuota->cuota_fija, 2) }}</td>
                    <td class="right">{{ number_format($cuota->amortizacion_cap, 2) }}</td>
                    <td class="right">{{ number_format($cuota->interes, 2) }}</td>
                    <td class="right">{{ number_format($cuota->min_defensa, 2) }}</td>
                    <td class="right">{{ number_format($cuota->itf, 2) }}</td>
                    <td class="right">{{ number_format($cuota->papel, 2) }}</td>
                    <td class="right">{{ number_format($cuota->saldo_capital_reporte, 2) }}</td>
                    <td>{{ $cuota->situacion_reporte }}</td>
                    <td>{{ $cuota->observaciones_reporte ?: '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="12" class="empty">No existen cuotas registradas para este préstamo.</td>
                </tr>
            @endforelse
        </tbody>
        @if($cuotasDetalle->isNotEmpty())
            <tfoot>
                <tr class="totals">
                    <td colspan="8" class="right">TOTAL CANCELADO</td>
                    <td class="right">{{ number_format($totalMontoCancelado, 2) }}</td>
                    <td colspan="3"></td>
                </tr>
            </tfoot>
        @endif
    </table>

    @if($amortizacionesReporte->isNotEmpty())
        <div class="section-title">AMORTIZACIONES DE CAPITAL</div>
        <table class="payments">
            <thead>
                <tr>
                    <th width="4%">N.º</th>
                    <th width="8%">Fecha</th>
                    <th width="12%">Modalidad</th>
                    <th width="12%">Efectivo Bs</th>
                    <th width="12%">Capital amortizado</th>
                    <th width="8%">T.C.</th>
                    <th width="12%">Saldo anterior</th>
                    <th width="12%">Saldo nuevo</th>
                    <th width="20%">Autorización / Observaciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($amortizacionesReporte as $pago)
                    @php($amortizacion = $pago->amortizacionCapital)
                    <tr>
                        <td class="center">{{ $loop->iteration }}</td>
                        <td class="center">
                            {{ ($pago->fecha_deposito ?? $pago->fecha)->format('d/m/Y') }}
                        </td>
                        <td>
                            {{ $amortizacion->tipo_recalculo === 'CUOTA'
                                ? 'Reducir cuota'
                                : 'Reducir plazo' }}
                        </td>
                        <td class="right">{{ number_format($pago->monto, 2) }}</td>
                        <td class="right">
                            {{ $monedaPrestamo }} {{ number_format($amortizacion->monto_capital, 2) }}
                        </td>
                        <td class="right">
                            {{ $pago->tipo_cambio ? number_format($pago->tipo_cambio, 5) : '-' }}
                        </td>
                        <td class="right">
                            {{ $monedaPrestamo }} {{ number_format($amortizacion->saldo_anterior, 2) }}
                        </td>
                        <td class="right">
                            {{ $monedaPrestamo }} {{ number_format($amortizacion->saldo_nuevo, 2) }}
                        </td>
                        <td>
                            Aut.: {{ $amortizacion->autorizacion }}
                            @if($amortizacion->observaciones)
                                / Obs.: {{ $amortizacion->observaciones }}
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="totals">
                    <td colspan="3" class="right">TOTALES</td>
                    <td class="right">{{ number_format($totalEfectivoAmortizaciones, 2) }}</td>
                    <td class="right">
                        {{ $monedaPrestamo }} {{ number_format($totalAmortizadoCapital, 2) }}
                    </td>
                    <td colspan="4"></td>
                </tr>
            </tfoot>
        </table>
    @endif

    @if(false)
    <table class="payments">
        <thead>
            <tr>
                <th width="3%">N.º</th>
                <th width="8%">F. depósito</th>
                <th width="9%">NOP</th>
                <th width="9%">Tipo</th>
                <th width="10%">Cuotas</th>
                <th width="10%">Monto aplicado</th>
                <th width="10%">Efectivo Bs</th>
                <th width="7%">T.C.</th>
                <th width="8%">Diferencia Bs</th>
                <th width="20%">Glosa</th>
                <th width="6%">Estado</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pagos as $pago)
                <tr>
                    <td class="center">{{ $loop->iteration }}</td>
                    <td class="center">{{ ($pago->fecha_deposito ?? $pago->fecha)->format('d/m/Y') }}</td>
                    <td>{{ $pago->nop ?: '-' }}</td>
                    <td class="center type">
                        @if($pago->es_amortizacion_reporte)
                            AMORTIZACIÓN
                        @elseif($pago->tipo_pago === 'PT')
                            PAGO TOTAL
                        @else
                            POR CUOTAS
                        @endif
                    </td>
                    <td>{{ $pago->cuotas_reporte }}</td>
                    <td class="right">{{ $monedaPrestamo }} {{ number_format($pago->monto_aplicado_reporte, 2) }}</td>
                    <td class="right">{{ number_format($pago->monto, 2) }}</td>
                    <td class="right">{{ $pago->tipo_cambio ? number_format($pago->tipo_cambio, 5) : '-' }}</td>
                    <td class="right">{{ number_format($pago->diferencia, 2) }}</td>
                    <td>{{ $pago->glosa_reporte }}</td>
                    <td class="center">{{ $pago->estado === 'AC' ? 'REG.' : $pago->estado }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" class="empty">No existen pagos registrados para este préstamo.</td>
                </tr>
            @endforelse
        </tbody>
        @if($pagos->isNotEmpty())
            <tfoot>
                <tr class="totals">
                    <td colspan="5" class="right">TOTALES</td>
                    <td class="right">{{ number_format($totalAplicado, 2) }}</td>
                    <td class="right">{{ number_format($totalEfectivo, 2) }}</td>
                    <td></td>
                    <td class="right">{{ number_format($totalDiferencia, 2) }}</td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
        @endif
    </table>
    @endif
</body>
</html>
