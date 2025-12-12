<?php

use App\Http\Controllers\Webhook\AsaasWebhookController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Platform\DashboardController;
use App\Http\Controllers\Platform\TenantController;
use App\Http\Controllers\Platform\PlanController;
use App\Http\Controllers\Platform\SubscriptionController;
use App\Http\Controllers\Platform\InvoiceController;
use App\Http\Controllers\Platform\NotificationOutboxController;
use App\Http\Controllers\Platform\SystemNotificationController;
use App\Http\Controllers\Platform\MedicalSpecialtyCatalogController;
use App\Http\Controllers\Platform\UserController;
use App\Http\Controllers\Platform\PaisController;
use App\Http\Controllers\Platform\EstadoController;
use App\Http\Controllers\Platform\CidadeController;
use App\Http\Controllers\Platform\LocationController;
use App\Http\Controllers\Platform\SystemSettingsController;
use App\Http\Controllers\Platform\KioskMonitorController;
use App\Http\Controllers\Platform\PlanAccessManagerController;
use App\Http\Controllers\Platform\PreTenantController;
use App\Http\Controllers\Platform\NotificationTemplateController;
use App\Http\Controllers\Platform\ApiTenantTokenController;
use App\Models\Platform\SystemNotification;
use App\Http\Controllers\Platform\WhatsAppController;
use App\Http\Controllers\Tenant\Integrations\GoogleCalendarController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Rotas da Landing Page (públicas)
use App\Http\Controllers\Landing\LandingController;

Route::get('/', [LandingController::class, 'index'])->name('landing.home');
Route::get('/funcionalidades', [LandingController::class, 'features'])->name('landing.features');
Route::get('/planos', [LandingController::class, 'plans'])->name('landing.plans');
Route::get('/planos/json/{id}', [LandingController::class, 'getPlan'])->name('landing.plan.json');
Route::get('/contato', [LandingController::class, 'contact'])->name('landing.contact');
Route::get('/manual', [LandingController::class, 'manual'])->name('landing.manual');
Route::post('/pre-cadastro', [LandingController::class, 'storePreRegister'])->name('landing.pre-register')
    ->middleware('throttle:10,1'); // Rate limit: 10 requisições por minuto

Route::get('/kiosk/monitor', [KioskMonitorController::class, 'index'])->name('platform.kiosk.monitor');
Route::get('/kiosk/monitor/data', [KioskMonitorController::class, 'data'])->name('platform.kiosk.monitor.data');

//Rota do Webhook do asaas...
Route::post('/webhook/asaas', [AsaasWebhookController::class, 'handle'])->middleware('verify.asaas.token');

// Webhook exclusivo para pré-cadastro
Route::post('/webhook/asaas/pre-registration', [\App\Http\Controllers\Webhook\PreRegistrationWebhookController::class, 'handle'])
    ->middleware('verify.asaas.token');

// Callback global do Google Calendar (não fica no grupo /t/{tenant})
Route::get('/google/callback', [GoogleCalendarController::class, 'callback'])->name('google.callback');

// Rotas públicas para Google OAuth (Política de Privacidade e Termos de Serviço)
Route::view('/politica-de-privacidade', 'public.privacy')->name('public.privacy');
Route::view('/termos-de-servico', 'public.terms')->name('public.terms');


Route::middleware(['auth'])->prefix('Platform')->name('Platform.')->group(function () {

    // 🔹 Perfil do usuário autenticado (acesso sempre permitido)
    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // 🔹 Autenticação de dois fatores (2FA)
    Route::prefix('two-factor')->name('two-factor.')->group(function () {
        Route::get('/', [\App\Http\Controllers\TwoFactorController::class, 'index'])->name('index');
        Route::post('/generate-secret', [\App\Http\Controllers\TwoFactorController::class, 'generateSecret'])->name('generate-secret');
        Route::post('/confirm', [\App\Http\Controllers\TwoFactorController::class, 'confirm'])->name('confirm');
        Route::post('/set-method', [\App\Http\Controllers\TwoFactorController::class, 'setMethod'])->name('set-method');
        Route::post('/activate-with-code', [\App\Http\Controllers\TwoFactorController::class, 'activateWithCode'])->name('activate-with-code');
        Route::post('/confirm-with-code', [\App\Http\Controllers\TwoFactorController::class, 'confirmWithCode'])->name('confirm-with-code');
        Route::post('/disable', [\App\Http\Controllers\TwoFactorController::class, 'disable'])->name('disable');
        Route::post('/regenerate-recovery-codes', [\App\Http\Controllers\TwoFactorController::class, 'regenerateRecoveryCodes'])->name('regenerate-recovery-codes');
    });

    // 🔹 Dashboard (acesso sempre permitido)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // =======================================================
    // 🔸 Módulo: Tenants
    // =======================================================
    Route::middleware('module.access:tenants')->group(function () {
        Route::resource('tenants', TenantController::class);
        Route::post('/tenants/{tenant}/sync', [TenantController::class, 'syncWithAsaas'])->name('tenants.sync');
        
        // Tokens de API para bots
        Route::middleware('module.access:api_tokens')->group(function () {
            Route::get('/tenants/{tenant}/api-tokens', [ApiTenantTokenController::class, 'index'])->name('tenants.api-tokens.index');
            Route::get('/tenants/{tenant}/api-tokens/create', [ApiTenantTokenController::class, 'create'])->name('tenants.api-tokens.create');
            Route::post('/tenants/{tenant}/api-tokens', [ApiTenantTokenController::class, 'store'])->name('tenants.api-tokens.store');
            Route::get('/tenants/{tenant}/api-tokens/{token}', [ApiTenantTokenController::class, 'show'])->name('tenants.api-tokens.show');
            Route::get('/tenants/{tenant}/api-tokens/{token}/edit', [ApiTenantTokenController::class, 'edit'])->name('tenants.api-tokens.edit');
            Route::put('/tenants/{tenant}/api-tokens/{token}', [ApiTenantTokenController::class, 'update'])->name('tenants.api-tokens.update');
            Route::delete('/tenants/{tenant}/api-tokens/{token}', [ApiTenantTokenController::class, 'destroy'])->name('tenants.api-tokens.destroy');
        });
    });

    // 🔸 Módulo: Planos
    Route::middleware('module.access:plans')->group(function () {
        Route::resource('plans', PlanController::class);
        Route::resource('subscription-access', PlanAccessManagerController::class)->names([
            'index' => 'subscription-access.index',
            'create' => 'subscription-access.create',
            'store' => 'subscription-access.store',
            'show' => 'subscription-access.show',
            'edit' => 'subscription-access.edit',
            'update' => 'subscription-access.update',
            'destroy' => 'subscription-access.destroy',
        ]);
    });

    // 🔸 Módulo: Assinaturas
    Route::middleware('module.access:subscriptions')->group(function () {
        Route::resource('subscriptions', SubscriptionController::class);
        Route::post('subscriptions/{id}/renew', [SubscriptionController::class, 'renew'])
            ->where('id', '[0-9]+')
            ->name('subscriptions.renew');
        Route::get('tenants/{tenant}/subscriptions', [SubscriptionController::class, 'getByTenant'])->name('subscriptions.getByTenant');
        Route::post('/subscriptions/{subscription}/sync', [SubscriptionController::class, 'syncWithAsaas'])->name('subscriptions.sync');
    });

    // 🔸 Módulo: Faturas
    Route::middleware('module.access:invoices')->group(function () {
        Route::resource('invoices', InvoiceController::class);
        Route::post('/invoices/{invoice}/sync', [InvoiceController::class, 'syncManual'])->name('invoices.sync');
    });

    // 🔸 Módulo: Pré-Cadastros
    Route::middleware('module.access:pre_tenants')->group(function () {
        Route::resource('pre-tenants', PreTenantController::class)->names([
            'index' => 'pre_tenants.index',
            'show' => 'pre_tenants.show',
            'destroy' => 'pre_tenants.destroy',
        ])->only(['index', 'show', 'destroy']);
        Route::post('pre-tenants/{preTenant}/approve', [PreTenantController::class, 'approve'])->name('pre_tenants.approve');
        Route::post('pre-tenants/{preTenant}/cancel', [PreTenantController::class, 'cancel'])->name('pre_tenants.cancel');
        Route::post('pre-tenants/{preTenant}/confirm-payment', [PreTenantController::class, 'confirmPayment'])->name('pre_tenants.confirm_payment');
    });

    // 🔸 Módulo: Catálogo de Especialidades Médicas
    Route::middleware('module.access:medical_specialties_catalog')->group(function () {
        Route::resource('medical_specialties_catalog', MedicalSpecialtyCatalogController::class);
    });

    // 🔸 Módulo: Notificações Enviadas
    Route::middleware('module.access:notifications_outbox')->group(function () {
        Route::resource('notifications_outbox', NotificationOutboxController::class);
    });

    // 🔸 Módulo: Notificações do Sistema
    Route::middleware('module.access:system_notifications')->group(function () {
        Route::resource('system_notifications', SystemNotificationController::class)
            ->except(['create', 'edit', 'update', 'store', 'destroy'])
            ->whereUuid('system_notification');
    });

    // 🔸 Módulo: Templates de Notificação
    Route::middleware('module.access:notification_templates')->group(function () {
        Route::resource('notification-templates', NotificationTemplateController::class)
            ->except(['show', 'create', 'store']);
        
        Route::post('notification-templates/{notificationTemplate}/restore', 
            [NotificationTemplateController::class, 'restore'])
            ->name('notification-templates.restore');
        
        Route::post('notification-templates/{notificationTemplate}/test', 
            [NotificationTemplateController::class, 'testSend'])
            ->name('notification-templates.test');
        
        Route::post('notification-templates/{notificationTemplate}/toggle', 
            [NotificationTemplateController::class, 'toggle'])
            ->name('notification-templates.toggle');
        
        // Layouts de Email
        Route::get('email-layouts', [\App\Http\Controllers\Platform\EmailLayoutController::class, 'index'])
            ->name('email-layouts.index');
        Route::get('email-layouts/{emailLayout}/edit', [\App\Http\Controllers\Platform\EmailLayoutController::class, 'edit'])
            ->name('email-layouts.edit');
        Route::put('email-layouts/{emailLayout}', [\App\Http\Controllers\Platform\EmailLayoutController::class, 'update'])
            ->name('email-layouts.update');
        Route::get('email-layouts/{emailLayout}/preview', [\App\Http\Controllers\Platform\EmailLayoutController::class, 'preview'])
            ->name('email-layouts.preview');
    });

    // 🔸 Módulo: Localização (Países, Estados, Cidades)
    Route::middleware('module.access:locations')->group(function () {
        Route::resource('paises', PaisController::class)->except(['create', 'edit']);
        Route::resource('estados', EstadoController::class)->except(['create', 'edit']);
        Route::resource('cidades', CidadeController::class)->except(['create', 'edit']);
    });

    // 🔸 Módulo: Usuários
    Route::middleware('module.access:users')->group(function () {
        Route::resource('users', UserController::class);
        Route::post('users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
        Route::post('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
    });

    // 🔸 Módulo: Configurações do Sistema
    Route::middleware('module.access:settings')->group(function () {
        Route::get('settings/', [SystemSettingsController::class, 'index'])->name('settings.index');
        Route::post('settings/update/general', [SystemSettingsController::class, 'updateGeneral'])->name('settings.update.general');
        Route::post('settings/update/integrations', [SystemSettingsController::class, 'updateIntegrations'])->name('settings.update.integrations');
        // Service pode ser uma string, então não precisa restrição numérica
        Route::get('settings/test/{service}', [SystemSettingsController::class, 'testConnection'])->name('settings.test');
    });

    // 🔸 Módulo: Z-API (acessível a todos os usuários autenticados)
    Route::get('zapi', [\App\Http\Controllers\Platform\ZApiController::class, 'index'])->name('zapi.index');
    Route::post('zapi/send', [\App\Http\Controllers\Platform\ZApiController::class, 'sendMessage'])->name('zapi.send');

    // =======================================================
    // 🔸 Rotas auxiliares (sem restrição de módulo)
    // =======================================================
    Route::get('/api/estados/{pais}', [LocationController::class, 'getEstados'])->name('api.estados');
    Route::get('/api/cidades/{estado}', [LocationController::class, 'getCidades'])->name('api.cidades');

    Route::post('whatsapp/send', [WhatsAppController::class, 'sendMessage'])->name('whatsapp.send');
    Route::post('whatsapp/invoice/{invoice}', [WhatsAppController::class, 'sendInvoiceNotification'])->name('whatsapp.invoice');

    Route::get('system_notifications/json', function () {
        $notifications = SystemNotification::latest('created_at')->take(5)->get();
        $unreadCount = SystemNotification::where('status', 'new')->count();

        return response()->json([
            'unread_count' => $unreadCount,
            'notifications' => $notifications,
        ]);
    })->name('system_notifications.json');
});

require __DIR__ . '/auth.php';
