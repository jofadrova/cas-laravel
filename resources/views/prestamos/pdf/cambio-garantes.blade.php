<!DOCTYPE html>
    <html lang="es">
    <head>
    <meta charset="UTF-8">

<style>

@page{
    margin:20px;
}

body{
    font-family: DejaVu Sans, sans-serif;
    font-size:10px;
    color:#1f2937;
}

table{
    width:100%;
    border-collapse:collapse;
}

.header td{
    vertical-align:top;
}

.logo{
    width:80px;
}

.titulo{
    text-align:center;
}

.titulo h2{
    margin:0;
    font-size:18px;
}

.titulo h3{
    margin:2px 0;
    font-size:12px;
    font-weight:normal;
}

.registro{
    border:1px solid #444;
    padding:8px;
    text-align:center;
    width:150px;
    font-size:9px;
}

.section-title{
    margin-top:18px;
    padding:6px;
    background:#e5e7eb;
    font-weight:bold;
    border:1px solid #999;
}

.info td{
    padding:4px;
    border:1px solid #ccc;
}

.historial th{
    background:#374151;
    color:white;
    padding:6px;
    border:1px solid #999;
}

.historial td{
    border:1px solid #ccc;
    padding:6px;
}

.justificacion{

    border:1px solid #999;
    min-height:40px;
    padding:10px;
    text-align:justify;

}

.firmas{

    margin-top:70px;

}

.firmas td{

    text-align:center;
    width:50%;

}

.linea{

    border-top:1px solid #000;
    width:220px;
    margin:auto;
    padding-top:4px;

}

.footer{

    position:fixed;
    bottom:0;
    left:0;
    right:0;
    text-align:center;
    font-size:8px;
    color:#666;

}

</style>

    </head>
    <body>
        <table class="header">
            <tr>
                <td width="15%" align="center">
                    <img src="{{ public_path('images/cas_sidebar.png') }}" width="85">
                </td>
                <td class="titulo" width="60%">
                    <h2>COOPERATIVA DE AHORRO Y CRÉDITO DE VÍNCULO LABORAL</h2>
                    <h3>APÓSTOL SANTIAGO CAS R.L.</h3>
                    <h2>CAMBIO DE GARANTES</h2>
                </td>
                <td width="25%">
                    <div class="registro">
                        <strong>Registro N°</strong><br>
                        {{ $historial->id }}
                        <hr>
                        <strong>{{ $historial->tipo_cambio }}</strong>
                        <hr>
                        {{ \Carbon\Carbon::parse($historial->fecha)->format('d/m/Y H:i') }}
                    </div>
                </td>
            </tr>
        </table>

<div class="section-title">
DATOS DEL PRÉSTAMO
</div>

<table class="info">

<tr>

<td width="18%"><strong>Solicitud</strong></td>
<td width="32%">{{ $historial->prestamo->nro_solicitud }}</td>

<td width="18%"><strong>Papeleta</strong></td>
<td>

{{ $historial->prestamo->socio->institucion->papeleta }}

</td>

</tr>

<tr>

<td><strong>Asociado</strong></td>

<td colspan="3">

{{ $historial->prestamo->socio->paterno }}
{{ $historial->prestamo->socio->materno }}
{{ $historial->prestamo->socio->nombres }}

</td>

</tr>

<tr>

<td><strong>Tipo préstamo</strong></td>
<td>{{ $historial->prestamo->tipo->descripcion_tasa }}</td>

<td><strong>Estado</strong></td>
<td>{{ $historial->prestamo->estado }}</td>

</tr>

<tr>

<td><strong>Monto</strong></td>

<td>

{{ number_format($historial->prestamo->monto,2,',','.') }}

</td>

<td><strong>Saldo</strong></td>

<td>

{{ number_format($historial->prestamo->saldo_actual,2,',','.') }}

</td>

</tr>

</table>

<div class="section-title">
CAMBIO DE GARANTES
</div>

<table class="historial">

<thead>

<tr>

<th width="20%">Concepto</th>
<th width="40%">Anterior</th>
<th width="40%">Nuevo</th>

</tr>

</thead>

<tbody>

<tr>

<td><strong>Garante 1</strong></td>

<td>

{{ optional($historial->garante1Old)->paterno }}
{{ optional($historial->garante1Old)->materno }}
{{ optional($historial->garante1Old)->nombres }}

<br>

<small>

{{ optional($historial->garante1Old->institucion)->papeleta }}

</small>

</td>

<td>

{{ optional($historial->garante1New)->paterno }}
{{ optional($historial->garante1New)->materno }}
{{ optional($historial->garante1New)->nombres }}

<br>

<small>

{{ optional($historial->garante1New->institucion)->papeleta }}

</small>

</td>

</tr>

<tr>

<td><strong>Garante 2</strong></td>

<td>

{{ optional($historial->garante2Old)->paterno }}
{{ optional($historial->garante2Old)->materno }}
{{ optional($historial->garante2Old)->nombres }}

<br>

<small>

{{ optional($historial->garante2Old->institucion)->papeleta }}

</small>

</td>

<td>

{{ optional($historial->garante2New)->paterno }}
{{ optional($historial->garante2New)->materno }}
{{ optional($historial->garante2New)->nombres }}

<br>

<small>

{{ optional($historial->garante2New->institucion)->papeleta }}

</small>

</td>

</tr>

</tbody>

</table>

<div class="section-title">
JUSTIFICACIÓN DEL CAMBIO
</div>

<div class="justificacion">

{{ $historial->observaciones }}

</div>

<div class="section-title">
AUDITORÍA
</div>

<table class="info">

<tr>

<td width="20%"><strong>Usuario</strong></td>

<td width="30%">

{{ $historial->usuario->name }}

</td>

<td width="20%"><strong>Fecha</strong></td>

<td>

{{ \Carbon\Carbon::parse($historial->fecha)->format('d/m/Y H:i') }}

</td>

</tr>

<tr>

<td><strong>Tipo</strong></td>

<td colspan="3">

{{ $historial->tipo_cambio }}

</td>

</tr>

</table>

<table class="firmas">

<tr>


<td>

<div class="linea">

Vo.Bo.

</div>

</td>

</tr>

</table>

<div class="footer">

Este documento forma parte del historial de modificaciones del préstamo y fue generado automáticamente por el Sistema SCAS.

</div>

</body>
</html>