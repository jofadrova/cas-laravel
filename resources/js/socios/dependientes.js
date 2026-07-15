import * as bootstrap from "bootstrap";

export function iniciarDependientes() {
    const tabla = document.getElementById("tablaDependientes");

    if (!tabla) return;

    let indiceDependiente = 0;

    document
        .getElementById("btnAgregarDependiente")
        .addEventListener("click", function (e) {
            e.preventDefault();
            const nombres = document.getElementById("dep_nombres").value.trim();
            const paterno = document.getElementById("dep_paterno").value.trim();
            const materno = document.getElementById("dep_materno").value.trim();
            const ci = document.getElementById("dep_ci").value.trim();
            const expedido = document.getElementById("dep_expedido").value;
            const selectParentesco = document.getElementById("dep_parentesco");
            const parentesco = selectParentesco.value;
            const parentescoTexto =
                selectParentesco.options[selectParentesco.selectedIndex].text;
            const porcentaje = document
                .getElementById("dep_porcentaje")
                .value.trim();

            if (!nombres || !ci || !parentesco || !porcentaje) {
                mostrarMensajeDependiente(
                    "Debe completar todos los campos obligatorios.",
                    "danger",
                );
                return;
            }

            agregarFilaDependiente({
                nombres,
                paterno,
                materno,

                ci,

                exp: expedido,

                parentesco,
                parentescoTexto,

                porcentaje,
            });

            document
                .getElementById("mensajeDependiente")
                .classList.add("d-none");
            bootstrap.Modal.getInstance(
                document.getElementById("modalDependiente"),
            ).hide();
            limpiarFormularioDependiente();
        });

    function limpiarFormularioDependiente() {
        document.getElementById("dep_nombres").value = "";
        document.getElementById("dep_paterno").value = "";
        document.getElementById("dep_materno").value = "";
        document.getElementById("dep_ci").value = "";
        document.getElementById("dep_expedido").value = "";
        document.getElementById("dep_parentesco").value = "";
        document.getElementById("dep_porcentaje").value = "";
    }

    function agregarFilaDependiente(datos) {
        const tbody = document.querySelector("#tablaDependientes tbody");

        const filaVacia = document.getElementById("filaSinDependientes");

        if (filaVacia) {
            filaVacia.remove();
        }

        const fila = document.createElement("tr");

        fila.dataset.nombres = datos.nombres;
        fila.dataset.paterno = datos.paterno;
        fila.dataset.materno = datos.materno;
        fila.dataset.ci = datos.ci;
        fila.dataset.exp = datos.exp;
        fila.dataset.parentesco = datos.parentesco;
        fila.dataset.parentescoTexto = datos.parentescoTexto;
        fila.dataset.porcentaje = datos.porcentaje;

        fila.innerHTML = `
        <td>
            ${datos.nombres} ${datos.paterno} ${datos.materno}

            <input type="hidden" name="dependientes[${indiceDependiente}][nombres]" value="${datos.nombres}">
            <input type="hidden" name="dependientes[${indiceDependiente}][paterno]" value="${datos.paterno}">
            <input type="hidden" name="dependientes[${indiceDependiente}][materno]" value="${datos.materno}">
        </td>

        <td>
            ${datos.ci}
            <input type="hidden" name="dependientes[${indiceDependiente}][ci]" value="${datos.ci}">
        </td>

        <td>
            ${datos.exp}
            <input type="hidden" name="dependientes[${indiceDependiente}][exp]" value="${datos.exp}">
        </td>

        <td>
            ${datos.parentescoTexto}
            <input type="hidden" name="dependientes[${indiceDependiente}][parentesco]" value="${datos.parentesco}">
        </td>

        <td>
            ${datos.porcentaje} %
            <input type="hidden" name="dependientes[${indiceDependiente}][porcentaje]" value="${datos.porcentaje}">
        </td>

        <td class="text-center">

            <button
                type="button"
                class="btn btn-outline-danger btn-sm btnEliminarDependiente">

                <i class="fas fa-trash"></i>

            </button>

        </td>
    `;

        tbody.appendChild(fila);

        indiceDependiente++;

        actualizarTotal();
    }

    function actualizarTotal() {
        let total = 0;
        document
            .querySelectorAll('input[name$="[porcentaje]"]')
            .forEach(function (input) {
                total += parseFloat(input.value) || 0;
            });
        document.getElementById("totalPorcentaje").textContent = total + " %";
    }

    document.addEventListener("click", function (e) {
        const boton = e.target.closest(".btnEliminarDependiente");
        if (!boton) return;
        boton.closest("tr").remove();
        actualizarTotal();
        const tbody = document.querySelector("#tablaDependientes tbody");
        if (tbody.children.length === 0) {
            tbody.innerHTML = `
        <tr id="filaSinDependientes">
            <td colspan="6" class="text-center text-muted py-4">
                No existen beneficiarios registrados.
            </td>
        </tr>`;
        }
    });

    document
        .getElementById("modalDependiente")
        .addEventListener("show.bs.modal", function () {
            document
                .getElementById("mensajeDependiente")
                .classList.add("d-none");
        });

    function mostrarMensajeDependiente(mensaje, tipo = "danger") {
        const contenedor = document.getElementById("mensajeDependiente");
        contenedor.className = `alert alert-${tipo} mb-3`;
        contenedor.innerHTML = `<i class="fas fa-exclamation-circle me-2"></i>${mensaje}`;
    }

    // Reconstruir beneficiarios
    let dependientesIniciales = [];

    if (
        Array.isArray(window.oldDependientes) &&
        window.oldDependientes.length > 0
    ) {
        dependientesIniciales = window.oldDependientes;
    } else if (Array.isArray(window.dependientes)) {
        dependientesIniciales = window.dependientes;
    }

    dependientesIniciales.forEach((dep) => {
        const opcion = document.querySelector(
            `#dep_parentesco option[value="${dep.parentesco}"]`,
        );

        agregarFilaDependiente({
            nombres: dep.nombres,
            paterno: dep.paterno,
            materno: dep.materno,

            ci: dep.ci,

            exp: dep.exp,

            parentesco: dep.parentesco,

            parentescoTexto: opcion ? opcion.textContent : dep.parentesco,

            porcentaje: dep.porcentaje,
        });
    });
}
