<x-app-layout>
    <x-slot name="header">Nuevo Socio</x-slot>
    <div class="card shadow-sm">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0">
                <i class="bi bi-person-plus-fill me-2"></i>
                Registro de Nuevo Socio
            </h5>
        </div>
        <div class="card-body">
            <form action="#" method="POST" enctype="multipart/form-data">
                @csrf
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
                                <input type="text" class="form-control" name="nombres">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Paterno</label>
                                <input type="text" class="form-control" name="paterno">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Materno</label>
                                <input type="text" class="form-control" name="materno">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">CI</label>
                                <input type="text" class="form-control" name="nro_doc">
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">Expedido</label>
                                <select class="form-select" name="expedido">
                                    <option value="">Seleccione</option>
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
                            <div class="col-md-3">
                                <label class="form-label">Sexo</label>
                                <select class="form-select" name="sexo">
                                    <option value="">Seleccione</option>
                                    <option value="M">Masculino</option>
                                    <option value="F">Femenino</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Estado Civil</label>
                                <select class="form-select" name="estado_civil">
                                    <option value="">Seleccione</option>
                                    <option value="SO">Soltero</option>
                                    <option value="CA">Casado</option>
                                    <option value="DI">Divorciado</option>
                                    <option value="VI">Viudo</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Fecha Nacimiento</label>
                                <input type="date" class="form-control" name="fecha_nac">
                            </div>
                        </div>
                    </div>
                    {{-- DATOS INSTITUCIONALES --}}
                    <div class="tab-pane fade" id="institucionales">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Papeleta</label>
                                <input type="text" class="form-control" name="papeleta">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Carnet Militar</label>
                                <input type="text" class="form-control" name="carnet_mil">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">COSSMIL</label>
                                <input type="text" class="form-control" name="cossmil">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Salario</label>
                                <input type="number" step="0.01" class="form-control" name="salario">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Escalafón</label>
                                <select class="form-select" name="id_escalafon">
                                    <option value="">Seleccione</option>
                                    @foreach($escalafones as $item)
                                        <option value="{{ $item->id }}">
                                            {{ $item->descripcion }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Fuerza</label>
                                <select class="form-select" name="id_fuerza">
                                    <option value="">Seleccione</option>
                                    @foreach($fuerzas as $item)
                                        <option value="{{ $item->id_fuerza }}">
                                            {{ $item->fuerza }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Arma</label>
                                <select class="form-select" name="id_arma">
                                    <option value="">Seleccione</option>
                                    @foreach($armas as $item)
                                        <option value="{{ $item->id_arma }}">
                                            {{ $item->descripcion_arma }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Grado</label>
                                <select class="form-select" name="id_grado">
                                    <option value="">Seleccione</option>
                                    @foreach($grados as $item)
                                        <option value="{{ $item->id_grado }}">
                                            {{ $item->grado }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Diplomado</label>
                                <select class="form-select" name="id_diplomado">
                                    <option value="">Seleccione</option>
                                    @foreach($diplomados as $item)
                                        <option value="{{ $item->id }}">
                                            {{ $item->descripcion }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    {{-- RESIDENCIA --}}
                    <div class="tab-pane fade" id="residencia">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Departamento</label>
                                <input type="text" class="form-control" name="departamento">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Ciudad</label>
                                <input type="text" class="form-control" name="ciudad">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Zona</label>
                                <input type="text" class="form-control" name="zona">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Calle</label>
                                <input type="text" class="form-control" name="calle">
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">Nro</label>
                                <input type="text" class="form-control" name="nro">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Teléfono</label>
                                <input type="text" class="form-control" name="telefono">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Correo</label>
                                <input type="email" class="form-control" name="correo">
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="documentacion">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Formulario de</label>
                                <select name="solicitud" class="form-select">
                                    <option value="">Seleccione</option>
                                    <option value="FA">Solicitud de afiliación 001 A-B</option>
                                    <option value="FI">Solicitud de ingreso 006</option>
                                    <option value="OB">Observado AFCOOP</option>
                                </select>
                            </div>
                            <div class="col-md-6">&nbsp;</div>
                            <div class="col-md-6">
                                <div class="form-check mt-3">
                                    <input type="checkbox" class="form-check-input" id="afiliacion" name="afiliacion">
                                    <label class="form-check-label" for="afiliacion">
                                        Formulario de Cuadro de Afiliación AFCOOP
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check mt-3">
                                    <input type="checkbox" class="form-check-input" id="fotocopia" name="fotocopia">
                                    <label class="form-check-label" for="fotocopia">
                                        Fotocopias de Carnet de Identidad (huella digital)
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- FOTOGRAFIA --}}
                    <div class="tab-pane fade" id="fotografia">
                        <div class="text-center">
                            <img id="preview" src="https://placehold.co/200x250?text=FOTO" class="img-thumbnail mb-3" width="200">
                            <input type="file" name="foto" class="form-control" accept=".jpg,.jpeg,.png" onchange="previewFoto(event)">
                        </div>
                    </div>
                </div>
                <div class="mt-4 d-flex justify-content-end">
                    <a href="{{ route('socios.index') }}" class="btn btn-secondary me-2">
                        Cancelar
                    </a>
                    <button type="submit" class="btn btn-success"> Guardar Socio</button>
                </div>
            </form>
        </div>
    </div>
    <script>
        function previewFoto(event)
        {
            const reader = new FileReader();
            reader.onload = function()
            {
                document.getElementById('preview').src = reader.result;
            }
            reader.readAsDataURL(event.target.files[0]);
        }
    </script>
</x-app-layout>
