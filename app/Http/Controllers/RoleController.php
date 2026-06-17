<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Models\User;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::withCount('users')
            ->orderBy('name')
            ->get();

        return view(
            'roles.index',
            compact('roles')
        );
    }

    public function store(StoreRoleRequest $request)
    {
        Role::create([
            'name' => $request->name,
            'guard_name' => 'web'
        ]);

        return redirect()
            ->route('roles.index')
            ->with(
                'success',
                'Rol registrado correctamente.'
            );
    }

    public function update(UpdateRoleRequest $request, Role $role)
    {
        $role->update(['name' => $request->name]);

        return redirect()
            ->route('roles.index')
            ->with('success','Rol actualizado correctamente.');
    }

    public function usuarios(Role $role)
    {
        $usuarios = User::orderBy('username')->get();

        $asignados = $role->users->pluck('id')->toArray();

        return response()->json([
            'role' => $role,
            'usuarios' => $usuarios,
            'asignados' => $asignados
        ]);
    }

    public function guardarUsuarios(Request $request,Role $role)
    {
        $usuarios = User::all();

        foreach ($usuarios as $usuario) {
            $usuario->removeRole($role);
        }

        if ($request->filled('usuarios')) {

            User::whereIn('id',$request->usuarios)->get()->each(function ($user) use ($role) {
                $user->assignRole($role);
            });

        }

        return redirect()
            ->route('roles.index')
            ->with('success','Usuarios asignados correctamente.');
    }

    public function permisos(Role $role)
    {
        $permisos = Permission::orderBy('name')->get();

        $asignados = $role->permissions->pluck('id')->toArray();

        return response()->json([
            'role' => $role,
            'permisos' => $permisos,
            'asignados' => $asignados
        ]);
    }

    public function guardarPermisos(Request $request,Role $role)
    {
        $role->syncPermissions($request->permisos ?? []);
        return redirect()
            ->route('roles.index')
            ->with('success','Permisos asignados correctamente.');
    }
}
