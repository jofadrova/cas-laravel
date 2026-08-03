<x-app-layout>
    <x-slot name="header">Nueva cuenta contable</x-slot>
    <div class="card shadow-sm">
        <div class="card-header"><h5 class="mb-0">Datos de la cuenta</h5></div>
        <div class="card-body">
            <form method="POST" action="{{ route('contabilidad.cuentas.store') }}" novalidate>
                @csrf
                @include('contabilidad.cuentas._form')
            </form>
        </div>
    </div>
</x-app-layout>
