<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalle del Préstamo</title>
    <style>
        @page { margin: 13mm 9mm 15mm; }
        body {
            margin: 0;
            font-family: DejaVu Sans, sans-serif;
            font-size: 7px;
            color: #1f2937;
        }
        table { width: 100%; border-collapse: collapse; }
        .header {
            border-bottom: 3px solid #198754;
            margin-bottom: 8px;
            padding-bottom: 6px;
        }
        .header td { vertical-align: middle; }
        .institution {
            color: #14532d;
            font-size: 12px;
            font-weight: bold;
            line-height: 1.4;
        }
        .title {
            color: #198754;
            font-size: 17px;
            font-weight: bold;
            text-align: right;
        }
        .meta {
            color: #6b7280;
            text-align: right;
            margin-top: 4px;
        }
        .section-title {
            margin-top: 8px;
            padding: 5px 7px;
            background: #198754;
            color: #fff;
            font-size: 9px;
            font-weight: bold;
        }
        .section-title.pending { background: #b7791f; }
        .section-title.amortization { background: #087990; }
        .summary td {
            width: 25%;
            padding: 4px 6px;
            border: 1px solid #cfd8d2;
            vertical-align: top;
        }
        .label {
            color: #6b7280;
            font-size: 6px;
            text-transform: uppercase;
        }
        .value {
            margin-top: 2px;
            font-size: 8px;
            font-weight: bold;
        }
        .detail-table thead { display: table-header-group; }
        .detail-table tr { page-break-inside: avoid; }
        .detail-table th {
            padding: 3px;
            background: #e8f3ec;
            color: #14532d;
            border: 1px solid #9fb5a6;
            text-align: center;
            font-size: 6.5px;
        }
        .detail-table td {
            padding: 2px 3px;
            border: 1px solid #cfd8d2;
        }
        .detail-table tbody tr:nth-child(even) { background: #f7faf8; }
        .right { text-align: right; }
        .center { text-align: center; }
        .empty {
            padding: 12px !important;
            color: #6b7280;
            text-align: center;
        }
        .empty-block {
            padding: 7px;
            border: 1px solid #cfd8d2;
            color: #6b7280;
            text-align: center;
        }
        .footer {
            position: fixed;
            left: 0;
            right: 0;
            bottom: -10mm;
            padding-top: 4px;
            border-top: 1px solid #cfd8d2;
            color: #6b7280;
            text-align: center;
            font-size: 6px;
        }
        .page-break-avoid { page-break-inside: avoid; }
    </style>
</head>
<body>
    @php
        $monedaPrestamo = $esPrestamoDolares ? '$us' : 'Bs';
    @endphp

    <div class="footer">
        Sistema SCAS - Detalle generado el {{ now()->format('d/m/Y H:i') }}
    </div>
    <table class="header">
        <tr>
            <td width="11%">
                <img src="{{ public_path('images/cas_sidebar.png') }}" width="62" alt="CAS">
            </td>
            <td width="55%">
                <div class="institution">
                    COOPERATIVA DE AHORRO Y CRÉDITO DE VÍNCULO LABORAL<br>
                    "OFICIALES DE CABALLERÍA APÓSTOL SANTIAGO" R.L.
                </div>
                <div>La Paz - Bolivia</div>
            </td>
            <td width="34%">
                <div class="title">DETALLE DEL PRÉSTAMO</div>
                <div class="meta">
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
                <div class="value">{{ trim($prestamo->socio->nombres.' '.$prestamo->socio->paterno.' '.$prestamo->socio->materno) }}</div>
            </td>
            <td>
                <div class="label">C.I.</div>
                <div class="value">{{ $prestamo->socio->nro_doc }} {{ $prestamo->socio->expedido }}</div>
            </td>
            <td>
                <div class="label">Papeleta / Grado</div>
                <div class="value">
                    {{ $prestamo->socio->institucion->papeleta }} /
                    {{ $prestamo->socio->institucion->grado->grado }}
                </div>
            </td>
            <td>
                <div class="label">Estado</div>
                <div class="value">{{ $prestamo->estado_texto }}</div>
            </td>
        </tr>
        @if($prestamo->refinanciado)
        <tr>
            <td colspan="2">
                <div class="label">Saldo absorbido del prÃ©stamo anterior</div>
                <div class="value">{{ $monedaPrestamo }} {{ number_format($prestamo->saldo_refinanciado, 2) }}</div>
            </td>
            <td colspan="2">
                <div class="label">Desembolso neto</div>
                <div class="value">{{ $monedaPrestamo }} {{ number_format($prestamo->monto_desembolso_refinanciamiento, 2) }}</div>
            </td>
        </tr>
        @endif
        @if($prestamo->prestamoOrigen || ($prestamo->estado === 'CE' && $prestamo->refinanciamientos->isNotEmpty()))
        <tr>
            <td colspan="4">
                <div class="label">Trazabilidad del refinanciamiento</div>
                <div class="value">
                    @if($prestamo->prestamoOrigen)
                        PrÃ©stamo anterior: solicitud N.Âº {{ $prestamo->prestamoOrigen->nro_solicitud }}
                    @else
                        Nuevo prÃ©stamo: solicitud N.Âº {{ $prestamo->refinanciamientos->first()->nro_solicitud }}
                    @endif
                </div>
            </td>
        </tr>
        @endif
        <tr>
            <td>
                <div class="label">Tipo de préstamo</div>
                <div class="value">{{ $prestamo->tipo->descripcion_tasa }}</div>
            </td>
            <td>
                <div class="label">Monto / Saldo</div>
                <div class="value">
                    {{ $monedaPrestamo }} {{ number_format($prestamo->monto, 2) }} /
                    {{ $monedaPrestamo }} {{ number_format($prestamo->saldo_actual, 2) }}
                </div>
            </td>
            <td>
                <div class="label">Interés / Periodo</div>
                <div class="value">{{ number_format($prestamo->interes, 2) }} % / {{ $prestamo->periodo }} cuotas</div>
            </td>
            <td>
                <div class="label">Cuotas</div>
                <div class="value">{{ $cuotasPagadas->count() }} pagadas / {{ $cuotasPendientes->count() }} pendientes</div>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <div class="label">Primer garante</div>
                <div class="value">
                    {{ $prestamo->garante1
                        ? trim($prestamo->garante1->nombres.' '.$prestamo->garante1->paterno.' '.$prestamo->garante1->materno)
                        : 'No registrado' }}
                </div>
            </td>
            <td colspan="2">
                <div class="label">Segundo garante</div>
                <div class="value">
                    {{ $prestamo->garante2
                        ? trim($prestamo->garante2->nombres.' '.$prestamo->garante2->paterno.' '.$prestamo->garante2->materno)
                        : 'No registrado' }}
                </div>
            </td>
        </tr>
    </table>

    <div class="section-title">CUOTAS PAGADAS ({{ $cuotasPagadas->count() }})</div>
    @if($cuotasPagadas->isNotEmpty())
    <table class="detail-table">
        <thead>
            <tr>
                <th width="7%">Cuota</th>
                <th width="11%">Periodo</th>
                <th width="15%">Cuota fija {{ $monedaPrestamo }}</th>
                <th width="15%">Capital {{ $monedaPrestamo }}</th>
                <th width="14%">Interés {{ $monedaPrestamo }}</th>
                <th width="14%">Otros cargos</th>
                <th width="15%">Saldo {{ $monedaPrestamo }}</th>
                <th width="9%">Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($cuotasPagadas as $cuota)
                @php
                    $otrosCargos =
                        (float) $cuota->min_defensa
                        + (float) $cuota->itf
                        + (float) $cuota->papel
                        + (float) $cuota->comision_mindef
                        + (float) $cuota->rep_formulario;
                @endphp
                <tr>
                    <td class="center">{{ $cuota->nro_cuota }}</td>
                    <td class="center">{{ sprintf('%02d', $cuota->mes) }}/{{ $cuota->gestion }}</td>
                    <td class="right">{{ number_format($cuota->cuota_fija, 2) }}</td>
                    <td class="right">{{ number_format($cuota->amortizacion_cap, 2) }}</td>
                    <td class="right">{{ number_format($cuota->interes, 2) }}</td>
                    <td class="right">{{ number_format($otrosCargos, 2) }}</td>
                    <td class="right">{{ number_format($cuota->saldo, 2) }}</td>
                    <td class="center">PAGADA</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    @else
        <div class="empty-block">No existen cuotas pagadas.</div>
    @endif

    <div class="section-title pending">CUOTAS POR PAGAR ({{ $cuotasPendientes->count() }})</div>
    @if($cuotasPendientes->isNotEmpty())
    <table class="detail-table">
        <thead>
            <tr>
                <th width="7%">Cuota</th>
                <th width="11%">Periodo</th>
                <th width="15%">Cuota fija {{ $monedaPrestamo }}</th>
                <th width="15%">Capital {{ $monedaPrestamo }}</th>
                <th width="14%">Interés {{ $monedaPrestamo }}</th>
                <th width="14%">Otros cargos</th>
                <th width="15%">Saldo {{ $monedaPrestamo }}</th>
                <th width="9%">Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($cuotasPendientes as $cuota)
                @php
                    $otrosCargos =
                        (float) $cuota->min_defensa
                        + (float) $cuota->itf
                        + (float) $cuota->papel
                        + (float) $cuota->comision_mindef
                        + (float) $cuota->rep_formulario;
                @endphp
                <tr>
                    <td class="center">{{ $cuota->nro_cuota }}</td>
                    <td class="center">{{ sprintf('%02d', $cuota->mes) }}/{{ $cuota->gestion }}</td>
                    <td class="right">{{ number_format($cuota->cuota_fija, 2) }}</td>
                    <td class="right">{{ number_format($cuota->amortizacion_cap, 2) }}</td>
                    <td class="right">{{ number_format($cuota->interes, 2) }}</td>
                    <td class="right">{{ number_format($otrosCargos, 2) }}</td>
                    <td class="right">{{ number_format($cuota->saldo, 2) }}</td>
                    <td class="center">PENDIENTE</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    @else
        <div class="empty-block">No existen cuotas pendientes.</div>
    @endif

    @if($amortizaciones->isNotEmpty())
        <div class="section-title amortization">
            AMORTIZACIONES DE CAPITAL ({{ $amortizaciones->count() }})
        </div>
        <table class="detail-table">
            <thead>
                <tr>
                    <th width="7%">Fecha</th>
                    <th width="10%">Modalidad</th>
                    <th width="9%">Efectivo Bs</th>
                    <th width="10%">Capital {{ $monedaPrestamo }}</th>
                    <th width="7%">T.C.</th>
                    <th width="9%">Saldo anterior</th>
                    <th width="9%">Saldo nuevo</th>
                    <th width="9%">Cuota anterior</th>
                    <th width="9%">Cuota nueva</th>
                    <th width="7%">Plazo</th>
                    <th width="14%">Autorización / Obs.</th>
                </tr>
            </thead>
            <tbody>
                @foreach($amortizaciones as $amortizacion)
                    <tr>
                        <td class="center">{{ $amortizacion->fecha->format('d/m/Y') }}</td>
                        <td>{{ $amortizacion->tipo_recalculo === 'CUOTA' ? 'Reducir cuota' : 'Reducir plazo' }}</td>
                        <td class="right">{{ number_format($amortizacion->monto_efectivo, 2) }}</td>
                        <td class="right">{{ number_format($amortizacion->monto_capital, 2) }}</td>
                        <td class="right">{{ $amortizacion->tipo_cambio ? number_format($amortizacion->tipo_cambio, 5) : '-' }}</td>
                        <td class="right">{{ number_format($amortizacion->saldo_anterior, 2) }}</td>
                        <td class="right">{{ number_format($amortizacion->saldo_nuevo, 2) }}</td>
                        <td class="right">{{ number_format($amortizacion->cuota_anterior, 2) }}</td>
                        <td class="right">{{ number_format($amortizacion->cuota_nueva, 2) }}</td>
                        <td class="center">{{ $amortizacion->periodo_anterior }} a {{ $amortizacion->periodo_nuevo }}</td>
                        <td>
                            Aut.: {{ $amortizacion->autorizacion }}
                            @if($amortizacion->observaciones)
                                / Obs.: {{ $amortizacion->observaciones }}
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</body>
</html>
