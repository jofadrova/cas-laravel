<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Grado;
use App\Models\Fuerza;
use App\Models\Arma;
use App\Models\Escalafon;
use App\Models\Diplomado;
use App\Models\Dominio;
use App\Models\ResolucionesJuridica;
use App\Http\Requests\StoreSocioRequest;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

use App\Models\Socio;
use App\Models\Residencia;
use App\Models\SocioInstitucion;
use App\Models\ContaSubcuenta;

class SocioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $socios = Socio::with([
            'institucion',
            'institucion.grado'
        ])
        ->orderByDesc('id')
        ->paginate(15);

        return view('socios.index', compact('socios'));
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

            'departamentos' => Dominio::where('dominio','DEPARTAMENTOS')->orderBy('Descripcion')->get(),
            'sexos' => Dominio::where('dominio','SEXO')->orderBy('Descripcion')->get(),
            'estadosCiviles' => Dominio::where('dominio','ECIVIL')->orderBy('Descripcion')->get(),
            'meses' => Dominio::where('dominio','MESES')->orderBy('id')->get(),
            'juridicas' => Dominio::where('dominio','JURIDICA')->orderBy('Descripcion')->get(),
            'resoluciones' => ResolucionesJuridica::where('tipo', 59) ->where('estado', 'AC')->orderByDesc('gestion')->orderByDesc('num')->get(),
        ]);
    }
        /**
     * Store a newly created resource in storage.
     */
   public function store(StoreSocioRequest $request)
    {
        DB::transaction(function () use ($request) {

            $foto = null;

            if ($request->hasFile('foto')) {

                $foto = time() . '_' .
                        $request->file('foto')->getClientOriginalName();

                $request->file('foto')
                    ->storeAs(
                        'socios',
                        $foto,
                        'public'
                    );
            }

            $socio = Socio::create([

                'nombres' => strtoupper($request->nombres),
                'paterno' => strtoupper($request->paterno),
                'materno' => strtoupper($request->materno),

                'nro_doc' => $request->nro_doc,
                'expedido' => $request->expedido,

                'sexo' => $request->sexo,
                'fecha_nac' => $request->fecha_nac,
                'estado_civil' => $request->estado_civil,

                'foto' => $foto,

                'estado' => 'AC',

                'num_correlativo' => 0,

                'estado_kardex' => 'AC',

                'mindef' => 'NO',

                'es_revinculacion' => 0,
                'cantidad_revinculaciones' => 0,

                'vinculacion_actual' => 1,
            ]);

            Residencia::create([

                'id_socio' => $socio->id,

                'departamento' => $request->departamento,
                'ciudad' => $request->ciudad,

                'radicatoria' => $request->radicatoria,

                'zona' => $request->zona,
                'calle' => $request->calle,

                'nro' => $request->nro,

                'telefono' => $request->telefono,

                'correo' => $request->correo,

                'formularioSolicitud' => $request->solicitud,

                'afiliacionAfcoop' =>
                    $request->boolean('afiliacion_afcoop')
                        ? 'CA'
                        : 'NO',

                'fotocopiaCarnet' =>
                    $request->boolean('fotocopia_ci')
                        ? 'FC'
                        : 'NO',

                'resolucion' => $request->resolucion,

                'estado' => 'AC',
            ]);

            SocioInstitucion::create([

                'id_socio' => $socio->id,

                'papeleta' => $request->papeleta,
                'carnet_mil' => $request->carnet_mil,
                'cossmil' => $request->cossmil,

                'afil_mes' => $request->afil_mes,
                'afil_anio' => $request->afil_anio,

                'anio_prom' => $request->anio_prom,

                'id_escalafon' => $request->id_escalafon,
                'id_fuerza' => $request->id_fuerza,
                'id_arma' => $request->id_arma,
                'id_grado' => $request->id_grado,
                'id_diplomado' => $request->id_diplomado,

                'salario' => $request->salario,

                'estado' => 'AC',

                'devolAportes' => '',
                'devolCapitalizacion' => '',
            ]);

            ContaSubcuenta::create([

                'codigo' => $request->papeleta,

                'descripcion' =>
                    trim(
                        $request->paterno . ' ' .
                        $request->materno . ' ' .
                        $request->nombres
                    ),

                'cod_tipo' => 'SOCIO',

                'estado' => 'AC',

                'id_socio' => $socio->id,

            ]);

        });

        return redirect()
            ->route('socios.index')
            ->with(
                'success',
                'Socio registrado correctamente.'
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
