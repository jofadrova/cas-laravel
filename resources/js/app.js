import "./bootstrap";
import * as bootstrap from "bootstrap";

window.bootstrap = bootstrap;

import Alpine from "alpinejs";

import "bootstrap/dist/css/bootstrap.min.css";
import "@fortawesome/fontawesome-free/css/all.min.css";

import "./usuarios";
import "./roles";
import "./permisos";
import "./socios";

window.Alpine = Alpine;

Alpine.start();
