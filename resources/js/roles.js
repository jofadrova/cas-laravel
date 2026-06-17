import * as bootstrap from "bootstrap";

document.addEventListener("DOMContentLoaded", function () {
    const modalEditarElement = document.getElementById("modalEditarRol");

    if (!modalEditarElement) return;

    const modalEditar = bootstrap.Modal.getOrCreateInstance(modalEditarElement);

    document.querySelectorAll(".btnEditarRol").forEach((btn) => {
        btn.addEventListener("click", function () {
            document.getElementById("edit_name").value = this.dataset.name;

            document.getElementById("formEditarRol").action =
                `/roles/${this.dataset.id}`;

            modalEditar.show();
        });
    });
});
/*
 * Modal Usuarios Rol
 */
const modalUsuariosElement = document.getElementById("modalUsuariosRol");

if (modalUsuariosElement) {
    const modalUsuarios =
        bootstrap.Modal.getOrCreateInstance(modalUsuariosElement);

    document.querySelectorAll(".btnUsuariosRol").forEach((btn) => {
        btn.addEventListener("click", function () {
            const roleId = this.dataset.id;

            document.getElementById("formUsuariosRol").action =
                `/roles/${roleId}/usuarios`;
            document.getElementById("nombreRol").textContent =
                this.dataset.name;

            axios
                .get(`/roles/${roleId}/usuarios`)
                .then((response) => {
                    const listaUsuarios =
                        document.getElementById("listaUsuarios");

                    listaUsuarios.innerHTML = "";

                    const asignados = response.data.asignados;

                    response.data.usuarios.forEach((usuario) => {
                        const checked = asignados.includes(usuario.id)
                            ? "checked"
                            : "";

                        listaUsuarios.innerHTML += `<div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" value="${usuario.id}" name="usuarios[]" id="usuario_${usuario.id}"${checked}>
                        <label class="form-check-label" for="usuario_${usuario.id}"><strong>${usuario.username}</strong>- ${usuario.name} </label></div>`;
                    });
                })
                .catch((error) => {
                    console.error("ERROR AXIOS:");
                    console.error(error);
                });

            modalUsuarios.show();
        });
    });
}

/*
|--------------------------------------------------------------------------
| Modal Permisos
|--------------------------------------------------------------------------
*/

const modalPermisosElement = document.getElementById("modalPermisosRol");

if (modalPermisosElement) {
    const modalPermisos =
        bootstrap.Modal.getOrCreateInstance(modalPermisosElement);

    document.querySelectorAll(".btnPermisosRol").forEach((btn) => {
        btn.addEventListener("click", function () {
            const roleId = this.dataset.id;

            document.getElementById("nombreRolPermiso").textContent =
                this.dataset.name;

            document.getElementById("formPermisosRol").action =
                `/roles/${roleId}/permisos`;

            axios
                .get(`/roles/${roleId}/permisos`)
                .then((response) => {
                    const listaPermisos =
                        document.getElementById("listaPermisos");

                    listaPermisos.innerHTML = "";

                    const asignados = response.data.asignados;

                    response.data.permisos.forEach((permiso) => {
                        const checked = asignados.includes(permiso.id)
                            ? "checked"
                            : "";

                        listaPermisos.innerHTML += `
        <div class="form-check mb-2">

            <input
                class="form-check-input"
                type="checkbox"
                value="${permiso.name}"
                name="permisos[]"
                id="permiso_${permiso.id}"
                ${checked}>

            <label
                class="form-check-label"
                for="permiso_${permiso.id}">

                ${permiso.name}

            </label>

        </div>
    `;
                    });
                })
                .catch((error) => {
                    console.error(error);
                });

            modalPermisos.show();
        });
    });
}
