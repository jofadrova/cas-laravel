<x-app-layout>
<x-slot name="header">Nueva Solicitud de Préstamo</x-slot>
<form id="frmPrestamo" method="POST" action="{{ route('prestamos.store') }}">
    @csrf
    @include('prestamos.partials.form')
</form>
</x-app-layout>
