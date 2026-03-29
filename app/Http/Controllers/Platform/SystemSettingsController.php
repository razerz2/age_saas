<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Providers\ProviderConfigResolver;
use App\Services\WhatsApp\TenantGlobalProviderCatalogService;
use App\Services\WhatsApp\WhatsAppBusinessProvider;
use App\Services\WhatsApp\WahaClient;
use App\Services\WhatsApp\WahaProvider;
use App\Services\WhatsApp\EvolutionClient;
use App\Services\WhatsApp\EvolutionProvider;
use App\Services\WhatsApp\ZApiProvider;
use App\Services\WhatsApp\PhoneNormalizer;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SystemSettingsController extends Controller
{
    private const BRAZIL_COUNTRY_ID = 31;
    /**
     * Exibe a pÃ¡gina de configuraÃ§Ãµes.
     */
    public function index()
    {
        $tenantGlobalProviderCatalog = app(TenantGlobalProviderCatalogService::class);

        $settings = [
            'timezone' => sysconfig('timezone', 'America/Sao_Paulo'),
            'language' => sysconfig('language', 'pt_BR'),
            // demais integraÃ§Ãµes
            'ASAAS_API_URL' => sysconfig('ASAAS_API_URL', env('ASAAS_API_URL')),
            'ASAAS_API_KEY' => sysconfig('ASAAS_API_KEY', env('ASAAS_API_KEY')),
            'ASAAS_ENV' => sysconfig('ASAAS_ENV', env('ASAAS_ENV', 'sandbox')),
            'META_ACCESS_TOKEN' => sysconfig('META_ACCESS_TOKEN', env('META_ACCESS_TOKEN')),
            'META_PHONE_NUMBER_ID' => sysconfig('META_PHONE_NUMBER_ID', env('META_PHONE_NUMBER_ID')),
            'META_WABA_ID' => sysconfig('META_WABA_ID', env('META_WABA_ID')),
            'WHATSAPP_PROVIDER' => sysconfig('WHATSAPP_PROVIDER', env('WHATSAPP_PROVIDER', 'whatsapp_business')),
            'ZAPI_API_URL' => sysconfig('ZAPI_API_URL', env('ZAPI_API_URL', 'https://api.z-api.io')),
            'ZAPI_TOKEN' => sysconfig('ZAPI_TOKEN', env('ZAPI_TOKEN')),
            'ZAPI_CLIENT_TOKEN' => sysconfig('ZAPI_CLIENT_TOKEN', env('ZAPI_CLIENT_TOKEN')),
            'ZAPI_INSTANCE_ID' => sysconfig('ZAPI_INSTANCE_ID', env('ZAPI_INSTANCE_ID')),
            'WAHA_BASE_URL' => sysconfig('WAHA_BASE_URL', env('WAHA_BASE_URL')),
            'WAHA_API_KEY' => sysconfig('WAHA_API_KEY', env('WAHA_API_KEY')),
            'WAHA_SESSION' => sysconfig('WAHA_SESSION', env('WAHA_SESSION', 'default')),
            'EVOLUTION_BASE_URL' => sysconfig('EVOLUTION_BASE_URL', env('EVOLUTION_BASE_URL', env('EVOLUTION_API_URL'))),
            'EVOLUTION_API_KEY' => sysconfig('EVOLUTION_API_KEY', env('EVOLUTION_API_KEY', env('EVOLUTION_KEY'))),
            'EVOLUTION_INSTANCE' => sysconfig('EVOLUTION_INSTANCE', env('EVOLUTION_INSTANCE', env('EVOLUTION_INSTANCE_NAME', 'default'))),
            'WHATSAPP_GLOBAL_ENABLED_PROVIDERS' => $tenantGlobalProviderCatalog->enabledProviders(),
            'MAIL_HOST' => sysconfig('MAIL_HOST', env('MAIL_HOST')),
            'MAIL_PORT' => sysconfig('MAIL_PORT', env('MAIL_PORT')),
            'MAIL_USERNAME' => sysconfig('MAIL_USERNAME', env('MAIL_USERNAME')),
            'MAIL_FROM_ADDRESS' => sysconfig('MAIL_FROM_ADDRESS', env('MAIL_FROM_ADDRESS')),
            'MAIL_FROM_NAME' => sysconfig('MAIL_FROM_NAME', env('MAIL_FROM_NAME')),
            // Logos e Favicons
            'system.default_logo' => sysconfig('system.default_logo'),
            'system.default_favicon' => sysconfig('system.default_favicon'),
            'platform.logo' => sysconfig('platform.logo'),
            'platform.favicon' => sysconfig('platform.favicon'),
            'landing.logo' => sysconfig('landing.logo'),
            'landing.favicon' => sysconfig('landing.favicon'),
            'tenant.default_logo' => sysconfig('tenant.default_logo'),
            'tenant.default_logo_mini' => sysconfig('tenant.default_logo_mini'),
            'tenant.default_favicon' => sysconfig('tenant.default_favicon'),
            // ConfiguraÃ§Ãµes de Billing
            'billing.invoice_days_before_due' => sysconfig('billing.invoice_days_before_due', 10),
            'billing.notify_days_before_due' => sysconfig('billing.notify_days_before_due', 5),
            'billing.recovery_days_after_suspension' => sysconfig('billing.recovery_days_after_suspension', 5),
            'billing.purge_days_after_cancellation' => sysconfig('billing.purge_days_after_cancellation', 90),
            // ConfiguraÃ§Ãµes de NotificaÃ§Ãµes
            'notifications.enabled' => sysconfig('notifications.enabled', '1') === '1',
            'notifications.update_interval' => (int) sysconfig('notifications.update_interval', 5),
            'notifications.display_count' => (int) sysconfig('notifications.display_count', 5),
            'notifications.show_badge' => sysconfig('notifications.show_badge', '1') === '1',
            'notifications.sound_enabled' => sysconfig('notifications.sound_enabled', '0') === '1',
            // Tipos de eventos para notificaÃ§Ãµes
            'notifications.types.payment' => sysconfig('notifications.types.payment', '1') === '1',
            'notifications.types.invoice' => sysconfig('notifications.types.invoice', '1') === '1',
            'notifications.types.subscription' => sysconfig('notifications.types.subscription', '1') === '1',
            'notifications.types.tenant' => sysconfig('notifications.types.tenant', '1') === '1',
            'notifications.types.command' => sysconfig('notifications.types.command', '1') === '1',
            'notifications.types.webhook' => sysconfig('notifications.types.webhook', '0') === '1',
        ];

        // Comandos agendados
        $scheduledCommands = $this->getScheduledCommands();
        $tenantGlobalWhatsAppProviderOptions = $tenantGlobalProviderCatalog->supportedProviderOptions();
        $tenantGlobalWhatsAppEnabledProviders = $tenantGlobalProviderCatalog->enabledProviders();

        return view('platform.settings.index', compact(
            'settings',
            'scheduledCommands',
            'tenantGlobalWhatsAppProviderOptions',
            'tenantGlobalWhatsAppEnabledProviders'
        ));
    }

    /**
     * Atualiza as configuraÃ§Ãµes gerais (timezone, paÃ­s, idioma)
     */
    public function updateGeneral(Request $request)
    {
        $request->validate([
            'timezone' => 'required|string',
            'language' => 'required|string',
        ]);

        set_sysconfig('timezone', $request->timezone);
        set_sysconfig('country_id', self::BRAZIL_COUNTRY_ID);
        set_sysconfig('language', $request->language);

        return back()->with('success', 'ConfiguraÃ§Ãµes gerais atualizadas com sucesso.');
    }

    /**
     * Atualiza integraÃ§Ãµes ASAAS / Meta / Z-API / Email
     */
    public function updateIntegrations(Request $request)
    {
        $tenantGlobalProviderCatalog = app(TenantGlobalProviderCatalogService::class);

        $rules = [
            'tab' => 'nullable|string|in:integracoes,whatsapp,email',
            'ASAAS_API_KEY' => 'nullable|string',
            'ASAAS_ENV' => 'nullable|string',
            'META_ACCESS_TOKEN' => 'nullable|string',
            'META_PHONE_NUMBER_ID' => 'nullable|string',
            'META_WABA_ID' => 'nullable|string',
            'WHATSAPP_PROVIDER' => 'nullable|string|in:whatsapp_business,zapi,waha,evolution',
            'ZAPI_API_URL' => 'nullable|string|url',
            'ZAPI_TOKEN' => 'nullable|string',
            'ZAPI_CLIENT_TOKEN' => 'nullable|string',
            'ZAPI_INSTANCE_ID' => 'nullable|string',
            'WAHA_BASE_URL' => 'nullable|string|url',
            'WAHA_API_KEY' => 'nullable|string',
            'WAHA_SESSION' => 'nullable|string',
            'EVOLUTION_BASE_URL' => 'nullable|string|url',
            'EVOLUTION_API_KEY' => 'nullable|string',
            'EVOLUTION_INSTANCE' => 'nullable|string|max:120',
            'WHATSAPP_GLOBAL_ENABLED_PROVIDERS' => 'nullable|array',
            'WHATSAPP_GLOBAL_ENABLED_PROVIDERS.*' => [
                'string',
                Rule::in($tenantGlobalProviderCatalog->supportedProviders()),
            ],
            'MAIL_HOST' => 'nullable|string',
            'MAIL_PORT' => 'nullable|string',
            'MAIL_USERNAME' => 'nullable|string',
            'MAIL_PASSWORD' => 'nullable|string',
            'MAIL_FROM_ADDRESS' => 'nullable|string',
            'MAIL_FROM_NAME' => 'nullable|string',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $tab = $this->resolveSettingsTab($request->input('tab'));

            return redirect()
                ->route('Platform.settings.index', ['tab' => $tab])
                ->withErrors($validator)
                ->withInput();
        }

        // Lista de campos que serÃ£o atualizados
        $fields = [
            'ASAAS_API_KEY',
            'ASAAS_ENV',
            'META_ACCESS_TOKEN',
            'META_PHONE_NUMBER_ID',
            'META_WABA_ID',
            'WHATSAPP_PROVIDER',
            'ZAPI_API_URL',
            'ZAPI_TOKEN',
            'ZAPI_CLIENT_TOKEN',
            'ZAPI_INSTANCE_ID',
            'WAHA_BASE_URL',
            'WAHA_API_KEY',
            'WAHA_SESSION',
            'EVOLUTION_BASE_URL',
            'EVOLUTION_API_KEY',
            'EVOLUTION_INSTANCE',
            'MAIL_HOST',
            'MAIL_PORT',
            'MAIL_USERNAME',
            'MAIL_PASSWORD',
            'MAIL_FROM_ADDRESS',
            'MAIL_FROM_NAME',
        ];

        // Atualiza configuraÃ§Ãµes no banco
        foreach ($fields as $field) {
            if ($request->exists($field)) {
                set_sysconfig($field, $request->input($field));
            }
        }

        $tab = $this->resolveSettingsTab($request->input('tab'));
        if ($tab === 'whatsapp') {
            set_sysconfig(
                TenantGlobalProviderCatalogService::SYS_CONFIG_KEY,
                $tenantGlobalProviderCatalog->encodeProviders(
                    (array) $request->input('WHATSAPP_GLOBAL_ENABLED_PROVIDERS', [])
                )
            );
        }
        // Persistencia administrativa fica no banco (SystemSetting).
        // Evita restart do servidor local por escrita no .env durante uso do painel.

        return redirect()
            ->route('Platform.settings.index', ['tab' => $tab])
            ->with('success', 'IntegraÃ§Ãµes e configuraÃ§Ãµes de e-mail atualizadas com sucesso.');
    }

    private function resolveSettingsTab(?string $tab): string
    {
        $allowedTabs = ['integracoes', 'whatsapp', 'email'];

        return in_array($tab, $allowedTabs, true) ? $tab : 'integracoes';
    }

    /**
     * Atualiza logos e favicons
     */
    public function updateLogos(Request $request)
    {
        $request->validate([
            'system_default_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:5120',
            'system_default_favicon' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,ico|max:1024',
            'platform_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:5120',
            'platform_favicon' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,ico|max:1024',
            'landing_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:5120',
            'landing_favicon' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,ico|max:1024',
            'tenant_default_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:5120',
            'tenant_default_logo_mini' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:5120',
            'tenant_default_favicon' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,ico|max:1024',
            'remove_system_default_logo' => 'nullable|boolean',
            'remove_system_default_favicon' => 'nullable|boolean',
            'remove_platform_logo' => 'nullable|boolean',
            'remove_platform_favicon' => 'nullable|boolean',
            'remove_landing_logo' => 'nullable|boolean',
            'remove_landing_favicon' => 'nullable|boolean',
            'remove_tenant_default_logo' => 'nullable|boolean',
            'remove_tenant_default_logo_mini' => 'nullable|boolean',
            'remove_tenant_default_favicon' => 'nullable|boolean',
        ]);

        try {
            // Processar logo padrÃ£o do sistema
            if ($request->hasFile('system_default_logo')) {
                $path = $request->file('system_default_logo')->store('platform/system-logos', 'public');
                Log::info('Logo padrÃ£o do sistema salva', ['path' => $path]);
                set_sysconfig('system.default_logo', $path);
            } elseif ($request->input('remove_system_default_logo') == '1') {
                $oldLogo = sysconfig('system.default_logo');
                if ($oldLogo && Storage::disk('public')->exists($oldLogo)) {
                    Storage::disk('public')->delete($oldLogo);
                }
                set_sysconfig('system.default_logo', null);
            }

            // Processar favicon padrÃ£o do sistema
            if ($request->hasFile('system_default_favicon')) {
                $path = $request->file('system_default_favicon')->store('platform/system-favicons', 'public');
                Log::info('Favicon padrÃ£o do sistema salvo', ['path' => $path]);
                set_sysconfig('system.default_favicon', $path);
            } elseif ($request->input('remove_system_default_favicon') == '1') {
                $oldFavicon = sysconfig('system.default_favicon');
                if ($oldFavicon && Storage::disk('public')->exists($oldFavicon)) {
                    Storage::disk('public')->delete($oldFavicon);
                }
                set_sysconfig('system.default_favicon', null);
            }

            // Processar logo da plataforma
            if ($request->hasFile('platform_logo')) {
                $path = $request->file('platform_logo')->store('platform/logos', 'public');
                Log::info('Logo da plataforma salva', ['path' => $path]);
                set_sysconfig('platform.logo', $path);
                Log::info('ConfiguraÃ§Ã£o salva', ['key' => 'platform.logo', 'value' => $path]);
            } elseif ($request->input('remove_platform_logo') == '1') {
                $oldLogo = sysconfig('platform.logo');
                if ($oldLogo && Storage::disk('public')->exists($oldLogo)) {
                    Storage::disk('public')->delete($oldLogo);
                }
                set_sysconfig('platform.logo', null);
            }

            // Processar favicon da plataforma
            if ($request->hasFile('platform_favicon')) {
                $path = $request->file('platform_favicon')->store('platform/favicons', 'public');
                set_sysconfig('platform.favicon', $path);
            } elseif ($request->input('remove_platform_favicon') == '1') {
                $oldFavicon = sysconfig('platform.favicon');
                if ($oldFavicon && Storage::disk('public')->exists($oldFavicon)) {
                    Storage::disk('public')->delete($oldFavicon);
                }
                set_sysconfig('platform.favicon', null);
            }

            // Processar logo da landing page
            if ($request->hasFile('landing_logo')) {
                $path = $request->file('landing_logo')->store('platform/landing-logos', 'public');
                set_sysconfig('landing.logo', $path);
            } elseif ($request->input('remove_landing_logo') == '1') {
                $oldLogo = sysconfig('landing.logo');
                if ($oldLogo && Storage::disk('public')->exists($oldLogo)) {
                    Storage::disk('public')->delete($oldLogo);
                }
                set_sysconfig('landing.logo', null);
            }

            // Processar favicon da landing page
            if ($request->hasFile('landing_favicon')) {
                $path = $request->file('landing_favicon')->store('platform/landing-favicons', 'public');
                set_sysconfig('landing.favicon', $path);
            } elseif ($request->input('remove_landing_favicon') == '1') {
                $oldFavicon = sysconfig('landing.favicon');
                if ($oldFavicon && Storage::disk('public')->exists($oldFavicon)) {
                    Storage::disk('public')->delete($oldFavicon);
                }
                set_sysconfig('landing.favicon', null);
            }

            // Processar logo padrÃ£o para tenants
            if ($request->hasFile('tenant_default_logo')) {
                $path = $request->file('tenant_default_logo')->store('platform/tenant-logos', 'public');
                set_sysconfig('tenant.default_logo', $path);
            } elseif ($request->input('remove_tenant_default_logo') == '1') {
                $oldLogo = sysconfig('tenant.default_logo');
                if ($oldLogo && Storage::disk('public')->exists($oldLogo)) {
                    Storage::disk('public')->delete($oldLogo);
                }
                set_sysconfig('tenant.default_logo', null);
            }

            // Processar logo retrÃ¡til padrÃ£o para tenants
            if ($request->hasFile('tenant_default_logo_mini')) {
                $path = $request->file('tenant_default_logo_mini')->store('platform/tenant-logos', 'public');
                set_sysconfig('tenant.default_logo_mini', $path);
            } elseif ($request->input('remove_tenant_default_logo_mini') == '1') {
                $oldLogoMini = sysconfig('tenant.default_logo_mini');
                if ($oldLogoMini && Storage::disk('public')->exists($oldLogoMini)) {
                    Storage::disk('public')->delete($oldLogoMini);
                }
                set_sysconfig('tenant.default_logo_mini', null);
            }

            // Processar favicon padrÃ£o para tenants
            if ($request->hasFile('tenant_default_favicon')) {
                $path = $request->file('tenant_default_favicon')->store('platform/tenant-favicons', 'public');
                set_sysconfig('tenant.default_favicon', $path);
            } elseif ($request->input('remove_tenant_default_favicon') == '1') {
                $oldFavicon = sysconfig('tenant.default_favicon');
                if ($oldFavicon && Storage::disk('public')->exists($oldFavicon)) {
                    Storage::disk('public')->delete($oldFavicon);
                }
                set_sysconfig('tenant.default_favicon', null);
            }

            // Limpar cache de configuraÃ§Ãµes e views
            Cache::flush();
            Artisan::call('view:clear');
            Artisan::call('config:clear');
            
            return back()->with('success', 'Logos e favicons atualizados com sucesso.');
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar logos e favicons', ['error' => $e->getMessage()]);
            return back()->with('error', 'Erro ao atualizar logos e favicons: ' . $e->getMessage());
        }
    }

    /**
     * Atualiza configuraÃ§Ãµes de billing
     */
    public function updateBilling(Request $request)
    {
        $request->validate([
            'billing_invoice_days_before_due' => 'nullable|integer|min:1|max:30',
            'billing_notify_days_before_due' => 'nullable|integer|min:1|max:30',
            'billing_recovery_days_after_suspension' => 'nullable|integer|min:1|max:30',
            'billing_purge_days_after_cancellation' => 'nullable|integer|min:30|max:365',
        ]);

        // Atualiza configuraÃ§Ãµes de billing
        if ($request->filled('billing_invoice_days_before_due')) {
            set_sysconfig('billing.invoice_days_before_due', $request->input('billing_invoice_days_before_due'));
        }
        if ($request->filled('billing_notify_days_before_due')) {
            set_sysconfig('billing.notify_days_before_due', $request->input('billing_notify_days_before_due'));
        }
        if ($request->filled('billing_recovery_days_after_suspension')) {
            set_sysconfig('billing.recovery_days_after_suspension', $request->input('billing_recovery_days_after_suspension'));
        }
        if ($request->filled('billing_purge_days_after_cancellation')) {
            set_sysconfig('billing.purge_days_after_cancellation', $request->input('billing_purge_days_after_cancellation'));
        }

        return back()->with('success', 'ConfiguraÃ§Ãµes de billing atualizadas com sucesso.');
    }

    /**
     * Atualiza configuraÃ§Ãµes de notificaÃ§Ãµes
     */
    public function updateNotifications(Request $request)
    {
        $request->validate([
            'notifications_enabled' => 'nullable|boolean',
            'notifications_update_interval' => 'required|integer|min:3|max:60',
            'notifications_display_count' => 'required|integer|min:3|max:20',
            'notifications_show_badge' => 'nullable|boolean',
            'notifications_sound_enabled' => 'nullable|boolean',
            'notify_payment' => 'nullable|boolean',
            'notify_invoice' => 'nullable|boolean',
            'notify_subscription' => 'nullable|boolean',
            'notify_tenant' => 'nullable|boolean',
            'notify_command' => 'nullable|boolean',
            'notify_webhook' => 'nullable|boolean',
        ]);

        // Atualiza configuraÃ§Ãµes de notificaÃ§Ãµes
        set_sysconfig('notifications.enabled', $request->has('notifications_enabled') ? '1' : '0');
        set_sysconfig('notifications.update_interval', (string) $request->input('notifications_update_interval'));
        set_sysconfig('notifications.display_count', (string) $request->input('notifications_display_count'));
        set_sysconfig('notifications.show_badge', $request->has('notifications_show_badge') ? '1' : '0');
        set_sysconfig('notifications.sound_enabled', $request->has('notifications_sound_enabled') ? '1' : '0');
        
        // Atualiza configuraÃ§Ãµes de tipos de eventos
        set_sysconfig('notifications.types.payment', $request->has('notify_payment') ? '1' : '0');
        set_sysconfig('notifications.types.invoice', $request->has('notify_invoice') ? '1' : '0');
        set_sysconfig('notifications.types.subscription', $request->has('notify_subscription') ? '1' : '0');
        set_sysconfig('notifications.types.tenant', $request->has('notify_tenant') ? '1' : '0');
        set_sysconfig('notifications.types.command', $request->has('notify_command') ? '1' : '0');
        set_sysconfig('notifications.types.webhook', $request->has('notify_webhook') ? '1' : '0');

        return back()->with('success', 'ConfiguraÃ§Ãµes de notificaÃ§Ãµes atualizadas com sucesso.');
    }

    /**
     * Retorna lista de comandos padrÃ£o do sistema
     */
    private function getDefaultCommandsList(): array
    {
        return [
            [
                'key' => 'subscriptions:subscriptions-process',
                'name' => 'Processamento de Assinaturas',
                'description' => 'Gera faturas automÃ¡ticas de assinaturas vencidas e renova os perÃ­odos.',
                'default_time' => '01:00',
                'frequency' => 'daily',
            ],
            [
                'key' => 'invoices:generate',
                'name' => 'GeraÃ§Ã£o AutomÃ¡tica de Faturas',
                'description' => 'Gera faturas automaticamente X dias antes do vencimento (PIX/Boleto).',
                'default_time' => '01:30',
                'frequency' => 'daily',
            ],
            [
                'key' => 'invoices:notify-upcoming',
                'name' => 'NotificaÃ§Ãµes de Faturas PrÃ³ximas',
                'description' => 'Envia notificaÃ§Ãµes Y dias antes do vencimento (exclui faturas de cartÃ£o).',
                'default_time' => '01:45',
                'frequency' => 'daily',
            ],
            [
                'key' => 'invoices:invoices-check-overdue',
                'name' => 'VerificaÃ§Ã£o de Faturas Vencidas',
                'description' => 'Marca faturas vencidas e suspende tenants imediatamente (sem perÃ­odo de carÃªncia).',
                'default_time' => '02:00',
                'frequency' => 'daily',
            ],
            [
                'key' => 'subscriptions:process-recovery',
                'name' => 'Processamento de Recovery',
                'description' => 'Inicia processo de recovery para assinaturas de cartÃ£o suspensas â‰¥ 5 dias.',
                'default_time' => '02:30',
                'frequency' => 'daily',
            ],
            [
                'key' => 'subscriptions:notify-trial-reminders',
                'name' => 'Lembretes de Trial Comercial',
                'description' => 'Dispara lembretes de trial (7 dias, 3 dias, hoje e expirado) com idempotÃªncia por assinatura/evento.',
                'default_time' => '09:00',
                'frequency' => 'daily',
            ],
            [
                'key' => 'tenants:purge-canceled',
                'name' => 'Purga de Tenants Cancelados',
                'description' => 'Remove dados e banco de tenants cancelados hÃ¡ X dias (padrÃ£o: 90 dias).',
                'default_time' => '03:00',
                'frequency' => 'daily',
            ],
            [
                'key' => 'recurring-appointments:process',
                'name' => 'Processamento de Agendamentos Recorrentes',
                'description' => 'Processa agendamentos recorrentes e gera sessÃµes automaticamente.',
                'default_time' => '03:00',
                'frequency' => 'daily',
            ],
            [
                'key' => 'google-calendar:renew-recurring-events',
                'name' => 'RenovaÃ§Ã£o de Eventos Recorrentes (Google Calendar)',
                'description' => 'Renova eventos recorrentes no Google Calendar que estÃ£o prÃ³ximos do fim.',
                'default_time' => '04:00',
                'default_day' => 1,
                'frequency' => 'monthly',
            ],
            [
                'key' => 'appointments:notify-upcoming',
                'name' => 'Lembretes de Agendamentos PrÃ³ximos',
                'description' => 'Envia lembretes automÃ¡ticos aos pacientes sobre agendamentos prÃ³ximos (email/WhatsApp).',
                'default_time' => '08:00',
                'frequency' => 'daily',
            ],
        ];
    }

    /**
     * Retorna lista de comandos agendados com suas configuraÃ§Ãµes
     */
    private function getScheduledCommands(): array
    {
        // Comandos padrÃ£o do sistema (hardcoded)
        $defaultCommands = $this->getDefaultCommandsList();

        // Carrega comandos customizados do banco (adicionados pela interface)
        $customCommandsJson = sysconfig('commands.custom_list', '[]');
        $customCommands = json_decode($customCommandsJson, true) ?: [];

        // Remove duplicados da lista customizada (comandos que jÃ¡ existem na lista padrÃ£o)
        $defaultCommandKeys = array_column($defaultCommands, 'key');
        $customCommands = array_filter($customCommands, function($cmd) use ($defaultCommandKeys) {
            return !in_array($cmd['key'], $defaultCommandKeys);
        });
        $customCommands = array_values($customCommands); // Reindexa

        // Se houve duplicados removidos, salva a lista limpa
        if (count($customCommands) !== count(json_decode($customCommandsJson, true) ?: [])) {
            set_sysconfig('commands.custom_list', json_encode($customCommands, JSON_UNESCAPED_UNICODE));
        }

        // Remove duplicados dentro da prÃ³pria lista customizada (caso existam)
        $seen = [];
        $customCommands = array_filter($customCommands, function($cmd) use (&$seen) {
            if (in_array($cmd['key'], $seen)) {
                return false; // Duplicado
            }
            $seen[] = $cmd['key'];
            return true;
        });
        $customCommands = array_values($customCommands); // Reindexa

        // Se houve duplicados removidos, salva a lista limpa novamente
        $currentCount = count($customCommands);
        $previousJson = sysconfig('commands.custom_list', '[]');
        $previousCount = count(json_decode($previousJson, true) ?: []);
        if ($currentCount !== $previousCount) {
            set_sysconfig('commands.custom_list', json_encode($customCommands, JSON_UNESCAPED_UNICODE));
        }

        // Merge: comandos padrÃ£o + comandos customizados
        $allCommands = array_merge($defaultCommands, $customCommands);

        // Carrega configuraÃ§Ãµes do banco para cada comando
        foreach ($allCommands as &$command) {
            $command['enabled'] = sysconfig("commands.{$command['key']}.enabled", '1') === '1';
            $command['time'] = sysconfig("commands.{$command['key']}.time", $command['default_time']);
            if (isset($command['default_day'])) {
                $command['day'] = (int) sysconfig("commands.{$command['key']}.day", $command['default_day']);
            }
            // Marca se Ã© customizado (nÃ£o pode ser removido da lista padrÃ£o)
            $command['is_custom'] = !in_array($command['key'], array_column($defaultCommands, 'key'));
        }

        return $allCommands;
    }

    /**
     * Retorna lista de comandos disponÃ­veis no sistema (para adicionar novos)
     */
    public function getAvailableCommands()
    {
        try {
            $artisan = \Illuminate\Support\Facades\Artisan::all();
            $commands = [];
            
            // Lista de comandos padrÃ£o do Laravel que nÃ£o devem aparecer
            $excludedPrefixes = [
                'make:', 'route:', 'config:', 'cache:', 'view:', 'migrate:', 
                'db:', 'queue:', 'schedule:', 'vendor:', 'tinker', 'serve', 
                'test', 'key:', 'optimize', 'down', 'up', 'env:', 'about'
            ];
            
            foreach ($artisan as $command) {
                $signature = $command->getName();
                $shouldExclude = false;
                
                foreach ($excludedPrefixes as $prefix) {
                    if (str_starts_with($signature, $prefix)) {
                        $shouldExclude = true;
                        break;
                    }
                }
                
                if (!$shouldExclude) {
                    $commands[] = [
                        'signature' => $signature,
                        'description' => $command->getDescription() ?: 'Sem descriÃ§Ã£o',
                    ];
                }
            }
            
            // Ordena por signature
            usort($commands, function($a, $b) {
                return strcmp($a['signature'], $b['signature']);
            });
            
            return response()->json($commands);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar comandos disponÃ­veis', ['error' => $e->getMessage()]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Atualiza configuraÃ§Ãµes de comandos agendados
     */
    public function updateScheduledCommands(Request $request)
    {
        $commands = $this->getScheduledCommands();
        
        foreach ($commands as $command) {
            $key = $command['key'];
            $enabledKey = "command_{$key}_enabled";
            $timeKey = "command_{$key}_time";
            $dayKey = "command_{$key}_day";

            // Atualiza status (habilitado/desabilitado)
            $enabled = $request->has($enabledKey) ? '1' : '0';
            set_sysconfig("commands.{$key}.enabled", $enabled);

            // Atualiza horÃ¡rio se fornecido
            if ($request->filled($timeKey)) {
                $time = $request->input($timeKey);
                // Valida formato HH:MM
                if (preg_match('/^([0-1][0-9]|2[0-3]):[0-5][0-9]$/', $time)) {
                    set_sysconfig("commands.{$key}.time", $time);
                } else {
                    Log::warning("HorÃ¡rio invÃ¡lido para comando {$key}: {$time}");
                }
            }

            // Atualiza dia do mÃªs (para comandos mensais)
            if ($command['frequency'] === 'monthly' && $request->filled($dayKey)) {
                $day = (int) $request->input($dayKey);
                if ($day >= 1 && $day <= 28) {
                    set_sysconfig("commands.{$key}.day", (string) $day);
                } else {
                    Log::warning("Dia invÃ¡lido para comando {$key}: {$day}");
                }
            }
        }

        return back()->with('success', 'ConfiguraÃ§Ãµes de comandos agendados atualizadas com sucesso.');
    }

    /**
     * Adiciona um novo comando customizado Ã  lista de agendados
     */
    public function addScheduledCommand(Request $request)
    {
        $request->validate([
            'command_signature' => 'required|string',
            'command_name' => 'required|string|max:255',
            'command_description' => 'nullable|string|max:500',
            'command_time' => 'required|date_format:H:i',
            'command_frequency' => 'required|in:daily,monthly',
            'command_day' => 'nullable|integer|min:1|max:28',
        ]);

        // Verifica se o comando existe no sistema
        try {
            $artisan = \Illuminate\Support\Facades\Artisan::all();
            if (!isset($artisan[$request->command_signature])) {
                return back()->with('error', 'Comando nÃ£o encontrado no sistema. Verifique se o comando estÃ¡ registrado.');
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao verificar comando: ' . $e->getMessage());
        }

        // Carrega comandos customizados existentes
        $customCommandsJson = sysconfig('commands.custom_list', '[]');
        $customCommands = json_decode($customCommandsJson, true) ?: [];

        // Verifica se o comando jÃ¡ existe (tanto na lista padrÃ£o quanto na customizada)
        $commandKey = $request->command_signature;
        
        // Verifica na lista padrÃ£o
        $defaultCommands = $this->getDefaultCommandsList();
        foreach ($defaultCommands as $cmd) {
            if ($cmd['key'] === $commandKey) {
                return back()->with('error', 'Este comando jÃ¡ estÃ¡ na lista padrÃ£o do sistema e nÃ£o pode ser adicionado novamente.');
            }
        }
        
        // Verifica na lista customizada
        foreach ($customCommands as $cmd) {
            if ($cmd['key'] === $commandKey) {
                return back()->with('error', 'Este comando jÃ¡ estÃ¡ na lista de agendados. Remova o duplicado antes de adicionar novamente.');
            }
        }

        // Adiciona novo comando
        $newCommand = [
            'key' => $commandKey,
            'name' => $request->command_name,
            'description' => $request->command_description ?: 'Comando customizado adicionado pela interface.',
            'default_time' => $request->command_time,
            'frequency' => $request->command_frequency,
        ];

        if ($request->command_frequency === 'monthly') {
            $newCommand['default_day'] = $request->command_day ?? 1;
        }

        $customCommands[] = $newCommand;
        set_sysconfig('commands.custom_list', json_encode($customCommands, JSON_UNESCAPED_UNICODE));

        // Configura valores padrÃ£o
        set_sysconfig("commands.{$commandKey}.enabled", '1');
        set_sysconfig("commands.{$commandKey}.time", $request->command_time);
        if ($request->command_frequency === 'monthly') {
            set_sysconfig("commands.{$commandKey}.day", (string) ($request->command_day ?? 1));
        }

        return back()->with('success', 'Comando adicionado com sucesso!');
    }

    /**
     * Remove um comando customizado da lista
     */
    public function removeScheduledCommand(Request $request, $commandKey)
    {
        // Verifica se Ã© um comando padrÃ£o (nÃ£o pode ser removido)
        $defaultCommands = $this->getDefaultCommandsList();
        $defaultCommandKeys = array_column($defaultCommands, 'key');
        
        if (in_array($commandKey, $defaultCommandKeys)) {
            return back()->with('error', 'Comandos padrÃ£o do sistema nÃ£o podem ser removidos.');
        }

        // Carrega comandos customizados
        $customCommandsJson = sysconfig('commands.custom_list', '[]');
        $customCommands = json_decode($customCommandsJson, true) ?: [];

        // Remove o comando (remove todos os duplicados se houver)
        $customCommands = array_filter($customCommands, function($cmd) use ($commandKey) {
            return $cmd['key'] !== $commandKey;
        });

        // Reindexa array
        $customCommands = array_values($customCommands);
        set_sysconfig('commands.custom_list', json_encode($customCommands, JSON_UNESCAPED_UNICODE));

        // Remove configuraÃ§Ãµes do comando
        \App\Models\Platform\SystemSetting::where('key', 'like', "commands.{$commandKey}.%")->delete();

        return back()->with('success', 'Comando removido com sucesso!');
    }

    /**
     * Remove todos os comandos duplicados da lista customizada
     */
    public function removeDuplicateCommands()
    {
        // Carrega comandos customizados
        $customCommandsJson = sysconfig('commands.custom_list', '[]');
        $customCommands = json_decode($customCommandsJson, true) ?: [];

        // ObtÃ©m lista de comandos padrÃ£o
        $defaultCommands = $this->getDefaultCommandsList();
        $defaultCommandKeys = array_column($defaultCommands, 'key');

        $removedCount = 0;
        $seen = [];

        // Remove duplicados: comandos que jÃ¡ existem na lista padrÃ£o
        $customCommands = array_filter($customCommands, function($cmd) use ($defaultCommandKeys, &$removedCount) {
            if (in_array($cmd['key'], $defaultCommandKeys)) {
                $removedCount++;
                return false;
            }
            return true;
        });

        // Remove duplicados dentro da prÃ³pria lista customizada
        $customCommands = array_filter($customCommands, function($cmd) use (&$seen, &$removedCount) {
            if (in_array($cmd['key'], $seen)) {
                $removedCount++;
                return false; // Duplicado
            }
            $seen[] = $cmd['key'];
            return true;
        });

        // Reindexa array
        $customCommands = array_values($customCommands);
        set_sysconfig('commands.custom_list', json_encode($customCommands, JSON_UNESCAPED_UNICODE));

        if ($removedCount > 0) {
            return back()->with('success', "{$removedCount} comando(s) duplicado(s) removido(s) com sucesso!");
        }

        return back()->with('info', 'Nenhum comando duplicado encontrado.');
    }

    /**
     * Testa conexÃ£o de um serviÃ§o (ASAAS / META / EMAIL / WHATSAPP)
     */
    public function testConnection(Request $request, $service)
    {
        $serviceKey = $this->normalizeService($service);
        if ($serviceKey === 'waha') {
            $this->applyPlatformWahaConfig($request);
            $provider = new WahaProvider();
            $result = $provider->testSession();

            return response()->json([
                'status' => ($result['status'] ?? 'ERROR') === 'OK' ? 'OK' : 'ERROR',
                'message' => $result['message'] ?? 'Falha ao testar sessao WAHA.',
                'data' => $result['data'] ?? [],
                'http_status' => $result['http_status'] ?? null,
            ]);
        }
        if ($serviceKey === 'evolution') {
            $this->applyPlatformEvolutionConfig($request);
            $client = EvolutionClient::fromConfig();

            if (!$client->isConfigured()) {
                return response()->json([
                    'status' => 'ERROR',
                    'message' => 'Evolution API nao esta configurada corretamente. Defina EVOLUTION_BASE_URL e EVOLUTION_API_KEY.',
                ]);
            }

            $result = $client->testConnection();

            if (!empty($result['ok'])) {
                return response()->json([
                    'status' => 'OK',
                    'message' => 'Conexao Evolution API OK! Endpoint de saude respondeu com sucesso.',
                    'data' => $result['body'] ?? [],
                    'http_status' => $result['status'] ?? null,
                ]);
            }

            return response()->json([
                'status' => 'ERROR',
                'message' => $this->extractEvolutionErrorMessage($result['body'] ?? null),
                'data' => $result['body'] ?? [],
                'http_status' => $result['status'] ?? null,
            ]);
        }

        $result = testConnection($serviceKey);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'status' => $result['status'] ? 'OK' : 'ERROR',
                'message' => $result['message'],
            ]);
        }

        return back()->with(
            $result['status'] ? 'success' : 'error',
            $result['message']
        );
    }

    /**
     * Envia mensagem de teste via Meta (WhatsApp Business) usando provider especÃ­fico
     */
    public function testMetaSend(Request $request)
    {
        $validated = $request->validate([
            'number' => 'required|string',
            'message' => 'required|string|max:1000',
        ]);

        try {
            // Instancia diretamente o provider Meta, que lÃª apenas as configs META
            $provider = new WhatsAppBusinessProvider();

            $ok = $provider->sendMessage($validated['number'], $validated['message']);

            if ($ok) {
                return response()->json([
                    'status' => 'OK',
                    'message' => 'Mensagem enviada com sucesso.',
                ]);
            }

            return response()->json([
                'status' => 'ERROR',
                'message' => 'Falha ao enviar mensagem de teste Meta. Verifique as configuraÃ§Ãµes.',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'ERROR',
                'message' => $e->getMessage() ?: 'Erro ao enviar mensagem de teste Meta.',
            ]);
        }
    }

    /**
     * Envia mensagem de teste via Z-API usando provider especÃ­fico
     */
    public function testZapiSend(Request $request)
    {
        $validated = $request->validate([
            'number' => 'required|string',
            'message' => 'required|string|max:1000',
        ]);

        try {
            // Instancia diretamente o provider Z-API, que lÃª apenas as configs Z-API
            $provider = new ZApiProvider();

            $ok = $provider->sendMessage($validated['number'], $validated['message']);

            if ($ok) {
                return response()->json([
                    'status' => 'OK',
                    'message' => 'Mensagem enviada com sucesso.',
                ]);
            }

            return response()->json([
                'status' => 'ERROR',
                'message' => 'Falha ao enviar mensagem de teste Z-API. Verifique as configuraÃ§Ãµes.',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'ERROR',
                'message' => $e->getMessage() ?: 'Erro ao enviar mensagem de teste Z-API.',
            ]);
        }
    }

    /**
     * Envia mensagem de teste via WAHA (apenas diagnÃ³stico na Platform)
     */
    public function testWahaSend(Request $request)
    {
        $validated = $request->validate([
            'number' => 'required|string',
            'message' => 'required|string|max:1000',
        ]);

        try {
            $chatId = WahaClient::formatChatIdFromPhone($validated['number']);
        } catch (\InvalidArgumentException $e) {
            Log::warning('Numero invalido no teste WAHA (platform)', [
                'number' => PhoneNormalizer::maskPhone($validated['number']),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => 'ERROR',
                'message' => 'Telefone invÃ¡lido para WhatsApp. Use DDD + nÃºmero (ex: 67999998888).',
            ]);
        }

        $this->applyPlatformWahaConfig($request);
        $provider = new WahaProvider();
        $sessionCheck = $provider->testSession();
        if (($sessionCheck['status'] ?? 'ERROR') !== 'OK') {
            return response()->json([
                'status' => 'ERROR',
                'message' => $sessionCheck['message'] ?? 'Sessao WAHA nao esta pronta para envio.',
                'data' => $sessionCheck['data'] ?? [],
                'http_status' => $sessionCheck['http_status'] ?? null,
            ]);
        }

        try {
            $client = WahaClient::fromConfig();

            $sendResult = $client->sendText($chatId, $validated['message']);
            $sendBody = $sendResult['body'] ?? null;
            $ok = !empty($sendResult['ok']) && !(is_array($sendBody) && isset($sendBody['error']));

            if ($ok) {
                return response()->json([
                    'status' => 'OK',
                    'message' => 'Mensagem de teste WAHA enviada com sucesso.',
                    'raw' => [
                        'http_status' => $sendResult['status'] ?? null,
                        'body' => $sendBody,
                    ],
                ]);
            }

            return response()->json([
                'status' => 'ERROR',
                'message' => 'Falha ao enviar mensagem WAHA (HTTP ' . ($sendResult['status'] ?? 'erro') . ').',
                'raw' => [
                    'http_status' => $sendResult['status'] ?? null,
                    'body' => $sendBody,
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'ERROR',
                'message' => 'Erro ao enviar mensagem WAHA: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Envia mensagem de teste via Evolution API (apenas diagnostico na Platform)
     */
    public function testEvolutionSend(Request $request)
    {
        $validated = $request->validate([
            'number' => 'required|string',
            'message' => 'required|string|max:1000',
        ]);

        $this->applyPlatformEvolutionConfig($request);

        try {
            $provider = new EvolutionProvider();
            $ok = $provider->sendMessage($validated['number'], $validated['message']);

            if ($ok) {
                return response()->json([
                    'status' => 'OK',
                    'message' => 'Mensagem de teste Evolution enviada com sucesso.',
                ]);
            }

            return response()->json([
                'status' => 'ERROR',
                'message' => 'Falha ao enviar mensagem Evolution. Verifique configuracao e estado da instancia.',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'ERROR',
                'message' => 'Erro ao enviar mensagem Evolution: ' . $e->getMessage(),
            ]);
        }
    }

    private function applyPlatformWahaConfig(?Request $request = null): void
    {
        $resolver = new ProviderConfigResolver();
        $resolvedConfig = $resolver->resolveWahaConfig();
        $overrides = $this->extractWahaRuntimeOverrides($request);

        $runtimeConfig = array_merge($resolvedConfig, $overrides);
        $resolver->applyWahaConfig($runtimeConfig);

        Log::info('WAHA runtime config applied for platform test', [
            'source' => empty($overrides)
                ? ($resolvedConfig['source'] ?? 'global')
                : 'request_overrides',
            'base_url' => $runtimeConfig['base_url'] ?? null,
            'session' => $runtimeConfig['session'] ?? null,
            'api_key_hint' => WahaClient::maskApiKey((string) ($runtimeConfig['api_key'] ?? '')),
        ]);
    }

    private function applyPlatformEvolutionConfig(?Request $request = null): void
    {
        $resolver = new ProviderConfigResolver();
        $resolvedConfig = $resolver->resolveEvolutionConfig();
        $overrides = $this->extractEvolutionRuntimeOverrides($request);
        $runtimeConfig = array_merge($resolvedConfig, $overrides);
        $resolver->applyEvolutionConfig($runtimeConfig);

        Log::info('Evolution runtime config applied for platform test', [
            'source' => empty($overrides)
                ? ($resolvedConfig['source'] ?? 'global')
                : 'request_overrides',
            'base_url' => $runtimeConfig['base_url'] ?? null,
            'instance' => $runtimeConfig['instance'] ?? null,
            'api_key_hint' => EvolutionClient::maskApiKey((string) ($runtimeConfig['api_key'] ?? '')),
        ]);
    }

    private function extractWahaRuntimeOverrides(?Request $request = null): array
    {
        if (!$request) {
            return [];
        }

        $baseUrl = $this->extractWahaValue($request, ['WAHA_BASE_URL', 'waha_base_url', 'base_url']);
        $apiKey = $this->extractWahaValue($request, ['WAHA_API_KEY', 'waha_api_key', 'api_key']);
        $session = $this->extractWahaValue($request, ['WAHA_SESSION', 'waha_session', 'session']);

        $overrides = [];
        if ($baseUrl !== '') {
            $overrides['base_url'] = rtrim($baseUrl, '/');
        }

        if ($apiKey !== '') {
            $overrides['api_key'] = $apiKey;
        }

        if ($session !== '') {
            $overrides['session'] = $session;
        }

        return $overrides;
    }

    private function extractEvolutionRuntimeOverrides(?Request $request = null): array
    {
        if (!$request) {
            return [];
        }

        $baseUrl = $this->extractEvolutionValue($request, ['EVOLUTION_BASE_URL', 'evolution_base_url', 'base_url']);
        $apiKey = $this->extractEvolutionValue($request, ['EVOLUTION_API_KEY', 'EVOLUTION_KEY', 'evolution_api_key', 'api_key']);
        $instance = $this->extractEvolutionValue($request, ['EVOLUTION_INSTANCE', 'evolution_instance', 'instance']);

        $overrides = [];
        if ($baseUrl !== '') {
            $overrides['base_url'] = rtrim($baseUrl, '/');
        }

        if ($apiKey !== '') {
            $overrides['api_key'] = $apiKey;
        }

        if ($instance !== '') {
            $overrides['instance'] = $instance;
        }

        return $overrides;
    }

    private function extractWahaValue(Request $request, array $keys): string
    {
        foreach ($keys as $key) {
            $value = trim((string) $request->input($key, ''));
            if ($value !== '') {
                return $value;
            }
        }

        return '';
    }

    private function extractEvolutionValue(Request $request, array $keys): string
    {
        foreach ($keys as $key) {
            $value = trim((string) $request->input($key, ''));
            if ($value !== '') {
                return $value;
            }
        }

        return '';
    }

    private function extractEvolutionErrorMessage(mixed $body): string
    {
        if (!is_array($body)) {
            return 'Falha ao testar conexao Evolution API.';
        }

        $message = trim((string) (
            $body['message']
            ?? $body['error']
            ?? data_get($body, 'response.message')
            ?? data_get($body, 'response.error')
            ?? ''
        ));

        if ($message !== '') {
            return $message;
        }

        return 'Falha ao testar conexao Evolution API.';
    }

    private function normalizeService(string $service): string
    {
        $normalized = strtolower(trim($service));
        $aliases = [
            'whatsapp_business' => 'meta',
            'whatsapp-business' => 'meta',
            'business' => 'meta',
            'z-api' => 'zapi',
            'z_api' => 'zapi',
            'waha_core' => 'waha',
            'waha-core' => 'waha',
            'whatsapp_waha' => 'waha',
            'whatsapp-waha' => 'waha',
            'evolution_api' => 'evolution',
            'evolution-api' => 'evolution',
            'evo_api' => 'evolution',
            'evo-api' => 'evolution',
            'whatsapp_evolution' => 'evolution',
            'whatsapp-evolution' => 'evolution',
        ];

        return $aliases[$normalized] ?? $normalized;
    }
}

