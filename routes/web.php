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
use App\Models\Platform\SystemNotification;
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

Route::get('/', function () {
    if (Auth::check()) {
        // Usuário autenticado → vai para o dashboard
        return redirect()->route('Platform.dashboard');
    }

    // Não autenticado → vai para o login
    return redirect()->route('login');
});

Route::post('/webhook/asaas', [AsaasWebhookController::class, 'handle'])->name('webhook.asaas');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth'])->prefix('Platform')->name('Platform.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('tenants', TenantController::class);
    Route::resource('plans', PlanController::class);
    Route::resource('subscriptions', SubscriptionController::class);
    Route::post('/Platform/subscriptions/{id}/renew', [SubscriptionController::class, 'renew'])->name('subscriptions.renew');
    Route::resource('invoices', InvoiceController::class);
    Route::resource('medical_specialties_catalog', MedicalSpecialtyCatalogController::class);
    Route::resource('notifications_outbox', NotificationOutboxController::class);
    Route::resource('system_notifications', SystemNotificationController::class)->except(['create', 'edit', 'update', 'store', 'destroy'])->whereUuid('system_notification');
    Route::resource('paises', PaisController::class)->except(['create', 'edit']);
    Route::resource('estados', EstadoController::class)->except(['create', 'edit']);
    Route::resource('cidades', CidadeController::class)->except(['create', 'edit']);
    //Rotas para usuários...
    Route::resource('users', UserController::class);
    Route::post('users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
    Route::post('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
    //Rotas para Settings...
    Route::get('settings/', [SystemSettingsController::class, 'index'])->name('settings.index');
    Route::post('settings/update/general', [SystemSettingsController::class, 'updateGeneral'])->name('settings.update.general');
    Route::post('settings/update/integrations', [SystemSettingsController::class, 'updateIntegrations'])->name('settings.update.integrations');
    Route::get('settings/test/{service}', [SystemSettingsController::class, 'testConnection'])->name('settings.test');
    //Rotas para ferramentas do sistema consulta e etc...
    Route::get('/api/estados/{pais}', [LocationController::class, 'getEstados'])->name('api.estados');
    Route::get('/api/cidades/{estado}', [LocationController::class, 'getCidades'])->name('api.cidades');
    Route::get('tenants/{tenant}/subscriptions', [SubscriptionController::class, 'getByTenant'])->name('subscriptions.getByTenant');
    //Rota para carregar notificações...
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
