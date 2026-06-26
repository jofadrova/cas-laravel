<?php

namespace App\Http\Controllers;

use Spatie\Permission\Models\Permission;
use App\Http\Requests\StorePermissionRequest;
use App\Http\Requests\UpdatePermissionRequest;
use App\Traits\HasTable;
use App\Support\ScasTable;

class PermissionController extends Controller
{
   use HasTable;

    public function index()
    {
        $table = ScasTable::make(Permission::query())

            ->search([
                'name',
                'guard_name'
            ])

            ->sortable([
                'id',
                'name',
                'guard_name'
            ])

            ->defaultSort('id');

        $permisos = $table->paginate();

        return view(
            'permisos.index',
            compact('permisos','table')
        );
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
