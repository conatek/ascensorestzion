<?php

use App\Http\Controllers\Admin\CompanyAdminController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserAdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CardController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CompanySettingController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\PortalController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicCardController;
use App\Http\Controllers\ReportConfirmationController;
use App\Http\Controllers\ScheduledVisitController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ServiceReportController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\StatsController;
use App\Http\Controllers\TechnicianCheckinController;
use App\Http\Controllers\TechnicianScheduleController;
use App\Http\Controllers\VisitSignatureController;
use Illuminate\Support\Facades\Route;

// Autenticación pública
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');

// Recuperación de contraseña (pública)
Route::post('/forgot-password', [PasswordResetController::class, 'forgot'])->middleware('throttle:5,1');
Route::post('/reset-password', [PasswordResetController::class, 'reset'])->middleware('throttle:5,1');

// Formulario de contacto (público)
Route::post('/contact', [ContactController::class, 'send'])->middleware('throttle:5,1');

// Vistas públicas de tarjetas (sin auth)
Route::prefix('public')->group(function () {
    Route::get('/card/{cardSlug}', [PublicCardController::class, 'cardBySlug']);
    Route::get('/{companySlug}', [PublicCardController::class, 'company']);
    Route::get('/{companySlug}/{cardSlug}', [PublicCardController::class, 'card']);
});

// Plantillas públicas (para consultar schemas)
Route::get('/templates', [CompanySettingController::class, 'templates']);
Route::get('/templates/{templateName}/schema', [CompanySettingController::class, 'schema']);

// Confirmacion de recepcion de reportes (publico, sin auth)
Route::get('/report-confirmation/{token}', [ReportConfirmationController::class, 'show']);
Route::post('/report-confirmation/{token}', [ReportConfirmationController::class, 'confirm']);
// Descarga del PDF con el mismo token (genera con Browsershot: throttle bajo)
Route::get('/report-confirmation/{token}/pdf', [ReportConfirmationController::class, 'pdf'])
    ->middleware('throttle:10,1');

// Rutas protegidas con Sanctum
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/logout-all', [AuthController::class, 'logoutAll']);
    Route::post('/impersonate/{client}', [AuthController::class, 'impersonate']);
    Route::post('/stop-impersonating', [AuthController::class, 'stopImpersonating']);

    // Perfil propio (todos los roles)
    Route::put('/profile', [ProfileController::class, 'update']);
    Route::put('/profile/password', [ProfileController::class, 'updatePassword']);
    Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar']);
    Route::delete('/profile/avatar', [ProfileController::class, 'deleteAvatar']);

    // Panel Admin general (master y super)
    Route::middleware('role:master|super')->prefix('admin')->group(function () {
        Route::get('dashboard', [DashboardController::class, 'index']);
        Route::get('companies', [CompanyAdminController::class, 'index']);
    });

    // Gestión de usuarios (solo master)
    Route::middleware('role:master')->prefix('admin')->group(function () {
        Route::get('users', [UserAdminController::class, 'index']);
        Route::post('users', [UserAdminController::class, 'store']);
        Route::get('users/{user}', [UserAdminController::class, 'show']);
        Route::put('users/{user}', [UserAdminController::class, 'update']);
        Route::delete('users/{user}', [UserAdminController::class, 'destroy']);
    });

    // Clientes
    Route::apiResource('clients', ClientController::class);

    // Sedes (anidadas bajo cliente)
    Route::apiResource('clients.sites', SiteController::class);

    // Equipos (flat con filtros)
    Route::get('equipment/qr-codes-pdf', [EquipmentController::class, 'qrCodesPdf'])
        ->middleware('role:master|super|coordinator');
    Route::apiResource('equipment', EquipmentController::class);
    Route::get('equipment/{equipment}/history', [EquipmentController::class, 'history']);
    Route::post('equipment/{equipment}/attachments', [EquipmentController::class, 'uploadAttachment']);
    Route::delete('equipment/{equipment}/attachments/{attachment}', [EquipmentController::class, 'deleteAttachment']);
    Route::post('equipment/{equipment}/generate-qr', [EquipmentController::class, 'generateQr'])
        ->middleware('role:master|coordinator');

    // Check-in de técnicos
    Route::prefix('tech')->group(function () {
        Route::post('checkin', [TechnicianCheckinController::class, 'store']);
        Route::get('checkins', [TechnicianCheckinController::class, 'index']);
        Route::get('checkins/{checkin}', [TechnicianCheckinController::class, 'show']);

        // Firma diferida en lote
        Route::get('visits/pending-signature', [VisitSignatureController::class, 'pending']);
        Route::post('visits/sign-customer', [VisitSignatureController::class, 'signBatch'])->middleware('throttle:10,1');
    });

    // Cronograma de visitas (coordinación)
    Route::middleware('role:master|super|coordinator')->prefix('schedule')->group(function () {
        Route::get('visits', [ScheduledVisitController::class, 'index']);
        Route::post('visits', [ScheduledVisitController::class, 'store']);
        Route::get('visits/{scheduledVisit}', [ScheduledVisitController::class, 'show']);
        Route::put('visits/{scheduledVisit}', [ScheduledVisitController::class, 'update']);
        Route::post('visits/{scheduledVisit}/cancel', [ScheduledVisitController::class, 'cancel']);

        Route::get('technicians', [ScheduledVisitController::class, 'technicians']);
        Route::get('technicians/{user}/schedule', [TechnicianScheduleController::class, 'show']);
        Route::put('technicians/{user}/schedule', [TechnicianScheduleController::class, 'update']);
        Route::delete('technicians/{user}/schedule', [TechnicianScheduleController::class, 'destroy']);

        Route::get('equipment/{equipment}/duration', [ScheduledVisitController::class, 'equipmentDuration']);
    });

    // Notificaciones
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::get('/unread-count', [NotificationController::class, 'unreadCount']);
        Route::post('/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::post('/read-all', [NotificationController::class, 'markAllAsRead']);
    });

    // Reportes de servicio
    Route::apiResource('service-reports', ServiceReportController::class);
    Route::post('service-reports/{service_report}/sign-technician', [ServiceReportController::class, 'signTechnician'])->middleware('throttle:10,1');
    Route::post('service-reports/{service_report}/sign-customer', [ServiceReportController::class, 'signCustomer'])->middleware('throttle:10,1');
    Route::get('service-reports/{service_report}/pdf', [ServiceReportController::class, 'pdf']);
    Route::post('service-reports/{service_report}/send-email', [ServiceReportController::class, 'sendEmail'])->middleware('throttle:5,1');
    Route::post('service-reports/{service_report}/attachments', [ServiceReportController::class, 'uploadAttachment']);
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
