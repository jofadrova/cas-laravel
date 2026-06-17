<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;

use App\Http\Controllers\SocioController;
use App\Http\Controllers\SocioInformacionController;
use App\Http\Controllers\SocioReporteController;


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
    Route::resource('socios', SocioController::class)->middleware('permission:socios.ver');
    Route::get('/socios-informacion',[SocioInformacionController::class, 'index'])->name('socios.informacion')->middleware('permission:socios.informacion');
    Route::get('/socios-reportes',[SocioReporteController::class, 'index'])->name('socios.reportes')->middleware('permission:socios.reportes');
});

require __DIR__.'/auth.php';
