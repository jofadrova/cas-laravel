export default class GarantesManager {

    constructor() {

        this.chkGarante1 = document.getElementById('chkGarante1');
        this.chkGarante2 = document.getElementById('chkGarante2');

        this.card = document.getElementById('cardNuevosGarantes');

        this.bloque1 = document.getElementById('bloqueGarante1');
        this.bloque2 = document.getElementById('bloqueGarante2');

        if (!this.chkGarante1) {
            return;
        }

        this.eventos();
        this.actualizarVista();

    }

    eventos() {

        this.chkGarante1.addEventListener(
            'change',
            () => this.actualizarVista()
        );

        this.chkGarante2.addEventListener(
            'change',
            () => this.actualizarVista()
        );

    }

    actualizarVista() {

        const algunoSeleccionado =
            this.chkGarante1.checked ||
            this.chkGarante2.checked;

        this.card.classList.toggle(
            'd-none',
            !algunoSeleccionado
        );

        this.bloque1.classList.toggle(
            'd-none',
            !this.chkGarante1.checked
        );

        this.bloque2.classList.toggle(
            'd-none',
            !this.chkGarante2.checked
        );

    }

}