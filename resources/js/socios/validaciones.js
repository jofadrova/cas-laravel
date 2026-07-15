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



}


