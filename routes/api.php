<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\ServiceReportController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CompanySettingController;
use App\Http\Controllers\CardController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PublicCardController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserAdminController;
use App\Http\Controllers\Admin\CompanyAdminController;
use App\Http\Controllers\PortalController;
use App\Http\Controllers\StatsController;

// Autenticación pública
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');

// Vistas públicas de tarjetas (sin auth)
Route::prefix('public')->group(function () {
    Route::get('/card/{cardSlug}',           [PublicCardController::class, 'cardBySlug']);
    Route::get('/{companySlug}',             [PublicCardController::class, 'company']);
    Route::get('/{companySlug}/{cardSlug}',  [PublicCardController::class, 'card']);
});

// Plantillas públicas (para consultar schemas)
Route::get('/templates', [CompanySettingController::class, 'templates']);
Route::get('/templates/{templateName}/schema', [CompanySettingController::class, 'schema']);

// Rutas protegidas con Sanctum
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/logout-all', [AuthController::class, 'logoutAll']);
    Route::post('/impersonate/{client}', [AuthController::class, 'impersonate']);
    Route::post('/stop-impersonating', [AuthController::class, 'stopImpersonating']);

    // Panel Admin (solo master)
    Route::middleware('role:master')->prefix('admin')->group(function () {
        Route::get('dashboard', [DashboardController::class, 'index']);
        Route::get('users', [UserAdminController::class, 'index']);
        Route::post('users', [UserAdminController::class, 'store']);
        Route::get('users/{user}', [UserAdminController::class, 'show']);
        Route::put('users/{user}', [UserAdminController::class, 'update']);
        Route::get('companies', [CompanyAdminController::class, 'index']);
    });

    // Clientes
    Route::apiResource('clients', ClientController::class);

    // Sedes (anidadas bajo cliente)
    Route::apiResource('clients.sites', SiteController::class);

    // Equipos (flat con filtros)
    Route::apiResource('equipment', EquipmentController::class);
    Route::get('equipment/{equipment}/history', [EquipmentController::class, 'history']);
    Route::post('equipment/{equipment}/attachments', [EquipmentController::class, 'uploadAttachment']);
    Route::delete('equipment/{equipment}/attachments/{attachment}', [EquipmentController::class, 'deleteAttachment']);

    // Reportes de servicio
    Route::apiResource('service-reports', ServiceReportController::class);
    Route::post('service-reports/{service_report}/sign-technician', [ServiceReportController::class, 'signTechnician'])->middleware('throttle:10,1');
    Route::post('service-reports/{service_report}/sign-customer', [ServiceReportController::class, 'signCustomer'])->middleware('throttle:10,1');
    Route::get('service-reports/{service_report}/pdf', [ServiceReportController::class, 'pdf']);
    Route::post('service-reports/{service_report}/send-email', [ServiceReportController::class, 'sendEmail'])->middleware('throttle:5,1');
    Route::get('service-reports-export', [ServiceReportController::class, 'export']);

    // Estadísticas
    Route::prefix('stats')->group(function () {
        Route::get('overview', [StatsController::class, 'overview']);
        Route::get('reports-by-month', [StatsController::class, 'reportsByMonth']);
        Route::get('top-equipment-faults', [StatsController::class, 'topEquipmentFaults']);
        Route::get('fault-codes-frequency', [StatsController::class, 'faultCodesFrequency']);
        Route::get('technician-workload', [StatsController::class, 'technicianWorkload']);
        Route::get('maintenance-compliance', [StatsController::class, 'maintenanceCompliance']);
    });

    // Catálogos
    Route::get('catalogs', [CatalogController::class, 'index']);
    Route::middleware('role:master')->group(function () {
        Route::post('catalogs', [CatalogController::class, 'store']);
        Route::put('catalogs/{catalog}', [CatalogController::class, 'update']);
        Route::delete('catalogs/{catalog}', [CatalogController::class, 'destroy']);
    });

    // Portal Cliente
    Route::prefix('portal')->group(function () {
        Route::get('dashboard', [PortalController::class, 'dashboard']);
        Route::get('equipment', [PortalController::class, 'equipment']);
        Route::get('equipment/{equipment}', [PortalController::class, 'equipmentShow']);
        Route::get('reports', [PortalController::class, 'reports']);
        Route::get('reports/{service_report}', [PortalController::class, 'reportShow']);
    });

    // Tarjetas de presentación Ascensores Tzion (company.access)
    Route::middleware('company.access')->group(function () {
        Route::get('companies/{company}', [CompanyController::class, 'show']);
        Route::put('companies/{company}', [CompanyController::class, 'update']);

        Route::get('companies/{company}/settings', [CompanySettingController::class, 'show']);
        Route::put('companies/{company}/settings', [CompanySettingController::class, 'update']);
        Route::post('companies/{company}/settings/reset', [CompanySettingController::class, 'reset']);
        Route::post('companies/{company}/settings/upload-image', [CompanySettingController::class, 'uploadImage']);

        Route::apiResource('companies.cards', CardController::class);
        Route::apiResource('companies.services', ServiceController::class);
        Route::apiResource('companies.products', ProductController::class);
    });
});
