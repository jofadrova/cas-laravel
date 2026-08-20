<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEnvioMensualRequest;
use App\Models\EnvioMensual;
use App\Models\EnvioMensualArchivo;
use App\Models\LoteMensual;
use App\Services\ProcesamientoMensual\EnvioPrestamosTxtService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class EnvioMensualController extends Controller
{
    public function index(Request $request): View
    {
        $envios = EnvioMensual::query()
            ->with(['loteMensual', 'creador'])
            ->when($request->filled('gestion'), fn ($query) => $query->where(
                'gestion',
                $request->integer('gestion')
            ))
            ->when($request->filled('estado'), fn ($query) => $query->where(
                'estado',
                (string) $request->string('estado')
            ))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('procesamiento-mensual.envios-mensuales.index', [
            'envios' => $envios,
            'estados' => EnvioMensual::ESTADOS,
        ]);
    }

    public function create(): View
    {
        return view('procesamiento-mensual.envios-mensuales.create', [
            'meses' => LoteMensual::MESES,
        ]);
    }

    public function store(StoreEnvioMensualRequest $request): RedirectResponse
    {
        $envio = DB::transaction(
            fn (): EnvioMensual => EnvioMensual::create([
                'mes' => $request->integer('mes'),
                'gestion' => $request->integer('gestion'),
                'destinatario' => 'MINDEF',
                'estado' => EnvioMensual::ESTADO_BORRADOR,
                'observaciones' => $request->validated('observaciones'),
                'creado_por' => $request->user()->id,
            ])
        );

        return redirect()
            ->route('procesamiento-mensual.envios-mensuales.show', $envio)
            ->with('success', 'Lote de envío mensual creado correctamente.');
    }

    public function show(
        EnvioMensual $envioMensual,
        EnvioPrestamosTxtService $generadorPrestamos
    ): View
    {
        $envioMensual->load(['loteMensual', 'creador', 'archivoPrestamos.generador']);

        return view('procesamiento-mensual.envios-mensuales.show', [
            'envio' => $envioMensual,
            'resumenPrestamos' => $generadorPrestamos->resumen($envioMensual),
        ]);
    }

    public function generarPrestamos(
        Request $request,
        EnvioMensual $envioMensual,
        EnvioPrestamosTxtService $generador
    ): RedirectResponse {
        if (! in_array($envioMensual->estado, [
            EnvioMensual::ESTADO_BORRADOR,
            EnvioMensual::ESTADO_PREPARANDO,
            EnvioMensual::ESTADO_VALIDADO,
        ], true)) {
            return back()->with('error', 'El lote ya no admite la generación de archivos de envío.');
        }

        $datos = $generador->generar($envioMensual);
        $ruta = 'procesamiento-mensual/envios/'.$envioMensual->id
            .'/prestamos/'.Str::uuid().'.txt';

        if (! Storage::disk('local')->put($ruta, $datos['contenido'])) {
            return back()->with('error', 'No fue posible guardar el archivo TXT generado.');
        }

        $rutaAnterior = null;

        try {
            DB::transaction(function () use (
                $request,
                $envioMensual,
                $datos,
                $ruta,
                &$rutaAnterior
            ): void {
                $envio = EnvioMensual::query()
                    ->lockForUpdate()
                    ->findOrFail($envioMensual->id);

                if (! in_array($envio->estado, [
                    EnvioMensual::ESTADO_BORRADOR,
                    EnvioMensual::ESTADO_PREPARANDO,
                    EnvioMensual::ESTADO_VALIDADO,
                ], true)) {
                    throw new \RuntimeException(
                        'El lote cambió de estado y ya no admite archivos.'
                    );
                }

                $archivoAnterior = EnvioMensualArchivo::query()
                    ->where('envio_mensual_id', $envio->id)
                    ->where('tipo', EnvioMensualArchivo::TIPO_PRESTAMOS)
                    ->first();
                $rutaAnterior = $archivoAnterior?->ruta;

                EnvioMensualArchivo::query()->updateOrCreate(
                    [
                        'envio_mensual_id' => $envio->id,
                        'tipo' => EnvioMensualArchivo::TIPO_PRESTAMOS,
                    ],
                    [
                        'nombre_original' => $datos['nombre'],
                        'ruta' => $ruta,
                        'mime_type' => 'text/plain',
                        'hash_sha256' => $datos['hash_sha256'],
                        'cantidad_registros' => $datos['cantidad'],
                        'monto_total' => $datos['monto_total'],
                        'generado_por' => $request->user()?->id,
                        'generado_en' => now(),
                    ]
                );

                if ($envio->estado === EnvioMensual::ESTADO_BORRADOR) {
                    $envio->update(['estado' => EnvioMensual::ESTADO_PREPARANDO]);
                }
            });
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($ruta);
            throw $exception;
        }

        if ($rutaAnterior && $rutaAnterior !== $ruta) {
            Storage::disk('local')->delete($rutaAnterior);
        }

        return back()->with(
            'success',
            "Archivo de préstamos generado: {$datos['cantidad']} registros por Bs {$datos['monto_total']}."
        );
    }

    public function descargarPrestamos(
        EnvioMensual $envioMensual
    ): StreamedResponse|RedirectResponse {
        $archivo = $envioMensual->archivoPrestamos()->first();

        if (! $archivo || ! Storage::disk('local')->exists($archivo->ruta)) {
            return back()->with('error', 'El archivo de préstamos no existe o ya no está disponible.');
        }

        return Storage::disk('local')->download(
            $archivo->ruta,
            $archivo->nombre_original,
            ['Content-Type' => 'text/plain; charset=us-ascii']
        );
    }

    public function marcarEnviado(EnvioMensual $envioMensual): RedirectResponse
    {
        if (! $envioMensual->archivoPrestamos()->exists()) {
            return back()->with(
                'error',
                'Primero debe generar el archivo plano de préstamos.'
            );
        }

        DB::transaction(function () use ($envioMensual): void {
            $envio = EnvioMensual::query()
                ->lockForUpdate()
                ->findOrFail($envioMensual->id);

            if (! in_array($envio->estado, [
                EnvioMensual::ESTADO_BORRADOR,
                EnvioMensual::ESTADO_PREPARANDO,
                EnvioMensual::ESTADO_VALIDADO,
            ], true)) {
                return;
            }

            $envio->update([
                'estado' => EnvioMensual::ESTADO_ENVIADO,
                'fecha_envio' => today(),
            ]);
        });

        return redirect()
            ->route('procesamiento-mensual.envios-mensuales.show', $envioMensual)
            ->with('success', 'El lote fue marcado como enviado y ya puede registrarse su recepción.');
    }
}
