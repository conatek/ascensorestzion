<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Crear roles
        $master = Role::firstOrCreate(['name' => 'Master', 'guard_name' => 'web']);
        $admin  = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $guest  = Role::firstOrCreate(['name' => 'Guest', 'guard_name' => 'web']);

        // Master: todos los permisos
        $master->syncPermissions(Permission::all());

        // Admin: todos excepto view_any_company, delete_company, manage_users
        $admin->syncPermissions(
            Permission::whereNotIn('name', [
                'view_any_company',
                'delete_company',
                'manage_users',
            ])->get()
        );

        // Guest: lectura + edición de su empresa + creación limitada
        $guest->syncPermissions([
            'view_company',
            'edit_company',
            'view_cards',
            'create_card',
            'view_products',
            'create_product',
            'view_services',
            'create_service',
            'view_settings',
            'edit_settings',
        ]);
    }
}
