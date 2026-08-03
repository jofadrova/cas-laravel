<x-app-layout>
    <x-slot name="header">Editar cuenta contable</x-slot>
    <div class="card shadow-sm">
        <div class="card-header"><h5 class="mb-0">{{ $cuenta->codigo }} · {{ $cuenta->nombre }}</h5></div>
        <div class="card-body">
            <form method="POST" action="{{ route('contabilidad.cuentas.update', $cuenta) }}" novalidate>
                @csrf
                @method('PUT')
                @include('contabilidad.cuentas._form')
            </form>
        </div>
    </div>
</x-app-layout>
