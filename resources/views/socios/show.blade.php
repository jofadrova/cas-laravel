<x-app-layout>

<x-slot name="header">
    Información del Socio
</x-slot>

<div class="card shadow-sm mb-4">

    <div class="card-header bg-info text-white">
        <i class="fas fa-eye me-2"></i>
        Información del Socio
    </div>

    <div class="card-body">

        <div class="row">

            {{-- FOTO --}}
            <div class="col-md-3 text-center">

                @if($socio->foto)
                    <img src="{{ asset('storage/socios/'.$socio->foto) }}"
                         class="img-thumbnail"
                         style="max-height:220px;">
                @else
                    <img src="{{ asset('images/user-default.png') }}"
                         class="img-thumbnail"
                         style="max-height:220px;">
                @endif

            </div>

            {{-- DATOS --}}
            <div class="col-md-9">

                <h3 class="fw-bold">
                    {{ $socio->paterno }}
                    {{ $socio->materno }}
                    {{ $socio->nombres }}
                </h3>

                <hr>

                <div class="row">

                    <div class="col-md-6 mb-2">
                        <strong>Nro Papeleta:</strong>
                        {{ $socio->institucion?->papeleta }}
                    </div>

                    <div class="col-md-6 mb-2">
                        <strong>Documento:</strong>
                        {{ $socio->nro_doc }}
                        {{ $socio->expedido }}
                    </div>

                    <div class="col-md-6 mb-2">
                        <strong>Fecha Nacimiento:</strong>
                        {{ \Carbon\Carbon::parse($socio->fecha_nac)->format('d/m/Y') }}
                    </div>

                    <div class="col-md-6 mb-2">
                        <strong>Edad:</strong>
                        {{ \Carbon\Carbon::parse($socio->fecha_nac)->age }}
                        años
                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

<div class="card shadow-sm">

    <div class="card-body">

        <ul class="nav nav-tabs">

            <li class="nav-item">
                <button class="nav-link active"
                        data-bs-toggle="tab"
                        data-bs-target="#resumen">
                    Resumen
                </button>
            </li>

            <li class="nav-item">
                <button class="nav-link"
                        data-bs-toggle="tab"
                        data-bs-target="#documentacion">
                    Documentación
                </button>
            </li>

            <li class="nav-item">
                <button class="nav-link"
                        data-bs-toggle="tab"
                        data-bs-target="#prestamos">
                    Préstamos
                </button>
            </li>

            <li class="nav-item">
                <button class="nav-link"
                        data-bs-toggle="tab"
                        data-bs-target="#garantes">
                    Garantes
                </button>
            </li>

        </ul>

        <div class="tab-content mt-4">

    <div class="tab-pane fade show active"
         id="resumen">

        <div class="alert alert-info">

            <i class="fas fa-circle-info me-2"></i>

            Información financiera pendiente de migración.

        </div>

        <div class="row">

            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-header">
                        Total CAO
                    </div>
                    <div class="card-body">
                        0.00
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-header">
                        Total CAV
                    </div>
                    <div class="card-body">
                        0.00
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-header">
                        Total CPI
                    </div>
                    <div class="card-body">
                        0.00
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-header">
                        Certificados
                    </div>
                    <div class="card-body">
                        0.00
                    </div>
                </div>
            </div>

        </div>

    </div>

    <div class="tab-pane fade"
     id="documentacion">

    <div class="mt-3">

        <div class="mb-2">

            {!! $socio->residencia?->formularioSolicitud == 'FA'
                ? '<span class="text-success">✔</span>'
                : '<span class="text-danger">✘</span>' !!}

            Formulario de Solicitud
        </div>

        <div class="mb-2">

            {!! $socio->residencia?->afiliacionAfcoop == 'SI'
                ? '<span class="text-success">✔</span>'
                : '<span class="text-danger">✘</span>' !!}

            Formulario AFCOOP
        </div>

        <div class="mb-2">

            {!! $socio->residencia?->fotocopiaCarnet == 'SI'
                ? '<span class="text-success">✔</span>'
                : '<span class="text-danger">✘</span>' !!}

            Fotocopia de Carnet
        </div>

        <div class="mt-3">
            <strong>Resolución AFCOOP:</strong>
            {{ $socio->residencia?->resolucion }}
        </div>

    </div>

</div>

<div class="tab-pane fade" id="prestamos">
    <div class="alert alert-warning mt-3">
        Información de préstamos pendiente de migración.
    </div>
</div>

<div class="tab-pane fade" id="garantes">
    <div class="alert alert-warning mt-3">
        Información de garantes pendiente de migración.
    </div>
</div>

</div>
</div>
</div>
<div class="mt-4 text-end">

    <a href="{{ route('socios.index') }}"
       class="btn btn-secondary">

        <i class="fas fa-arrow-left me-1"></i>

        Aceptar

    </a>

</div>
</x-app-layout>
