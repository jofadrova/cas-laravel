import { confirmarOperacion } from '../scas/confirmacion';

export function iniciarRefinanciamiento() {
    const formulario = document.getElementById('formRefinanciamiento');

    if (!formulario) {
        return;
    }

    const montoInput = document.getElementById('nuevoMontoRefinanciamiento');
    const plazoInput = document.getElementById('plazoRefinanciamiento');
    const fechaInput = document.getElementById('fechaRefinanciamiento');
    const tipoCambioInput = document.getElementById('tipoCambioRefinanciamiento');
    const botonTipoCambio = document.getElementById('btnTipoCambioRefinanciamiento');
    const cuerpoCronograma = document.getElementById('cronogramaRefinanciamiento');
    const saldoActual = Number.parseFloat(formulario.dataset.saldoActual) || 0;
    const esDolares = formulario.dataset.moneda === 'SU';
    let temporizador;

    function simbolo() {
        return esDolares ? '$us' : 'Bs';
    }

    function formatear(monto) {
        return `${simbolo()} ${Number(monto).toLocaleString('es-BO', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        })}`;
    }

    function actualizarResumenBasico() {
        const monto = Number.parseFloat(montoInput.value) || 0;
        const plazo = Number.parseInt(plazoInput.value, 10) || 0;
        const desembolso = Math.max(0, monto - saldoActual);

        document.getElementById('resumenNuevoMonto').textContent =
            monto > 0 ? formatear(monto) : '-';
        document.getElementById('resumenDesembolso').textContent =
            monto > 0 ? formatear(desembolso) : '-';
        document.getElementById('resumenPlazo').textContent =
            plazo > 0 ? `${plazo} meses` : '-';

        const resumenBs = document.getElementById('resumenDesembolsoBs');
        if (resumenBs) {
            const tipoCambio = Number.parseFloat(tipoCambioInput?.value) || 0;
            resumenBs.textContent =
                tipoCambio > 0
                    ? `Bs ${Number(desembolso * tipoCambio).toLocaleString('es-BO', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2,
                    })}`
                    : '-';
        }
    }

    function programarSimulacion() {
        actualizarResumenBasico();
        clearTimeout(temporizador);
        temporizador = setTimeout(simular, 350);
    }

    async function simular() {
        const monto = Number.parseFloat(montoInput.value) || 0;
        const plazo = Number.parseInt(plazoInput.value, 10) || 0;

        if (monto <= 0 || plazo <= 0 || !fechaInput.value) {
            return;
        }

        const response = await fetch(formulario.dataset.urlSimular, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({
                monto,
                plazo,
                porcentaje: formulario.dataset.porcentaje,
                fecha: fechaInput.value,
                tipo_moneda: formulario.dataset.moneda,
                tipo: formulario.dataset.tipo,
                itf: formulario.dataset.itf,
                papeleria: formulario.dataset.papeleria,
                min_defensa: formulario.dataset.minDefensa,
            }),
        });

        if (!response.ok) {
            return;
        }

        const datos = await response.json();
        cuerpoCronograma.innerHTML = '';

        datos.cronograma.forEach((cuota) => {
            cuerpoCronograma.insertAdjacentHTML('beforeend', `
                <tr>
                    <td>${cuota.numero}</td>
                    <td>${cuota.fecha}</td>
                    <td class="text-end">${Number(cuota.capital).toFixed(2)}</td>
                    <td class="text-end">${Number(cuota.interes).toFixed(2)}</td>
                    <td class="text-end">${Number(cuota.min_defensa).toFixed(2)}</td>
                    <td class="text-end">${Number(cuota.itf).toFixed(2)}</td>
                    <td class="text-end">${Number(cuota.interes_dias + cuota.reposicion).toFixed(2)}</td>
                    <td class="text-end fw-bold">${Number(cuota.cuota).toFixed(2)}</td>
                    <td class="text-end">${Number(cuota.saldo).toFixed(2)}</td>
                </tr>
            `);
        });

        document.getElementById('resumenCuota').textContent = formatear(datos.cuota);
        document.getElementById('resumenInteres').textContent = formatear(datos.interesTotal);
        document.getElementById('resumenTotal').textContent = formatear(datos.totalPagado);
    }

    async function cargarTipoCambio() {
        if (!fechaInput.value || !botonTipoCambio) {
            return;
        }

        botonTipoCambio.disabled = true;
        try {
            const url = formulario.dataset.rutaTipoCambio.replace(
                '__FECHA__',
                fechaInput.value
            );
            const response = await fetch(url);
            const datos = await response.json();
            if (datos.ok) {
                tipoCambioInput.value = Number(datos.tipo_cambio).toFixed(5);
                actualizarResumenBasico();
            }
        } finally {
            botonTipoCambio.disabled = false;
        }
    }

    montoInput.addEventListener('input', programarSimulacion);
    plazoInput.addEventListener('input', programarSimulacion);
    fechaInput.addEventListener('change', programarSimulacion);
    tipoCambioInput?.addEventListener('input', actualizarResumenBasico);
    botonTipoCambio?.addEventListener('click', cargarTipoCambio);
    formulario.addEventListener('submit', async (evento) => {
        evento.preventDefault();

        const monto = Number.parseFloat(montoInput.value) || 0;
        const plazo = Number.parseInt(plazoInput.value, 10) || 0;
        const desembolso = Math.max(0, monto - saldoActual);
        const garantes = Array.from(
            formulario.querySelectorAll(
                '[name="id_garante1"], [name="id_garante2"]'
            )
        ).map((campo) =>
            campo.closest('.scas-papeleta')
                ?.querySelector('.scas-nombre')
                ?.textContent.trim() || 'Sin seleccionar'
        );

        const confirmado = await confirmarOperacion({
            titulo: 'Confirmar Refinanciamiento',
            mensaje:
                'Se creará un nuevo préstamo con un cronograma calculado sobre el monto total y el préstamo actual quedará únicamente como histórico.',
            detalles: [
                {
                    etiqueta: 'Nuevo monto del préstamo',
                    valor: formatear(monto),
                },
                {
                    etiqueta: 'Saldo anterior absorbido',
                    valor: formatear(saldoActual),
                },
                {
                    etiqueta: 'Desembolso neto',
                    valor: formatear(desembolso),
                    clase: 'text-success',
                },
                {
                    etiqueta: 'Nuevo plazo',
                    valor: `${plazo} meses`,
                },
                {
                    etiqueta: 'Tipo de cambio',
                    valor: (Number.parseFloat(tipoCambioInput?.value) || 0).toFixed(5),
                },
                {
                    etiqueta: 'Primer garante',
                    valor: garantes[0],
                },
                {
                    etiqueta: 'Segundo garante',
                    valor: garantes[1],
                },
                {
                    etiqueta: 'Préstamo anterior',
                    valor: 'CERRADO (CE)',
                    clase: 'text-danger',
                },
            ],
            textoConfirmar: 'Crear refinanciamiento',
        });

        if (confirmado) {
            formulario.submit();
        }
    });

    actualizarResumenBasico();
    programarSimulacion();
}
