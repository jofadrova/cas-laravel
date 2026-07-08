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
import "./tasa.js";
import "./scas/papeleta-search.js";
import "./prestamos/form";
import "./scas/notifier";
import './theme';

import { initDashboard } from "./dashboard";

window.initDashboard = initDashboard;
window.Alpine = Alpine;

Alpine.start();
