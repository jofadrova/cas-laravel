export function iniciarPagos() {
    const tablaCuotas = document.querySelector('.cuota-pendiente');
    let totalSeleccionadoAnterior = 0;

    if (!tablaCuotas) {
        return;
    }

    const checkboxes = document.querySelectorAll('.cuota-pendiente');
    const cantidadElemento = document.getElementById(
        'cantidadCuotasSeleccionadas'
    );
    const totalElemento = document.getElementById(
        'totalCuotasSeleccionadas'
    );
    const montoEfectivoInput = document.getElementById('montoEfectivo');
    const diferenciaElemento = document.getElementById('diferenciaPago');
    const contenedorDiferencia = document.getElementById(
        'contenedorDiferenciaPago'
    );

    let totalSeleccionado = 0;

    function actualizarResumenPago() {
        let cantidad = 0;
        let total = 0;

        checkboxes.forEach((checkbox) => {
            if (!checkbox.checked) {
                return;
            }

            cantidad++;
            total += Number.parseFloat(checkbox.dataset.total) || 0;
        });

        totalSeleccionado = total;

        const montoActual = Number.parseFloat(montoEfectivoInput.value) || 0;
        // Si el usuario aún no modificó el monto,
        // sincronizar automáticamente con el total.
        if (
            montoActual === 0 ||
            montoActual === totalSeleccionadoAnterior
        ) {
            montoEfectivoInput.value = total.toFixed(2);
        }

        cantidadElemento.textContent = cantidad;
        totalElemento.textContent = formatearMonto(total);

        totalSeleccionadoAnterior = total;

        actualizarDiferencia();
    }

    function actualizarDiferencia() {
        const montoEfectivo =
            Number.parseFloat(montoEfectivoInput.value) || 0;

        const diferencia = montoEfectivo - totalSeleccionado;

        diferenciaElemento.textContent = formatearMonto(diferencia);

        contenedorDiferencia.classList.remove(
            'text-success',
            'text-danger',
            'text-muted'
        );

        if (diferencia > 0) {
            contenedorDiferencia.classList.add('text-success');
            return;
        }

        if (diferencia < 0) {
            contenedorDiferencia.classList.add('text-danger');
            return;
        }

        contenedorDiferencia.classList.add('text-muted');
    }

    function formatearMonto(monto) {
        return monto.toLocaleString('es-BO', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        });
    }

    checkboxes.forEach((checkbox) => {
        checkbox.addEventListener('change', actualizarResumenPago);
    });

    montoEfectivoInput.addEventListener('input', actualizarDiferencia);

    actualizarResumenPago();
}
