<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\HasTable;
use App\Support\ScasTable;
use App\Models\Prestamo;
use App\Models\Tasa;

class PrestamoController extends Controller
{

    use HasTable;
    public function index()
    {
        $tipos = Tasa::where('estado', 'AC')
            ->orderBy('descripcion_tasa')
            ->get();


        $table = ScasTable::make(
            Prestamo::with(['socio', 'tipo'])
        )

       ->customSearch(function ($query, $buscar) {

            switch (request('campo')) {

                case 'solicitud':

                    $query->where(
                        'nro_solicitud',
                        'like',
                        "%{$buscar}%"
                    );

                    break;

                case 'papeleta':

                    $query->whereHas('socio', function ($q) use ($buscar) {

                        $q->where(
                            'nro_papeleta',
                            'like',
                            "%{$buscar}%"
                        );

                    });

                    break;

                case 'asociado':

                    $query->whereHas('socio', function ($q) use ($buscar) {

                        $q->where('nombres', 'like', "%{$buscar}%")
                        ->orWhere('paterno', 'like', "%{$buscar}%")
                        ->orWhere('materno', 'like', "%{$buscar}%");

                    });

                    break;

                default:

                    $query->where(
                        'nro_solicitud',
                        'like',
                        "%{$buscar}%"
                    );

                    break;
            }

        })

        ->filters([
            'estado',
            'tipo_prestamo'
        ])

        ->sortable([
            'nro_solicitud',
            'monto',
            'saldo_actual',
            'estado'
        ])

        ->defaultSort('nro_solicitud');

        $prestamos = $table->paginate();

        return view('prestamos.index', compact('prestamos', 'table', 'tipos')
        );
    }
}
