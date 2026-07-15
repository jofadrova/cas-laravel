<!DOCTYPE html>
<html>
<head>
    <link rel="icon" type="image/png" href="{{ asset('images/favicon-96x96.png') }}">
    <meta charset="utf-8">
    <style>

        body{
            font-family: DejaVu Sans, sans-serif;
            font-size:11px;
            color:#222;
            margin:20px;
        }

        .header{
            text-align:center;
            border-bottom:2px solid #198754;
            padding-bottom:10px;
            margin-bottom:15px;
        }

        .logo{
            width:110px;
            margin-bottom:5px;
        }

        .titulo{
            font-size:20px;
            font-weight:bold;
            color:#198754;
        }

        .subtitulo{
            font-size:13px;
            margin-top:3px;
        }

        .fecha{
            text-align:right;
            font-size:10px;
            margin-bottom:10px;
        }

        .seccion{
            background:#198754;
            color:white;
            padding:6px;
            font-size:13px;
            font-weight:bold;
            margin-top:10px;
            margin-bottom:5px;
        }

        table{
            width:100%;
            border-collapse:collapse;
        }

        td{
            padding:3px;
            border:1px solid #dcdcdc;
            vertical-align:top;
        }

        .label{
            width:180px;
            background:#f4f4f4;
            font-weight:bold;
        }

        .foto{
            width:120px;
            height:140px;
            border:1px solid #999;
            text-align:center;
        }

        .foto img{
            width:120px;
            height:140px;
            object-fit:cover;
        }

        .footer{
            margin-top:20px;
            text-align:center;
            font-size:9px;
            color:#666;
        }

        .check{
            font-weight:bold;
            color:green;
        }

        .nocheck{
            color:red;
            font-weight:bold;
        }

    </style>
</head>
<body>
@php
    $logo = public_path('images/cas_sidebar.png');
    $foto = null;
    if(!empty($socio->foto))
    {
        $rutaFoto = storage_path('app/public/socios/'.$socio->foto);
        if(file_exists($rutaFoto))
        {
            $foto = $rutaFoto;
        }
    }
@endphp

<div class="fecha">
    Fecha emisión:
    {{ now()->format('d/m/Y H:i') }}
</div>
<div class="header">
    @if(file_exists($logo))
        <img src="{{ $logo }}" class="logo">
    @endif
    <div class="titulo">KARDEX DE ASOCIADO</div>
    <div class="subtitulo">
        COOPERATIVA DE AHORRO Y CRÉDITO DE VÍNCULO LABORAL
    </div>

    <div class="subtitulo">
        "OFICIALES DE CABALLERÍA APÓSTOL SANTIAGO"
    </div>

</div>
<div class="seccion">
    DATOS PERSONALES
</div>
<table>
    <tr>
        <td  rowspan="6" style="width:120px; text-align:center;">
            @if($foto)
                <img src="{{ $foto }}" class="foto">
            @else
                SIN FOTO
            @endif
        </td>
        <td class="label">Nombre Completo</td>
        <td>
            {{ $socio->paterno }}
            {{ $socio->materno }}
            {{ $socio->nombres }}
        </td>
        <td rowspan="6" style="width:150px; text-align:center;">
           <img src="data:image/svg+xml;base64,{{ $qr }}" width="120">
        </td>
    </tr>
    <tr>
        <td class="label">Documento</td>
        <td>{{ $socio->nro_doc }} {{ $socio->expedido }}</td>
    </tr>
    <tr>
        <td class="label">Fecha Nacimiento</td>
        <td>{{ date('d/m/Y', strtotime($socio->fecha_nac)) }}</td>
    </tr>
    <tr>
        <td class="label">Sexo</td>
        <td>{{ $socio->sexo }}</td>
    </tr>
    <tr>
        <td class="label">Estado Civil</td>
        <td>{{ $socio->estado_civil }}</td>
    </tr>
    <tr>
        <td class="label">Estado Asociado</td>
        <td>
            {{ $socio->estado == 'AC' ? 'ACTIVO' : 'BAJA' }}
        </td>
    </tr>
</table>
<div class="seccion">RESIDENCIA</div>
<table>
    <tr>
        <td class="label">Departamento</td>
        <td>{{ $socio->residencia->departamento ?? '' }}</td>
    </tr>
    <tr>
        <td class="label">Ciudad</td>
        <td>{{ $socio->residencia->ciudad ?? '' }}</td>
    </tr>
    <tr>
        <td class="label">Zona</td>
        <td>{{ $socio->residencia->zona ?? '' }}</td>
    </tr>
    <tr>
        <td class="label">Calle</td>
        <td>{{ $socio->residencia->calle ?? '' }}</td>
    </tr>
    <tr>
        <td class="label">Número</td>
        <td>{{ $socio->residencia->nro ?? '' }}</td>
    </tr>
    <tr>
        <td class="label">Teléfono</td>
        <td>{{ $socio->residencia->telefono ?? '' }}</td>
    </tr>
    <tr>
        <td class="label">Correo</td>
        <td>{{ $socio->residencia->correo ?? '' }}</td>
    </tr>
</table>
<div class="seccion">REGISTRO INSTITUCIONAL</div>
<table>
    <tr>
        <td class="label">Papeleta</td>
        <td>{{ $socio->institucion->papeleta ?? '' }}</td>
    </tr>
    <tr>
        <td class="label">Carnet Militar</td>
        <td>{{ $socio->institucion->carnet_mil ?? '' }}</td>
    </tr>
    <tr>
        <td class="label">COSSMIL</td>
        <td>{{ $socio->institucion->cossmil ?? '' }}</td>
    </tr>
    <tr>
        <td class="label">Mes Afiliación</td>
        <td>{{ $socio->institucion->afil_mes ?? '' }}</td>
    </tr>
    <tr>
        <td class="label">Año Afiliación</td>
        <td>{{ $socio->institucion->afil_anio ?? '' }}</td>
    </tr>
    <tr>
        <td class="label">Año Promoción</td>
        <td>{{ $socio->institucion->anio_prom ?? '' }}</td>
    </tr>
    <tr>
        <td class="label">Escalafón</td>
        <td>{{ $socio->institucion->escalafon->descripcion ?? '' }}</td>
    </tr>
    <tr>
        <td class="label">Fuerza</td>
        <td>{{ $socio->institucion->fuerza->fuerza ?? '' }}</td>
    </tr>
    <tr>
        <td class="label">Arma</td>
        <td>{{ $socio->institucion->arma->descripcion_arma ?? '' }}</td>
    </tr>
    <tr>
        <td class="label">Grado</td>
        <td>{{ $socio->institucion->grado->grado ?? '' }}</td>
    </tr>
    <tr>
        <td class="label">Diplomado</td>
        <td>{{ $socio->institucion->diplomado->descripcion ?? '' }}</td>
    </tr>
    <tr>
        <td class="label">Salario</td>
        <td>{{ number_format($socio->institucion->salario ?? 0,2) }}</td>
    </tr>
</table>
<div class="seccion">DOCUMENTACIÓN PRESENTADA</div>
<table>
    <tr>
        <td>
            {{ ($socio->residencia->formularioSolicitud ?? 'NO') == 'SI' ? '☑' : '☐' }}
            Formulario de Solicitud de Ingreso
        </td>
    </tr>
    <tr>
        <td>
            {{ ($socio->residencia->afiliacionAfcoop ?? 'NO') == 'SI' ? '☑' : '☐' }}
            Formulario AFCOOP
        </td>
    </tr>
    <tr>
        <td>
            {{ ($socio->residencia->fotocopiaCarnet ?? 'NO') == 'SI' ? '☑' : '☐' }}
            Fotocopia de Carnet de Identidad
        </td>
    </tr>
</table>
<div class="seccion">
    BENEFICIARIOS
</div>

<table>
    <thead>
        <tr style="background:#f4f4f4; font-weight:bold;">
            <td style="width:35%;">Nombre Completo</td>
            <td style="width:18%;">Documento</td>
            <td style="width:12%;">Exp.</td>
            <td style="width:20%;">Parentesco</td>
            <td style="width:15%; text-align:right;">%</td>
        </tr>
    </thead>

    <tbody>

        @forelse($socio->dependientes as $dependiente)

            <tr>

                <td>
                    {{ $dependiente->paterno }}
                    {{ $dependiente->materno }}
                    {{ $dependiente->nombres }}
                </td>

                <td>
                    {{ $dependiente->ci }}
                </td>

                <td>
                    {{ $dependiente->exp }}
                </td>

                <td>
                    {{ $dependiente->parentescoDominio->Descripcion ?? $dependiente->parentesco }}
                </td>

                <td style="text-align:right;">
                    {{ number_format($dependiente->porcentaje,2) }} %
                </td>

            </tr>

        @empty

            <tr>
                <td colspan="5" style="text-align:center; color:#777;">
                    No existen beneficiarios registrados.
                </td>
            </tr>

        @endforelse

    </tbody>

</table>

<div class="footer">Sistema Cooperativa Apóstol Santiago - SCAS</div>
<script type="text/php">
if (isset($pdf)) {

    $pdf->page_script('

        $font = $fontMetrics->get_font("Helvetica", "normal");

        $pdf->text(
            40,
            800,
            "SCAS - Sistema Cooperativa Apóstol Santiago",
            $font,
            8
        );

        $pdf->text(
            500,
            800,
            "Página $PAGE_NUM de $PAGE_COUNT",
            $font,
            8
        );

    ');
}
</script>
</body>
</html>
