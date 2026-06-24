<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tasa;

class TipoPrestamoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tasas = Tasa::orderBy('descripcion_tasa')->paginate(10);
        return view('prestamos.tipos.index', compact('tasas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
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
