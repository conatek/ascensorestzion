<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CompanySettingController;
use App\Http\Controllers\CardController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\PublicCardController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PaymentAdminController;
use App\Http\Controllers\Admin\PlanAdminController;
use App\Http\Controllers\Admin\SubscriptionAdminController;
use App\Http\Controllers\Admin\UserAdminController;
use App\Http\Controllers\Admin\CompanyAdminController;

// Rutas públicas de autenticación
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// Rutas públicas de tarjetas (sin auth)
Route::prefix('public')->group(function () {
    Route::get('/{companySlug}',             [PublicCardController::class, 'company']);
    Route::get('/{companySlug}/{cardSlug}',  [PublicCardController::class, 'card']);
});

// Rutas públicas de plantillas (sin auth - para consultar schemas)
Route::get('/templates', [CompanySettingController::class, 'templates']);
Route::get('/templates/{templateName}/schema', [CompanySettingController::class, 'schema']);

// PayU webhook (público, sin auth - PayU envía confirmaciones aquí)
Route::post('/payu/webhook', [PaymentController::class, 'webhook']);

// Resultado de pago (público con referenceCode)
Route::get('/payments/result', [PaymentController::class, 'result']);

// Planes disponibles (público)
Route::get('/plans', [PlanController::class, 'index']);

// Rutas protegidas con Sanctum
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/logout-all', [AuthController::class, 'logoutAll']);

    // Panel Admin (solo Master)
    Route::middleware('role:Master')->prefix('admin')->group(function () {
        Route::get('dashboard', [DashboardController::class, 'index']);
        Route::get('plans', [PlanAdminController::class, 'index']);
        Route::post('plans', [PlanAdminController::class, 'store']);
        Route::put('plans/{plan}', [PlanAdminController::class, 'update']);
        Route::patch('plans/{plan}/toggle', [PlanAdminController::class, 'toggle']);

        // Suscripciones
        Route::get('subscriptions', [SubscriptionAdminController::class, 'index']);
        Route::get('subscriptions/{subscription}', [SubscriptionAdminController::class, 'show']);
        Route::put('subscriptions/{subscription}', [SubscriptionAdminController::class, 'update']);
        Route::post('subscriptions/{subscription}/extend', [SubscriptionAdminController::class, 'extend']);
        Route::post('subscriptions/{subscription}/cancel', [SubscriptionAdminController::class, 'cancel']);

        // Pagos
        Route::get('payments', [PaymentAdminController::class, 'index']);
        Route::get('payments/{payment}', [PaymentAdminController::class, 'show']);
        Route::post('payments/{payment}/approve', [PaymentAdminController::class, 'approve']);
        Route::post('payments/{payment}/refund', [PaymentAdminController::class, 'refund']);

        // Usuarios
        Route::get('users', [UserAdminController::class, 'index']);
        Route::get('users/{user}', [UserAdminController::class, 'show']);
        Route::put('users/{user}', [UserAdminController::class, 'update']);

        // Empresas (vista admin con suscripción)
        Route::get('companies', [CompanyAdminController::class, 'index']);
    });

    // Pagos y suscripciones
    Route::post('payments/checkout', [PaymentController::class, 'checkout']);
    Route::get('payments/history', [PaymentController::class, 'history']);
    Route::get('subscription', [SubscriptionController::class, 'current']);

    // Empresas (sin contexto de empresa específica)
    Route::get('companies', [CompanyController::class, 'index']);
    Route::post('companies', [CompanyController::class, 'store']);

    // Rutas con contexto de empresa (verificación de acceso via middleware)
    Route::middleware('company.access')->group(function () {

        // Empresas (operaciones sobre empresa específica)
        Route::get('companies/{company}', [CompanyController::class, 'show']);
        Route::put('companies/{company}', [CompanyController::class, 'update']);
        Route::delete('companies/{company}', [CompanyController::class, 'destroy']);

        // Configuración de plantilla por empresa
        Route::get('companies/{company}/settings', [CompanySettingController::class, 'show']);
        Route::put('companies/{company}/settings', [CompanySettingController::class, 'update']);
        Route::post('companies/{company}/settings/reset', [CompanySettingController::class, 'reset']);
        Route::post('companies/{company}/settings/upload-image', [CompanySettingController::class, 'uploadImage']);

        // Tarjetas (anidadas bajo empresa)
        Route::apiResource('companies.cards', CardController::class)->except('store');
        Route::post('companies/{company}/cards', [CardController::class, 'store'])
            ->middleware('check.limits:cards')
            ->name('companies.cards.store');

        // Servicios (anidados bajo empresa)
        Route::apiResource('companies.services', ServiceController::class)->except('store');
        Route::post('companies/{company}/services', [ServiceController::class, 'store'])
            ->middleware('check.limits:services')
            ->name('companies.services.store');

        // Productos (anidados bajo empresa)
        Route::apiResource('companies.products', ProductController::class)->except('store');
        Route::post('companies/{company}/products', [ProductController::class, 'store'])
            ->middleware('check.limits:products')
            ->name('companies.products.store');
    });
});
