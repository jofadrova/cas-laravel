const cacheSocios = new Map();

export function iniciarInformacionSocio() {
    const modalElemento = document.getElementById('modalInformacionSocio');
    const contenido = document.getElementById('contenidoInformacionSocio');

    if (!modalElemento || !contenido) {
        return;
    }

    const modal = window.bootstrap.Modal.getOrCreateInstance(modalElemento);

    document.addEventListener('click', async (event) => {
        const enlace = event.target.closest('.btnInformacionSocio');

        if (!enlace) {
            return;
        }

        event.preventDefault();
        modal.show();

        if (cacheSocios.has(enlace.href)) {
            contenido.innerHTML = cacheSocios.get(enlace.href);
            prepararContenido(contenido);
            return;
        }

        contenido.innerHTML = `
            <div class="d-flex justify-content-center align-items-center gap-2 py-5 text-muted">
                <div class="spinner-border spinner-border-sm text-info" role="status"></div>
                <span>Cargando información...</span>
            </div>`;

        try {
            const response = await fetch(enlace.href, {
                headers: {
                    Accept: 'text/html',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (!response.ok) {
                throw new Error('No fue posible cargar la información del socio.');
            }

            const html = await response.text();
            cacheSocios.set(enlace.href, html);
            contenido.innerHTML = html;
            prepararContenido(contenido);
        } catch (error) {
            contenido.innerHTML = `
                <div class="alert alert-danger mb-0">
                    ${error.message}
                </div>`;
        }
    });

    function prepararContenido(contenedor) {
        const botonAceptar = contenedor.querySelector(
            'a[href*="/socios"]:last-of-type'
        );

        if (botonAceptar) {
            botonAceptar.href = '#';
            botonAceptar.setAttribute('data-bs-dismiss', 'modal');
        }
    }
}
