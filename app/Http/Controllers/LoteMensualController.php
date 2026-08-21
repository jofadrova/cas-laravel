<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLoteMensualRequest;
use App\Http\Requests\UpdateLoteMensualRequest;
use App\Models\EnvioMensual;
use App\Models\LoteMensual;
use App\Services\ProcesamientoMensual\GaranteEnvioSyncService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use LogicException;

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
            'enviosDisponibles' => EnvioMensual::query()
                ->where('estado', EnvioMensual::ESTADO_ENVIADO)
                ->whereDoesntHave('loteMensual')
                ->orderByDesc('gestion')
                ->orderByDesc('mes')
                ->get(),
        ]);
    }

    public function store(
        StoreLoteMensualRequest $request,
        GaranteEnvioSyncService $sincronizadorGarantes
    ): RedirectResponse
    {
        $lote = DB::transaction(function () use (
            $request,
            $sincronizadorGarantes
        ): LoteMensual {
            $envio = EnvioMensual::query()
                ->lockForUpdate()
                ->findOrFail($request->integer('envio_mensual_id'));

            if ($envio->estado !== EnvioMensual::ESTADO_ENVIADO
                || $envio->loteMensual()->exists()) {
                throw ValidationException::withMessages([
                    'envio_mensual_id' => 'El lote ya fue recibido o no se encuentra enviado.',
                ]);
            }

            if (! $envio->archivoPrestamos()->exists()
                || ! $envio->archivoGarantes()->exists()) {
                throw ValidationException::withMessages([
                    'envio_mensual_id' => 'El envío no tiene el TXT y el Excel de garantes requeridos.',
                ]);
            }

            $lote = LoteMensual::create([
                'envio_mensual_id' => $envio->id,
                'mes' => $envio->mes,
                'gestion' => $envio->gestion,
                'fecha_recepcion' => $request->validated('fecha_recepcion'),
                'observaciones' => $request->validated('observaciones'),
                'estado' => LoteMensual::ESTADO_BORRADOR,
                'creado_por' => $request->user()->id,
            ]);

            try {
                $sincronizadorGarantes->sincronizar(
                    $lote,
                    $request->user()->id
                );
            } catch (LogicException $exception) {
                throw ValidationException::withMessages([
                    'envio_mensual_id' => $exception->getMessage(),
                ]);
            }

            $envio->update(['estado' => EnvioMensual::ESTADO_RECIBIDO]);

            return $lote;
        });

        return redirect()
            ->route('procesamiento-mensual.lotes.show', $lote)
            ->with('success', 'Lote mensual registrado correctamente.');
    }

    public function show(LoteMensual $lote): View
    {
        $lote->load(['creador', 'cerrador', 'envioMensual.archivoGarantes']);

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
