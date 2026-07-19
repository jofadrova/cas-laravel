import "./bootstrap";
import * as bootstrap from "bootstrap";
import Chart from "chart.js/auto";

window.bootstrap = bootstrap;
window.Chart = Chart;

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
import "./scas/confirmacion";

import { initDashboard } from "./dashboard";
import { iniciarPagos } from './pagos/index';
import { iniciarAmortizacionCapital } from './prestamos/amortizacion-capital';
import { iniciarRefinanciamiento } from './prestamos/refinanciamiento';
import { iniciarSidebar } from './sidebar';
import { iniciarDetallePrestamo } from './prestamos/detalleModal';

import PrestamoGarantes from './prestamos/PrestamoGarantes';
if (document.getElementById('cardNuevosGarantes')) {
    new PrestamoGarantes();
}

document.addEventListener('DOMContentLoaded', () => {
    iniciarPagos();
    iniciarAmortizacionCapital();
    iniciarRefinanciamiento();
    iniciarSidebar();
    iniciarDetallePrestamo();
});

window.initDashboard = initDashboard;
window.Alpine = Alpine;

Alpine.start();
