<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEnvioMensualRequest;
use App\Models\EnvioMensual;
use App\Models\EnvioMensualArchivo;
use App\Models\LoteMensual;
use App\Services\ProcesamientoMensual\EnvioGaranteExcelService;
use App\Services\ProcesamientoMensual\EnvioPrestamosTxtService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use InvalidArgumentException;
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
        $envioMensual->load([
            'loteMensual',
            'creador',
            'archivoPrestamos.generador',
            'archivoGarantes.generador',
        ]);

        return view('procesamiento-mensual.envios-mensuales.show', [
            'envio' => $envioMensual,
            'resumenPrestamos' => $generadorPrestamos->resumen($envioMensual),
        ]);
    }

    public function generarPrestamos(
        Request $request,
        EnvioMensual $envioMensual,
        EnvioPrestamosTxtService $generador,
        EnvioGaranteExcelService $lectorGarantes
    ): RedirectResponse {
        if (! in_array($envioMensual->estado, [
            EnvioMensual::ESTADO_BORRADOR,
            EnvioMensual::ESTADO_PREPARANDO,
            EnvioMensual::ESTADO_VALIDADO,
        ], true)) {
            return back()->with('error', 'El lote ya no admite la generación de archivos de envío.');
        }

        $request->validate([
            'archivo_garantes' => [
                'required',
                'file',
                'mimes:xlsx,xls',
                'max:10240',
            ],
        ], [
            'archivo_garantes.required' => 'El Excel de garantes es obligatorio para generar el TXT.',
            'archivo_garantes.file' => 'Debe seleccionar un archivo de garantes válido.',
            'archivo_garantes.mimes' => 'El archivo de garantes debe ser Excel (.xlsx o .xls).',
            'archivo_garantes.max' => 'El Excel de garantes no debe superar 10 MB.',
        ]);

        /** @var UploadedFile $archivoSubido */
        $archivoSubido = $request->file('archivo_garantes');

        try {
            $garantes = $lectorGarantes->leer(
                (string) $archivoSubido->getRealPath(),
                $envioMensual
            );
        } catch (InvalidArgumentException $exception) {
            throw ValidationException::withMessages([
                'archivo_garantes' => $exception->getMessage(),
            ]);
        }

        $datos = $generador->generar($envioMensual, $garantes);
        $rutaTxt = 'procesamiento-mensual/envios/'.$envioMensual->id
            .'/prestamos/'.Str::uuid().'.txt';
        $rutaGaranteNueva = $archivoSubido->storeAs(
            'procesamiento-mensual/envios/'.$envioMensual->id.'/garantes',
            Str::uuid().'.'.strtolower($archivoSubido->getClientOriginalExtension()),
            'local'
        );

        if (! $rutaGaranteNueva) {
            return back()->with('error', 'No fue posible guardar el Excel de garantes.');
        }

        if (! Storage::disk('local')->put($rutaTxt, $datos['contenido'])) {
            if ($rutaGaranteNueva) {
                Storage::disk('local')->delete($rutaGaranteNueva);
            }

            return back()->with('error', 'No fue posible guardar el archivo TXT generado.');
        }

        $rutasAnteriores = [];

        try {
            DB::transaction(function () use (
                $request,
                $envioMensual,
                $datos,
                $garantes,
                $archivoSubido,
                $rutaTxt,
                $rutaGaranteNueva,
                &$rutasAnteriores
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

                $rutasAnteriores = EnvioMensualArchivo::query()
                    ->where('envio_mensual_id', $envio->id)
                    ->whereIn('tipo', [
                        EnvioMensualArchivo::TIPO_PRESTAMOS,
                        EnvioMensualArchivo::TIPO_GARANTES_ORIGEN,
                    ])
                    ->lockForUpdate()
                    ->pluck('ruta')
                    ->filter()
                    ->values()
                    ->all();

                EnvioMensualArchivo::query()->updateOrCreate(
                    [
                        'envio_mensual_id' => $envio->id,
                        'tipo' => EnvioMensualArchivo::TIPO_GARANTES_ORIGEN,
                    ],
                    [
                        'nombre_original' => $archivoSubido->getClientOriginalName(),
                        'ruta' => $rutaGaranteNueva,
                        'mime_type' => $archivoSubido->getMimeType()
                            ?: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'hash_sha256' => $garantes['hash_sha256'],
                        'cantidad_registros' => $garantes['cantidad'],
                        'monto_total' => $garantes['monto_total'],
                        'generado_por' => $request->user()?->id,
                        'generado_en' => now(),
                    ]
                );

                EnvioMensualArchivo::query()->updateOrCreate(
                    [
                        'envio_mensual_id' => $envio->id,
                        'tipo' => EnvioMensualArchivo::TIPO_PRESTAMOS,
                    ],
                    [
                        'nombre_original' => $datos['nombre'],
                        'ruta' => $rutaTxt,
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
            Storage::disk('local')->delete($rutaTxt);

            if ($rutaGaranteNueva) {
                Storage::disk('local')->delete($rutaGaranteNueva);
            }

            throw $exception;
        }

        $rutasAConservar = [$rutaTxt, $rutaGaranteNueva];
        $rutasAEliminar = array_values(array_diff(
            $rutasAnteriores,
            $rutasAConservar
        ));

        if ($rutasAEliminar !== []) {
            Storage::disk('local')->delete($rutasAEliminar);
        }

        return back()->with(
            'success',
            "TXT generado: {$datos['cantidad_prestamos']} préstamos y "
            ."{$datos['cantidad_garantes']} garantes, por Bs {$datos['monto_total']}."
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

    public function descargarGarantes(
        EnvioMensual $envioMensual
    ): StreamedResponse|RedirectResponse {
        $archivo = $envioMensual->archivoGarantes()->first();

        if (! $archivo || ! Storage::disk('local')->exists($archivo->ruta)) {
            return back()->with('error', 'El Excel de garantes no existe o ya no está disponible.');
        }

        return Storage::disk('local')->download(
            $archivo->ruta,
            $archivo->nombre_original
        );
    }

    public function marcarEnviado(EnvioMensual $envioMensual): RedirectResponse
    {
        if (! $envioMensual->archivoPrestamos()->exists()
            || ! $envioMensual->archivoGarantes()->exists()) {
            return back()->with(
                'error',
                'Primero debe generar el TXT con préstamos y garantes.'
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
