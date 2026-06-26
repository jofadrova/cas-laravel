<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Requests\StoreUsuarioRequest;
use App\Http\Requests\ResetPasswordRequest;
use Illuminate\Support\Facades\Hash;
use App\Traits\HasTable;
use App\Support\ScasTable;

class UsuarioController extends Controller
{
    
     use HasTable;

    public function index()
    {
        $table = ScasTable::make(User::query())

            ->search([
                'name',
                'username',
                'email',
            ])

            ->sortable([
                'name',
                'username',
                'email',
                'estado',
            ])

            ->defaultSort('name');

        $usuarios = $table->paginate();

        return view(
            'usuarios.index',
            compact('usuarios', 'table')
        );
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUsuarioRequest $request)
    {
        User::create([
            'username' => $request->username,
            'name' => $request->name,
            'email' => $request->email,
            'estado' => $request->estado,
            'password' => bcrypt($request->password),

        ]);

        return redirect()
            ->route('usuarios.index')
            ->with('success','Usuario creado correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $usuario)
    {
        $request->validate([
            'name' => 'required|max:255',
            'email' => 'required|email|unique:users,email,' . $usuario->id,
            'estado' => 'required'
        ]);

        $usuario->update([
            'name' => $request->name,
            'email' => $request->email,
            'estado' => $request->estado
        ]);

        return redirect()
            ->route('usuarios.index')
            ->with('success','Usuario actualizado correctamente.');
    }

    public function cambiarEstado(User $usuario)
    {
        $usuario->estado =
            $usuario->estado === 'ACTIVO'
                ? 'INACTIVO'
                : 'ACTIVO';

        $usuario->save();

        return back()->with(
            'success',
            'Estado actualizado correctamente.'
        );
    }
    public function resetPassword(ResetPasswordRequest $request,User $usuario
)
    {
        $request->validateWithBag('password', ['password' => ['required','min:8','confirmed']]);

        $usuario->password = Hash::make($request->password);
        $usuario->save();

        return redirect()
            ->route('usuarios.index')
            ->with('success','Contraseña actualizada correctamente.');
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
