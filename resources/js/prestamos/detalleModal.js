export function iniciarDetallePrestamo() {
    const contenedor = document.getElementById('contenedorDetallePrestamo');

    if (!contenedor) {
        return;
    }

    document.addEventListener('click', async (event) => {
        const boton = event.target.closest('.btn-ver-cuotas');

        if (!boton) {
            return;
        }

        event.preventDefault();

        const contenidoOriginal = boton.innerHTML;
        boton.disabled = true;
        boton.innerHTML =
            '<span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>Cargando...';

        try {
            const response = await fetch(boton.dataset.url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (!response.ok) {
                throw new Error('No fue posible cargar el detalle del préstamo.');
            }

            contenedor.innerHTML = await response.text();

            const elementoModal = document.getElementById(
                'modalDetallePrestamo'
            );
            window.bootstrap.Modal.getOrCreateInstance(elementoModal).show();
        } catch (error) {
            window.alert(error.message);
        } finally {
            boton.disabled = false;
            boton.innerHTML = contenidoOriginal;
        }
    });
}
