<x-app-layout>
    <x-slot name="header">Registrar recepción mensual</x-slot>

    <div class="container-fluid py-4">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white py-3">
                <div class="d-flex align-items-center">
                    <i class="bi bi-calendar2-plus fs-4 text-success me-2"></i>
                    <div>
                        <h5 class="mb-0">Registrar recepción de lote enviado</h5>
                        <small class="text-muted">
                            Seleccione el lote enviado por MINDEF que regresó para su procesamiento.
                        </small>
                    </div>
                </div>
            </div>

            <div class="card-body p-4">
                <form
                    method="POST"
                    action="{{ route('procesamiento-mensual.lotes.store') }}"
                    novalidate
                >
                    @csrf
                    @include('procesamiento-mensual.lotes._form')
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
