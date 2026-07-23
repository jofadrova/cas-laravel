import { confirmarOperacion } from '../scas/confirmacion';

export function iniciarReprogramacionPrestamo() {
    const formulario = document.getElementById('formReprogramacionPrestamo');

    if (!formulario) {
        return;
    }

    const cuotasInput = document.getElementById('cuotasPendientesNuevas');
    const saldo = Number.parseFloat(formulario.dataset.saldo) || 0;
    const tasa = Number.parseFloat(formulario.dataset.tasa) || 0;
    const minDefensa = Number.parseFloat(formulario.dataset.minDefensa) || 0;
    const otrosCargos = Number.parseFloat(formulario.dataset.otrosCargos) || 0;
    const pendientesActuales =
        Number.parseInt(formulario.dataset.pendientesActuales, 10) || 0;
    const ultimaPagada =
        Number.parseInt(formulario.dataset.ultimaPagada, 10) || 0;
    const moneda = formulario.dataset.moneda;

    function formatear(monto) {
        return `${moneda} ${monto.toLocaleString('es-BO', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        })}`;
    }

    function calcular() {
        const cuotas = Number.parseInt(cuotasInput.value, 10) || 0;
        let cuota = 0;

        if (cuotas > 0) {
            if (tasa > 0) {
                const factor = Math.pow(1 + tasa, cuotas);
                cuota = saldo * ((tasa * factor) / (factor - 1));
            } else {
                cuota = saldo / cuotas;
            }
        }

        const cuotaTotal = cuota + (cuota * minDefensa) + otrosCargos;

        document.getElementById('resumenCuotasReprogramadas').textContent =
            cuotas > 0 ? String(cuotas) : '-';
        document.getElementById('resumenPlazoReprogramado').textContent =
            cuotas > 0 ? `${ultimaPagada + cuotas} cuotas` : '-';
        document.getElementById('resumenCuotaReprogramada').textContent =
            cuotas > 0 ? formatear(cuotaTotal) : '-';

        return { cuotas, cuotaTotal };
    }

    cuotasInput.addEventListener('input', calcular);

    formulario.addEventListener('submit', async (evento) => {
        evento.preventDefault();

        const resultado = calcular();
        const confirmado = await confirmarOperacion({
            titulo: 'Confirmar Reprogramación',
            mensaje:
                'Se conservarán las cuotas pagadas y se reemplazará el cronograma pendiente con la nueva cantidad de cuotas.',
            detalles: [
                {
                    etiqueta: 'Saldo capital',
                    valor: formatear(saldo),
                },
                {
                    etiqueta: 'Cuotas pendientes actuales',
                    valor: String(pendientesActuales),
                },
                {
                    etiqueta: 'Nuevas cuotas pendientes',
                    valor: String(resultado.cuotas),
                },
                {
                    etiqueta: 'Nuevo plazo total',
                    valor: `${ultimaPagada + resultado.cuotas} cuotas`,
                },
                {
                    etiqueta: 'Nueva cuota estimada',
                    valor: formatear(resultado.cuotaTotal),
                    clase: 'text-success',
                },
            ],
            textoConfirmar: 'Reprogramar préstamo',
        });

        if (confirmado) {
            formulario.submit();
        }
    });

    calcular();
}
