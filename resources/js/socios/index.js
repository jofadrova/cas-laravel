import { iniciarValidaciones } from "./validaciones";
import { iniciarFotografia } from "./fotografia";
import { iniciarEstadoSocio } from "./estado";
import { iniciarDependientes } from "./dependientes";

document.addEventListener("DOMContentLoaded", () => {

    iniciarValidaciones();
    iniciarFotografia();
    iniciarEstadoSocio();
    iniciarDependientes();

});