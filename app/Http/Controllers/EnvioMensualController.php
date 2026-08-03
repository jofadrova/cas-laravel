<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class EnvioMensualController extends Controller
{
    public function index(): View
    {
        return view('procesamiento-mensual.envios-mensuales.index');
    }
}
