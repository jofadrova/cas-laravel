import "./bootstrap";
import * as bootstrap from "bootstrap";

window.bootstrap = bootstrap;

import Alpine from "alpinejs";

import "bootstrap/dist/css/bootstrap.min.css";
import "@fortawesome/fontawesome-free/css/all.min.css";

import "./usuarios";
import "./roles";
import "./permisos";
import "./tasa.js";
import "./socios/index";
import "./scas/papeleta-search.js";
import "./prestamos/form";
import "./scas/notifier";

import { initDashboard } from "./dashboard";

import PrestamoGarantes from './prestamos/PrestamoGarantes';
if (document.getElementById('cardNuevosGarantes')) {
    new PrestamoGarantes();
}

window.initDashboard = initDashboard;
window.Alpine = Alpine;

Alpine.start();
