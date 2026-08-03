<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header bg-white py-3">
        <div class="d-flex justify-content-between align-items-center gap-2">
            <h5 class="mb-0">
                <i class="bi {{ $icono }} me-2"></i>{{ $titulo }}
            </h5>
            <span class="badge bg-light text-dark">{{ number_format($coleccion->count()) }}</span>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Archivo</th>
                    <th class="text-center">Filas</th>
                    <th class="text-end">Monto descuento</th>
                    <th class="text-end">Total neto</th>
                    <th class="text-end">Comisión</th>
                    <th class="text-center">Estado</th>
                    <th>Fecha de carga</th>
                </tr>
            </thead>
            <tbody>
                @forelse($coleccion as $archivo)
                    <tr>
                        <td><i class="bi bi-file-earmark-excel text-success me-1"></i>{{ $archivo->nombre_original }}</td>
                        <td class="text-center">{{ number_format($archivo->filas_importadas) }}</td>
                        <td class="text-end">{{ number_format((float) $archivo->total_monto_descuento, 2, ',', '.') }}</td>
                        <td class="text-end">{{ number_format((float) $archivo->total_tot_2, 2, ',', '.') }}</td>
                        <td class="text-end">{{ number_format((float) $archivo->total_comision, 2, ',', '.') }}</td>
                        <td class="text-center"><span class="badge bg-success">{{ $archivo->estado }}</span></td>
                        <td>{{ $archivo->created_at?->format('d/m/Y H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No existen archivos en este grupo.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
