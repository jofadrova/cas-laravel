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


Route::get('/', function () {
    if (Auth::check()) { return redirect('/dashboard');
    }
    return redirect('/login');
});

Route::get('/dashboard', function () {
    return view('dashboard');
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
    Route::prefix('prestamos')->name('prestamos.')->group(function() {
        Route::get('/', [PrestamoController::class, 'index'])->name('index');
        Route::get('/create', [PrestamoController::class, 'create'])->name('create');
        Route::post('/', [PrestamoController::class, 'store'])->name('store');
        Route::get('/{prestamo}/edit', [PrestamoController::class, 'edit'])->name('edit');
        Route::put('/{prestamo}', [PrestamoController::class, 'update'])->name('update');
        Route::post('/validar-solicitud', [PrestamoController::class, 'validarSolicitud'])->name('validarSolicitud');   
        //Route::get('/simular',[PrestamoController::class, 'simular'])->name('simular');    
        Route::post('/simular',[PrestamoController::class, 'simular'])->name('simular');     
        Route::get('/tipos', [TipoPrestamoController::class, 'index'])->name('tipos.index');
        Route::get('/tipos/create',[TipoPrestamoController::class, 'create'])->name('tipos.create');
        Route::post('/tipos', [TipoPrestamoController::class, 'store'])->name('tipos.store');
        Route::get('/tipos/{tasa}/edit', [TipoPrestamoController::class, 'edit'])->name('tipos.edit');
        Route::put('/tipos/{tasa}', [TipoPrestamoController::class, 'update'])->name('tipos.update');
        Route::patch('/tipos/{tasa}/estado', [TipoPrestamoController::class, 'estado'])->name('tipos.estado');
        Route::get('/proyeccion', [PrestamoController::class, 'proyeccion'])->name('proyeccion');
        Route::get('/depositos', [PrestamoController::class, 'depositos'])->name('depositos');
    });

    });

require __DIR__.'/auth.php';
