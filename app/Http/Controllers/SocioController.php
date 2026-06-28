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
use App\Http\Requests\UpdateSocioRequest;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class SocioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Socio::with([
            'institucion.grado'
        ]);

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }
        if ($request->filled('valor')) {

            switch ($request->buscar_por) {
                case 'papeleta':
                    $query->whereHas('institucion', function ($q) use ($request) {
                        $q->where('papeleta','like','%' . $request->valor . '%');
                    });

                    break;

                case 'ci':
                    $query->where('nro_doc','like','%' . $request->valor . '%');
                    break;
                case 'apellido':
                    $query->where(function ($q) use ($request) {
                        $q->where('paterno','like','%' . $request->valor . '%')
                        ->orWhere('materno','like','%' . $request->valor . '%');
                    });
                    break;
                case 'nombre':
                    $query->where('nombres','like','%' . $request->valor . '%');
                    break;
            }
        }

        $sort = $request->input('sort', 'paterno');
        $direction = $request->input('direction', 'asc');

        $allowedSorts = [
            'paterno',
            'nro_doc',
            'estado'
        ];

        if (!in_array($sort, $allowedSorts)) {
            $sort = 'paterno';
        }


        $perPage = $request->per_page ?? 10;

        $socios = $query
            ->orderBy($sort, $direction)
            ->paginate($perPage)
            ->withQueryString();

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

                $request->file('foto')->storeAs('socios',$foto,'public');
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
            ->with('success','Socio registrado correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $socio = Socio::with(['institucion.grado','residencia'])->findOrFail($id);

        return view('socios.show', compact('socio'));
    }

    /**
     * Show the form for editing the specified resource.
     */
   public function edit($id)
    {
        $socio = Socio::with(['institucion','residencia'])->findOrFail($id);

        $departamentos = Dominio::where('dominio', 'DEPARTAMENTOS')->get();
        $sexos = Dominio::where('dominio', 'SEXO')->get();
        $estadosCiviles = Dominio::where('dominio', 'ECIVIL')->get();
        $meses = Dominio::where('dominio', 'MESES')->orderBy('id')->get();

        $grados = Grado::orderBy('grado')->get();
        $fuerzas = Fuerza::orderBy('fuerza')->get();
        $armas = Arma::orderBy('descripcion_arma')->get();
        $escalafones = Escalafon::orderBy('descripcion')->get();
        $diplomados = Diplomado::orderBy('descripcion')->get();

        $resoluciones = ResolucionesJuridica::where('estado', 'AC')->get();

        return view('socios.edit', compact(
            'socio',
            'departamentos',
            'sexos',
            'estadosCiviles',
            'meses',
            'grados',
            'fuerzas',
            'armas',
            'escalafones',
            'diplomados',
            'resoluciones'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSocioRequest $request, Socio $socio)
    {
        DB::transaction(function () use ($request, $socio) {

            $foto = $socio->foto;

            if ($request->hasFile('foto')) {
                if ($socio->foto && Storage::disk('public')->exists('socios/' . $socio->foto)) {
                    Storage::disk('public')->delete('socios/' . $socio->foto);
                }
                $foto = time() . '_' . $request->file('foto')->getClientOriginalName();
                $request->file('foto')->storeAs('socios', $foto, 'public');

                $socio->foto = $foto;
            }
            $socio->save();

            $socio->update([

                'nombres' => strtoupper($request->nombres),
                'paterno' => strtoupper($request->paterno),
                'materno' => strtoupper($request->materno),

                'nro_doc' => $request->nro_doc,
                'expedido' => $request->expedido,

                'sexo' => $request->sexo,
                'fecha_nac' => $request->fecha_nac,
                'estado_civil' => $request->estado_civil,

                'foto' => $foto,
            ]);

            $socio->residencia()->update([

                'id_socio' => $socio->id,
                'departamento' => $request->departamento,
                'ciudad' => $request->ciudad,
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
            ]);

            $socio->institucion()->update([

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

            $subcuenta = ContaSubcuenta::where('id_socio', $socio->id)->first();
            if ($subcuenta) {
                $subcuenta->descripcion = trim(
                    $request->paterno . ' ' .
                    $request->materno . ' ' .
                    $request->nombres
                );
                $subcuenta->codigo = $request->papeleta;
                $subcuenta->save();
            }
        });
        return redirect()
            ->route('socios.index')
            ->with('success', 'Socio actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $socio = Socio::findOrFail($id);

        $socio->estado = 'BA';

        $socio->save();

        return redirect()
            ->route('socios.index')
            ->with('success', 'Socio dado de baja correctamente.');
    }

    public function reactivar($id)
    {
        $socio = Socio::findOrFail($id);

        $socio->estado = 'AC';

        $socio->save();

        return back()
            ->with('success', 'Socio reactivado correctamente.');
    }

    public function cambiarEstado(Request $request, Socio $socio)
    {
        $socio->estado = $request->estado;

        $socio->save();

        return redirect()
            ->route('socios.index')
            ->with(
                'success',
                $request->estado == 'BA'
                    ? 'Asociado dado de baja correctamente.'
                    : 'Asociado reactivado correctamente.'
            );
    }

    public function kardex(Socio $socio)
    {
        $socio->load([
            'institucion.grado',
            'institucion.escalafon',
            'institucion.fuerza',
            'institucion.arma',
            'institucion.diplomado',
            'residencia',
        ]);

         $contenidoQR = "SCAS
        ID: {$socio->id}
        PAPELETA: {$socio->institucion->papeleta}
        CI: {$socio->nro_doc}
        NOMBRE: {$socio->paterno} {$socio->materno} {$socio->nombres}
        ESTADO: {$socio->estado}
        ";
        $qr = base64_encode(QrCode::format('svg')->size(200)->generate($contenidoQR));

        $pdf = Pdf::loadView(
            'socios.kardex',
            compact('socio', 'qr')
        );

        $pdf->setPaper('letter');
        $dompdf = $pdf->getDomPDF();
        $dompdf->render();
        $canvas = $dompdf->getCanvas();
        $canvas->page_text(480,760,"Página {PAGE_NUM} de {PAGE_COUNT}",null,8);
        return $pdf->stream(
            'KARDEX_'.$socio->nro_doc.'.pdf'
        );
    }

    public function revincular(Socio $socio)
    {
        return back()->with('info', 'funcionalidad en desarrollo');
    }

    public function buscar(Request $request)
    {
        $buscar = trim($request->q);
        if (strlen($buscar) < 2) {
            return response()->json([]);
        }

        $socios = SocioInstitucion::with('socio')
            ->where('estado', 'AC')
            ->where(function ($q) use ($buscar) {
                $q->where('papeleta', 'like', "%{$buscar}%")
                ->orWhereHas('socio', function ($s) use ($buscar) {
                        $s->where('nombres', 'like', "%{$buscar}%")
                        ->orWhere('paterno', 'like', "%{$buscar}%")
                        ->orWhere('materno', 'like', "%{$buscar}%");
                });
            })
            ->orderBy('papeleta')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id_socio,
                    'papeleta' => $item->papeleta,
                    'nombre' => trim(
                        $item->socio->paterno.' '.
                        $item->socio->materno.' '.
                        $item->socio->nombres
                    ),
                    'estado' => $item->estado
                ];
            });
        return response()->json($socios);
    }
}
