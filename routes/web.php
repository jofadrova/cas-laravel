<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\RoleController;


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
    Route::resource('usuarios', UsuarioController::class);
    Route::put('/usuarios/{usuario}', [UsuarioController::class, 'update'])->name('usuarios.update');
    Route::patch('/usuarios/{usuario}/estado',[UsuarioController::class, 'cambiarEstado'])->name('usuarios.estado');
    Route::patch('/usuarios/{usuario}/password',[UsuarioController::class, 'resetPassword'])->name('usuarios.password');
    Route::resource('roles',RoleController::class);
    Route::get('/roles/{role}/usuarios',[RoleController::class, 'usuarios'])->name('roles.usuarios');
    Route::post('/roles/{role}/usuarios',[RoleController::class, 'guardarUsuarios'])->name('roles.guardarUsuarios');
    
});

require __DIR__.'/auth.php';
