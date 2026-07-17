import { confirmarOperacion } from '../scas/confirmacion';

export function iniciarAmortizacionCapital() {
    const formulario = document.getElementById('formAmortizacionCapital');

    if (!formulario) {
        return;
    }

    const esDolares = formulario.dataset.esDolares === '1';
    const saldoActual = Number.parseFloat(formulario.dataset.saldo) || 0;
    const tasaMensual =
        Number.parseFloat(formulario.dataset.tasaMensual) || 0;
    const tasaMinDefensa =
        Number.parseFloat(formulario.dataset.minDefensa) || 0;
    const cuotasRestantes =
        Number.parseInt(formulario.dataset.cuotasRestantes, 10) || 0;
    const cuotaTotalActual =
        Number.parseFloat(formulario.dataset.cuotaTotalActual) || 0;
    const otrosCargos =
        Number.parseFloat(formulario.dataset.otrosCargos) || 0;
    const primerNumeroCuota =
        Number.parseInt(formulario.dataset.primerNumeroCuota, 10) || 1;
    const periodoActual =
        Number.parseInt(formulario.dataset.periodoActual, 10) || 0;
    const cargosPendientes = JSON.parse(
        document.getElementById('cargosCuotasPendientes')?.textContent ||
            '[]'
    );

    const montoInput = document.getElementById(
        'montoEfectivoAmortizacion'
    );
    const tipoCambioInput = document.getElementById(
        'tipoCambioAmortizacion'
    );
    const montoConvertidoInput = document.getElementById(
        'montoCapitalConvertido'
    );
    const nuevaCuotaInput = document.getElementById(
        'nuevaCuotaEstimada'
    );
    const nuevoPlazoInput = document.getElementById(
        'nuevoPlazoEstimado'
    );
    const nuevoSaldoInput = document.getElementById(
        'nuevoSaldoCapital'
    );
    const botonSimular = document.getElementById(
        'btnSimularAmortizacion'
    );
    const mensaje = document.getElementById('mensajeTipoRecalculo');
    const error = document.getElementById(
        'errorSimulacionAmortizacion'
    );

    function redondear(monto) {
        return Math.round((monto + Number.EPSILON) * 100) / 100;
    }

    function montoCapital() {
        const montoEfectivo = Number.parseFloat(montoInput.value) || 0;

        if (!esDolares) {
            return redondear(montoEfectivo);
        }

        const tipoCambio =
            Number.parseFloat(tipoCambioInput?.value) || 0;

        return tipoCambio > 0
            ? redondear(montoEfectivo / tipoCambio)
            : 0;
    }

    function actualizarConversion() {
        if (montoConvertidoInput) {
            montoConvertidoInput.value = montoCapital().toFixed(2);
        }

        invalidarSimulacion();
    }

    function invalidarSimulacion() {
        nuevaCuotaInput.value = '';
        nuevoPlazoInput.value = '';
        nuevoSaldoInput.value = '';
        error.classList.add('d-none');
        error.textContent = '';
    }

    function mostrarError(texto) {
        invalidarSimulacion();
        error.textContent = texto;
        error.classList.remove('d-none');
    }

    function calcularCuotaBase(saldo, numeroCuotas) {
        if (tasaMensual === 0) {
            return saldo / numeroCuotas;
        }

        const factor = Math.pow(1 + tasaMensual, numeroCuotas);
        return saldo * ((tasaMensual * factor) / (factor - 1));
    }

    function calcularCuotasNuevoPlazo(saldo) {
        let saldoTemporal = saldo;
        let cuotas = 0;

        while (saldoTemporal > 0 && cuotas < cuotasRestantes) {
            const interes = redondear(saldoTemporal * tasaMensual);
            const cargos =
                Number.parseFloat(cargosPendientes[cuotas]) || 0;
            const capital = redondear(
                cuotaTotalActual - interes - cargos
            );

            if (capital <= 0) {
                return 0;
            }

            saldoTemporal = redondear(
                saldoTemporal - Math.min(capital, saldoTemporal)
            );
            cuotas++;
        }

        return saldoTemporal <= 0 && cuotas < cuotasRestantes
            ? cuotas
            : 0;
    }

    function simular() {
        const capital = montoCapital();

        if (capital <= 0) {
            mostrarError('Ingrese un monto válido para amortizar.');
            return;
        }

        if (capital >= saldoActual) {
            mostrarError(
                'La amortización debe ser menor al saldo actual. Para cancelar el préstamo utilice Pago Total.'
            );
            return;
        }

        const nuevoSaldo = redondear(saldoActual - capital);
        const tipo = formulario.querySelector(
            'input[name="tipo_recalculo"]:checked'
        )?.value;

        if (tipo === 'CUOTA') {
            const cuotaBase = calcularCuotaBase(
                nuevoSaldo,
                cuotasRestantes
            );
            const minDefensa = redondear(
                cuotaBase * tasaMinDefensa
            );
            nuevaCuotaInput.value = redondear(
                cuotaBase + minDefensa + otrosCargos
            ).toFixed(2);
            nuevoPlazoInput.value = periodoActual;
        } else {
            const nuevasCuotas = calcularCuotasNuevoPlazo(nuevoSaldo);

            if (nuevasCuotas <= 0) {
                mostrarError(
                    'El monto no alcanza para reducir al menos una cuota del plazo.'
                );
                return;
            }

            nuevaCuotaInput.value = cuotaTotalActual.toFixed(2);
            nuevoPlazoInput.value =
                primerNumeroCuota + nuevasCuotas - 1;
        }

        nuevoSaldoInput.value = nuevoSaldo.toFixed(2);
        error.classList.add('d-none');
    }

    function actualizarTipo() {
        const tipo = formulario.querySelector(
            'input[name="tipo_recalculo"]:checked'
        )?.value;

        if (tipo === 'PLAZO') {
            mensaje.textContent =
                'La cuota mensual se mantendrá y se reducirá el tiempo del préstamo.';
            botonSimular.innerHTML =
                '<i class="bi bi-calculator me-1"></i>Simular nuevo plazo';
        } else {
            mensaje.textContent =
                'La amortización reducirá la cuota mensual desde el próximo periodo y mantendrá el plazo.';
            botonSimular.innerHTML =
                '<i class="bi bi-calculator me-1"></i>Simular nueva cuota';
        }

        invalidarSimulacion();
    }

    montoInput.addEventListener('input', actualizarConversion);
    tipoCambioInput?.addEventListener('input', actualizarConversion);
    formulario.querySelectorAll('.tipo-recalculo').forEach((radio) => {
        radio.addEventListener('change', actualizarTipo);
    });
    botonSimular.addEventListener('click', simular);
    formulario.addEventListener('submit', async (evento) => {
        evento.preventDefault();

        const tipo = formulario.querySelector(
            'input[name="tipo_recalculo"]:checked'
        )?.value;
        const montoEfectivo =
            Number.parseFloat(montoInput.value) || 0;
        const simboloCapital = esDolares ? '$us' : 'Bs';
        const confirmado = await confirmarOperacion({
            titulo: 'Confirmar Amortización de Capital',
            mensaje:
                tipo === 'PLAZO'
                    ? 'Se aplicará una amortización que mantendrá la cuota y reducirá el tiempo del préstamo.'
                    : 'Se aplicará una amortización que reducirá la cuota mensual y mantendrá el plazo.',
            detalles: [
                {
                    etiqueta: 'Efectivo recibido',
                    valor: `Bs ${montoEfectivo.toFixed(2)}`,
                },
                {
                    etiqueta: 'Capital amortizado',
                    valor: `${simboloCapital} ${montoCapital().toFixed(2)}`,
                    clase: 'text-success',
                },
                ...(esDolares
                    ? [{
                        etiqueta: 'Tipo de cambio',
                        valor: (Number.parseFloat(tipoCambioInput?.value) || 0).toFixed(5),
                    }]
                    : []),
                {
                    etiqueta: 'Tipo de recálculo',
                    valor: tipo === 'PLAZO'
                        ? 'REDUCIR TIEMPO'
                        : 'REDUCIR CUOTA',
                },
                {
                    etiqueta: 'Nuevo saldo estimado',
                    valor: `${simboloCapital} ${nuevoSaldoInput.value || '-'}`,
                },
                {
                    etiqueta: tipo === 'PLAZO'
                        ? 'Nuevo plazo estimado'
                        : 'Nueva cuota estimada',
                    valor: tipo === 'PLAZO'
                        ? `${nuevoPlazoInput.value || '-'} cuotas totales`
                        : `${simboloCapital} ${nuevaCuotaInput.value || '-'}`,
                },
            ],
            textoConfirmar: 'Aplicar amortización',
        });

        if (confirmado) {
            formulario.submit();
        }
    });

    actualizarTipo();
    actualizarConversion();
}
