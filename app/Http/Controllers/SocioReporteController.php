<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SocioReporteController extends Controller
{
    public function index()
    {
        return view('socios.reportes.index');
    }
}
