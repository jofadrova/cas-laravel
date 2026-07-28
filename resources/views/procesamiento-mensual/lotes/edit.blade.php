<x-app-layout>
    <x-slot name="header">Editar lote {{ $lote->periodo }} </x-slot>
    <div class="container-fluid py-4">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white py-3">
                <div class="d-flex align-items-center">
                    <i class="bi bi-pencil-square fs-4 text-primary me-2"></i>
                    <div>
                        <h5 class="mb-0">Editar lote mensual</h5>
                        <small class="text-muted">
                            Código de periodo: {{ $lote->codigo_periodo }}
                        </small>
                    </div>
                </div>
            </div>

            <div class="card-body p-4">
                <form method="POST" action="{{ route('procesamiento-mensual.lotes.update', $lote) }}" novalidate>
                    @csrf
                    @method('PUT')
                    @include('procesamiento-mensual.lotes._form')
                </form>
            </div>
        </div>
    </div>
</x-app-layout>