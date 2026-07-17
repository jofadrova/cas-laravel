const STORAGE_KEY = 'scas.sidebar.collapsed';

export function iniciarSidebar() {
    const boton = document.getElementById('sidebarToggle');
    const menu = document.getElementById('sidebarMenu');

    if (!boton || !menu) {
        return;
    }

    let contraido = false;

    try {
        contraido = window.localStorage.getItem(STORAGE_KEY) === 'true';
    } catch {
        contraido = false;
    }

    function actualizarSidebar() {
        document.body.classList.toggle('sidebar-collapsed', contraido);
        boton.setAttribute('aria-expanded', String(!contraido));
        boton.setAttribute(
            'aria-label',
            contraido ? 'Expandir menú lateral' : 'Contraer menú lateral'
        );
        boton.setAttribute(
            'title',
            contraido ? 'Expandir menú' : 'Contraer menú'
        );

        const icono = boton.querySelector('i');
        icono.className = contraido
            ? 'bi bi-chevron-right'
            : 'bi bi-chevron-left';
    }

    boton.addEventListener('click', () => {
        contraido = !contraido;
        actualizarSidebar();

        try {
            window.localStorage.setItem(STORAGE_KEY, String(contraido));
        } catch {
            // El menú sigue funcionando aunque el navegador bloquee storage.
        }
    });

    actualizarSidebar();
}
