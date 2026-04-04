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
use App\Http\Controllers\Platform\EstadoController;
use App\Http\Controllers\Platform\CidadeController;
use App\Http\Controllers\Platform\LocationController;
use App\Http\Controllers\Platform\ZipcodeController;
use App\Http\Controllers\Platform\SystemSettingsController;
use App\Http\Controllers\Platform\KioskMonitorController;
use App\Http\Controllers\Platform\PlanAccessManagerController;
use App\Http\Controllers\Platform\PreTenantController;
use App\Http\Controllers\Platform\PlatformEmailTemplateController;
use App\Http\Controllers\Platform\TenantEmailTemplateController;
use App\Http\Controllers\Platform\WhatsAppUnofficialTemplateController;
use App\Http\Controllers\Platform\WhatsAppOfficialTemplateController;
use App\Http\Controllers\Platform\WhatsAppOfficialTemplateBindingController;
use App\Http\Controllers\Platform\WhatsAppOfficialTenantTemplateController;
use App\Http\Controllers\Platform\TenantDefaultNotificationTemplateController;
use App\Http\Controllers\Platform\ApiTenantTokenController;
use App\Http\Controllers\Platform\ClinicNetworkController;
use App\Models\Platform\SystemNotification;
use App\Models\Platform\PlatformEmailTemplate;
use App\Models\Platform\TenantEmailTemplate;
use App\Models\Platform\WhatsAppUnofficialTemplate;
use App\Models\Platform\WhatsAppOfficialTemplate;
use App\Models\Platform\WhatsAppOfficialTenantTemplate;
use App\Models\Platform\TenantDefaultNotificationTemplate;
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

// Webhook do módulo financeiro (por tenant)
Route::prefix('t/{slug}')->middleware(['tenant-web'])->group(function () {
    Route::post('/webhooks/asaas', [\App\Http\Controllers\Tenant\AsaasWebhookController::class, 'handle'])
        ->middleware([
            'throttle.asaas.webhook',
            'verify.asaas.webhook.secret',
            'verify.asaas.webhook.ip',
        ])
        ->name('tenant.webhooks.asaas');
    
    // Páginas públicas de pagamento
    Route::get('/pagamento/{charge}', [\App\Http\Controllers\Tenant\PaymentController::class, 'show'])
        ->where('charge', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}')
        ->name('tenant.payment.show');
    
    Route::get('/pagamento/{charge}/sucesso', [\App\Http\Controllers\Tenant\PaymentController::class, 'success'])
        ->where('charge', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}')
        ->name('tenant.payment.success');
    
    Route::get('/pagamento/{charge}/erro', [\App\Http\Controllers\Tenant\PaymentController::class, 'error'])
        ->where('charge', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}')
        ->name('tenant.payment.error');
});

// Callback global do Google Calendar (não fica no grupo /t/{tenant})
Route::get('/google/callback', [GoogleCalendarController::class, 'callback'])->name('google.callback');

// Rotas públicas para Google OAuth (Política de Privacidade e Termos de Serviço)
Route::view('/politica-de-privacidade', 'public.privacy')->name('public.privacy');
Route::view('/termos-de-servico', 'public.terms')->name('public.terms');

// Rotas públicas de localização para pré-cadastro
Route::get('/api/location/estados', [\App\Http\Controllers\Platform\LocationController::class, 'getEstados'])->name('api.public.estados');
Route::get('/api/location/cidades/{estado}', [\App\Http\Controllers\Platform\LocationController::class, 'getCidades'])->name('api.public.cidades');
Route::get('/api/zipcode/{zipcode}', [ZipcodeController::class, 'show'])->name('api.zipcode');

// Compatibilidade: URL antiga de login (raiz) -> padrão atual da Platform
Route::redirect('/login', '/Platform/login', 301);


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
        Route::post('/tenants/{tenant}/send-credentials', [TenantController::class, 'sendCredentials'])->name('tenants.send-credentials');
        
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
        
        // Solicitações de Mudança de Plano
        Route::get('plan-change-requests', [\App\Http\Controllers\Platform\PlanChangeRequestController::class, 'index'])->name('plan-change-requests.index');
        Route::get('plan-change-requests/{planChangeRequest}', [\App\Http\Controllers\Platform\PlanChangeRequestController::class, 'show'])->name('plan-change-requests.show');
        Route::post('plan-change-requests/{planChangeRequest}/approve', [\App\Http\Controllers\Platform\PlanChangeRequestController::class, 'approve'])->name('plan-change-requests.approve');
        Route::post('plan-change-requests/{planChangeRequest}/reject', [\App\Http\Controllers\Platform\PlanChangeRequestController::class, 'reject'])->name('plan-change-requests.reject');
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

    // 🔸 Módulo: Templates de Email Platform
    Route::middleware('module.access:platform_email_templates')->group(function () {
        Route::get('platform-email-templates', [PlatformEmailTemplateController::class, 'index'])
            ->middleware('can:viewAny,' . PlatformEmailTemplate::class)
            ->name('platform-email-templates.index');
        Route::get('platform-email-templates/{platformEmailTemplate}', [PlatformEmailTemplateController::class, 'show'])
            ->middleware('can:view,platformEmailTemplate')
            ->whereUuid('platformEmailTemplate')
            ->name('platform-email-templates.show');
        Route::get('platform-email-templates/{platformEmailTemplate}/edit', [PlatformEmailTemplateController::class, 'edit'])
            ->middleware('can:update,platformEmailTemplate')
            ->whereUuid('platformEmailTemplate')
            ->name('platform-email-templates.edit');
        Route::put('platform-email-templates/{platformEmailTemplate}', [PlatformEmailTemplateController::class, 'update'])
            ->middleware('can:update,platformEmailTemplate')
            ->whereUuid('platformEmailTemplate')
            ->name('platform-email-templates.update');
        Route::post('platform-email-templates/{platformEmailTemplate}/restore', [PlatformEmailTemplateController::class, 'restore'])
            ->middleware('can:restore,platformEmailTemplate')
            ->whereUuid('platformEmailTemplate')
            ->name('platform-email-templates.restore');
        Route::post('platform-email-templates/{platformEmailTemplate}/toggle', [PlatformEmailTemplateController::class, 'toggle'])
            ->middleware('can:toggle,platformEmailTemplate')
            ->whereUuid('platformEmailTemplate')
            ->name('platform-email-templates.toggle');
        Route::post('platform-email-templates/{platformEmailTemplate}/test-send', [PlatformEmailTemplateController::class, 'testSend'])
            ->middleware('can:update,platformEmailTemplate')
            ->whereUuid('platformEmailTemplate')
            ->name('platform-email-templates.test-send');
    });

    // 🔸 Módulo: Templates de Email Tenant (baseline)
    Route::middleware('module.access:tenant_email_templates')->group(function () {
        Route::get('tenant-email-templates', [TenantEmailTemplateController::class, 'index'])
            ->middleware('can:viewAny,' . TenantEmailTemplate::class)
            ->name('tenant-email-templates.index');
        Route::get('tenant-email-templates/{tenantEmailTemplate}', [TenantEmailTemplateController::class, 'show'])
            ->middleware('can:view,tenantEmailTemplate')
            ->whereUuid('tenantEmailTemplate')
            ->name('tenant-email-templates.show');
        Route::get('tenant-email-templates/{tenantEmailTemplate}/edit', [TenantEmailTemplateController::class, 'edit'])
            ->middleware('can:update,tenantEmailTemplate')
            ->whereUuid('tenantEmailTemplate')
            ->name('tenant-email-templates.edit');
        Route::put('tenant-email-templates/{tenantEmailTemplate}', [TenantEmailTemplateController::class, 'update'])
            ->middleware('can:update,tenantEmailTemplate')
            ->whereUuid('tenantEmailTemplate')
            ->name('tenant-email-templates.update');
        Route::post('tenant-email-templates/{tenantEmailTemplate}/restore', [TenantEmailTemplateController::class, 'restore'])
            ->middleware('can:restore,tenantEmailTemplate')
            ->whereUuid('tenantEmailTemplate')
            ->name('tenant-email-templates.restore');
        Route::post('tenant-email-templates/{tenantEmailTemplate}/toggle', [TenantEmailTemplateController::class, 'toggle'])
            ->middleware('can:toggle,tenantEmailTemplate')
            ->whereUuid('tenantEmailTemplate')
            ->name('tenant-email-templates.toggle');
        Route::post('tenant-email-templates/{tenantEmailTemplate}/test-send', [TenantEmailTemplateController::class, 'testSend'])
            ->middleware('can:update,tenantEmailTemplate')
            ->whereUuid('tenantEmailTemplate')
            ->name('tenant-email-templates.test-send');
    });

    // 🔸 Módulo: Layouts de Email
    Route::middleware('module.access:notification_templates')->group(function () {
        Route::get('email-layouts', [\App\Http\Controllers\Platform\EmailLayoutController::class, 'index'])
            ->name('email-layouts.index');
        Route::get('email-layouts/{emailLayout}/edit', [\App\Http\Controllers\Platform\EmailLayoutController::class, 'edit'])
            ->name('email-layouts.edit');
        Route::put('email-layouts/{emailLayout}', [\App\Http\Controllers\Platform\EmailLayoutController::class, 'update'])
            ->name('email-layouts.update');
        Route::get('email-layouts/{emailLayout}/preview', [\App\Http\Controllers\Platform\EmailLayoutController::class, 'preview'])
            ->name('email-layouts.preview');
    });

    // 🔸 Módulo: Templates WhatsApp Oficial (Meta Cloud API)
    Route::middleware(['module.access:whatsapp_official_templates', 'whatsapp.official.provider'])->group(function () {
        Route::get('whatsapp-official-templates', [WhatsAppOfficialTemplateController::class, 'index'])
            ->middleware('can:viewAny,' . WhatsAppOfficialTemplate::class)
            ->name('whatsapp-official-templates.index');
        Route::get('whatsapp-official-templates/bindings', [WhatsAppOfficialTemplateBindingController::class, 'platformIndex'])
            ->middleware('can:manageBindings,' . WhatsAppOfficialTemplate::class)
            ->name('whatsapp-official-templates.bindings.index');
        Route::post('whatsapp-official-templates/bindings', [WhatsAppOfficialTemplateBindingController::class, 'upsertPlatform'])
            ->middleware('can:manageBindings,' . WhatsAppOfficialTemplate::class)
            ->name('whatsapp-official-templates.bindings.upsert');
        Route::get('whatsapp-official-templates/create', [WhatsAppOfficialTemplateController::class, 'create'])
            ->middleware('can:create,' . WhatsAppOfficialTemplate::class)
            ->name('whatsapp-official-templates.create');
        Route::post('whatsapp-official-templates', [WhatsAppOfficialTemplateController::class, 'store'])
            ->middleware('can:create,' . WhatsAppOfficialTemplate::class)
            ->name('whatsapp-official-templates.store');
        Route::get('whatsapp-official-templates/{whatsappOfficialTemplate}', [WhatsAppOfficialTemplateController::class, 'show'])
            ->middleware('can:view,whatsappOfficialTemplate')
            ->name('whatsapp-official-templates.show');
        Route::get('whatsapp-official-templates/{whatsappOfficialTemplate}/edit', [WhatsAppOfficialTemplateController::class, 'edit'])
            ->middleware('can:update,whatsappOfficialTemplate')
            ->name('whatsapp-official-templates.edit');
        Route::put('whatsapp-official-templates/{whatsappOfficialTemplate}', [WhatsAppOfficialTemplateController::class, 'update'])
            ->middleware('can:update,whatsappOfficialTemplate')
            ->name('whatsapp-official-templates.update');
        Route::post('whatsapp-official-templates/{whatsappOfficialTemplate}/duplicate', [WhatsAppOfficialTemplateController::class, 'duplicate'])
            ->middleware('can:duplicate,whatsappOfficialTemplate')
            ->name('whatsapp-official-templates.duplicate');
        Route::post('whatsapp-official-templates/{whatsappOfficialTemplate}/submit', [WhatsAppOfficialTemplateController::class, 'submitToMeta'])
            ->middleware('can:submitToMeta,whatsappOfficialTemplate')
            ->name('whatsapp-official-templates.submit');
        Route::post('whatsapp-official-templates/{whatsappOfficialTemplate}/sync', [WhatsAppOfficialTemplateController::class, 'syncStatus'])
            ->middleware('can:syncStatus,whatsappOfficialTemplate')
            ->name('whatsapp-official-templates.sync');
        Route::post('whatsapp-official-templates/{whatsappOfficialTemplate}/archive', [WhatsAppOfficialTemplateController::class, 'archive'])
            ->middleware('can:archive,whatsappOfficialTemplate')
            ->name('whatsapp-official-templates.archive');
        Route::post('whatsapp-official-templates/{whatsappOfficialTemplate}/test-send', [WhatsAppOfficialTemplateController::class, 'testSend'])
            ->middleware('can:testSend,whatsappOfficialTemplate')
            ->name('whatsapp-official-templates.test-send');
    });

    // 🔸 Módulo: Templates WhatsApp Oficial Tenant (baseline oficial tenant)
    Route::middleware(['module.access:whatsapp_official_tenant_templates', 'whatsapp.official.provider'])->group(function () {
        Route::get('whatsapp-official-tenant-templates', [WhatsAppOfficialTenantTemplateController::class, 'index'])
            ->middleware('can:viewAny,' . WhatsAppOfficialTenantTemplate::class)
            ->name('whatsapp-official-tenant-templates.index');
        Route::get('whatsapp-official-tenant-templates/bindings', [WhatsAppOfficialTemplateBindingController::class, 'tenantIndex'])
            ->middleware('can:manageBindings,' . WhatsAppOfficialTenantTemplate::class)
            ->name('whatsapp-official-tenant-templates.bindings.index');
        Route::post('whatsapp-official-tenant-templates/bindings', [WhatsAppOfficialTemplateBindingController::class, 'upsertTenant'])
            ->middleware('can:manageBindings,' . WhatsAppOfficialTenantTemplate::class)
            ->name('whatsapp-official-tenant-templates.bindings.upsert');
        Route::get('whatsapp-official-tenant-templates/create', [WhatsAppOfficialTenantTemplateController::class, 'create'])
            ->middleware('can:create,' . WhatsAppOfficialTenantTemplate::class)
            ->name('whatsapp-official-tenant-templates.create');
        Route::post('whatsapp-official-tenant-templates', [WhatsAppOfficialTenantTemplateController::class, 'store'])
            ->middleware('can:create,' . WhatsAppOfficialTenantTemplate::class)
            ->name('whatsapp-official-tenant-templates.store');
        Route::get('whatsapp-official-tenant-templates/{whatsappOfficialTemplate}', [WhatsAppOfficialTenantTemplateController::class, 'show'])
            ->middleware('can:viewAny,' . WhatsAppOfficialTenantTemplate::class)
            ->name('whatsapp-official-tenant-templates.show');
        Route::get('whatsapp-official-tenant-templates/{whatsappOfficialTemplate}/edit', [WhatsAppOfficialTenantTemplateController::class, 'edit'])
            ->middleware('can:update,' . WhatsAppOfficialTenantTemplate::class)
            ->name('whatsapp-official-tenant-templates.edit');
        Route::put('whatsapp-official-tenant-templates/{whatsappOfficialTemplate}', [WhatsAppOfficialTenantTemplateController::class, 'update'])
            ->middleware('can:update,' . WhatsAppOfficialTenantTemplate::class)
            ->name('whatsapp-official-tenant-templates.update');
        Route::post('whatsapp-official-tenant-templates/{whatsappOfficialTemplate}/submit', [WhatsAppOfficialTenantTemplateController::class, 'submitToMeta'])
            ->middleware('can:submitToMeta,' . WhatsAppOfficialTenantTemplate::class)
            ->name('whatsapp-official-tenant-templates.submit');
        Route::post('whatsapp-official-tenant-templates/{whatsappOfficialTemplate}/republish', [WhatsAppOfficialTenantTemplateController::class, 'republishToMeta'])
            ->middleware('can:submitToMeta,' . WhatsAppOfficialTenantTemplate::class)
            ->name('whatsapp-official-tenant-templates.republish');
        Route::post('whatsapp-official-tenant-templates/{whatsappOfficialTemplate}/sync', [WhatsAppOfficialTenantTemplateController::class, 'syncStatus'])
            ->middleware('can:syncStatus,' . WhatsAppOfficialTenantTemplate::class)
            ->name('whatsapp-official-tenant-templates.sync');
        Route::post('whatsapp-official-tenant-templates/{whatsappOfficialTemplate}/test-send', [WhatsAppOfficialTenantTemplateController::class, 'testSend'])
            ->middleware('can:testSend,' . WhatsAppOfficialTenantTemplate::class)
            ->name('whatsapp-official-tenant-templates.test-send');
    });

    // 🔸 Módulo: Templates WhatsApp Não Oficial (internos da Platform)
    Route::middleware('module.access:whatsapp_unofficial_templates')->group(function () {
        Route::get('whatsapp-unofficial-templates', [WhatsAppUnofficialTemplateController::class, 'index'])
            ->middleware('can:viewAny,' . WhatsAppUnofficialTemplate::class)
            ->name('whatsapp-unofficial-templates.index');
        Route::get('whatsapp-unofficial-templates/{whatsappUnofficialTemplate}', [WhatsAppUnofficialTemplateController::class, 'show'])
            ->middleware('can:view,whatsappUnofficialTemplate')
            ->whereUuid('whatsappUnofficialTemplate')
            ->name('whatsapp-unofficial-templates.show');
        Route::get('whatsapp-unofficial-templates/{whatsappUnofficialTemplate}/edit', [WhatsAppUnofficialTemplateController::class, 'edit'])
            ->middleware('can:update,whatsappUnofficialTemplate')
            ->whereUuid('whatsappUnofficialTemplate')
            ->name('whatsapp-unofficial-templates.edit');
        Route::put('whatsapp-unofficial-templates/{whatsappUnofficialTemplate}', [WhatsAppUnofficialTemplateController::class, 'update'])
            ->middleware('can:update,whatsappUnofficialTemplate')
            ->whereUuid('whatsappUnofficialTemplate')
            ->name('whatsapp-unofficial-templates.update');
        Route::post('whatsapp-unofficial-templates/{whatsappUnofficialTemplate}/preview', [WhatsAppUnofficialTemplateController::class, 'preview'])
            ->middleware('can:preview,whatsappUnofficialTemplate')
            ->whereUuid('whatsappUnofficialTemplate')
            ->name('whatsapp-unofficial-templates.preview');
        Route::post('whatsapp-unofficial-templates/{whatsappUnofficialTemplate}/test-send', [WhatsAppUnofficialTemplateController::class, 'testSend'])
            ->middleware('can:testSend,whatsappUnofficialTemplate')
            ->whereUuid('whatsappUnofficialTemplate')
            ->name('whatsapp-unofficial-templates.test-send');
        Route::post('whatsapp-unofficial-templates/{whatsappUnofficialTemplate}/toggle', [WhatsAppUnofficialTemplateController::class, 'toggle'])
            ->middleware('can:toggle,whatsappUnofficialTemplate')
            ->whereUuid('whatsappUnofficialTemplate')
            ->name('whatsapp-unofficial-templates.toggle');
    });

    // 🔸 Módulo: Tenant Default Notification Templates (baseline operacional)
    Route::middleware('module.access:tenant_default_notification_templates')->group(function () {
        Route::get('tenant-default-notification-templates', [TenantDefaultNotificationTemplateController::class, 'index'])
            ->middleware('can:viewAny,' . TenantDefaultNotificationTemplate::class)
            ->name('tenant-default-notification-templates.index');
        Route::get('tenant-default-notification-templates/{tenantDefaultTemplate}/edit', [TenantDefaultNotificationTemplateController::class, 'edit'])
            ->middleware('can:update,tenantDefaultTemplate')
            ->whereUuid('tenantDefaultTemplate')
            ->name('tenant-default-notification-templates.edit');
        Route::put('tenant-default-notification-templates/{tenantDefaultTemplate}', [TenantDefaultNotificationTemplateController::class, 'update'])
            ->middleware('can:update,tenantDefaultTemplate')
            ->whereUuid('tenantDefaultTemplate')
            ->name('tenant-default-notification-templates.update');
    });

    // 🔸 Módulo: Localização (Estados e Cidades - Brasil)
    Route::middleware('module.access:locations')->group(function () {
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
        Route::get('settings/update/integrations', function () {
            return redirect()
                ->route('Platform.settings.index')
                ->with('warning', 'Atualizacao de integracoes requer requisicao POST. Use o botao "Salvar Integracoes".');
        })->name('settings.update.integrations.get');
        Route::post('settings/update/integrations', [SystemSettingsController::class, 'updateIntegrations'])->name('settings.update.integrations');
        Route::post('settings/update/logos', [SystemSettingsController::class, 'updateLogos'])->name('settings.update.logos');
        Route::post('settings/update/billing', [SystemSettingsController::class, 'updateBilling'])->name('settings.update.billing');
        Route::post('settings/update/notifications', [SystemSettingsController::class, 'updateNotifications'])->name('settings.update.notifications');
        Route::post('settings/update/commands', [SystemSettingsController::class, 'updateScheduledCommands'])->name('settings.update.commands');
        Route::post('settings/commands/add', [SystemSettingsController::class, 'addScheduledCommand'])->name('settings.commands.add');
        Route::delete('settings/commands/{commandKey}', [SystemSettingsController::class, 'removeScheduledCommand'])->name('settings.commands.remove');
        Route::post('settings/commands/remove-duplicates', [SystemSettingsController::class, 'removeDuplicateCommands'])->name('settings.commands.remove-duplicates');
        Route::get('settings/commands/available', [SystemSettingsController::class, 'getAvailableCommands'])->name('settings.commands.available');
        // Service pode ser uma string, então não precisa restrição numérica
        Route::get('settings/test/{service}', [SystemSettingsController::class, 'testConnection'])->name('settings.test');
        Route::get('settings/integrations/google/test', [SystemSettingsController::class, 'testConnection'])
            ->defaults('service', 'google')
            ->name('settings.integrations.google.test');
        Route::post('settings/test/meta/send', [SystemSettingsController::class, 'testMetaSend'])->name('settings.test.meta.send');
        Route::post('settings/test/zapi/send', [SystemSettingsController::class, 'testZapiSend'])->name('settings.test.zapi.send');
        Route::post('settings/test/waha/send', [SystemSettingsController::class, 'testWahaSend'])->name('settings.test.waha.send');
        Route::post('settings/test/evolution/send', [SystemSettingsController::class, 'testEvolutionSend'])->name('settings.test.evolution.send');
    });

    // 🔸 Módulo: Z-API (acessível a todos os usuários autenticados)
    Route::get('zapi', [\App\Http\Controllers\Platform\ZApiController::class, 'index'])->name('zapi.index');
    Route::post('zapi/send', [\App\Http\Controllers\Platform\ZApiController::class, 'sendMessage'])->name('zapi.send');

    // =======================================================
    // 🔸 Rotas auxiliares (sem restrição de módulo)
    // =======================================================
    Route::get('/api/estados', [LocationController::class, 'getEstados'])->name('api.estados');
    Route::get('/api/cidades/{estado}', [LocationController::class, 'getCidades'])->name('api.cidades');

    Route::post('whatsapp/send', [WhatsAppController::class, 'sendMessage'])->name('whatsapp.send');
    Route::post('whatsapp/invoice/{invoice}', [WhatsAppController::class, 'sendInvoiceNotification'])->name('whatsapp.invoice');

    Route::get('system_notifications/json', function () {
        $displayCount = sysconfig('notifications.display_count', 5);
        $notifications = SystemNotification::latest('created_at')->take($displayCount)->get();
        $unreadCount = SystemNotification::where('status', 'new')->count();

        return response()->json([
            'unread_count' => $unreadCount,
            'notifications' => $notifications,
        ]);
    })->name('system_notifications.json');
});

require __DIR__ . '/auth.php';
