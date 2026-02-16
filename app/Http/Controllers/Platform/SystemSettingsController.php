<?php

namespace App\Http\Controllers\Platform;

use App\Models\Platform\Pais; // ✅ importante

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\WhatsApp\WhatsAppBusinessProvider;
use App\Services\WhatsApp\ZApiProvider;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

class SystemSettingsController extends Controller
{
    /**
     * Exibe a página de configurações.
     */
    public function index()
    {
        $paises = Pais::orderBy('nome')->get();

        $settings = [
            'timezone' => sysconfig('timezone', 'America/Sao_Paulo'),
            'country_id' => sysconfig('country_id'), // armazenará o id_pais
            'language' => sysconfig('language', 'pt_BR'),
            // demais integrações
            'ASAAS_API_URL' => sysconfig('ASAAS_API_URL', env('ASAAS_API_URL')),
            'ASAAS_API_KEY' => sysconfig('ASAAS_API_KEY', env('ASAAS_API_KEY')),
            'ASAAS_ENV' => sysconfig('ASAAS_ENV', env('ASAAS_ENV', 'sandbox')),
            'META_ACCESS_TOKEN' => sysconfig('META_ACCESS_TOKEN', env('META_ACCESS_TOKEN')),
            'META_PHONE_NUMBER_ID' => sysconfig('META_PHONE_NUMBER_ID', env('META_PHONE_NUMBER_ID')),
            'WHATSAPP_PROVIDER' => sysconfig('WHATSAPP_PROVIDER', env('WHATSAPP_PROVIDER', 'whatsapp_business')),
            'ZAPI_API_URL' => sysconfig('ZAPI_API_URL', env('ZAPI_API_URL', 'https://api.z-api.io')),
            'ZAPI_TOKEN' => sysconfig('ZAPI_TOKEN', env('ZAPI_TOKEN')),
            'ZAPI_CLIENT_TOKEN' => sysconfig('ZAPI_CLIENT_TOKEN', env('ZAPI_CLIENT_TOKEN')),
            'ZAPI_INSTANCE_ID' => sysconfig('ZAPI_INSTANCE_ID', env('ZAPI_INSTANCE_ID')),
            'WAHA_BASE_URL' => sysconfig('WAHA_BASE_URL', env('WAHA_BASE_URL')),
            'WAHA_API_KEY' => sysconfig('WAHA_API_KEY', env('WAHA_API_KEY')),
            'WAHA_SESSION' => sysconfig('WAHA_SESSION', env('WAHA_SESSION', 'default')),
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
            // Configurações de Billing
            'billing.invoice_days_before_due' => sysconfig('billing.invoice_days_before_due', 10),
            'billing.notify_days_before_due' => sysconfig('billing.notify_days_before_due', 5),
            'billing.recovery_days_after_suspension' => sysconfig('billing.recovery_days_after_suspension', 5),
            'billing.purge_days_after_cancellation' => sysconfig('billing.purge_days_after_cancellation', 90),
            // Configurações de Notificações
            'notifications.enabled' => sysconfig('notifications.enabled', '1') === '1',
            'notifications.update_interval' => (int) sysconfig('notifications.update_interval', 5),
            'notifications.display_count' => (int) sysconfig('notifications.display_count', 5),
            'notifications.show_badge' => sysconfig('notifications.show_badge', '1') === '1',
            'notifications.sound_enabled' => sysconfig('notifications.sound_enabled', '0') === '1',
            // Tipos de eventos para notificações
            'notifications.types.payment' => sysconfig('notifications.types.payment', '1') === '1',
            'notifications.types.invoice' => sysconfig('notifications.types.invoice', '1') === '1',
            'notifications.types.subscription' => sysconfig('notifications.types.subscription', '1') === '1',
            'notifications.types.tenant' => sysconfig('notifications.types.tenant', '1') === '1',
            'notifications.types.command' => sysconfig('notifications.types.command', '1') === '1',
            'notifications.types.webhook' => sysconfig('notifications.types.webhook', '0') === '1',
        ];

        // Comandos agendados
        $scheduledCommands = $this->getScheduledCommands();

        return view('platform.settings.index', compact('settings', 'paises', 'scheduledCommands'));
    }

    /**
     * Atualiza as configurações gerais (timezone, país, idioma)
     */
    public function updateGeneral(Request $request)
    {
        $request->validate([
            'timezone' => 'required|string',
            'country_id' => 'nullable|integer|exists:paises,id_pais',
            'language' => 'required|string',
        ]);

        set_sysconfig('timezone', $request->timezone);
        set_sysconfig('country_id', $request->country_id);
        set_sysconfig('language', $request->language);

        return back()->with('success', 'Configurações gerais atualizadas com sucesso.');
    }

    /**
     * Atualiza integrações ASAAS / Meta / Z-API / Email
     */
    public function updateIntegrations(Request $request)
    {
        $request->validate([
            'ASAAS_API_KEY' => 'nullable|string',
            'ASAAS_ENV' => 'nullable|string',
            'META_ACCESS_TOKEN' => 'nullable|string',
            'META_PHONE_NUMBER_ID' => 'nullable|string',
            'WHATSAPP_PROVIDER' => 'nullable|string|in:whatsapp_business,zapi,waha',
            'ZAPI_API_URL' => 'nullable|string|url',
            'ZAPI_TOKEN' => 'nullable|string',
            'ZAPI_CLIENT_TOKEN' => 'nullable|string',
            'ZAPI_INSTANCE_ID' => 'nullable|string',
            'WAHA_BASE_URL' => 'nullable|string|url',
            'WAHA_API_KEY' => 'nullable|string',
            'WAHA_SESSION' => 'nullable|string',
            'MAIL_HOST' => 'nullable|string',
            'MAIL_PORT' => 'nullable|string',
            'MAIL_USERNAME' => 'nullable|string',
            'MAIL_PASSWORD' => 'nullable|string',
            'MAIL_FROM_ADDRESS' => 'nullable|string',
            'MAIL_FROM_NAME' => 'nullable|string',
        ]);

        // Lista de campos que serão atualizados
        $fields = [
            'ASAAS_API_KEY',
            'ASAAS_ENV',
            'META_ACCESS_TOKEN',
            'META_PHONE_NUMBER_ID',
            'WHATSAPP_PROVIDER',
            'ZAPI_API_URL',
            'ZAPI_TOKEN',
            'ZAPI_CLIENT_TOKEN',
            'ZAPI_INSTANCE_ID',
            'WAHA_BASE_URL',
            'WAHA_API_KEY',
            'WAHA_SESSION',
            'MAIL_HOST',
            'MAIL_PORT',
            'MAIL_USERNAME',
            'MAIL_PASSWORD',
            'MAIL_FROM_ADDRESS',
            'MAIL_FROM_NAME',
        ];

        // Atualiza configurações no banco
        foreach ($fields as $field) {
            if ($request->filled($field)) {
                set_sysconfig($field, $request->$field);
            }
        }

        // Atualiza o .env
        updateEnv($request->only($fields));

        return back()->with('success', 'Integrações e configurações de e-mail atualizadas com sucesso.');
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
            // Processar logo padrão do sistema
            if ($request->hasFile('system_default_logo')) {
                $path = $request->file('system_default_logo')->store('platform/system-logos', 'public');
                Log::info('Logo padrão do sistema salva', ['path' => $path]);
                set_sysconfig('system.default_logo', $path);
            } elseif ($request->input('remove_system_default_logo') == '1') {
                $oldLogo = sysconfig('system.default_logo');
                if ($oldLogo && Storage::disk('public')->exists($oldLogo)) {
                    Storage::disk('public')->delete($oldLogo);
                }
                set_sysconfig('system.default_logo', null);
            }

            // Processar favicon padrão do sistema
            if ($request->hasFile('system_default_favicon')) {
                $path = $request->file('system_default_favicon')->store('platform/system-favicons', 'public');
                Log::info('Favicon padrão do sistema salvo', ['path' => $path]);
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
                Log::info('Configuração salva', ['key' => 'platform.logo', 'value' => $path]);
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

            // Processar logo padrão para tenants
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

            // Processar logo retrátil padrão para tenants
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

            // Processar favicon padrão para tenants
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

            // Limpar cache de configurações e views
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
     * Atualiza configurações de billing
     */
    public function updateBilling(Request $request)
    {
        $request->validate([
            'billing_invoice_days_before_due' => 'nullable|integer|min:1|max:30',
            'billing_notify_days_before_due' => 'nullable|integer|min:1|max:30',
            'billing_recovery_days_after_suspension' => 'nullable|integer|min:1|max:30',
            'billing_purge_days_after_cancellation' => 'nullable|integer|min:30|max:365',
        ]);

        // Atualiza configurações de billing
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

        return back()->with('success', 'Configurações de billing atualizadas com sucesso.');
    }

    /**
     * Atualiza configurações de notificações
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

        // Atualiza configurações de notificações
        set_sysconfig('notifications.enabled', $request->has('notifications_enabled') ? '1' : '0');
        set_sysconfig('notifications.update_interval', (string) $request->input('notifications_update_interval'));
        set_sysconfig('notifications.display_count', (string) $request->input('notifications_display_count'));
        set_sysconfig('notifications.show_badge', $request->has('notifications_show_badge') ? '1' : '0');
        set_sysconfig('notifications.sound_enabled', $request->has('notifications_sound_enabled') ? '1' : '0');
        
        // Atualiza configurações de tipos de eventos
        set_sysconfig('notifications.types.payment', $request->has('notify_payment') ? '1' : '0');
        set_sysconfig('notifications.types.invoice', $request->has('notify_invoice') ? '1' : '0');
        set_sysconfig('notifications.types.subscription', $request->has('notify_subscription') ? '1' : '0');
        set_sysconfig('notifications.types.tenant', $request->has('notify_tenant') ? '1' : '0');
        set_sysconfig('notifications.types.command', $request->has('notify_command') ? '1' : '0');
        set_sysconfig('notifications.types.webhook', $request->has('notify_webhook') ? '1' : '0');

        return back()->with('success', 'Configurações de notificações atualizadas com sucesso.');
    }

    /**
     * Retorna lista de comandos padrão do sistema
     */
    private function getDefaultCommandsList(): array
    {
        return [
            [
                'key' => 'subscriptions:subscriptions-process',
                'name' => 'Processamento de Assinaturas',
                'description' => 'Gera faturas automáticas de assinaturas vencidas e renova os períodos.',
                'default_time' => '01:00',
                'frequency' => 'daily',
            ],
            [
                'key' => 'invoices:generate',
                'name' => 'Geração Automática de Faturas',
                'description' => 'Gera faturas automaticamente X dias antes do vencimento (PIX/Boleto).',
                'default_time' => '01:30',
                'frequency' => 'daily',
            ],
            [
                'key' => 'invoices:notify-upcoming',
                'name' => 'Notificações de Faturas Próximas',
                'description' => 'Envia notificações Y dias antes do vencimento (exclui faturas de cartão).',
                'default_time' => '01:45',
                'frequency' => 'daily',
            ],
            [
                'key' => 'invoices:invoices-check-overdue',
                'name' => 'Verificação de Faturas Vencidas',
                'description' => 'Marca faturas vencidas e suspende tenants imediatamente (sem período de carência).',
                'default_time' => '02:00',
                'frequency' => 'daily',
            ],
            [
                'key' => 'subscriptions:process-recovery',
                'name' => 'Processamento de Recovery',
                'description' => 'Inicia processo de recovery para assinaturas de cartão suspensas ≥ 5 dias.',
                'default_time' => '02:30',
                'frequency' => 'daily',
            ],
            [
                'key' => 'tenants:purge-canceled',
                'name' => 'Purga de Tenants Cancelados',
                'description' => 'Remove dados e banco de tenants cancelados há X dias (padrão: 90 dias).',
                'default_time' => '03:00',
                'frequency' => 'daily',
            ],
            [
                'key' => 'recurring-appointments:process',
                'name' => 'Processamento de Agendamentos Recorrentes',
                'description' => 'Processa agendamentos recorrentes e gera sessões automaticamente.',
                'default_time' => '03:00',
                'frequency' => 'daily',
            ],
            [
                'key' => 'google-calendar:renew-recurring-events',
                'name' => 'Renovação de Eventos Recorrentes (Google Calendar)',
                'description' => 'Renova eventos recorrentes no Google Calendar que estão próximos do fim.',
                'default_time' => '04:00',
                'default_day' => 1,
                'frequency' => 'monthly',
            ],
            [
                'key' => 'appointments:notify-upcoming',
                'name' => 'Lembretes de Agendamentos Próximos',
                'description' => 'Envia lembretes automáticos aos pacientes sobre agendamentos próximos (email/WhatsApp).',
                'default_time' => '08:00',
                'frequency' => 'daily',
            ],
        ];
    }

    /**
     * Retorna lista de comandos agendados com suas configurações
     */
    private function getScheduledCommands(): array
    {
        // Comandos padrão do sistema (hardcoded)
        $defaultCommands = $this->getDefaultCommandsList();

        // Carrega comandos customizados do banco (adicionados pela interface)
        $customCommandsJson = sysconfig('commands.custom_list', '[]');
        $customCommands = json_decode($customCommandsJson, true) ?: [];

        // Remove duplicados da lista customizada (comandos que já existem na lista padrão)
        $defaultCommandKeys = array_column($defaultCommands, 'key');
        $customCommands = array_filter($customCommands, function($cmd) use ($defaultCommandKeys) {
            return !in_array($cmd['key'], $defaultCommandKeys);
        });
        $customCommands = array_values($customCommands); // Reindexa

        // Se houve duplicados removidos, salva a lista limpa
        if (count($customCommands) !== count(json_decode($customCommandsJson, true) ?: [])) {
            set_sysconfig('commands.custom_list', json_encode($customCommands, JSON_UNESCAPED_UNICODE));
        }

        // Remove duplicados dentro da própria lista customizada (caso existam)
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

        // Merge: comandos padrão + comandos customizados
        $allCommands = array_merge($defaultCommands, $customCommands);

        // Carrega configurações do banco para cada comando
        foreach ($allCommands as &$command) {
            $command['enabled'] = sysconfig("commands.{$command['key']}.enabled", '1') === '1';
            $command['time'] = sysconfig("commands.{$command['key']}.time", $command['default_time']);
            if (isset($command['default_day'])) {
                $command['day'] = (int) sysconfig("commands.{$command['key']}.day", $command['default_day']);
            }
            // Marca se é customizado (não pode ser removido da lista padrão)
            $command['is_custom'] = !in_array($command['key'], array_column($defaultCommands, 'key'));
        }

        return $allCommands;
    }

    /**
     * Retorna lista de comandos disponíveis no sistema (para adicionar novos)
     */
    public function getAvailableCommands()
    {
        try {
            $artisan = \Illuminate\Support\Facades\Artisan::all();
            $commands = [];
            
            // Lista de comandos padrão do Laravel que não devem aparecer
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
                        'description' => $command->getDescription() ?: 'Sem descrição',
                    ];
                }
            }
            
            // Ordena por signature
            usort($commands, function($a, $b) {
                return strcmp($a['signature'], $b['signature']);
            });
            
            return response()->json($commands);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar comandos disponíveis', ['error' => $e->getMessage()]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Atualiza configurações de comandos agendados
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

            // Atualiza horário se fornecido
            if ($request->filled($timeKey)) {
                $time = $request->input($timeKey);
                // Valida formato HH:MM
                if (preg_match('/^([0-1][0-9]|2[0-3]):[0-5][0-9]$/', $time)) {
                    set_sysconfig("commands.{$key}.time", $time);
                } else {
                    Log::warning("Horário inválido para comando {$key}: {$time}");
                }
            }

            // Atualiza dia do mês (para comandos mensais)
            if ($command['frequency'] === 'monthly' && $request->filled($dayKey)) {
                $day = (int) $request->input($dayKey);
                if ($day >= 1 && $day <= 28) {
                    set_sysconfig("commands.{$key}.day", (string) $day);
                } else {
                    Log::warning("Dia inválido para comando {$key}: {$day}");
                }
            }
        }

        return back()->with('success', 'Configurações de comandos agendados atualizadas com sucesso.');
    }

    /**
     * Adiciona um novo comando customizado à lista de agendados
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
                return back()->with('error', 'Comando não encontrado no sistema. Verifique se o comando está registrado.');
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao verificar comando: ' . $e->getMessage());
        }

        // Carrega comandos customizados existentes
        $customCommandsJson = sysconfig('commands.custom_list', '[]');
        $customCommands = json_decode($customCommandsJson, true) ?: [];

        // Verifica se o comando já existe (tanto na lista padrão quanto na customizada)
        $commandKey = $request->command_signature;
        
        // Verifica na lista padrão
        $defaultCommands = $this->getDefaultCommandsList();
        foreach ($defaultCommands as $cmd) {
            if ($cmd['key'] === $commandKey) {
                return back()->with('error', 'Este comando já está na lista padrão do sistema e não pode ser adicionado novamente.');
            }
        }
        
        // Verifica na lista customizada
        foreach ($customCommands as $cmd) {
            if ($cmd['key'] === $commandKey) {
                return back()->with('error', 'Este comando já está na lista de agendados. Remova o duplicado antes de adicionar novamente.');
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

        // Configura valores padrão
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
        // Verifica se é um comando padrão (não pode ser removido)
        $defaultCommands = $this->getDefaultCommandsList();
        $defaultCommandKeys = array_column($defaultCommands, 'key');
        
        if (in_array($commandKey, $defaultCommandKeys)) {
            return back()->with('error', 'Comandos padrão do sistema não podem ser removidos.');
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

        // Remove configurações do comando
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

        // Obtém lista de comandos padrão
        $defaultCommands = $this->getDefaultCommandsList();
        $defaultCommandKeys = array_column($defaultCommands, 'key');

        $removedCount = 0;
        $seen = [];

        // Remove duplicados: comandos que já existem na lista padrão
        $customCommands = array_filter($customCommands, function($cmd) use ($defaultCommandKeys, &$removedCount) {
            if (in_array($cmd['key'], $defaultCommandKeys)) {
                $removedCount++;
                return false;
            }
            return true;
        });

        // Remove duplicados dentro da própria lista customizada
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
     * Testa conexão de um serviço (ASAAS / META / EMAIL / WHATSAPP)
     */
    public function testConnection(Request $request, $service)
    {
        $result = testConnection($service);

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
     * Envia mensagem de teste via Meta (WhatsApp Business) usando provider específico
     */
    public function testMetaSend(Request $request)
    {
        $validated = $request->validate([
            'number' => 'required|string',
            'message' => 'required|string|max:1000',
        ]);

        try {
            // Instancia diretamente o provider Meta, que lê apenas as configs META
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
                'message' => 'Falha ao enviar mensagem de teste Meta. Verifique as configurações.',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'ERROR',
                'message' => $e->getMessage() ?: 'Erro ao enviar mensagem de teste Meta.',
            ]);
        }
    }

    /**
     * Envia mensagem de teste via Z-API usando provider específico
     */
    public function testZapiSend(Request $request)
    {
        $validated = $request->validate([
            'number' => 'required|string',
            'message' => 'required|string|max:1000',
        ]);

        try {
            // Instancia diretamente o provider Z-API, que lê apenas as configs Z-API
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
                'message' => 'Falha ao enviar mensagem de teste Z-API. Verifique as configurações.',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'ERROR',
                'message' => $e->getMessage() ?: 'Erro ao enviar mensagem de teste Z-API.',
            ]);
        }
    }

    /**
     * Envia mensagem de teste via WAHA (apenas diagnóstico na Platform)
     */
    public function testWahaSend(Request $request)
    {
        $validated = $request->validate([
            'number' => 'required|string',
            'message' => 'required|string|max:1000',
        ]);

        $baseUrl = config('services.whatsapp.waha.base_url');
        $apiKey  = config('services.whatsapp.waha.api_key');
        $session = config('services.whatsapp.waha.session', 'default');

        if (empty($baseUrl) || empty($apiKey) || empty($session)) {
            return response()->json([
                'status' => 'ERROR',
                'message' => 'WAHA não está configurado corretamente.',
            ]);
        }

        $sessionEndpoint = rtrim($baseUrl, '/') . '/api/sessions/' . urlencode($session);

        try {
            $verifyOption = app()->environment('local') ? false : true;

            // 1) Valida sessão
            $sessionResponse = \Illuminate\Support\Facades\Http::timeout(8)
                ->withOptions(['verify' => $verifyOption])
                ->withHeaders([
                    'X-Api-Key' => $apiKey,
                ])->get($sessionEndpoint);

            $sessionData = $sessionResponse->json();

            if (!$sessionResponse->successful() || !isset($sessionData['status']) || $sessionData['status'] !== 'WORKING') {
                return response()->json([
                    'status' => 'ERROR',
                    'message' => 'Sessão WAHA não está WORKING.',
                    'raw' => [
                        'http_status' => $sessionResponse->status(),
                        'body' => $sessionData,
                    ],
                ]);
            }

            // 2) Envia mensagem de teste
            $sendEndpoint = rtrim($baseUrl, '/') . '/api/sendText';

            $chatId = $validated['number'];
            if (!str_contains($chatId, '@')) {
                $chatId .= '@c.us';
            }

            $payload = [
                'session' => $session,
                'chatId' => $chatId,
                'text' => $validated['message'],
            ];

            $sendResponse = \Illuminate\Support\Facades\Http::timeout(8)
                ->withOptions(['verify' => $verifyOption])
                ->withHeaders([
                    'X-Api-Key' => $apiKey,
                ])->post($sendEndpoint, $payload);

            $sendBody = $sendResponse->json();

            if ($sendResponse->successful()) {
                return response()->json([
                    'status' => 'OK',
                    'message' => 'Mensagem de teste WAHA enviada com sucesso.',
                    'raw' => [
                        'http_status' => $sendResponse->status(),
                        'body' => $sendBody,
                    ],
                ]);
            }

            return response()->json([
                'status' => 'ERROR',
                'message' => 'Falha ao enviar mensagem WAHA (HTTP ' . $sendResponse->status() . ').',
                'raw' => [
                    'http_status' => $sendResponse->status(),
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
}
