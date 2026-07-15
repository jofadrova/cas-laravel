import * as bootstrap from "bootstrap";

export function iniciarEstadoSocio() {

    const modalEstadoSocioElement =
        document.getElementById("modalEstadoSocio");

    if (!modalEstadoSocioElement) return;

    const modalEstadoSocio =
        new bootstrap.Modal(modalEstadoSocioElement);

    if (modalEstadoSocioElement) {
     const modalEstadoSocio =
        new bootstrap.Modal(modalEstadoSocioElement);

    document
        .querySelectorAll(".btnCambiarEstadoSocio")
        .forEach((btn) => {

            btn.addEventListener("click", function () {

                const id = this.dataset.id;
                const socio = this.dataset.socio;
                const estado = this.dataset.estado;

                document.getElementById(
                    "nuevoEstadoSocio"
                ).value = estado;

                document.getElementById(
                    "mensajeEstadoSocio"
                ).innerHTML =

                    estado === "BA"
                        ? `¿Desea dar de baja al asociado <strong>${socio}</strong>?`
                        : `¿Desea reactivar al asociado <strong>${socio}</strong>?`;

                document.getElementById(
                    "formEstadoSocio"
                ).action = `/socios/${id}/estado`;
                modalEstadoSocio.show();
            });
        });
}


}




