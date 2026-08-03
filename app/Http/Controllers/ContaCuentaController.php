<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContaCuentaRequest;
use App\Models\ContaCuenta;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContaCuentaController extends Controller
{
    public function index(Request $request): View
    {
        $cuentas = ContaCuenta::query()
            ->with('padre:id,codigo,nombre')
            ->when($request->filled('buscar'), function ($query) use ($request) {
                $buscar = '%'.trim($request->string('buscar')).'%';
                $query->where(fn ($q) => $q->where('codigo', 'like', $buscar)->orWhere('nombre', 'like', $buscar));
            })
            ->when($request->filled('tipo'), fn ($q) => $q->where('tipo', $request->string('tipo')))
            ->when($request->filled('estado'), fn ($q) => $q->where('estado', $request->input('estado') === '1'))
            ->ordenJerarquico()
            ->paginate(20)
            ->withQueryString();

        return view('contabilidad.cuentas.index', compact('cuentas'));
    }

    public function create(): View
    {
        return view('contabilidad.cuentas.create', ['padres' => $this->cuentasPadre()]);
    }

    public function store(ContaCuentaRequest $request): RedirectResponse
    {
        $datos = $request->validated();
        $datos['nivel'] = $this->calcularNivel($datos['cuenta_padre_id'] ?? null);
        ContaCuenta::create($datos);

        return redirect()->route('contabilidad.cuentas.index')->with('success', 'Cuenta contable registrada correctamente.');
    }

    public function edit(ContaCuenta $cuenta): View
    {
        return view('contabilidad.cuentas.edit', [
            'cuenta' => $cuenta,
            'padres' => $this->cuentasPadre($cuenta),
        ]);
    }

    public function update(ContaCuentaRequest $request, ContaCuenta $cuenta): RedirectResponse
    {
        $datos = $request->validated();
        $datos['nivel'] = $this->calcularNivel($datos['cuenta_padre_id'] ?? null);
        $cuenta->update($datos);
        $this->actualizarNivelesDescendientes($cuenta);

        return redirect()->route('contabilidad.cuentas.index')->with('success', 'Cuenta contable actualizada correctamente.');
    }

    public function estado(Request $request, ContaCuenta $cuenta): RedirectResponse
    {
        $datos = $request->validate(['estado' => ['required', 'boolean']]);
        if (! $datos['estado'] && $cuenta->hijas()->where('estado', true)->exists()) {
            return back()->withErrors(['estado' => 'No se puede inactivar una cuenta que tiene dependientes activos.']);
        }
        $cuenta->update(['estado' => $datos['estado']]);

        return back()->with('success', 'Estado de la cuenta actualizado correctamente.');
    }

    private function cuentasPadre(?ContaCuenta $excluir = null)
    {
        return ContaCuenta::query()
            ->where('estado', true)
            ->where('acepta_movimientos', false)
            ->when($excluir, fn ($q) => $q->whereKeyNot($excluir->id))
            ->ordenJerarquico()
            ->get(['id', 'codigo', 'nombre', 'nivel']);
    }

    private function calcularNivel(?int $padreId): int
    {
        return $padreId ? ContaCuenta::findOrFail($padreId)->nivel + 1 : 1;
    }

    private function actualizarNivelesDescendientes(ContaCuenta $cuenta): void
    {
        foreach ($cuenta->hijas as $hija) {
            $hija->update(['nivel' => $cuenta->nivel + 1]);
            $this->actualizarNivelesDescendientes($hija);
        }
    }
}
