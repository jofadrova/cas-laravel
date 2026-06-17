<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permisos = [

            // Dashboard
            'dashboard.ver',

            // Usuarios
            'usuarios.ver',
            'usuarios.crear',
            'usuarios.editar',
            'usuarios.estado',
            'usuarios.password',

            // Roles
            'roles.ver',
            'roles.crear',
            'roles.editar',
            'roles.usuarios',

            // Permisos
            'permisos.ver',
            'permisos.asignar',

            // Socios
            'socios.ver',
            'socios.crear',
            'socios.editar',
            'socios.eliminar',
            'socios.revincular',

            'socios.informacion',
            'socios.reportes',

        ];

        foreach ($permisos as $permiso) {

            Permission::firstOrCreate([
                'name' => $permiso,
                'guard_name' => 'web'
            ]);

        }
    }
}
