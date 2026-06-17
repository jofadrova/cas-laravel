document.addEventListener("DOMContentLoaded", () => {
    const modalEditarElement = document.getElementById("modalEditarPermiso");

    if (!modalEditarElement) {
        return;
    }

    const modalEditar = bootstrap.Modal.getOrCreateInstance(modalEditarElement);

    document.querySelectorAll(".btnEditarPermiso").forEach((btn) => {
        btn.addEventListener("click", function () {
            document.getElementById("edit_name").value = this.dataset.name;

            document.getElementById("formEditarPermiso").action =
                `/permisos/${this.dataset.id}`;

            modalEditar.show();
        });
    });
});
