export function iniciarValidaciones()
{
    document.querySelectorAll('.letters-only').forEach(function (input) {
    input.addEventListener('keydown', function (event) {
        const allowedKeys = [
            'Backspace', 'Tab', 'Enter', 'Escape', 'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown',
            'Home', 'End', 'Delete', 'Shift', 'Control', 'Alt', 'Meta'
        ];

        if (allowedKeys.includes(event.key)) {
            return;
        }

        const letterRegex = /^[A-Za-z\s]$/;
        if (!letterRegex.test(event.key)) {
            event.preventDefault();
        }
    });

        input.addEventListener('input', function () {
            this.value = this.value.replace(/[^A-Za-z\s]/g, '');
        });
    });

    document.querySelectorAll('.numbers-only').forEach(function (input) {
input.addEventListener('keydown', function (event) {
    const allowedKeys = [
        'Backspace', 'Tab', 'Enter', 'Escape', 'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown',
        'Home', 'End', 'Delete', 'Shift', 'Control', 'Alt', 'Meta'
    ];

    if (allowedKeys.includes(event.key)) {
        return;
    }

    const numberRegex = /^[0-9]$/;
    if (!numberRegex.test(event.key)) {
        event.preventDefault();
    }
});

input.addEventListener('input', function () {
    this.value = this.value.replace(/[^0-9]/g, '');
});
});

document.querySelectorAll('.alphanumeric-only').forEach(function (input) {
input.addEventListener('keydown', function (event) {
    const allowedKeys = [
        'Backspace', 'Tab', 'Enter', 'Escape', 'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown',
        'Home', 'End', 'Delete', 'Shift', 'Control', 'Alt', 'Meta'
    ];

    if (allowedKeys.includes(event.key)) {
        return;
    }

    const alphanumericRegex = /^[A-Za-z0-9\s]$/;
    if (!alphanumericRegex.test(event.key)) {
        event.preventDefault();
    }
});

input.addEventListener('input', function () {
    this.value = this.value.replace(/[^A-Za-z0-9\s]/g, '');
});
});

    const camposUnicos = [...document.querySelectorAll('[data-duplicate-validation-url]')];

    if (!camposUnicos.length) return;

    let enviando = false;

    const validadores = camposUnicos.map((campo) => {
        const feedback = campo.parentElement.querySelector('[data-duplicate-feedback]');
        const longitudMinima = Number(campo.dataset.duplicateMinLength);
        let temporizador;
        let ultimoValorValidado = '';

        const mostrarError = (mensaje) => {
            campo.classList.toggle('is-invalid', Boolean(mensaje));
            campo.setCustomValidity(mensaje);
            feedback.textContent = mensaje;
        };

        const validar = async () => {
            const valor = campo.value.trim();

            if (valor.length < longitudMinima) {
                ultimoValorValidado = '';
                mostrarError('');
                return true;
            }

            if (valor === ultimoValorValidado) return !campo.validationMessage;

            const url = new URL(campo.dataset.duplicateValidationUrl, window.location.origin);
            url.searchParams.set(campo.name, valor);

            if (campo.dataset.socioId) url.searchParams.set('socio_id', campo.dataset.socioId);

            try {
                const respuesta = await fetch(url, { headers: { Accept: 'application/json' } });

                if (!respuesta.ok) throw new Error();

                const datos = await respuesta.json();
                ultimoValorValidado = valor;

                if (campo.value.trim() !== valor) return false;

                mostrarError(datos.disponible ? '' : campo.dataset.duplicateMessage);
                return datos.disponible;
            } catch (error) {
                mostrarError(`No se pudo validar ${campo.name === 'nro_doc' ? 'el CI' : 'la papeleta'}. Intente nuevamente.`);
                return false;
            }
        };

        campo.addEventListener('input', () => {
            clearTimeout(temporizador);
            ultimoValorValidado = '';
            mostrarError('');
            temporizador = setTimeout(validar, 400);
        });

        campo.addEventListener('blur', validar);

        return { campo, validar, cancelar: () => clearTimeout(temporizador) };
    });

    camposUnicos[0].form.addEventListener('submit', async (evento) => {
        if (enviando) return;

        evento.preventDefault();
        validadores.forEach((validador) => validador.cancelar());

        const resultados = await Promise.all(validadores.map((validador) => validador.validar()));
        const primerInvalido = validadores.find((validador, indice) => !resultados[indice]);

        if (primerInvalido) {
            primerInvalido.campo.focus();
            return;
        }

        enviando = true;
        camposUnicos[0].form.requestSubmit();
    });



}
