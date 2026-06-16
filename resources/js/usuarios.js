import * as bootstrap from "bootstrap";

document.addEventListener("DOMContentLoaded", function () {
    /*
     * Modal Alta Usuario
     */
    if (document.getElementById("abrirModalUsuario")) {
        const modalUsuario = bootstrap.Modal.getOrCreateInstance(
            document.getElementById("modalUsuario"),
        );

        modalUsuario.show();
    }

    /*
     * Tooltips
     */
    document.querySelectorAll("[title]").forEach((elemento) => {
        new bootstrap.Tooltip(elemento);
    });

    /*
     * Modal Editar Usuario
     */
    const modalEditarElement = document.getElementById("modalEditarUsuario");

    if (modalEditarElement) {
        const modalEditar =
            bootstrap.Modal.getOrCreateInstance(modalEditarElement);

        document.querySelectorAll(".btnEditarUsuario").forEach((btn) => {
            btn.addEventListener("click", function () {
                document.getElementById("edit_username").value =
                    this.dataset.username;

                document.getElementById("edit_name").value = this.dataset.name;

                document.getElementById("edit_email").value =
                    this.dataset.email;

                document.getElementById("edit_estado").value =
                    this.dataset.estado;

                document.getElementById("formEditarUsuario").action =
                    `/usuarios/${this.dataset.id}`;

                modalEditar.show();
            });
        });
    }

    /*
     * Modal Estado Usuario
     */
    const modalEstadoElement = document.getElementById("modalEstadoUsuario");

    if (modalEstadoElement) {
        const modalEstado =
            bootstrap.Modal.getOrCreateInstance(modalEstadoElement);

        document.querySelectorAll(".btnCambiarEstado").forEach((btn) => {
            btn.addEventListener("click", function () {
                const id = this.dataset.id;
                const usuario = this.dataset.usuario;
                const estado = this.dataset.estado;

                document.getElementById("nuevoEstado").value = estado;

                document.getElementById("mensajeEstado").innerHTML =
                    estado === "INACTIVO"
                        ? `¿Desea desactivar al usuario <strong>${usuario}</strong>?`
                        : `¿Desea activar al usuario <strong>${usuario}</strong>?`;

                document.getElementById("formEstadoUsuario").action =
                    `/usuarios/${id}/estado`;

                modalEstado.show();
            });
        });
    }

    /*
     * Modal Restablecer Contraseña
     */
    const modalPasswordElement = document.getElementById("modalPassword");

    if (modalPasswordElement) {
        const modalPassword =
            bootstrap.Modal.getOrCreateInstance(modalPasswordElement);

        document.querySelectorAll(".btnResetPassword").forEach((btn) => {
            btn.addEventListener("click", function () {
                document.getElementById("password_username").value =
                    this.dataset.usuario;

                document.getElementById("formPassword").action =
                    `/usuarios/${this.dataset.id}/password`;

                document.getElementById("password").value = "";
                document.getElementById("password_confirmation").value = "";

                const mensaje = document.getElementById("mensajePasswordReset");

                if (mensaje) {
                    mensaje.innerHTML = "";
                }

                modalPassword.show();
            });
        });
    }

    /*
     * Reabrir modal Password si hubo error de validación
     */
    if (document.getElementById("abrirModalPassword")) {
        const userId = document.getElementById("password_user_id")?.value;

        if (userId) {
            document.getElementById("formPassword").action =
                `/usuarios/${userId}/password`;
        }

        const modalPassword = bootstrap.Modal.getOrCreateInstance(
            document.getElementById("modalPassword"),
        );

        modalPassword.show();
    }
});
