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
            // Empresas (tarjetas Tzion)
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

            // Clientes
            'view_clients',
            'create_client',
            'edit_client',
            'delete_client',

            // Sedes
            'view_sites',
            'create_site',
            'edit_site',
            'delete_site',

            // Equipos
            'view_equipment',
            'create_equipment',
            'edit_equipment',
            'delete_equipment',

            // Usuarios
            'manage_users',

            // Catalogos
            'manage_catalogs',

            // Reportes de servicio
            'view_reports',
            'create_report',
            'edit_own_report',
            'edit_any_report',
            'sign_report_technician',
            'sign_report_customer',
            'export_report_pdf',

            // Check-in de técnicos
            'checkin_equipment',

            // Notificaciones
            'view_notifications',

            // Confirmación de reportes (cliente externo)
            'confirm_report',

            // Impersonation
            'impersonate_user',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }
    }
}
