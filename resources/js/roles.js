import * as bootstrap from "bootstrap";

document.addEventListener("DOMContentLoaded", function () {

    const modalEditarElement =
        document.getElementById("modalEditarRol");

    if (!modalEditarElement) return;

    const modalEditar =
        bootstrap.Modal.getOrCreateInstance(
            modalEditarElement
        );

    document.querySelectorAll(".btnEditarRol")
        .forEach((btn) => {

            btn.addEventListener("click", function () {

                document.getElementById("edit_name")
                    .value =
                    this.dataset.name;

                document.getElementById("formEditarRol")
                    .action =
                    `/roles/${this.dataset.id}`;

                modalEditar.show();

            });

        });

});

/*
 * Modal Usuarios Rol
 */
const modalUsuariosElement =
    document.getElementById("modalUsuariosRol");

if (modalUsuariosElement) {

    const modalUsuarios =
        bootstrap.Modal.getOrCreateInstance(
            modalUsuariosElement
        );

   document.querySelectorAll(".btnUsuariosRol")
    .forEach((btn) => {

        btn.addEventListener("click", function () {

    const roleId = this.dataset.id;

    console.log("ROLE ID:", roleId);

    document.getElementById("nombreRol").textContent =
        this.dataset.name;
console.log("ANTES AXIOS");
    axios.get(`/roles/${roleId}/usuarios`).then((response) => {

            const listaUsuarios =
        document.getElementById("listaUsuarios");

    listaUsuarios.innerHTML = "";

   const asignados = response.data.asignados;

response.data.usuarios.forEach((usuario) => {

    const checked =
        asignados.includes(usuario.id)
            ? "checked"
            : "";

    listaUsuarios.innerHTML += `
        <div class="form-check mb-2">

            <input
                class="form-check-input"
                type="checkbox"
                value="${usuario.id}"
                name="usuarios[]"
                id="usuario_${usuario.id}"
                ${checked}>

            <label
                class="form-check-label"
                for="usuario_${usuario.id}">

                <strong>${usuario.username}</strong>
                - ${usuario.name}

            </label>

        </div>
    `;

});


        })
        .catch((error) => {

            console.error("ERROR AXIOS:");
            console.error(error);

        });
        console.log("despues AXIOS");

    modalUsuarios.show();

});

  

        });

}