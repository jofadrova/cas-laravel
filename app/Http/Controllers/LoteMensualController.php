<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLoteMensualRequest;
use App\Http\Requests\UpdateLoteMensualRequest;
use App\Models\LoteMensual;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LoteMensualController extends Controller
{
    public function index(Request $request): View
    {
        $lotes = LoteMensual::query()
            ->with('creador')
            ->when(
                $request->filled('mes'),
                fn ($query) => $query->where('mes', $request->integer('mes'))
            )
            ->when(
                $request->filled('gestion'),
                fn ($query) => $query->where(
                    'gestion',
                    $request->integer('gestion')
                )
            )
            ->when(
                $request->filled('estado'),
                fn ($query) => $query->where(
                    'estado',
                    (string) $request->string('estado')
                )
            )
            ->orderByDesc('gestion')
            ->orderByDesc('mes')
            ->paginate(15)
            ->withQueryString();

        return view('procesamiento-mensual.lotes.index', [
            'lotes' => $lotes,
            'meses' => LoteMensual::MESES,
            'estados' => LoteMensual::ESTADOS,
        ]);
    }

    public function create(): View
    {
        return view('procesamiento-mensual.lotes.create', [
            'meses' => LoteMensual::MESES,
        ]);
    }

    public function store(StoreLoteMensualRequest $request): RedirectResponse
    {
        $datos = $request->validated();
        $datos['estado'] = LoteMensual::ESTADO_BORRADOR;
        $datos['creado_por'] = $request->user()->id;

        $lote = LoteMensual::create($datos);

        return redirect()
            ->route('procesamiento-mensual.lotes.show', $lote)
            ->with('success', 'Lote mensual registrado correctamente.');
    }

    public function show(LoteMensual $lote): View
    {
        $lote->load(['creador', 'cerrador']);

        return view('procesamiento-mensual.lotes.show', compact('lote'));
    }

    public function edit(LoteMensual $lote): View|RedirectResponse
    {
        if (! $lote->puedeEditar()) {
            return redirect()
                ->route('procesamiento-mensual.lotes.show', $lote)
                ->with(
                    'error',
                    'El lote no puede editarse porque se encuentra '
                    . strtolower($lote->estado) . '.'
                );
        }

        return view('procesamiento-mensual.lotes.edit', [
            'lote' => $lote,
            'meses' => LoteMensual::MESES,
        ]);
    }

    public function update(
        UpdateLoteMensualRequest $request,
        LoteMensual $lote
    ): RedirectResponse {
        $lote->update($request->validated());

        return redirect()
            ->route('procesamiento-mensual.lotes.show', $lote)
            ->with('success', 'Lote mensual actualizado correctamente.');
    }
}
