<x-app-layout>
<x-slot name="header">Editar Solicitud de Préstamo</x-slot>
<form id="frmPrestamo" method="POST" action="{{ route('prestamos.update', $prestamo) }}">
    @csrf
    @method('PUT')
    @include('prestamos.partials.form')
</form>
</x-app-layout>
