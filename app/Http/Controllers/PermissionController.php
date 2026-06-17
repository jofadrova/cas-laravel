<?php

namespace App\Http\Controllers;

use Spatie\Permission\Models\Permission;
use App\Http\Requests\StorePermissionRequest;
use App\Http\Requests\UpdatePermissionRequest;

class PermissionController extends Controller
{
    public function index()
    {
        $permisos = Permission::orderBy('name')
            ->get();

        return view('permisos.index',compact('permisos'));
    }

    public function store(StorePermissionRequest $request)
    {
        Permission::create([
            'name' => $request->name,
            'guard_name' => 'web'
        ]);

        return redirect()
            ->route('permisos.index')
            ->with('success','Permiso registrado correctamente.');
    }
    public function update(UpdatePermissionRequest $request,Permission $permiso)
    {
        $permiso->update([
            'name' => $request->name
        ]);

        return redirect()
            ->route('permisos.index')
            ->with('success','Permiso actualizado correctamente.');
    }
}
