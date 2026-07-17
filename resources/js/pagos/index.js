import { confirmarOperacion } from '../scas/confirmacion';

export function iniciarPagos() {
    const formulario = document.getElementById('formPagoCuotas');
    let totalSeleccionadoAnterior = 0;

    if (!formulario) {
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
    const esPrestamoDolares = formulario.dataset.monedaPrestamo === 'SU';
    const tipoCambioInput = document.getElementById('tipoCambio');
    const totalExigidoBolivianos = document.getElementById('totalExigidoBolivianos');
    const montoEquivalenteDolares = document.getElementById('montoEquivalenteDolares');

    let totalSeleccionado = 0;

    function obtenerTipoCambio() {
        return Number.parseFloat(tipoCambioInput?.value) || 0;
    }

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

        const tipoCambio = obtenerTipoCambio();
        const totalEfectivoSugerido = redondearMonto(
            esPrestamoDolares ? total * tipoCambio : total
        );
        const montoActual = Number.parseFloat(montoEfectivoInput.value) || 0;
        // Si el usuario aún no modificó el monto,
        // sincronizar automáticamente con el total.
        if (
            montoActual === 0 ||
            montoActual === totalSeleccionadoAnterior
        ) {
            montoEfectivoInput.value = totalEfectivoSugerido.toFixed(2);
        }

        cantidadElemento.textContent = cantidad;
        totalElemento.textContent = formatearMonto(total);

        totalSeleccionadoAnterior = totalEfectivoSugerido;

        actualizarDiferencia();
    }

    function actualizarDiferencia() {
        const montoEfectivo =
            Number.parseFloat(montoEfectivoInput.value) || 0;
        const tipoCambio = obtenerTipoCambio();

        const totalExigido = redondearMonto(
            esPrestamoDolares
                ? totalSeleccionado * tipoCambio
                : totalSeleccionado
        );
        const diferencia = redondearMonto(montoEfectivo - totalExigido);

        diferenciaElemento.textContent = formatearMonto(diferencia);

        if (esPrestamoDolares) {
            totalExigidoBolivianos.textContent = formatearMonto(totalExigido);
            montoEquivalenteDolares.textContent = formatearMonto(
                tipoCambio > 0 ? montoEfectivo / tipoCambio : 0
            );
        }

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

    function redondearMonto(monto) {
        return Math.round((monto + Number.EPSILON) * 100) / 100;
    }

    checkboxes.forEach((checkbox) => {
        checkbox.addEventListener('change', actualizarResumenPago);
    });

    montoEfectivoInput.addEventListener('input', actualizarDiferencia);

    tipoCambioInput?.addEventListener('input', actualizarResumenPago);

    actualizarResumenPago();

    formulario.addEventListener('submit', async (evento) => {
        evento.preventDefault();

        const cantidad = Array.from(checkboxes)
            .filter((checkbox) => checkbox.checked)
            .length;
        const confirmado = await confirmarOperacion({
            titulo: 'Confirmar Pago por Cuotas',
            mensaje:
                'Se registrará el pago de las cuotas seleccionadas y se actualizará el saldo del préstamo.',
            detalles: [
                {
                    etiqueta: 'Cuotas seleccionadas',
                    valor: String(cantidad),
                },
                {
                    etiqueta: 'Total de cuotas',
                    valor: `${esPrestamoDolares ? '$us' : 'Bs'} ${formatearMonto(totalSeleccionado)}`,
                },
                {
                    etiqueta: 'Efectivo recibido',
                    valor: `Bs ${formatearMonto(Number.parseFloat(montoEfectivoInput.value) || 0)}`,
                },
                ...(esPrestamoDolares
                    ? [{
                        etiqueta: 'Tipo de cambio',
                        valor: obtenerTipoCambio().toFixed(5),
                    }]
                    : []),
            ],
            textoConfirmar: 'Registrar pago',
        });

        if (confirmado) {
            formulario.submit();
        }
    });

    const formularioTotal = document.getElementById('formPagoTotal');

    if (!formularioTotal) {
        return;
    }

    const saldoTotal =
        Number.parseFloat(formularioTotal.dataset.saldoTotal) || 0;
    const esTotalDolares =
        formularioTotal.dataset.monedaPrestamo === 'SU';
    const montoEfectivoTotalInput =
        document.getElementById('montoEfectivoTotal');
    const tipoCambioTotalInput =
        document.getElementById('tipoCambioTotal');
    const diferenciaTotalElemento =
        document.getElementById('diferenciaPagoTotal');
    const contenedorDiferenciaTotal =
        document.getElementById('contenedorDiferenciaTotal');
    const totalExigidoBolivianosTotal =
        document.getElementById('totalExigidoBolivianosTotal');
    const montoEquivalenteDolaresTotal =
        document.getElementById('montoEquivalenteDolaresTotal');

    let totalEfectivoSugeridoAnterior = 0;

    function obtenerTipoCambioTotal() {
        return Number.parseFloat(tipoCambioTotalInput?.value) || 0;
    }

    function actualizarPagoTotal(sincronizarEfectivo = false) {
        const tipoCambio = obtenerTipoCambioTotal();
        const totalExigido = redondearMonto(
            esTotalDolares ? saldoTotal * tipoCambio : saldoTotal
        );
        const montoActual =
            Number.parseFloat(montoEfectivoTotalInput.value) || 0;

        if (
            sincronizarEfectivo &&
            (montoActual === 0 ||
                montoActual === totalEfectivoSugeridoAnterior)
        ) {
            montoEfectivoTotalInput.value = totalExigido.toFixed(2);
        }

        totalEfectivoSugeridoAnterior = totalExigido;

        const montoEfectivo =
            Number.parseFloat(montoEfectivoTotalInput.value) || 0;
        const diferencia = redondearMonto(montoEfectivo - totalExigido);

        diferenciaTotalElemento.textContent = formatearMonto(diferencia);

        if (esTotalDolares) {
            totalExigidoBolivianosTotal.textContent =
                formatearMonto(totalExigido);
            montoEquivalenteDolaresTotal.textContent = formatearMonto(
                tipoCambio > 0 ? montoEfectivo / tipoCambio : 0
            );
        }

        contenedorDiferenciaTotal.classList.remove(
            'text-success',
            'text-danger',
            'text-muted'
        );

        if (diferencia > 0) {
            contenedorDiferenciaTotal.classList.add('text-success');
        } else if (diferencia < 0) {
            contenedorDiferenciaTotal.classList.add('text-danger');
        } else {
            contenedorDiferenciaTotal.classList.add('text-muted');
        }
    }

    montoEfectivoTotalInput.addEventListener('input', () => {
        actualizarPagoTotal(false);
    });

    tipoCambioTotalInput?.addEventListener('input', () => {
        actualizarPagoTotal(true);
    });

    actualizarPagoTotal(true);

    formularioTotal.addEventListener('submit', async (evento) => {
        evento.preventDefault();

        const montoEfectivo =
            Number.parseFloat(montoEfectivoTotalInput.value) || 0;
        const confirmado = await confirmarOperacion({
            titulo: 'Confirmar Pago Total',
            mensaje:
                'Esta operación cancelará completamente el préstamo y marcará todas sus cuotas pendientes como pagadas.',
            detalles: [
                {
                    etiqueta: 'Saldo que será cancelado',
                    valor: `${esTotalDolares ? '$us' : 'Bs'} ${formatearMonto(saldoTotal)}`,
                    clase: 'text-danger',
                },
                {
                    etiqueta: 'Cuotas pendientes',
                    valor: formularioTotal.dataset.cuotasPendientes,
                },
                {
                    etiqueta: 'Efectivo recibido',
                    valor: `Bs ${formatearMonto(montoEfectivo)}`,
                },
                ...(esTotalDolares
                    ? [{
                        etiqueta: 'Tipo de cambio',
                        valor: obtenerTipoCambioTotal().toFixed(5),
                    }]
                    : []),
                {
                    etiqueta: 'Estado final',
                    valor: 'CANCELADO (PA)',
                    clase: 'text-danger',
                },
            ],
            textoConfirmar: 'Cancelar préstamo',
        });

        if (confirmado) {
            formularioTotal.submit();
        }
    });
}
