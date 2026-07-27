export function iniciarProyeccionPrestamo() {
    const raiz = document.getElementById('proyeccionPrestamo');
    if (!raiz) return;

    const formulario = document.getElementById('formProyeccionPrestamo');
    const tipo = document.getElementById('proyeccionTipo');
    const fecha = document.getElementById('proyeccionFecha');
    const monto = document.getElementById('proyeccionMonto');
    const plazo = document.getElementById('proyeccionPlazo');
    const tipoCambio = document.getElementById('proyeccionTipoCambio');
    const botonCalcular = document.getElementById('calcularProyeccion');
    const botonImprimir = document.getElementById('imprimirProyeccion');
    const botonTipoCambio = document.getElementById('actualizarTipoCambioProyeccion');
    let ultimaProyeccionValida = false;

    const moneda = (codigo) => codigo === 'SU' ? '$us' : 'Bs';
    const dinero = (valor, simbolo) => `${simbolo} ${Number(valor).toLocaleString('es-BO', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;

    function limpiarErrores() {
        formulario.querySelectorAll('.is-invalid').forEach((campo) => campo.classList.remove('is-invalid'));
        formulario.querySelectorAll('[data-error]').forEach((error) => {
            error.textContent = '';
            error.classList.remove('d-block');
        });
    }

    function mostrarErrores(errores) {
        Object.entries(errores).forEach(([nombre, mensajes]) => {
            const campo = formulario.elements.namedItem(nombre);
            const error = formulario.querySelector(`[data-error="${nombre}"]`);
            campo?.classList.add('is-invalid');
            if (error) {
                error.textContent = mensajes[0];
                error.classList.add('d-block');
            }
        });
    }

    function actualizarTipo() {
        const opcion = tipo.options[tipo.selectedIndex];
        const resumen = document.getElementById('resumenTipoProyeccion');
        ultimaProyeccionValida = false;
        botonImprimir.disabled = true;

        if (!opcion?.value) {
            resumen.classList.add('d-none');
            document.getElementById('proyeccionMoneda').textContent = '-';
            return;
        }

        const simbolo = moneda(opcion.dataset.moneda);
        document.getElementById('proyeccionMoneda').textContent = simbolo;
        document.getElementById('proyeccionMontoMax').textContent = `Máximo: ${dinero(opcion.dataset.monto, simbolo)}`;
        document.getElementById('proyeccionPlazoMax').textContent = `Máximo: ${opcion.dataset.plazo} meses`;
        document.getElementById('datoMoneda').textContent = opcion.dataset.moneda === 'SU' ? 'Dólares ($us)' : 'Bolivianos (Bs)';
        document.getElementById('datoInteres').textContent = `${Number(opcion.dataset.interes).toFixed(2)} %`;
        document.getElementById('datoMontoMax').textContent = dinero(opcion.dataset.monto, simbolo);
        document.getElementById('datoPlazoMax').textContent = `${opcion.dataset.plazo} meses`;
        resumen.classList.remove('d-none');

        const esDolares = opcion.dataset.moneda === 'SU';
        tipoCambio.disabled = !esDolares;
        botonTipoCambio.disabled = !esDolares;
        if (!esDolares) {
            tipoCambio.value = '';
            document.getElementById('mensajeTipoCambioProyeccion').textContent = 'No se realizan conversiones para préstamos en bolivianos.';
        } else {
            cargarTipoCambio();
        }
    }

    async function cargarTipoCambio() {
        const opcion = tipo.options[tipo.selectedIndex];
        if (!fecha.value || opcion?.dataset.moneda !== 'SU') return;

        const mensaje = document.getElementById('mensajeTipoCambioProyeccion');
        botonTipoCambio.disabled = true;
        try {
            const respuesta = await fetch(raiz.dataset.urlTipoCambio.replace('__FECHA__', fecha.value));
            const datos = await respuesta.json();
            if (datos.ok) {
                tipoCambio.value = Number(datos.tipo_cambio).toFixed(5);
                mensaje.className = 'text-success';
                mensaje.textContent = 'Cotización oficial cargada.';
            } else {
                mensaje.className = 'text-warning';
                mensaje.textContent = 'No existe cotización para esta fecha; puede ingresarla manualmente.';
            }
        } catch {
            mensaje.className = 'text-danger';
            mensaje.textContent = 'No fue posible consultar la cotización.';
        } finally {
            botonTipoCambio.disabled = false;
        }
    }

    function mostrarResultado(datos) {
        const simbolo = datos.moneda;
        const resultado = datos.simulacion;
        document.getElementById('resultadoCapital').textContent = dinero(resultado.capital, simbolo);
        document.getElementById('resultadoCuota').textContent = dinero(resultado.cuota, simbolo);
        document.getElementById('resultadoInteres').textContent = dinero(resultado.interesTotal, simbolo);
        document.getElementById('resultadoItf').textContent = dinero(resultado.itfTotal, simbolo);
        document.getElementById('resultadoCargos').textContent = dinero(
            resultado.minDefensaTotal + resultado.interesDiasTotal + resultado.reposicionTotal,
            simbolo
        );
        document.getElementById('resultadoTotal').textContent = dinero(resultado.totalPagado, simbolo);

        document.getElementById('cronogramaProyeccion').innerHTML = resultado.cronograma.map((cuota) => `
            <tr>
                <td>${cuota.numero}</td><td>${cuota.fecha}</td>
                <td class="text-end">${Number(cuota.capital).toFixed(2)}</td>
                <td class="text-end">${Number(cuota.interes).toFixed(2)}</td>
                <td class="text-end">${Number(cuota.min_defensa).toFixed(2)}</td>
                <td class="text-end">${Number(cuota.itf).toFixed(2)}</td>
                <td class="text-end">${Number(cuota.interes_dias).toFixed(2)}</td>
                <td class="text-end">${Number(cuota.reposicion).toFixed(2)}</td>
                <td class="text-end fw-bold">${Number(cuota.cuota).toFixed(2)}</td>
                <td class="text-end">${Number(cuota.saldo).toFixed(2)}</td>
            </tr>`).join('');

        document.getElementById('resultadoProyeccion').classList.remove('d-none');
        botonImprimir.disabled = false;
        ultimaProyeccionValida = true;
    }

    formulario.addEventListener('submit', async (evento) => {
        evento.preventDefault();
        limpiarErrores();
        ultimaProyeccionValida = false;
        botonImprimir.disabled = true;
        botonCalcular.disabled = true;
        botonCalcular.querySelector('.spinner-border').classList.remove('d-none');

        try {
            const respuesta = await fetch(raiz.dataset.urlCalcular, {
                method: 'POST',
                headers: { 'Accept': 'application/json' },
                body: new FormData(formulario),
            });
            const datos = await respuesta.json();
            if (!respuesta.ok) {
                mostrarErrores(datos.errors || {});
                return;
            }
            mostrarResultado(datos);
        } catch {
            window.ScasNotifier?.show('No fue posible calcular la proyección.', 'danger');
        } finally {
            botonCalcular.disabled = false;
            botonCalcular.querySelector('.spinner-border').classList.add('d-none');
        }
    });

    botonImprimir.addEventListener('click', () => {
        if (!ultimaProyeccionValida) return;
        const reporte = document.getElementById('formReporteProyeccion');
        ['tipo_prestamo', 'fecha', 'monto', 'plazo', 'tipo_cambio'].forEach((nombre) => {
            reporte.elements.namedItem(nombre).value = formulario.elements.namedItem(nombre).value;
        });
        reporte.submit();
    });

    [monto, plazo, fecha, tipoCambio].forEach((campo) => campo.addEventListener('input', () => {
        ultimaProyeccionValida = false;
        botonImprimir.disabled = true;
        document.getElementById('resultadoProyeccion').classList.add('d-none');
    }));
    tipo.addEventListener('change', actualizarTipo);
    fecha.addEventListener('change', cargarTipoCambio);
    botonTipoCambio.addEventListener('click', cargarTipoCambio);
}
