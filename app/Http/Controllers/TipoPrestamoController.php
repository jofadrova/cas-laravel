<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tasa;
use App\Http\Requests\StoreTipoPrestamoRequest;
use App\Http\Requests\UpdateTipoPrestamoRequest;

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
        return view('prestamos.tipos.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTipoPrestamoRequest $request)
    {
         Tasa::create([
        'descripcion_tasa' => strtoupper($request->descripcion_tasa),
        'porcentaje' => $request->porcentaje,
        'cod_desc' => 0,
        'tipo_moneda' => $request->tipo_moneda,
        'min_defensa' => $request->min_defensa,
        'itf' => $request->itf,
        'int_penal' => $request->int_penal,
        'papeleria' => $request->papeleria,
        'monto_max' => $request->monto_max,
        'plazo_max' => $request->plazo_max,
        'garante' => $request->garante,
        'obs' => strtoupper($request->obs),
        'estado' => $request->estado,
        'tipo_tasa' => null

    ]);

    return redirect()
        ->route('prestamos.tipos.index')
        ->with(
            'success',
            'Tipo de préstamo registrado correctamente.'
        );
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
    public function edit(Tasa $tasa)
    {
        return view('prestamos.tipos.edit', compact('tasa'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTipoPrestamoRequest $request, Tasa $tasa)
    {
        $tasa->update([
            'descripcion_tasa' => strtoupper($request->descripcion_tasa),
            'porcentaje' => $request->porcentaje,
            'tipo_moneda' => $request->tipo_moneda,
            'min_defensa' => $request->min_defensa,
            'itf' => $request->itf,
            'int_penal' => $request->int_penal,
            'papeleria' => $request->papeleria,
            'monto_max' => $request->monto_max,
            'plazo_max' => $request->plazo_max,
            'garante' => $request->garante,
            'obs' => strtoupper($request->obs),
            'estado' => $request->estado,
        ]);

        return redirect()
            ->route('prestamos.tipos.index')
            ->with('success','Tipo de préstamo actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function estado(string $id)
    {
        //
    }
}
