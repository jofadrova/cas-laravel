<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 9mm 10mm 12mm; }
        body { margin: 0; font-family: DejaVu Sans, sans-serif; font-size: 8px; color: #1f2937; }
        table { width: 100%; border-collapse: collapse; }
        .brand { border-bottom: 3px solid #01a70c; padding-bottom: 7px; }
        .brand-title { color: #015207; font-size: 14px; font-weight: bold; line-height: 16px; }
        .brand-subtitle { margin-top: 5px; color: #1e8e3e; font-size: 10px; font-weight: bold; }
        .report-title { color: #0b4ea2; font-size: 17px; font-weight: bold; text-align: right; }
        .band { margin: 7px 0; padding: 7px; background: #015207; color: #fff; font-size: 14px; font-weight: bold; text-align: center; }
        .summary { margin-bottom: 8px; border: 1px solid #015207; }
        .summary th { padding: 6px 8px; background: #015207; color: #fff; font-size: 10px; text-align: left; }
        .summary td { padding: 5px 7px; border: 1px solid #d5dde3; }
        .label { color: #6b7280; font-size: 7px; }
        .value { margin-top: 2px; font-size: 10px; font-weight: bold; }
        .schedule { page-break-inside: auto; }
        .schedule th { padding: 4px 3px; background: #01a70c; color: #fff; border: 1px solid #019609; font-size: 6.5px; }
        .schedule td { padding: 3px; border: 1px solid #d5dde3; font-size: 7px; }
        .schedule tr { page-break-inside: avoid; page-break-after: auto; }
        .schedule tbody tr:nth-child(even) { background: #f4f8fb; }
        .right { text-align: right; }
        .center { text-align: center; }
        .total td { padding: 5px 3px; background: #0b4ea2; color: #fff; font-weight: bold; border-color: #0b4ea2; }
        .notice { margin: 7px 0; padding: 6px 8px; background: #e8f4fd; border-left: 4px solid #0b4ea2; font-size: 8px; }
        .footer { position: fixed; bottom: -7mm; left: 0; right: 0; border-top: 1px solid #ccc; padding-top: 3px; color: #666; font-size: 7px; text-align: center; }
    </style>
</head>
<body>
    <table class="brand">
        <tr>
            <td width="13%" class="center">
                <img src="{{ public_path('images/cas_sidebar.png') }}" width="75">
            </td>
            <td width="55%">
                <div class="brand-title">COOPERATIVA DE AHORRO Y CRÉDITO DE VÍNCULO LABORAL</div>
                <div class="brand-title">“Oficiales de Caballería Apóstol Santiago” R.L.</div>
                <div class="brand-subtitle">La Paz - Bolivia</div>
            </td>
            <td width="32%">
                <div class="report-title">PROYECCIÓN DE PRÉSTAMO</div>
                <div class="right">Fecha de emisión: {{ now()->format('d/m/Y H:i') }}</div>
                <div class="right">Usuario: {{ optional(auth()->user())->username ?? 'Sistema' }}</div>
            </td>
        </tr>
    </table>

    <div class="band">RESUMEN DE LA PROYECCIÓN</div>

    <table class="summary">
        <tr>
            <th colspan="4">Condiciones del préstamo</th>
        </tr>
        <tr>
            <td width="25%"><div class="label">Tipo de préstamo</div><div class="value">{{ $tipo->descripcion_tasa }}</div></td>
            <td width="25%"><div class="label">Moneda</div><div class="value">{{ $tipo->tipo_moneda === 'SU' ? 'Dólares ($us)' : 'Bolivianos (Bs)' }}</div></td>
            <td width="25%"><div class="label">Tasa mensual</div><div class="value">{{ number_format((float) $tipo->porcentaje, 2) }} %</div></td>
            <td width="25%"><div class="label">Fecha de proyección</div><div class="value">{{ \Carbon\Carbon::parse($fechaProyeccion)->format('d/m/Y') }}</div></td>
        </tr>
        <tr>
            <td><div class="label">Monto proyectado</div><div class="value">{{ $moneda }} {{ number_format($simulacion['capital'], 2) }}</div></td>
            <td><div class="label">Plazo</div><div class="value">{{ $plazo }} meses</div></td>
            <td><div class="label">Tipo de cambio</div><div class="value">{{ $tipoCambio ? number_format($tipoCambio, 5) : 'No aplica' }}</div></td>
            <td><div class="label">Cuota mensual estimada</div><div class="value">{{ $moneda }} {{ number_format($simulacion['cuota'], 2) }}</div></td>
        </tr>
    </table>

    <table class="summary">
        <tr><th colspan="7">Totales calculados</th></tr>
        <tr>
            <td><div class="label">Capital</div><div class="value">{{ $moneda }} {{ number_format($simulacion['capitalTotal'], 2) }}</div></td>
            <td><div class="label">Interés</div><div class="value">{{ $moneda }} {{ number_format($simulacion['interesTotal'], 2) }}</div></td>
            <td><div class="label">Min. Defensa</div><div class="value">{{ $moneda }} {{ number_format($simulacion['minDefensaTotal'], 2) }}</div></td>
            <td><div class="label">ITF</div><div class="value">{{ $moneda }} {{ number_format($simulacion['itfTotal'], 2) }}</div></td>
            <td><div class="label">Interés días</div><div class="value">{{ $moneda }} {{ number_format($simulacion['interesDiasTotal'], 2) }}</div></td>
            <td><div class="label">Reposición</div><div class="value">{{ $moneda }} {{ number_format($simulacion['reposicionTotal'], 2) }}</div></td>
            <td><div class="label">Total a pagar</div><div class="value">{{ $moneda }} {{ number_format($simulacion['totalPagado'], 2) }}</div></td>
        </tr>
    </table>

    <div class="notice">
        Esta proyección es informativa, no corresponde a una solicitud registrada y puede variar si cambian las condiciones del tipo de préstamo.
    </div>

    <div class="band">CRONOGRAMA PROYECTADO</div>
    <table class="schedule">
        <thead>
            <tr>
                <th width="4%">N°</th><th width="9%">Fecha</th><th width="11%">Cuota</th>
                <th width="11%">Capital</th><th width="10%">Interés</th><th width="10%">Min. Def.</th>
                <th width="9%">ITF</th><th width="10%">Int. días</th><th width="9%">Reposición</th><th width="12%">Saldo</th>
            </tr>
        </thead>
        <tbody>
            @foreach($simulacion['cronograma'] as $cuota)
                <tr>
                    <td class="center">{{ $cuota['numero'] }}</td>
                    <td class="center">{{ \Carbon\Carbon::parse($cuota['fecha'])->format('d/m/Y') }}</td>
                    <td class="right">{{ number_format($cuota['cuota'], 2) }}</td>
                    <td class="right">{{ number_format($cuota['capital'], 2) }}</td>
                    <td class="right">{{ number_format($cuota['interes'], 2) }}</td>
                    <td class="right">{{ number_format($cuota['min_defensa'], 2) }}</td>
                    <td class="right">{{ number_format($cuota['itf'], 2) }}</td>
                    <td class="right">{{ number_format($cuota['interes_dias'], 2) }}</td>
                    <td class="right">{{ number_format($cuota['reposicion'], 2) }}</td>
                    <td class="right">{{ number_format($cuota['saldo'], 2) }}</td>
                </tr>
            @endforeach
            <tr class="total">
                <td colspan="2" class="right">TOTALES</td>
                <td class="right">{{ number_format($simulacion['totalPagado'], 2) }}</td>
                <td class="right">{{ number_format($simulacion['capitalTotal'], 2) }}</td>
                <td class="right">{{ number_format($simulacion['interesTotal'], 2) }}</td>
                <td class="right">{{ number_format($simulacion['minDefensaTotal'], 2) }}</td>
                <td class="right">{{ number_format($simulacion['itfTotal'], 2) }}</td>
                <td class="right">{{ number_format($simulacion['interesDiasTotal'], 2) }}</td>
                <td class="right">{{ number_format($simulacion['reposicionTotal'], 2) }}</td>
                <td></td>
            </tr>
        </tbody>
    </table>

    <div class="footer">SCAS · Reporte de proyección de préstamo · Documento informativo</div>
</body>
</html>
