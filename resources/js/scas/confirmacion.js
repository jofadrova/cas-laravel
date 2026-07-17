let resolverPendiente = null;

function elemento(id) {
    return document.getElementById(id);
}

export function confirmarOperacion({
    titulo = 'Confirmar Acción',
    mensaje,
    detalles = [],
    textoConfirmar = 'Confirmar',
}) {
    const modalElemento = elemento('modalConfirmarOperacion');

    if (!modalElemento) {
        return Promise.resolve(false);
    }

    elemento('textoTituloConfirmarOperacion').textContent = titulo;
    elemento('mensajeConfirmarOperacion').textContent = mensaje;
    elemento('btnConfirmarOperacion').textContent = textoConfirmar;

    const lista = elemento('detallesConfirmarOperacion');
    lista.innerHTML = '';
    lista.classList.toggle('d-none', detalles.length === 0);

    detalles.forEach(({ etiqueta, valor, clase = '' }) => {
        const fila = document.createElement('div');
        fila.className = 'd-flex justify-content-between gap-3 py-1';

        const etiquetaElemento = document.createElement('span');
        etiquetaElemento.className = 'text-muted';
        etiquetaElemento.textContent = etiqueta;

        const valorElemento = document.createElement('strong');
        valorElemento.className = `text-end ${clase}`.trim();
        valorElemento.textContent = valor || '-';

        fila.append(etiquetaElemento, valorElemento);
        lista.appendChild(fila);
    });

    const modal = bootstrap.Modal.getOrCreateInstance(modalElemento, {
        backdrop: 'static',
        keyboard: false,
    });

    if (resolverPendiente) {
        resolverPendiente(false);
    }

    return new Promise((resolve) => {
        resolverPendiente = resolve;
        modal.show();
    });
}

document.addEventListener('DOMContentLoaded', () => {
    const modalElemento = elemento('modalConfirmarOperacion');
    const botonConfirmar = elemento('btnConfirmarOperacion');

    if (!modalElemento || !botonConfirmar) {
        return;
    }

    botonConfirmar.addEventListener('click', () => {
        const resolver = resolverPendiente;
        resolverPendiente = null;
        bootstrap.Modal.getInstance(modalElemento)?.hide();
        resolver?.(true);
    });

    modalElemento.addEventListener('hidden.bs.modal', () => {
        if (!resolverPendiente) {
            return;
        }

        const resolver = resolverPendiente;
        resolverPendiente = null;
        resolver(false);
    });

    document.addEventListener('submit', async (evento) => {
        const formulario = evento.target.closest(
            'form[data-confirm-message]'
        );

        if (!formulario || formulario.dataset.confirmed === '1') {
            return;
        }

        evento.preventDefault();

        const confirmado = await confirmarOperacion({
            titulo:
                formulario.dataset.confirmTitle || 'Confirmar Acción',
            mensaje: formulario.dataset.confirmMessage,
            textoConfirmar:
                formulario.dataset.confirmButton || 'Confirmar',
        });

        if (!confirmado) {
            return;
        }

        formulario.dataset.confirmed = '1';
        formulario.submit();
    });
});
