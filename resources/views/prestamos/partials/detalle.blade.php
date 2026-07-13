<div class="modal fade" id="modalDetallePrestamo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <div>
                    <h5 class="modal-title mb-0">
                        <i class="bi bi-file-earmark-text me-2"></i>
                        Detalle del préstamo
                    </h5>
                    <small>Solicitud N° <strong>{{ $prestamo->nro_solicitud }}</strong>
                    </small>
                </div>
                <button class="btn-close btn-close-white" data-bs-dismiss="modal"> </button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-header bg-light fw-semibold">
                                Datos generales
                            </div>
                            <div class="card-body">
                                <table class="table table-sm mb-0">
                                    <tr>
                                        <th width="40%">Socio</th>
                                        <td>{{ $prestamo->socio->nombre_completo }}</td>
                                    </tr>
                                    <tr>
                                        <th>Papeleta</th>
                                        <td>{{ $prestamo->socio->papeleta }}</td>
                                    </tr>
                                    <tr>
                                        <th>Tipo</th>
                                        <td>{{ $prestamo->tipo->descripcion }}</td>
                                    </tr>
                                    <tr>
                                        <th>Estado</th>
                                        <td>
                                            @if($prestamo->estado)
                                                <span class="badge bg-success">
                                                    ACTIVO
                                                </span>
                                            @else
                                                <span class="badge bg-danger">
                                                    CANCELADO
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-header bg-light fw-semibold">
                                Datos económicos
                            </div>
                            <div class="card-body">
                                <table class="table table-sm mb-0">
                                    <tr>
                                        <th>Monto</th>
                                        <td class="text-end">
                                            {{ number_format($prestamo->monto,2) }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Saldo actual</th>
                                        <td class="text-end">
                                            {{ number_format($prestamo->saldo_actual,2) }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Interés</th>
                                        <td>
                                            {{ $prestamo->interes }} %
                                        </td>
                                    </tr>

                                    <tr>
                                        <th>Periodo</th>
                                        <td>
                                            {{ $prestamo->periodo }}
                                        </td>
                                    </tr>

                                    <tr>
                                        <th>Tipo cambio</th>
                                        <td>
                                            {{ $prestamo->tipo_cambio }}
                                        </td>
                                    </tr>

                                </table>

                            </div>

                        </div>

                    </div>
                    <div class="col-12">

                        <div class="card">

                            <div class="card-header bg-light fw-semibold">

                                Garantes

                            </div>

                            <div class="card-body">

                                <div class="row">

                                    <div class="col-md-6">

                                        <strong>Primer garante</strong>

                                        <hr>

                                        @if($prestamo->garante1)

                                            {{ $prestamo->garante1->nombre_completo }}

                                        @else

                                            <span class="text-muted">
                                                No registrado
                                            </span>

                                        @endif

                                    </div>

                                    <div class="col-md-6">

                                        <strong>Segundo garante</strong>

                                        <hr>

                                        @if($prestamo->garante2)

                                            {{ $prestamo->garante2->nombre_completo }}

                                        @else

                                            <span class="text-muted">
                                                No registrado
                                            </span>

                                        @endif

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>
                    <div class="modal-footer">

                    <button
                        type="button"
                        class="btn btn-outline-primary">

                        <i class="bi bi-printer"></i>

                        Imprimir préstamo

                    </button>

                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal">

                        Cerrar

                    </button>

                </div>


                </div>
            </div>
        </div>
    </div>
</div>
