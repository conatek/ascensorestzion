<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // Empresas
            'view_any_company',
            'view_company',
            'create_company',
            'edit_company',
            'delete_company',

            // Tarjetas
            'view_cards',
            'create_card',
            'edit_card',
            'delete_card',

            // Productos
            'view_products',
            'create_product',
            'edit_product',
            'delete_product',

            // Servicios
            'view_services',
            'create_service',
            'edit_service',
            'delete_service',

            // Plantillas
            'view_settings',
            'edit_settings',

            // Usuarios
            'manage_users',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }
    }
}
