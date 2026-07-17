import { confirmarOperacion } from '../scas/confirmacion';

export default class PrestamoGarantes {
    constructor() {

        this.chkGarante1 = document.getElementById('chkGarante1');
        this.chkGarante2 = document.getElementById('chkGarante2');

        this.card = document.getElementById('cardNuevosGarantes');

        this.bloque1 = document.getElementById('bloqueGarante1');
        this.bloque2 = document.getElementById('bloqueGarante2');

        this.form = document.getElementById('frmGarantes');
        this.btnGuardar = document.getElementById('btnGuardarGarantes');
        this.idSocio = document.getElementById('idSocio').value;
        this.garante1Original = document.getElementById('garante1Original').value;

        this.garante2Original = document.getElementById('garante2Original').value;
        this.eventos();

        this.actualizarVista();

        this.form.addEventListener('submit', async (e) => {
            e.preventDefault();

            if (!this.validarFormulario()) {
                return;
            }

            const cambios = [];

            if (this.chkGarante1.checked) {
                cambios.push({
                    etiqueta: 'Nuevo garante 1',
                    valor: this.nombreGarante('id_garante1'),
                });
            }

            if (this.chkGarante2.checked) {
                cambios.push({
                    etiqueta: 'Nuevo garante 2',
                    valor: this.nombreGarante('id_garante2'),
                });
            }

            const confirmado = await confirmarOperacion({
                titulo: 'Confirmar Cambio de Garantes',
                mensaje:
                    'Se actualizarán los garantes seleccionados y se registrará el cambio en el historial del préstamo.',
                detalles: cambios,
                textoConfirmar: 'Actualizar garantes',
            });

            if (confirmado) {
                this.form.submit();
            }
        });

        this.modal = new bootstrap.Modal(
            document.getElementById('modalValidacion')
        );

        this.lblMensaje = document.getElementById('mensajeValidacion');
    }

    eventos() {
        this.chkGarante1.addEventListener('change', () => {
           
            this.actualizarVista();
        });
        this.chkGarante2.addEventListener('change', () => {
          
            this.actualizarVista();
        });
    }

    actualizarVista() {
        const mostrar = this.chkGarante1.checked || this.chkGarante2.checked;
        this.card.classList.toggle(
            'd-none',
            !mostrar
        );

        // Posicionar los bloques
       this.bloque1.classList.toggle(
        'd-none',
        !this.chkGarante1.checked
        );

        this.bloque2.classList.toggle(
            'd-none',
            !this.chkGarante2.checked
        );
        if (!this.chkGarante1.checked && this.chkGarante2.checked) {
            this.bloque2.classList.add('offset-md-6');
        } else {
            this.bloque2.classList.remove('offset-md-6');
        }
       
    }
    mostrarError(mensaje) {

        this.lblMensaje.textContent = mensaje;
        this.modal.show();

    }

    nombreGarante(nombreCampo) {
        return document.querySelector(`input[name="${nombreCampo}"]`)
            ?.closest('.scas-papeleta')
            ?.querySelector('.scas-nombre')
            ?.textContent.trim() || 'Sin seleccionar';
    }

    validarFormulario() {
        console.log({
            chk1: this.chkGarante1.checked,
            chk2: this.chkGarante2.checked,
            g1: document.querySelector('input[name="id_garante1"]').value,
            g2: document.querySelector('input[name="id_garante2"]').value
        });
        if (!this.chkGarante1.checked && !this.chkGarante2.checked) {
            this.mostrarError(
                'Debe seleccionar al menos un garante para cambiar.'
            );
            return false;
        }
        // Nuevo garante 1 obligatorio

        if (this.chkGarante1.checked) {
            const idGarante1 = document.querySelector(
                'input[name="id_garante1"]'
            ).value.trim();

            if (!idGarante1) {

                this.mostrarError('Debe seleccionar el nuevo garante 1.');
                return false;
            }

        }
        if (this.chkGarante2.checked) {
            const idGarante2 = document.querySelector(
                'input[name="id_garante2"]'
            ).value.trim();
            if (!idGarante2) {
                this.mostrarError(
                    'Debe seleccionar el nuevo garante 2.'
                );
                return false;
            }
        }
        if (this.chkGarante1.checked) {
            const idGarante1 = document.querySelector(
                'input[name="id_garante1"]'
            ).value.trim();
            if (idGarante1 == this.idSocio) {
                this.mostrarError(
                    'El asociado solicitante no puede ser garante de su propio préstamo.'
                );
                return false;
            }
        }
        if (this.chkGarante2.checked) {
            const idGarante2 = document.querySelector(
                'input[name="id_garante2"]'
            ).value.trim();
            if (idGarante2 == this.idSocio) {
                this.mostrarError(
                    'El asociado solicitante no puede ser garante de su propio préstamo.'
                );
                return false;
            }
        }
        if (this.chkGarante1.checked) {
            const idGarante1 = document.querySelector(
                'input[name="id_garante1"]'
            ).value.trim();
            if (idGarante1 == this.garante1Original  || idGarante1 == this.garante2Original) {

                this.mostrarError(
                    'Debe seleccionar un garante diferente al garante actual.'
                );

                return false;
            }
        }
        if (this.chkGarante2.checked) {
            const idGarante2 = document.querySelector(
                'input[name="id_garante2"]'
            ).value.trim();

            if (idGarante2 == this.garante2Original || idGarante2 == this.garante1Original) {

                this.mostrarError(
                    'Debe seleccionar un garante diferente al garante actual.'
                );
                return false;
            }
        }

        // Los dos nuevos garantes no pueden ser la misma persona

        if (this.chkGarante1.checked && this.chkGarante2.checked) {

            const idGarante1 = document.querySelector(
                'input[name="id_garante1"]'
            ).value.trim();

            const idGarante2 = document.querySelector(
                'input[name="id_garante2"]'
            ).value.trim();

            if (idGarante1 == idGarante2) {

                this.mostrarError(
                    'Los nuevos garantes deben ser asociados diferentes.'
                );

                return false;

            }

        }
      
        return true;
    }

}
