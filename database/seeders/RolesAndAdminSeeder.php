<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class RolesAndAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear rol Administrador
        $adminRole = Role::firstOrCreate([
            'name' => 'Administrador'
        ]);

        // Crear usuario administrador
        $admin = User::firstOrCreate(
            [
                'username' => 'admin'
            ],
            [
                'name' => 'Administrador del Sistema',
                'email' => 'admin@cas.local',
                'password' => Hash::make('admin123'),
                'estado' => 'ACTIVO'
            ]
        );

        // Asignar rol
        $admin->assignRole($adminRole);
    }
}
