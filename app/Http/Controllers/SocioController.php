<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Grado;
use App\Models\Fuerza;
use App\Models\Arma;
use App\Models\Escalafon;
use App\Models\Diplomado;

class SocioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('socios.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('socios.create', [
            'grados' => Grado::orderBy('grado')->get(),
            'fuerzas' => Fuerza::orderBy('fuerza')->get(),
            'armas' => Arma::orderBy('descripcion_arma')->get(),
            'escalafones' => Escalafon::orderBy('descripcion')->get(),
            'diplomados' => Diplomado::orderBy('descripcion')->get(),
        ]);
    }
        /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
