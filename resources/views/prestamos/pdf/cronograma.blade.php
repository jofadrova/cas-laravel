<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
<style>

@page{
    margin:8mm 10mm;
}

body{
    margin:0;
    padding:0;
    font-family:DejaVu Sans,sans-serif;
    font-size:9px;
    color:#1f2937;
}

table{
    width:100%;
    border-collapse:collapse;
    border-spacing:0;
}

.header td{
    vertical-align:top;
}

.title{
    font-size:20px;
    font-weight:bold;
    color:#0B4EA2;
    line-height:22px;
}

.subtitle{
    font-size:14px;
    font-weight:bold;
    color:#198754;
    line-height:18px;
}

.band{
    margin:6px 0 8px;
    padding:8px;
    background:#015207;
    color:#fff;
    text-align:center;
    font-size:18px;
    font-weight:bold;
    border-radius:4px;
}

.card{
    margin-bottom:8px;
    border:1px solid #015207;
}

.card th{
    padding:7px 10px;
    background:#015207;
    color:#fff;
    font-size:12px;
    text-align:left;
}

.card td{
    padding:5px 8px;
    border-bottom:1px solid #d8d8d8;
    vertical-align:top;
}

.grid td{
    border:none;
}

.estado{
    display:inline-block;
    padding:2px 10px;
    background:#198754;
    color:#fff;
    font-size:8px;
    font-weight:bold;
    border-radius:10px;
}

.crono{
    margin-top:4px;
    page-break-inside:auto;
}

.crono th{
    padding:5px;
    background:#01A70C;
    color:#fff;
    font-size:7px;
    font-weight:bold;
    border:1px solid #019609;
}
.crono tr{
    page-break-inside:avoid;
    page-break-after:auto;
}

.crono td{
    padding:4px;
    border:1px solid #d5dde3;
    font-size:8px;
}

.crono tbody tr:nth-child(even){
    background:#f4f8fb;
}

.r{
    text-align:right;
}

.c{
    text-align:center;
}

.total td{
    padding:6px;
    background:#0B4EA2;
    color:#fff;
    font-weight:bold;
    border:1px solid #0B4EA2;
}

.footer{
    margin-top:4px;
    padding-top:4px;
    border-top:1px solid #cccccc;
    font-size:7px;
    color:#666;
    text-align:center;
}

</style>
</style>
</head>
<body>
<table width="100%" style="border-bottom:3px solid #01a70c;padding-bottom:8px;">
    <tr>
        <td width="13%" align="center">
            <img src="{{ public_path('images/cas_sidebar.png') }}" width="85">
        </td>

        <td width="50%">

            <div style="font-size:15px; font-weight:bold; color:#015207; line-height:15px;">
                COOPERATIVA DE AHORRO Y CRÉDITO DE VÍNCULO LABORAL
            </div>
            <div style="font-size:14px; font-weight:bold; color:#015207; ">
                "Oficiales de Caballería Apóstol Santiago" R.L.
            </div>
            <div style="margin-top:6px; color:#1E8E3E; font-size:11px; font-weight:bold;">
                La Paz - Bolivia
            </div>
        </td>
        <td width="50%" valign="top">
            <table width="100%" style=" border:1px solid #000000; border-radius:8px; padding:8px;">
                <tr>
                    <td width="30%" align="center">
                        <img src="data:image/png;base64,{{ $qr }}" width="90">
                    </td>
                    <td width="70%">
                        <div style="color:#146c43; font-size:11px; font-weight:bold; margin-bottom:8px;">
                            PRÉSTAMO EFECTIVO {{ strtoupper(optional($prestamo->tipo)->descripcion_tasa) }}
                        </div>
                        <table width="100%" style="font-size:9px">
                            <tr>
                                <td style="font-weight:bold;">N° Prestamo:</td>
                                <td>{{ $prestamo->id_solicitud }}</td>
                            </tr>
                            <tr>
                                <td style="font-weight:bold;">Fecha:</td>
                                <td> {{ \Carbon\Carbon::parse($prestamo->fecha)->format('d/m/Y') }}</td>
                            </tr>
                            <tr>
                                <td style="font-weight:bold;">Periodo</td>
                                <td>{{ $prestamo->periodo }} MESES</td>
                            </tr>
                            <tr>
                                <td style="font-weight:bold;">Asiento:</td>
                                <td>
                                    EG-{{ $prestamo->asiento }}
                                </td>
                            </tr>
                        </table>

                    </td>

                </tr>

            </table>

        </td>
<!--
        <td width="25%">

            <table width="100%">

                <tr>

                    <td style="font-weight:bold;">
                        Usuario:
                    </td>

                    <td>
                        {{ auth()->user()->username }}
                    </td>

                </tr>

                <tr>

                    <td style="font-weight:bold;">
                        Fecha:
                    </td>

                    <td>
                        {{ now()->format('d/m/Y H:i:s') }}
                    </td>

                </tr>

            </table>

        </td>
-->
    </tr>
</table>
<!-- ==========================================
     DATOS DEL PRÉSTAMO
=========================================== -->

<table width="100%" style=" border:1px solid #198754; border-radius:6px; margin-top:12px; margin-bottom:18px;border-collapse:collapse; ">
    <tr>
        <td colspan="3" style="background:#015207; color:white; font-size:12px; font-weight:bold; padding:8px 12px;">
            DATOS DEL PRÉSTAMO
        </td>
    </tr>
    <tr>
        <!-- ========================= -->
        <!-- COLUMNA IZQUIERDA -->
        <!-- ========================= -->
        <td width="36%" valign="top" style="padding:12px;"> 
            <table width="100%">
                <tr>
                    <td><strong>Otorgado a</strong></td>
                    <td>

                        {{ $prestamo->socio->paterno }}
                        {{ $prestamo->socio->materno }}
                        {{ $prestamo->socio->nombres }}
                    </td>
                </tr>
                <tr>
                    <td><strong>Por la suma</strong></td>
                    <td>Bs {{ number_format($prestamo->monto,2) }}</td>
                </tr>
                <tr>
                    <td><strong>Tiempo Mora</strong></td>
                    <td>{{ $prestamo->tiempo_mora ?? 0 }} meses</td>
                </tr>
                <tr>
                    <td><strong>Interés</strong></td>
                    <td>{{ number_format($prestamo->interes,2) }} %</td>
                </tr>
               @if($prestamo->id_garante1 != 0)

                <tr>
                    <td><strong>Garante 1</strong></td>
                    <td>
                        {{ optional($prestamo->garante1)->paterno }}
                        {{ optional($prestamo->garante1)->materno }}
                        {{ optional($prestamo->garante1)->nombres }}
                    </td>
                </tr>

                @endif

                @if($prestamo->id_garante2 != 0)

                <tr>
                    <td><strong>Garante 2</strong></td>
                    <td>
                        {{ optional($prestamo->garante2)->paterno }}
                        {{ optional($prestamo->garante2)->materno }}
                        {{ optional($prestamo->garante2)->nombres }}
                    </td>
                </tr>

                @endif
                <tr>
                    <td><strong>Motivo</strong></td>
                    <td>{{ $prestamo->motivo }}</td>
                </tr>
            </table>
        </td>

        <!-- ========================= -->
        <!-- CENTRO -->
        <!-- ========================= -->

        <td width="30%"
            valign="top"
            style="
                padding:12px;
                border-left:1px solid #dddddd;
            ">

            <table width="100%">               
                <tr>
                    <td><strong>Código</strong></td>
                    <td>{{ optional($prestamo->socio->institucion)->papeleta }}</td>

                </tr>
                <tr>
                    <td><strong>Int. Penal</strong></td>
                    <td>{{ number_format($prestamo->interes_penal ?? 0,2) }} %</td>
                </tr>

                <tr>

                    <td><strong>Min. Defensa</strong></td>

                    <td>

                        {{ number_format($prestamo->min_defensa,2) }} %

                    </td>

                </tr>

                <tr>

                    <td><strong>ITF</strong></td>

                    <td>

                        {{ number_format($prestamo->itf,2) }}

                    </td>

                </tr>

                <tr>

                    <td><strong>Estado</strong></td>

                    <td>

                        <span style="
                            background:#198754;
                            color:white;
                            padding:3px 10px;
                            border-radius:12px;
                            font-size:10px;
                            font-weight:bold;
                        ">

                            ACTIVO

                        </span>

                    </td>

                </tr>

            </table>

        </td>

        <!-- ========================= -->
        <!-- DERECHA -->
        <!-- ========================= -->

        <td width="34%"
            valign="top"
            style="
                padding:12px;
                border-left:1px solid #dddddd;
            ">

            <table width="100%">
                <tr>

                    <td><strong>Tipo Cambio</strong></td>

                    <td>

                        T/C Oficial

                    </td>

                </tr>

                <tr>

                    <td><strong>Desc. Desde</strong></td>

                    <td>

                        {{ \Carbon\Carbon::parse($prestamo->fecha)->translatedFormat('F/Y') }}

                    </td>

                </tr>

                <tr>

                    <td><strong>Gasto Papel.</strong></td>

                    <td>

                        {{ number_format($prestamo->papeleria ?? 0,2) }}

                    </td>

                </tr>

                <tr>

                    <td><strong>Total Cancelar</strong></td>

                    <td style="
                        color:#0B4EA2;
                        font-size:14px;
                        font-weight:bold;
                    ">

                        Bs {{ number_format($prestamo->monto,2) }}

                    </td>

                </tr>

            </table>

        </td>

    </tr>

</table>
<div class="band">CRONOGRAMA DE PAGOS</div>
<table class="crono">
    <thead>
        <tr><th>#</th><th>Periodo</th><th>Cuota</th><th>Capital</th><th>Interés</th><th>Min.Def.</th><th>ITF</th><th>Papel</th><th>Saldo</th></tr></thead>
<tbody>
@php($tCuota=$tCap=$tInt=$tMin=$tItf=$tPap=0)
@foreach($cuotas as $c)
@php($tCuota+=$c->cuota_fija) @php($tCap+=$c->amortizacion_cap) @php($tInt+=$c->interes) @php($tMin+=$c->min_defensa) @php($tItf+=$c->itf) @php($tPap+=$c->papel)
    <tr>
        <td class="c">{{ $c->nro_cuota }}</td>
        <td class="c">{{ sprintf('%02d',$c->mes) }}/{{ $c->gestion }}</td>
        <td class="r">{{ number_format($c->cuota_fija,2) }}</td><td class="r">{{ number_format($c->amortizacion_cap,2) }}</td><td class="r">{{ number_format($c->interes,2) }}</td><td class="r">{{ number_format($c->min_defensa,2) }}</td><td class="r">{{ number_format($c->itf,2) }}</td><td class="r">{{ number_format($c->papel,2) }}</td><td class="r">{{ number_format($c->saldo,2) }}</td></tr>
@endforeach
<tr class="total"><td colspan="2">TOTAL</td><td class="r">{{ number_format($tCuota,2) }}</td><td class="r">{{ number_format($tCap,2) }}</td><td class="r">{{ number_format($tInt,2) }}</td><td class="r">{{ number_format($tMin,2) }}</td><td class="r">{{ number_format($tItf,2) }}</td><td class="r">{{ number_format($tPap,2) }}</td><td></td></tr>
</tbody></table>
<div class="footer">Documento generado por SCAS</div>
<hr style="border:none;border-top:1px solid #cccccc;margin-top:10px;">
</body>

</html>
