<x-app-layout>
    <x-slot name="header">Editar Socio</x-slot>
    <div class="card shadow-sm">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0">
                <i class="fas fa-user-edit"></i>
                Edición de Socio
            </h5>
        </div>
        <div class="card-body">
            <form action="{{ route('socios.update', $socio->id) }}" method="POST" enctype="multipart/form-data" novalidate>
                @csrf
                @method('PUT')
                <ul class="nav nav-tabs" id="socioTabs" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#personales" type="button">Datos Personales</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#institucionales" type="button">Datos Institucionales</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#residencia" type="button">Residencia</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#documentacion" type="button">Documentación</button>
                    </li>
                     </li><li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#dependientes" type="button">Beneficiarios</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#fotografia" type="button">Fotografía</button>
                    </li>
                </ul>
                <div class="tab-content border border-top-0 p-4">
                    {{-- DATOS PERSONALES --}}
                    <div class="tab-pane fade show active" id="personales">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Nombres</label>
                                <input type="text" class="form-control letters-only" name="nombres" maxlength="35" value="{{ old('nombres', $socio->nombres) }}">
                                @error('nombres')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Paterno</label>
                                <input type="text" class="form-control letters-only" name="paterno" maxlength="35" value="{{ old('paterno', $socio->paterno) }}">
                                @error('paterno')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Materno</label>
                                <input type="text" class="form-control letters-only"  name="materno" maxlength="35" value="{{ old('materno', $socio->materno) }}">
                                @error('materno')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">CI</label>
                                <input type="text" class="form-control numbers-only" name="nro_doc" maxlength="15" value="{{ old('nro_doc', $socio->nro_doc) }}">
                                @error('nro_doc')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">Expedido</label>
                                <select class="form-select" name="expedido">
                                    <option value="">- Seleccione -</option>
                                    <option value="LP" {{ old('expedido', $socio->expedido) == 'LP' ? 'selected' : '' }}>LP</option>
                                    <option value="CB" {{ old('expedido', $socio->expedido) == 'CB' ? 'selected' : '' }}>CB</option>
                                    <option value="SC" {{ old('expedido', $socio->expedido) == 'SC' ? 'selected' : '' }}>SC</option>
                                    <option value="OR" {{ old('expedido', $socio->expedido) == 'OR' ? 'selected' : '' }}>OR</option>
                                    <option value="PT" {{ old('expedido', $socio->expedido) == 'PT' ? 'selected' : '' }}>PT</option>
                                    <option value="CH" {{ old('expedido', $socio->expedido) == 'CH' ? 'selected' : '' }}>CH</option>
                                    <option value="TJ" {{ old('expedido', $socio->expedido) == 'TJ' ? 'selected' : '' }}>TJ</option>
                                    <option value="BE" {{ old('expedido', $socio->expedido) == 'BE' ? 'selected' : '' }}>BE</option>
                                    <option value="PD" {{ old('expedido', $socio->expedido) == 'PD' ? 'selected' : '' }}>PD</option>
                                </select>
                                @error('expedido')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Sexo</label>
                                <select class="form-select" name="sexo">
                                    <option value="">- Seleccione -</option>
                                    @foreach($sexos as $item)
                                        <option value="{{ $item->abrev }}" {{ old('sexo', $socio->sexo  ) == $item->abrev ? 'selected' : '' }}>{{ $item->Descripcion }}</option>
                                    @endforeach
                                </select>
                                @error('sexo')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Estado Civil</label>
                                <select class="form-select" name="estado_civil">
                                    <option value="">- Seleccione -</option>
                                    @foreach($estadosCiviles as $item)
                                        <option value="{{ $item->abrev }}"
                                            {{ old('estado_civil', $socio->estado_civil) == $item->abrev ? 'selected' : '' }}>
                                            {{ $item->Descripcion }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('estado_civil')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Fecha Nacimiento</label>
                                <input type="date" class="form-control" name="fecha_nac" value="{{ old('fecha_nac', $socio->fecha_nac) }}">
                                @error('fecha_nac')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                        </div>
                    </div>
                    {{-- DATOS INSTITUCIONALES --}}
                    <div class="tab-pane fade" id="institucionales">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Papeleta</label>
                                <input type="text" class="form-control numbers-only" maxlength="8" name="papeleta" value="{{ old('papeleta', $socio->institucion->papeleta) }}">
                                @error('papeleta')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Carnet Militar</label>
                                <input type="text" class="form-control numbers-only" maxlength="15" name="carnet_mil" value="{{ old('carnet_mil', $socio->institucion->carnet_mil) }}">
                                @error('carnet_mil')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">COSSMIL</label>
                                <input type="text" class="form-control numbers-only" maxlength="15" name="cossmil" value="{{ old('cossmil', $socio->institucion->cossmil) }}">
                                @error('cossmil')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                            {{-- AFILIACION --}}
                            <div class="col-md-2">
                                <label class="form-label">Mes Afiliación</label>
                                <select class="form-select"name="afil_mes">
                                    <option value="">- Seleccione -</option>
                                        @foreach($meses as $item)
                                            <option value="{{ $item->abrev }}" {{ old('afil_mes', $socio->institucion->afil_mes) == $item->abrev ? 'selected' : '' }}>{{ $item->Descripcion }}</option>
                                        @endforeach
                                </select>
                                @error('afil_mes')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Año Afiliación</label>
                                <select class="form-select" name="afil_anio">
                                    <option value="">- Seleccione -</option>
                                    @for($anio = date('Y'); $anio >= 2000; $anio--)
                                        <option value="{{ $anio }}" {{ old('afil_anio', $socio->institucion->afil_anio) == $anio ? 'selected' : '' }}>{{ $anio }}</option>
                                    @endfor
                                </select>
                                @error('afil_anio')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Fecha Graduación</label>
                                <input type="date" class="form-control" name="anio_prom" value="{{ old('anio_prom', $socio->institucion->anio_prom) }}">
                                @error('anio_prom')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                            <hr>
                            <div class="col-md-4">
                                <label class="form-label">Escalafón</label>
                                <select class="form-select" name="id_escalafon">
                                    <option value="">- Seleccione -</option>
                                    @foreach($escalafones as $item)
                                        <option value="{{ $item->id }}" {{ old('id_escalafon', $socio->institucion->id_escalafon) == $item->id ? 'selected' : '' }}>
                                            {{ $item->descripcion }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('id_escalafon')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                                <div class="col-md-4">
                                <label class="form-label">Fuerza</label>
                                <select class="form-select" name="id_fuerza">
                                    <option value="">- Seleccione -</option>
                                    @foreach($fuerzas as $item)
                                        <option value="{{ $item->id_fuerza }}" {{ old('id_fuerza', $socio->institucion->id_fuerza) == $item->id_fuerza ? 'selected' : '' }}>
                                            {{ $item->fuerza }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('id_fuerza')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Arma</label>
                                <select class="form-select" name="id_arma">
                                    <option value="">- Seleccione -</option>
                                    @foreach($armas as $item)
                                        <option value="{{ $item->id_arma }}" {{ old('id_arma', $socio->institucion->id_arma) == $item->id_arma ? 'selected' : '' }}>
                                            {{ $item->descripcion_arma }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('id_arma')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Grado</label>
                                <select class="form-select" name="id_grado">
                                    <option value="">- Seleccione -</option>
                                    @foreach($grados as $item)
                                        <option value="{{ $item->id_grado }}" {{ old('id_grado', $socio->institucion->id_grado) == $item->id_grado ? 'selected' : '' }}>
                                            {{ $item->grado }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('id_grado')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Diplomado</label>
                                <select class="form-select" name="id_diplomado">
                                    <option value="">- Seleccione -</option>
                                    @foreach($diplomados as $item)
                                        <option value="{{ $item->id }}" {{ old('id_diplomado', $socio->institucion->id_diplomado) == $item->id ? 'selected' : '' }}>
                                            {{ $item->descripcion }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('id_diplomado')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Salario</label>
                                <input type="number" max="99999.99" step="0.01" class="form-control" name="salario" value="{{ old('salario', $socio->institucion->salario) }}">
                                @error('salario')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                        </div>
                    </div>
                    {{-- RESIDENCIA --}}
                    <div class="tab-pane fade" id="residencia">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Departamento</label>
                                <select class="form-select" name="departamento">
                                    <option value="">- Seleccione -</option>
                                    @foreach($departamentos as $item)
                                        <option value="{{ $item->abrev }}" {{ old('departamento', $socio->residencia->departamento) == $item->abrev ? 'selected' : '' }}>
                                            {{ $item->Descripcion }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('departamento')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Radicatoria</label>
                                <input type="text" class="form-control alphanumeric-only" maxlength="50" name="ciudad" value="{{ old('ciudad', $socio->residencia->ciudad) }}">
                                @error('ciudad')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Zona</label>
                                <input type="text" class="form-control alphanumeric-only" maxlength="50" name="zona" value="{{ old('zona', $socio->residencia->zona) }}">
                                @error('zona')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Calle</label>
                                <input type="text" class="form-control alphanumeric-only" maxlength="50" name="calle" value="{{ old('calle', $socio->residencia->calle) }}">
                                @error('calle')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">Nro</label>
                                <input type="text" class="form-control numbers-only" maxlength="5" name="nro" value="{{ old('nro', $socio->residencia->nro) }}">
                                @error('nro')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Teléfono</label>
                                <input type="text" class="form-control numbers-only" maxlength="8" name="telefono" value="{{ old('telefono', $socio->residencia->telefono) }}">
                                @error('telefono')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Correo</label>
                                <input type="email" class="form-control" maxlength="100" name="correo" value="{{ old('correo', $socio->residencia->correo) }}">
                                @error('correo')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Resolución Inc. AFCOOP</label>
                                <select class="form-select" name="resolucion">
                                    <option value="">- Seleccione -</option>
                                        @foreach($resoluciones as $item)
                                            <option value="{{ $item->id }}" {{ old('resolucion', $socio->residencia->resolucion) == $item->id ? 'selected' : '' }}>
                                                {{ $item->num }}/{{ $item->gestion }}
                                            </option>
                                        @endforeach
                                </select>
                                @error('resolucion')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="documentacion">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Formulario de</label>
                                <select name="solicitud" class="form-select">
                                    <option value="">- Seleccione -</option>
                                    <option value="FA" {{ old('solicitud', $socio->residencia->formularioSolicitud) == 'FA' ? 'selected' : '' }}>Solicitud de afiliación 001 A-B</option>
                                    <option value="FI" {{ old('solicitud', $socio->residencia->formularioSolicitud) == 'FI' ? 'selected' : '' }}>Solicitud de ingreso 006</option>
                                    <option value="OB" {{ old('solicitud', $socio->residencia->formularioSolicitud) == 'OB' ? 'selected' : '' }}>Observado AFCOOP</option>
                                </select>
                                @error('solicitud')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                            <div class="col-md-4">
                                <div class="form-check mt-3">
                                    <label class="form-check-label" for="afiliacion">Formulario de Cuadro de Afiliación AFCOOP</label>
                                    <input type="checkbox" class="form-check-input" id="afiliacion" name="afiliacion"
                                        {{ old('afiliacion', $socio->residencia?->afiliacionAfcoop == 'SI' ) ? 'checked' : '' }}>
                                    @error('afiliacion')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check mt-3">
                                    <label class="form-check-label" for="fotocopia">Fotocopias de Carnet de Identidad (huella digital)</label>
                                    <input type="checkbox" class="form-check-input" id="fotocopia" name="fotocopia" {{ old('fotocopia', $socio->residencia->fotocopiaCarnet== 'SI') ? 'checked' : '' }}>
                                    @error('fotocopia')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- BENEFICIARIOS --}}
                    <div class="tab-pane fade" id="dependientes">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">
                                <i class="fas fa-users text-primary"></i>
                                Beneficiarios Registrados
                            </h5>
                            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalDependiente">
                                <i class="fas fa-plus-circle"></i>
                                Agregar Beneficiario
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover align-middle" id="tablaDependientes">
                                <thead class="table-light">
                                    <tr>
                                        <th width="30%">Nombre Completo</th>
                                        <th width="15%">CI</th>
                                        <th width="15%">Exp.</th>
                                        <th width="20%">Parentesco</th>
                                        <th width="10%">%</th>
                                        <th width="10%" class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="tbodyDependientes">
                                    <tr id="filaSinDependientes">
                                        <td colspan="6" class="text-center text-muted py-4">No existen beneficiarios registrados.</td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="4" class="text-end">Total Asignado</th>
                                        <th id="totalPorcentaje">0 %</th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    {{-- FOTOGRAFIA --}}
                    <div class="tab-pane fade" id="fotografia">
                        <div class="text-center">
                            @if($socio->foto)

                                <img src="{{ asset('storage/socios/' . $socio->foto) }}"
                                    alt="Fotografía"
                                    class="img-thumbnail"
                                    style="max-height: 220px;">

                            @else

                                <img src="{{ asset('images/user-default.png') }}"
                                    alt="Sin fotografía"
                                    class="img-thumbnail"
                                    style="max-height: 220px;">

                            @endif
                            <input type="file" name="foto" class="form-control" accept=".jpg,.jpeg,.png" onchange="previewFoto(event)">
                        </div>
                    </div>
                </div>
                <div class="mt-4 d-flex justify-content-end">
                    <a href="{{ route('socios.index') }}" class="btn btn-secondary me-2">
                        Cancelar
                    </a>
                    <button type="submit" class="btn btn-success"> Actualizar Socio</button>
                </div>
            </form>
            <script>
                window.oldDependientes = @json(old('dependientes', []));
                window.dependientes = @json($socio->dependientes ?? []);
            </script>
        </div>
    </div>
    <!-- MODAL PARA BENEFICIARIOS -->
    <div class="modal fade" id="modalDependiente" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-user-plus"></i>
                            Agregar Beneficiario
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="mensajeDependiente" class="d-none mb-3"></div>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Nombres</label>
                                <input type="text" class="form-control" id="dep_nombres">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Paterno</label>
                            <input type="text" class="form-control" id="dep_paterno">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Materno</label>
                            <input type="text" class="form-control" id="dep_materno">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">CI</label>
                            <input type="text" class="form-control" id="dep_ci">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Expedido</label>
                            <select class="form-select" id="dep_expedido">
                                <option value="">--</option>
                                <option>LP</option>
                                <option>CB</option>
                                <option>SC</option>
                                <option>OR</option>
                                <option>PT</option>
                                <option>CH</option>
                                <option>TJ</option>
                                <option>BE</option>
                                <option>PD</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Parentesco</label>
                            <select class="form-select" id="dep_parentesco">
                                <option value="">- Seleccione -</option>
                                @foreach($parentescos as $parentesco)
                                    <option value="{{ $parentesco->abrev }}">
                                        {{ $parentesco->Descripcion }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Porcentaje</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="dep_porcentaje">
                                <span class="input-group-text">
                                    %
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="button" class="btn btn-success" id="btnAgregarDependiente">
                        <i class="fas fa-plus-circle"></i>
                        Agregar
                    </button>
                </div>
            </div>
        </div>
    </div>
 
</x-app-layout>
