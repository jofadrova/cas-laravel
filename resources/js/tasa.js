import * as bootstrap from "bootstrap";

document.addEventListener("DOMContentLoaded", function () {
const modalEstadoElement = document.getElementById("modalEstadoTasa");

    if (modalEstadoElement) {
        const modalEstado =
            bootstrap.Modal.getOrCreateInstance(modalEstadoElement);

        document.querySelectorAll(".btnCambiarEstadoTasa").forEach((btn) => {
            btn.addEventListener("click", function () {
                const id = this.dataset.id;
                const tasa = this.dataset.descripcion;
                const estado = this.dataset.estado;

                document.getElementById("nuevoEstadoTasa").value = estado;

                document.getElementById("mensajeEstadoTasa").innerHTML =
                    estado === "IN"
                        ? `¿Desea desactivar el Tipo de Prestamo <strong>${tasa}</strong>?`
                        : `¿Desea activar el Tipo de Prestamo <strong>${tasa}</strong>?`;

                document.getElementById("formEstadoTasa").action =
                    `/prestamos/tipos/${id}/estado`;
                
                modalEstado.show();
            });
        });
    }
});