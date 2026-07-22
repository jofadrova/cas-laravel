<x-app-layout>
    <x-slot name="header">Reporte de Pagos</x-slot>

    @include('pagos.reporte')

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const modalElemento = document.getElementById('modalReportePagos');
                const modal = new bootstrap.Modal(modalElemento, {
                    backdrop: 'static'
                });

                modalElemento.addEventListener('hidden.bs.modal', function () {
                    window.location.href = @json(route('prestamos.index'));
                });

                modal.show();
            });
        </script>
    @endpush
</x-app-layout>
