<x-app-layout>
    <x-slot name="header">Nuevo Tipo de Préstamo</x-slot>
    <form method="POST" action="{{ route('prestamos.tipos.store') }}">
        @csrf
        @include('prestamos.tipos.form')
    </form>
</x-app-layout>
