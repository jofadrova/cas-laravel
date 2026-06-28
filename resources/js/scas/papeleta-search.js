class PapeletaSearch {

    constructor(container) {

        this.container = container;

        this.input = container.querySelector('.scas-papeleta-input');
        this.hidden = container.querySelector('.scas-id');
        this.resultados = container.querySelector('.scas-resultados');
        this.nombre = container.querySelector('.scas-nombre');

        this.url = this.input.dataset.url;

        this.timer = null;

        this.cache = {};

        this.items = [];

        this.selected = -1;

        this.eventos();

    }

    eventos() {

        // Buscar mientras escribe
        this.input.addEventListener('keyup', (e) => {

            // Ignorar teclas de navegación
            if ([
                'ArrowUp',
                'ArrowDown',
                'Enter',
                'Escape'
            ].includes(e.key)) {
                return;
            }

            clearTimeout(this.timer);

            const texto = this.input.value.trim();
            if (this.hidden.value !== '') {
                this.hidden.value = '';
                this.nombre.innerHTML = '';
            }

            if (texto.length < 2) {

                this.limpiar();

                return;

            }

            this.timer = setTimeout(() => {

                this.buscar(texto);

            }, 300);

        });

        // Navegación con teclado
        this.input.addEventListener('keydown', (e) => {

            if (!this.items.length) return;

            switch (e.key) {

                case 'ArrowDown':

                    e.preventDefault();

                    this.selected++;

                    this.marcar();

                    break;

                case 'ArrowUp':

                    e.preventDefault();

                    this.selected--;

                    this.marcar();

                    break;

                case 'Enter':

                    e.preventDefault();

                    if (this.selected >= 0) {

                        this.items[this.selected].click();

                    }

                    break;

                case 'Escape':

                    this.ocultar();

                    break;

            }

        });

        // Ocultar resultados al perder el foco
        this.input.addEventListener('blur', () => {

            setTimeout(() => {

                this.ocultar();

            }, 200);

        });

    }

    async buscar(texto) {

        // Cache
        if (this.cache[texto]) {

            this.render(this.cache[texto]);

            return;

        }

        try {

            const response = await fetch(
                this.url + '?q=' + encodeURIComponent(texto)
            );

            if (!response.ok) {

                throw new Error('Error al consultar el servidor.');

            }

            const datos = await response.json();

            this.cache[texto] = datos;

            this.render(datos);

        } catch (error) {

            console.error(error);

            this.ocultar();

        }

    }

    render(datos) {

        this.resultados.innerHTML = '';

        this.items = [];

        this.selected = -1;

        if (!datos.length) {

            this.ocultar();

            return;

        }

        datos.forEach((socio) => {

            const item = document.createElement('a');

            item.href = '#';

            item.className =
                'list-group-item list-group-item-action';

            item.innerHTML = `<div>
            <i class="bi bi-person-badge-fill text-primary me-2"></i>
            <strong>${socio.papeleta}</strong>
            <br>
            <small>${socio.nombre}</small>
            </div>
            `;

            item.addEventListener('click', (e) => {

                e.preventDefault();

                this.seleccionar(socio);

            });

            this.resultados.appendChild(item);

            this.items.push(item);

        });

        if (datos.length === 1) {
            this.seleccionar(datos[0]);
            return;
        }

        this.resultados.style.display = 'block';

    }

    seleccionar(socio) {

        this.hidden.value = socio.id;
        this.input.value = socio.papeleta;
        this.nombre.innerHTML = socio.nombre;
        this.ocultar();
        this.input.blur();

        this.container.dispatchEvent(new CustomEvent('scas:selected', {
            bubbles: true,
            detail: socio
        }));

    }

    marcar() {

        if (!this.items.length) return;
        if (this.selected < 0) {
            this.selected = this.items.length - 1;
        }

        if (this.selected >= this.items.length) {

            this.selected = 0;

        }

        this.items.forEach(item => {
            item.classList.remove('active');
        });

        this.items[this.selected].classList.add('active');

    }

    limpiar() {
        this.hidden.value = '';
        this.nombre.innerHTML = '';
        this.resultados.innerHTML = '';
        this.items = [];
        this.selected = -1;
        this.ocultar();
    }

    ocultar() {

        this.resultados.style.display = 'none';

    }

}

document.addEventListener('DOMContentLoaded', () => {

    document
        .querySelectorAll('.scas-papeleta')
        .forEach(container => {

            new PapeletaSearch(container);

        });

});
window.PapeletaSearch = PapeletaSearch;