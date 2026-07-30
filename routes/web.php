<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;

use App\Http\Controllers\SocioController;
use App\Http\Controllers\SocioInformacionController;
use App\Http\Controllers\SocioReporteController;
use App\Http\Controllers\PrestamoController;
use App\Http\Controllers\TipoPrestamoController;
use App\Services\ExchangeRateService;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\AmortizacionCapitalController;
use App\Http\Controllers\RefinanciamientoController;
use App\Http\Controllers\ReprogramacionPrestamoController;
use App\Http\Controllers\PrestamoReporteController;
use App\Http\Controllers\ProyeccionPrestamoController;
use App\Http\Controllers\LoteMensualController;
use App\Http\Controllers\LoteArchivoController;
use App\Http\Controllers\PrestamoArchivoController;
use App\Http\Controllers\PrestamoConciliacionController;
use App\Http\Controllers\GaranteArchivoController;
use App\Http\Controllers\FvsArchivoController;
use App\Http\Controllers\CertificadoAporteArchivoController;


Route::get('/', function () {
    if (Auth::check()) { return redirect('/dashboard');
    }
    return redirect('/login');
});
/*
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');
*/
Route::get('/dashboard', function (ExchangeRateService $exchangeRateService) {

    return view('dashboard', [
    'exchangeRate'   => $exchangeRateService->getLatest(),
    'monthlyAverage' => $exchangeRateService->getMonthlyAverages(),
    'history'        => $exchangeRateService->getHistory(30),

]);

})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    /////////////// recien agregados
   // Route::resource('usuarios', UsuarioController::class);
    Route::resource('usuarios',UsuarioController::class)->middleware('permission:usuarios.ver');
    Route::put('/usuarios/{usuario}', [UsuarioController::class, 'update'])->name('usuarios.update');
    Route::patch('/usuarios/{usuario}/estado',[UsuarioController::class, 'cambiarEstado'])->name('usuarios.estado');
    Route::patch('/usuarios/{usuario}/password',[UsuarioController::class, 'resetPassword'])->name('usuarios.password');
    //Route::resource('roles',RoleController::class);
    Route::resource('roles',RoleController::class)->middleware('permission:roles.ver');
    Route::get('/roles/{role}/usuarios',[RoleController::class, 'usuarios'])->name('roles.usuarios');
    Route::post('/roles/{role}/usuarios',[RoleController::class, 'guardarUsuarios'])->name('roles.guardarUsuarios');
    Route::get('/roles/{role}/permisos',[RoleController::class, 'permisos'])->name('roles.permisos');
    Route::post('/roles/{role}/permisos',[RoleController::class, 'guardarPermisos'])->name('roles.guardarPermisos');
    Route::resource('permisos',PermissionController::class)->middleware('permission:permisos.ver');
    /*
    |--------------------------------------------------------------------------
    | SOCIOS
    |--------------------------------------------------------------------------
    */
    Route::get('/socios/buscar', [SocioController::class, 'buscar'])->name('socios.buscar');
    Route::get('/socios/validar-ci', [SocioController::class, 'validarCi'])->name('socios.validar-ci')->middleware('permission:socios.ver');
    Route::get('/socios/validar-papeleta', [SocioController::class, 'validarPapeleta'])->name('socios.validar-papeleta')->middleware('permission:socios.ver');
    Route::resource('socios', SocioController::class)->middleware('permission:socios.ver');
    Route::get('/socios-informacion',[SocioInformacionController::class, 'index'])->name('socios.informacion')->middleware('permission:socios.informacion');
    Route::get('/socios-reportes',[SocioReporteController::class, 'index'])->name('socios.reportes')->middleware('permission:socios.reportes');
    Route::patch('/socios/{socio}/estado',[SocioController::class, 'cambiarEstado'])->name('socios.estado');
    Route::get('/socios/{socio}/kardex',[SocioController::class, 'kardex'])->name('socios.kardex');
    Route::get('/socios/{socio}/revincular',[SocioController::class,'revincular'])->name('socios.revincular');

    /*
    |--------------------------------------------------------------------------
    | PRESTAMOS
    |--------------------------------------------------------------------------
    */
   /*
|--------------------------------------------------------------------------
| PRESTAMOS
|--------------------------------------------------------------------------
*/
Route::prefix('prestamos')->name('prestamos.')->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Catálogos
        |--------------------------------------------------------------------------
        */
        Route::get('/tipos', [TipoPrestamoController::class, 'index'])->name('tipos.index');
        Route::get('/tipos/create', [TipoPrestamoController::class, 'create'])->name('tipos.create');
        Route::post('/tipos', [TipoPrestamoController::class, 'store'])->name('tipos.store');
        Route::get('/tipos/{tasa}/edit', [TipoPrestamoController::class, 'edit'])->name('tipos.edit');
        Route::put('/tipos/{tasa}', [TipoPrestamoController::class, 'update'])->name('tipos.update');
        Route::patch('/tipos/{tasa}/estado', [TipoPrestamoController::class, 'estado'])->name('tipos.estado');

          /*
        |--------------------------------------------------------------------------
        | Reportes
        |--------------------------------------------------------------------------
        */
        Route::prefix('reportes')->name('reportes.')->group(function () {
                Route::get('/', [PrestamoReporteController::class, 'index'])->name('index');
                Route::get('/tipos-prestamo', [PrestamoReporteController::class, 'tiposPrestamo'])->name('tipos-prestamo');
            });

        /*
        |--------------------------------------------------------------------------
        | Consultas y procesos
        |--------------------------------------------------------------------------
        */

        Route::post('/validar-solicitud', [PrestamoController::class, 'validarSolicitud'])
            ->name('validarSolicitud');

        Route::post('/simular', [PrestamoController::class, 'simular'])
            ->name('simular');

        Route::get('/proyeccion', [ProyeccionPrestamoController::class, 'index'])
            ->name('proyeccion');
        Route::post('/proyeccion/calcular', [ProyeccionPrestamoController::class, 'calcular'])
            ->name('proyeccion.calcular');
        Route::post('/proyeccion/reporte', [ProyeccionPrestamoController::class, 'reporte'])
            ->name('proyeccion.reporte');

        Route::get('/depositos', [PrestamoController::class, 'depositos'])
            ->name('depositos');

         Route::get('tipo-cambio/{fecha}',[PrestamoController::class, 'buscarTipoCambio'])->name('tipo-cambio');

        /*
        |--------------------------------------------------------------------------
        | CRUD de Préstamos
        |--------------------------------------------------------------------------
        */
        Route::get('/', [PrestamoController::class, 'index'])->name('index');
        Route::get('/create', [PrestamoController::class, 'create'])->name('create');
        Route::post('/', [PrestamoController::class, 'store'])->name('store');

        /*
        |--------------------------------------------------------------------------
        | Rutas con parámetros (SIEMPRE AL FINAL)
        |--------------------------------------------------------------------------
        */

        Route::get('/{prestamo}/edit', [PrestamoController::class, 'edit'])->name('edit');

        Route::put('/{prestamo}', [PrestamoController::class, 'update'])->name('update');
        Route::get('/{prestamo}/reporte', [PrestamoController::class, 'reporte'])->name('reporte');

        Route::get('/{prestamo}/detalle', [PrestamoController::class, 'detalle'])->name('detalle');
        Route::get('/{prestamo}/detalle/pdf', [PrestamoController::class, 'detallePdf'])->name('detalle.pdf');
        Route::patch('/{prestamo}/bloquear-edicion',[PrestamoController::class, 'bloquearEdicion'])->name('bloquear-edicion');
        Route::patch('/{prestamo}/habilitar-edicion',[PrestamoController::class, 'habilitarEdicion'])->name('habilitar-edicion');
        Route::get('/{prestamo}/garantes', [PrestamoController::class, 'garantes'])->name('garantes');
        Route::patch('/{prestamo}/garantes',[PrestamoController::class, 'actualizarGarantes'])->name('garantes.update');
        Route::get('garantes/{historial}/pdf', [PrestamoController::class, 'pdfCambioGarantes'])->name('garantes.pdf');

        Route::get('/{prestamo}', [PrestamoController::class, 'show'])->name('show');

        Route::get('{prestamo}/pagos', [PagoController::class, 'index'])->name('pagos');
        Route::post('{prestamo}/pagos', [PagoController::class, 'store'])->name('pagos.store');
        Route::post('{prestamo}/pagos/total', [PagoController::class, 'storeTotal'])->name('pagos.total.store');
        Route::get('{prestamo}/pagos/reporte', [PagoController::class, 'reporte'])->name('pagos.reporte');
        Route::get('{prestamo}/pagos/reporte/pdf', [PagoController::class, 'reportePdf'])->name('pagos.reporte.pdf');
        Route::get('{prestamo}/amortizacion-capital', [AmortizacionCapitalController::class, 'create'])->name('amortizacion-capital');
        Route::post('{prestamo}/amortizacion-capital', [AmortizacionCapitalController::class, 'store'])->name('amortizacion-capital.store');
        Route::get('{prestamo}/refinanciamiento', [RefinanciamientoController::class, 'create'])->name('refinanciamiento');
        Route::post('{prestamo}/refinanciamiento', [RefinanciamientoController::class, 'store'])->name('refinanciamiento.store');
        Route::get('{prestamo}/reprogramacion', [ReprogramacionPrestamoController::class, 'create'])->name('reprogramacion');
        Route::post('{prestamo}/reprogramacion', [ReprogramacionPrestamoController::class, 'store'])->name('reprogramacion.store');

    });

    /*
    |--------------------------------------------------------------------------
    | PROCESAMIENTO MENSUAL
    |--------------------------------------------------------------------------
    */
    Route::prefix('procesamiento-mensual')
        ->name('procesamiento-mensual.')
        ->group(function () {
            Route::get(
                'lotes/{lote}/archivos',
                [LoteArchivoController::class, 'index']
            )->name('lotes.archivos.index');

            Route::post(
                'lotes/{lote}/archivos/prestamos',
                [PrestamoArchivoController::class, 'store']
            )->name('lotes.archivos.prestamos.store');

            Route::delete(
                'lotes/{lote}/archivos/prestamos',
                [PrestamoArchivoController::class, 'limpiar']
            )->name('lotes.archivos.prestamos.limpiar');

            Route::get(
                'lotes/{lote}/archivos/prestamos/conciliacion',
                [PrestamoConciliacionController::class, 'index']
            )->name('lotes.archivos.prestamos.conciliacion.index');

            Route::post(
                'lotes/{lote}/archivos/prestamos/conciliacion',
                [PrestamoConciliacionController::class, 'comparar']
            )->name('lotes.archivos.prestamos.conciliacion.comparar');

            Route::post(
                'lotes/{lote}/archivos/prestamos/conciliacion/pagar',
                [PrestamoConciliacionController::class, 'pagar']
            )->name('lotes.archivos.prestamos.conciliacion.pagar');

            Route::get(
                'lotes/{lote}/archivos/prestamos/conciliacion/resumen-pago',
                [PrestamoConciliacionController::class, 'resumen']
            )->name(
                'lotes.archivos.prestamos.conciliacion.resumen'
            );

            Route::post(
                'lotes/{lote}/archivos/prestamos/conciliacion/garantes',
                [GaranteArchivoController::class, 'store']
            )->name('lotes.archivos.prestamos.conciliacion.garantes.store');

            Route::delete(
                'lotes/{lote}/archivos/prestamos/conciliacion/garantes',
                [GaranteArchivoController::class, 'limpiar']
            )->name('lotes.archivos.prestamos.conciliacion.garantes.limpiar');

            Route::get(
                'lotes/{lote}/fvs',
                [FvsArchivoController::class, 'index']
            )->name('lotes.fvs.index');

            Route::post(
                'lotes/{lote}/fvs',
                [FvsArchivoController::class, 'store']
            )->name('lotes.fvs.store');

            Route::delete(
                'lotes/{lote}/fvs',
                [FvsArchivoController::class, 'limpiar']
            )->name('lotes.fvs.limpiar');

            Route::get(
                'lotes/{lote}/certificados',
                [CertificadoAporteArchivoController::class, 'index']
            )->name('lotes.certificados.index');

            Route::post(
                'lotes/{lote}/certificados',
                [CertificadoAporteArchivoController::class, 'store']
            )->name('lotes.certificados.store');

            Route::delete(
                'lotes/{lote}/certificados',
                [CertificadoAporteArchivoController::class, 'limpiar']
            )->name('lotes.certificados.limpiar');

            Route::resource('lotes', LoteMensualController::class)
                ->parameters(['lotes' => 'lote'])
                ->except(['destroy']);
        });
});

require __DIR__.'/auth.php';
