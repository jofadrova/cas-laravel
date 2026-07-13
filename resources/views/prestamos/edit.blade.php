<x-app-layout>
<x-slot name="header">Editar Solicitud de Préstamo</x-slot>
<form id="frmPrestamo" method="POST" action="{{ route('prestamos.update', $prestamo) }}">
    <input type="hidden" id="modoEdicion" value="1">
    <input type="hidden" id="garante1Original" value="{{ $prestamo->id_garante1 }}">
    <input type="hidden" id="garante2Original" value="{{ $prestamo->id_garante2 }}">
    @csrf
    @method('PUT')
    @include('prestamos.partials.form')
</form>
</x-app-layout>
