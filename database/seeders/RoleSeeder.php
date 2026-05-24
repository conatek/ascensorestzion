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

        // ── master: acceso total ──
        $master = Role::firstOrCreate(['name' => 'master', 'guard_name' => 'web']);
        $master->syncPermissions(Permission::all());

        // ── coordinator: seguimiento de reportes, gestión de clientes/sedes/equipos ──
        $coordinator = Role::firstOrCreate(['name' => 'coordinator', 'guard_name' => 'web']);
        $coordinator->syncPermissions([
            'view_clients', 'create_client', 'edit_client',
            'view_sites', 'create_site', 'edit_site',
            'view_equipment', 'create_equipment', 'edit_equipment',
            'view_company', 'view_cards', 'view_products', 'view_services',
            'view_settings',
            'view_reports', 'create_report', 'edit_any_report',
            'sign_report_technician', 'export_report_pdf',
            'checkin_equipment', 'view_notifications',
        ]);

        // ── technician: carga de reportes, ve equipos ──
        $technician = Role::firstOrCreate(['name' => 'technician', 'guard_name' => 'web']);
        $technician->syncPermissions([
            'view_clients', 'view_sites', 'view_equipment',
            'view_reports', 'create_report', 'edit_own_report',
            'sign_report_technician', 'export_report_pdf',
            'checkin_equipment', 'view_notifications',
        ]);

        // ── admin: usuario externo del cliente, ve sus datos ──
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->syncPermissions([
            'view_clients', 'view_sites', 'view_equipment',
            'view_reports', 'sign_report_customer', 'export_report_pdf',
            'confirm_report', 'view_notifications',
        ]);
    }
}
