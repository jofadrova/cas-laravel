<x-app-layout>
    <x-slot name="header">Editar Tipo de Préstamo</x-slot>
    <form method="POST" action="{{ route('prestamos.tipos.update', $tasa->id_tasa) }}">
        @csrf
        @method('PUT')
        @include('prestamos.tipos.form')
    </form>
</x-app-layout>
