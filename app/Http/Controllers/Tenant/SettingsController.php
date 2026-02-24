<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\TenantSetting;
use App\Models\Tenant\Integrations;
use App\Models\Platform\Tenant;
use App\Models\Platform\Pais;
use App\Models\Platform\Estado;
use App\Models\Platform\Cidade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class SettingsController extends Controller
{
    /**
     * Exibe a página de configurações
     */
    public function index()
    {
        $appearanceLogoLight = TenantSetting::get('appearance.logo_light', TenantSetting::get('appearance.logo', ''));
        $appearanceLogoDark = TenantSetting::get('appearance.logo_dark', '');
        $appearanceLogoMiniLight = TenantSetting::get('appearance.logo_mini_light', TenantSetting::get('appearance.logo_mini', ''));
        $appearanceLogoMiniDark = TenantSetting::get('appearance.logo_mini_dark', '');
        $appearanceFavicon = TenantSetting::get('appearance.favicon', '');

        $settings = [
            // Geral
            'timezone' => TenantSetting::get('timezone', config('app.timezone', 'America/Sao_Paulo')),
            'date_format' => TenantSetting::get('date_format', 'd/m/Y'),
            'time_format' => TenantSetting::get('time_format', 'H:i'),
            'language' => TenantSetting::get('language', 'pt_BR'),
            
            // Agendamentos
            'appointments.default_duration' => TenantSetting::get('appointments.default_duration', '30'),
            'appointments.interval_between' => TenantSetting::get('appointments.interval_between', '0'),
            'appointments.auto_confirm' => TenantSetting::isEnabled('appointments.auto_confirm'),
            'appointments.allow_cancellation' => TenantSetting::isEnabled('appointments.allow_cancellation'),
            'appointments.cancellation_hours' => TenantSetting::get('appointments.cancellation_hours', '24'),
            'appointments.reminder_hours' => TenantSetting::get('appointments.reminder_hours', '24'),
            'appointments.default_appointment_mode' => TenantSetting::get('appointments.default_appointment_mode', 'user_choice'),
            
            // Calendário
            'calendar.default_start_time' => TenantSetting::get('calendar.default_start_time', '08:00'),
            'calendar.default_end_time' => TenantSetting::get('calendar.default_end_time', '18:00'),
            'calendar.default_weekdays' => TenantSetting::get('calendar.default_weekdays', '1,2,3,4,5'), // Segunda a Sexta
            'calendar.show_weekends' => TenantSetting::isEnabled('calendar.show_weekends'),
            
            // Notificações
            // Verifica explicitamente se o valor é 'true' para garantir que desabilitados retornem false
            'notifications.appointments.enabled' => TenantSetting::get('notifications.appointments.enabled') === 'true',
            'notifications.form_responses.enabled' => TenantSetting::get('notifications.form_responses.enabled') === 'true',
            // Para notificações aos pacientes, verifica explicitamente se é 'true' (opt-in)
            'notifications.send_email_to_patients' => TenantSetting::get('notifications.send_email_to_patients') === 'true',
            'notifications.send_whatsapp_to_patients' => TenantSetting::get('notifications.send_whatsapp_to_patients') === 'true',
            
            // Email
            'email.driver' => TenantSetting::get('email.driver', 'global'),
            'email.host' => TenantSetting::get('email.host', ''),
            'email.port' => TenantSetting::get('email.port', ''),
            'email.username' => TenantSetting::get('email.username', ''),
            'email.password' => TenantSetting::get('email.password', ''),
            'email.from_name' => TenantSetting::get('email.from_name', ''),
            'email.from_address' => TenantSetting::get('email.from_address', ''),
            
            // WhatsApp
            'whatsapp.driver' => TenantSetting::get('whatsapp.driver', 'global'),
            'WHATSAPP_PROVIDER' => TenantSetting::get('whatsapp.provider', 'whatsapp_business'),
            'META_ACCESS_TOKEN' => TenantSetting::get('whatsapp.meta.access_token', ''),
            'META_PHONE_NUMBER_ID' => TenantSetting::get('whatsapp.meta.phone_number_id', ''),
            'ZAPI_API_URL' => TenantSetting::get('whatsapp.zapi.api_url', 'https://api.z-api.io'),
            'ZAPI_TOKEN' => TenantSetting::get('whatsapp.zapi.token', ''),
            'ZAPI_CLIENT_TOKEN' => TenantSetting::get('whatsapp.zapi.client_token', ''),
            'ZAPI_INSTANCE_ID' => TenantSetting::get('whatsapp.zapi.instance_id', ''),
            'WAHA_BASE_URL' => TenantSetting::get('whatsapp.waha.base_url', ''),
            'WAHA_API_KEY' => TenantSetting::get('whatsapp.waha.api_key', ''),
            'WAHA_SESSION' => TenantSetting::get('whatsapp.waha.session', 'default'),
            
            // Integrações
            'integrations.google_calendar.enabled' => TenantSetting::isEnabled('integrations.google_calendar.enabled'),
            'integrations.google_calendar.auto_sync' => TenantSetting::isEnabled('integrations.google_calendar.auto_sync'),
            'integrations.apple_calendar.enabled' => TenantSetting::isEnabled('integrations.apple_calendar.enabled'),
            'integrations.apple_calendar.auto_sync' => TenantSetting::isEnabled('integrations.apple_calendar.auto_sync'),
            
            // Profissionais
            'professional.customization_enabled' => TenantSetting::get('professional.customization_enabled') === 'true',
            'professional.label_singular' => TenantSetting::get('professional.label_singular', ''),
            'professional.label_plural' => TenantSetting::get('professional.label_plural', ''),
            'professional.registration_label' => TenantSetting::get('professional.registration_label', ''),
            
            // Aparência
            'appearance.logo' => $appearanceLogoLight,
            'appearance.logo_mini' => $appearanceLogoMiniLight,
            'appearance.logo_light' => $appearanceLogoLight,
            'appearance.logo_dark' => $appearanceLogoDark,
            'appearance.logo_mini_light' => $appearanceLogoMiniLight,
            'appearance.logo_mini_dark' => $appearanceLogoMiniDark,
            'appearance.favicon' => $appearanceFavicon,
        ];

        // Buscar integrações ativas
        $integrations = Integrations::where('is_enabled', true)->get();
        
        // Verificar se Google Calendar está cadastrado e configurado
        $googleCalendarIntegration = Integrations::where('key', 'google_calendar')->first();
        $appleCalendarIntegration = Integrations::where('key', 'apple_calendar')->first();
        
        // Considera válida se existe, está habilitada e tem config não vazio
        $hasGoogleCalendarIntegration = false;
        
        if ($googleCalendarIntegration) {
            $hasConfig = false;
            if ($googleCalendarIntegration->config) {
                $config = $googleCalendarIntegration->config;
                if (is_array($config)) {
                    $hasConfig = !empty($config);
                } elseif (is_string($config)) {
                    $hasConfig = !empty(trim($config));
                } else {
                    $hasConfig = !empty($config);
                }
            }
            
            $hasGoogleCalendarIntegration = $googleCalendarIntegration->is_enabled && $hasConfig;
        }

        // Considera válida se existe, está habilitada e tem config não vazio
        $hasAppleCalendarIntegration = false;

        if ($appleCalendarIntegration) {
            $hasConfig = false;
            if ($appleCalendarIntegration->config) {
                $config = $appleCalendarIntegration->config;
                if (is_array($config)) {
                    $hasConfig = !empty($config);
                } elseif (is_string($config)) {
                    $hasConfig = !empty(trim($config));
                } else {
                    $hasConfig = !empty($config);
                }
            }

            $hasAppleCalendarIntegration = $appleCalendarIntegration->is_enabled && $hasConfig;
        }

        // Obter tenant atual para gerar o link de agendamento público
        $currentTenant = Tenant::current();
        $publicBookingUrl = null;
        
        if ($currentTenant) {
            $publicBookingUrl = url('/customer/' . $currentTenant->subdomain . '/agendamento/identificar');
        }

        $localizacao = $currentTenant ? $currentTenant->localizacao : null;
        $brazilId = Pais::where('nome', 'Brasil')->first()->id_pais ?? 31;

        return view('tenant.settings.index', compact(
            'settings', 
            'integrations', 
            'hasGoogleCalendarIntegration', 
            'googleCalendarIntegration', 
            'hasAppleCalendarIntegration',
            'appleCalendarIntegration',
            'publicBookingUrl',
            'currentTenant',
            'localizacao',
            'brazilId'
        ));
    }

    /**
     * Atualiza as informações de cadastro do tenant
     */
    public function updateRegistration(Request $request)
    {
        $tenant = Tenant::current();

        $request->validate([
            'legal_name' => 'required|string|max:255',
            'trade_name' => 'nullable|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'endereco' => 'required|string|max:255',
            'n_endereco' => 'required|string|max:20',
            'complemento' => 'nullable|string|max:255',
            'bairro' => 'required|string|max:255',
            'cep' => 'required|string|max:20',
            'estado_id' => 'required|integer',
            'cidade_id' => 'required|integer',
        ]);

        $tenant->update([
            'legal_name' => $request->legal_name,
            'trade_name' => $request->trade_name,
            'email' => $request->email,
            'phone' => $request->phone,
        ]);

        $tenant->localizacao()->updateOrCreate(['tenant_id' => $tenant->id], [
            'endereco' => $request->endereco,
            'n_endereco' => $request->n_endereco,
            'complemento' => $request->complemento,
            'bairro' => $request->bairro,
            'cep' => $request->cep,
            'pais_id' => 31, // Brasil fixo
            'estado_id' => $request->estado_id,
            'cidade_id' => $request->cidade_id,
        ]);

        return redirect()->to(route('tenant.settings.index', ['slug' => tenant()->subdomain]) . '#registration')
            ->with('success', 'Informações de cadastro atualizadas com sucesso.');
    }

    /**
     * Exibe a página dedicada do link de agendamento público
     * Esta página não requer acesso ao módulo de configurações
     */
    public function publicBookingLink()
    {
        // Obter tenant atual para gerar o link de agendamento público
        $currentTenant = Tenant::current();
        $publicBookingUrl = null;
        
        if ($currentTenant) {
            $publicBookingUrl = url('/customer/' . $currentTenant->subdomain . '/agendamento/identificar');
        }

        return view('tenant.settings.public-booking-link', compact('publicBookingUrl'));
    }

    /**
     * Atualiza as configurações gerais
     */
    public function updateGeneral(Request $request)
    {
        $request->validate([
            'timezone' => 'required|string',
            'date_format' => 'required|string|in:d/m/Y,Y-m-d,m/d/Y',
            'time_format' => 'required|string|in:H:i,h:i A',
            'language' => 'required|string|in:pt_BR,en_US,es_ES',
        ]);

        TenantSetting::set('timezone', $request->timezone);
        TenantSetting::set('date_format', $request->date_format);
        TenantSetting::set('time_format', $request->time_format);
        TenantSetting::set('language', $request->language);

        return redirect()->route('tenant.settings.index', ['slug' => tenant()->subdomain])
            ->with('success', 'Configurações gerais atualizadas com sucesso.');
    }

    /**
     * Atualiza as configurações de agendamentos
     */
    public function updateAppointments(Request $request)
    {
        $request->validate([
            'appointments_default_duration' => 'required|integer|min:15|max:480',
            'appointments_interval_between' => 'nullable|integer|min:0|max:60',
            'appointments_auto_confirm' => 'boolean',
            'appointments_allow_cancellation' => 'boolean',
            'appointments_cancellation_hours' => 'nullable|integer|min:1',
            'appointments_reminder_hours' => 'nullable|integer|min:1|max:168',
            'appointments_default_appointment_mode' => 'required|in:presencial,online,user_choice',
        ]);

        TenantSetting::set('appointments.default_duration', $request->appointments_default_duration);
        TenantSetting::set('appointments.interval_between', $request->appointments_interval_between ?? 0);
        
        if ($request->has('appointments_auto_confirm')) {
            TenantSetting::enable('appointments.auto_confirm');
        } else {
            TenantSetting::disable('appointments.auto_confirm');
        }

        if ($request->has('appointments_allow_cancellation')) {
            TenantSetting::enable('appointments.allow_cancellation');
            TenantSetting::set('appointments.cancellation_hours', $request->appointments_cancellation_hours ?? 24);
        } else {
            TenantSetting::disable('appointments.allow_cancellation');
        }

        TenantSetting::set('appointments.reminder_hours', $request->appointments_reminder_hours ?? 24);
        TenantSetting::set('appointments.default_appointment_mode', $request->appointments_default_appointment_mode);

        return redirect()->route('tenant.settings.index', ['slug' => tenant()->subdomain])
            ->with('success', 'Configurações de agendamentos atualizadas com sucesso.');
    }

    /**
     * Atualiza as configurações de calendário
     */
    public function updateCalendar(Request $request)
    {
        $request->validate([
            'calendar_default_start_time' => 'required|date_format:H:i',
            'calendar_default_end_time' => 'required|date_format:H:i',
            'calendar_default_weekdays' => 'required|string',
            'calendar_show_weekends' => 'boolean',
        ]);

        // Valida se o horário de término é depois do início
        if (strtotime($request->calendar_default_end_time) <= strtotime($request->calendar_default_start_time)) {
            return redirect()->route('tenant.settings.index', ['slug' => tenant()->subdomain])
                ->with('error', 'O horário de término deve ser posterior ao horário de início.');
        }

        TenantSetting::set('calendar.default_start_time', $request->calendar_default_start_time);
        TenantSetting::set('calendar.default_end_time', $request->calendar_default_end_time);
        TenantSetting::set('calendar.default_weekdays', $request->calendar_default_weekdays);
        
        if ($request->has('calendar_show_weekends')) {
            TenantSetting::enable('calendar.show_weekends');
        } else {
            TenantSetting::disable('calendar.show_weekends');
        }

        return redirect()->route('tenant.settings.index', ['slug' => tenant()->subdomain])
            ->with('success', 'Configurações de calendário atualizadas com sucesso.');
    }

    /**
     * Atualiza as configurações de notificações
     */
    public function updateNotifications(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'notifications_appointments_enabled' => 'nullable|boolean',
            'notifications_form_responses_enabled' => 'nullable|boolean',
            'notifications_send_email_to_patients' => 'nullable|boolean',
            'notifications_send_whatsapp_to_patients' => 'nullable|boolean',

            'email_driver' => 'required|in:global,tenancy',
            'email_host' => 'required_if:email_driver,tenancy',
            'email_port' => 'required_if:email_driver,tenancy',
            'email_username' => 'required_if:email_driver,tenancy',
            'email_password' => 'required_if:email_driver,tenancy',
            'email_from_name' => 'nullable|string',
            'email_from_address' => 'nullable|email',

            'whatsapp_driver' => 'required|in:global,tenancy',
            'whatsapp_provider' => 'nullable|in:whatsapp_business,zapi,waha',
            'META_ACCESS_TOKEN' => 'nullable|string',
            'META_PHONE_NUMBER_ID' => 'nullable|string',
            'ZAPI_API_URL' => 'nullable|url',
            'ZAPI_TOKEN' => 'nullable|string',
            'ZAPI_CLIENT_TOKEN' => 'nullable|string',
            'ZAPI_INSTANCE_ID' => 'nullable|string',
            'WAHA_BASE_URL' => 'nullable|url',
            'WAHA_API_KEY' => 'nullable|string',
            'WAHA_SESSION' => 'nullable|string',
        ]);

        $validator->after(function ($validator) use ($request) {
            if ($request->input('whatsapp_driver') !== 'tenancy') {
                return;
            }

            $provider = $request->input('whatsapp_provider');
            if (!in_array($provider, ['whatsapp_business', 'zapi', 'waha'], true)) {
                $validator->errors()->add('whatsapp_provider', 'Selecione um provedor de WhatsApp valido.');
                return;
            }

            if ($provider === 'whatsapp_business') {
                if (!$request->filled('META_ACCESS_TOKEN')) {
                    $validator->errors()->add('META_ACCESS_TOKEN', 'O Access Token e obrigatorio para o provedor Meta.');
                }
                if (!$request->filled('META_PHONE_NUMBER_ID')) {
                    $validator->errors()->add('META_PHONE_NUMBER_ID', 'O Phone Number ID e obrigatorio para o provedor Meta.');
                }
            }

            if ($provider === 'zapi') {
                if (!$request->filled('ZAPI_API_URL')) {
                    $validator->errors()->add('ZAPI_API_URL', 'A API URL e obrigatoria para o provedor Z-API.');
                }
                if (!$request->filled('ZAPI_TOKEN')) {
                    $validator->errors()->add('ZAPI_TOKEN', 'O Token e obrigatorio para o provedor Z-API.');
                }
                if (!$request->filled('ZAPI_CLIENT_TOKEN')) {
                    $validator->errors()->add('ZAPI_CLIENT_TOKEN', 'O Client Token e obrigatorio para o provedor Z-API.');
                }
                if (!$request->filled('ZAPI_INSTANCE_ID')) {
                    $validator->errors()->add('ZAPI_INSTANCE_ID', 'O Instance ID e obrigatorio para o provedor Z-API.');
                }
            }

            if ($provider === 'waha') {
                if (!$request->filled('WAHA_BASE_URL')) {
                    $validator->errors()->add('WAHA_BASE_URL', 'A Base URL e obrigatoria para o provedor WAHA.');
                }
                if (!$request->filled('WAHA_API_KEY')) {
                    $validator->errors()->add('WAHA_API_KEY', 'A API Key e obrigatoria para o provedor WAHA.');
                }
                if (!$request->filled('WAHA_SESSION')) {
                    $validator->errors()->add('WAHA_SESSION', 'O nome da sessao e obrigatorio para o provedor WAHA.');
                }
            }
        });

        $validated = $validator->validate();

        TenantSetting::set('notifications.appointments.enabled', $request->has('notifications_appointments_enabled') ? 'true' : 'false');
        TenantSetting::set('notifications.form_responses.enabled', $request->has('notifications_form_responses_enabled') ? 'true' : 'false');

        TenantSetting::set('notifications.send_email_to_patients', $request->has('notifications_send_email_to_patients') ? 'true' : 'false');
        TenantSetting::set('notifications.send_whatsapp_to_patients', $request->has('notifications_send_whatsapp_to_patients') ? 'true' : 'false');

        TenantSetting::set('email.driver', $validated['email_driver']);
        if ($validated['email_driver'] === 'tenancy') {
            TenantSetting::set('email.host', $validated['email_host']);
            TenantSetting::set('email.port', $validated['email_port']);
            TenantSetting::set('email.username', $validated['email_username']);
            TenantSetting::set('email.password', $validated['email_password']);
            TenantSetting::set('email.from_name', $validated['email_from_name'] ?? '');
            TenantSetting::set('email.from_address', $validated['email_from_address'] ?? '');
        } else {
            TenantSetting::set('email.host', '');
            TenantSetting::set('email.port', '');
            TenantSetting::set('email.username', '');
            TenantSetting::set('email.password', '');
        }

        TenantSetting::set('whatsapp.driver', $validated['whatsapp_driver']);
        if ($validated['whatsapp_driver'] === 'tenancy') {
            $provider = $validated['whatsapp_provider'];

            TenantSetting::set('whatsapp.provider', $provider);
            TenantSetting::set('whatsapp.meta.access_token', $validated['META_ACCESS_TOKEN'] ?? '');
            TenantSetting::set('whatsapp.meta.phone_number_id', $validated['META_PHONE_NUMBER_ID'] ?? '');
            TenantSetting::set('whatsapp.zapi.api_url', $validated['ZAPI_API_URL'] ?? '');
            TenantSetting::set('whatsapp.zapi.token', $validated['ZAPI_TOKEN'] ?? '');
            TenantSetting::set('whatsapp.zapi.client_token', $validated['ZAPI_CLIENT_TOKEN'] ?? '');
            TenantSetting::set('whatsapp.zapi.instance_id', $validated['ZAPI_INSTANCE_ID'] ?? '');
            TenantSetting::set('whatsapp.waha.base_url', $validated['WAHA_BASE_URL'] ?? '');
            TenantSetting::set('whatsapp.waha.api_key', $validated['WAHA_API_KEY'] ?? '');
            TenantSetting::set('whatsapp.waha.session', $validated['WAHA_SESSION'] ?? 'default');
        } else {
            TenantSetting::set('whatsapp.provider', '');
            TenantSetting::set('whatsapp.meta.access_token', '');
            TenantSetting::set('whatsapp.meta.phone_number_id', '');
            TenantSetting::set('whatsapp.zapi.api_url', '');
            TenantSetting::set('whatsapp.zapi.token', '');
            TenantSetting::set('whatsapp.zapi.client_token', '');
            TenantSetting::set('whatsapp.zapi.instance_id', '');
            TenantSetting::set('whatsapp.waha.base_url', '');
            TenantSetting::set('whatsapp.waha.api_key', '');
            TenantSetting::set('whatsapp.waha.session', '');
        }

        return redirect()->route('tenant.settings.index', ['slug' => tenant()->subdomain])
            ->with('success', 'Configurações de notificações atualizadas com sucesso.');
    }

    /**
     * Atualiza as configurações de integrações
     */
    public function updateIntegrations(Request $request)
    {
        $request->validate([
            'integrations_google_calendar_enabled' => 'boolean',
            'integrations_google_calendar_auto_sync' => 'boolean',
            'integrations_apple_calendar_enabled' => 'boolean',
            'integrations_apple_calendar_auto_sync' => 'boolean',
        ]);

        // Verificar se Google Calendar está cadastrado antes de permitir habilitar
        $googleCalendarIntegration = Integrations::where('key', 'google_calendar')->first();
        $appleCalendarIntegration = Integrations::where('key', 'apple_calendar')->first();

        if ($request->has('integrations_google_calendar_enabled')) {
            if (!$googleCalendarIntegration || !$googleCalendarIntegration->is_enabled || empty($googleCalendarIntegration->config)) {
                return redirect()->route('tenant.settings.index', ['slug' => tenant()->subdomain])
                    ->with('error', 'Não é possível habilitar o Google Calendar. Cadastre primeiro a integração em Integrações com a chave "google_calendar" e configure a API.');
            }

            TenantSetting::enable('integrations.google_calendar.enabled');

            if ($request->has('integrations_google_calendar_auto_sync')) {
                TenantSetting::enable('integrations.google_calendar.auto_sync');
            } else {
                TenantSetting::disable('integrations.google_calendar.auto_sync');
            }
        } else {
            TenantSetting::disable('integrations.google_calendar.enabled');
            TenantSetting::disable('integrations.google_calendar.auto_sync');
        }

        if ($request->has('integrations_apple_calendar_enabled')) {
            if (!$appleCalendarIntegration || !$appleCalendarIntegration->is_enabled || empty($appleCalendarIntegration->config)) {
                return redirect()->route('tenant.settings.index', ['slug' => tenant()->subdomain])
                    ->with('error', 'Não é possível habilitar o Apple Calendar. Cadastre primeiro a integração em Integrações com a chave "apple_calendar" e configure a API.');
            }

            TenantSetting::enable('integrations.apple_calendar.enabled');

            if ($request->has('integrations_apple_calendar_auto_sync')) {
                TenantSetting::enable('integrations.apple_calendar.auto_sync');
            } else {
                TenantSetting::disable('integrations.apple_calendar.auto_sync');
            }
        } else {
            TenantSetting::disable('integrations.apple_calendar.enabled');
            TenantSetting::disable('integrations.apple_calendar.auto_sync');
        }

        return redirect()->route('tenant.settings.index', ['slug' => tenant()->subdomain])
            ->with('success', 'Configurações de integrações atualizadas com sucesso.');
    }

    /**
     * Atualiza as configurações de módulos padrão por perfil de usuário
     */
    public function updateUserDefaults(Request $request)
    {
        $request->validate([
            'user_defaults' => 'nullable|array',
            'user_defaults.modules_common_user' => 'nullable|array',
            'user_defaults.modules_common_user.*' => 'string',
            'user_defaults.modules_doctor' => 'nullable|array',
            'user_defaults.modules_doctor.*' => 'string',
        ]);

        // Salvar módulos padrão para usuário comum
        // O formulário envia como user_defaults[modules_common_user][], então acessamos via dot notation
        $commonUserModules = $request->input('user_defaults.modules_common_user', []);
        // Se não vier nada, garantir que seja array vazio
        if (empty($commonUserModules)) {
            $commonUserModules = [];
        }
        TenantSetting::set('user_defaults.modules_common_user', json_encode($commonUserModules));

        // Salvar módulos padrão para médico
        $doctorModules = $request->input('user_defaults.modules_doctor', []);
        // Se não vier nada, garantir que seja array vazio
        if (empty($doctorModules)) {
            $doctorModules = [];
        }
        TenantSetting::set('user_defaults.modules_doctor', json_encode($doctorModules));

        return redirect()->route('tenant.settings.index', ['slug' => tenant()->subdomain])
            ->with('success', 'Configurações de usuários e permissões atualizadas com sucesso.');
    }

    /**
     * Atualiza as configurações de profissionais
     */
    public function updateProfessionals(Request $request)
    {
        $request->validate([
            'professional_customization_enabled' => 'nullable|boolean',
            'professional_label_singular' => 'nullable|string|max:50',
            'professional_label_plural' => 'nullable|string|max:50',
            'professional_registration_label' => 'nullable|string|max:50',
        ]);

        // Habilitar/desabilitar personalização
        // Checkbox não marcado não é enviado no request, então verificamos explicitamente
        if ($request->filled('professional_customization_enabled') || $request->has('professional_customization_enabled')) {
            TenantSetting::enable('professional.customization_enabled');
            
            // Salvar rótulos globais quando personalização está habilitada
            TenantSetting::set('professional.label_singular', $request->professional_label_singular ?? '');
            TenantSetting::set('professional.label_plural', $request->professional_label_plural ?? '');
            TenantSetting::set('professional.registration_label', $request->professional_registration_label ?? '');
        } else {
            TenantSetting::disable('professional.customization_enabled');
            
            // Limpar rótulos quando desabilitado
            TenantSetting::set('professional.label_singular', '');
            TenantSetting::set('professional.label_plural', '');
            TenantSetting::set('professional.registration_label', '');
        }

        return redirect()->route('tenant.settings.index', ['slug' => tenant()->subdomain])
            ->with('success', 'Configurações de profissionais atualizadas com sucesso.');
    }

    /**
     * Atualiza as configurações de aparência (logo e favicon)
     */
    public function updateAppearance(Request $request)
    {
        $request->validate([
            'appearance_logo_light' => 'nullable|image|mimes:jpeg,jpg,png,gif,svg|max:2048',
            'appearance_logo_dark' => 'nullable|image|mimes:jpeg,jpg,png,gif,svg|max:2048',
            'appearance_logo_mini_light' => 'nullable|image|mimes:jpeg,jpg,png,gif,svg|max:2048',
            'appearance_logo_mini_dark' => 'nullable|image|mimes:jpeg,jpg,png,gif,svg|max:2048',
            'appearance_favicon' => 'nullable|image|mimes:jpeg,jpg,png,ico,svg|max:1024',
            'remove_logo_light' => 'nullable|boolean',
            'remove_logo_dark' => 'nullable|boolean',
            'remove_logo_mini_light' => 'nullable|boolean',
            'remove_logo_mini_dark' => 'nullable|boolean',
            'remove_favicon' => 'nullable|boolean',
        ]);

        // Obter tenant atual para criar diretório específico
        $currentTenant = Tenant::current();
        $tenantId = $currentTenant ? $currentTenant->id : 'default';
        $storagePath = 'tenant/' . $tenantId . '/branding';
        
        // Garantir que o diretório existe
        if (!Storage::disk('public')->exists($storagePath)) {
            Storage::disk('public')->makeDirectory($storagePath, 0755, true);
        }

        $handleUpload = function (string $inputKey, string $settingKey, string $removeKey, string $prefix) use ($request, $storagePath): void {
            if ($request->boolean($removeKey)) {
                $currentValue = TenantSetting::get($settingKey);
                if ($currentValue && Storage::disk('public')->exists($currentValue)) {
                    Storage::disk('public')->delete($currentValue);
                }
                TenantSetting::set($settingKey, '');
                return;
            }

            if ($request->hasFile($inputKey)) {
                $currentValue = TenantSetting::get($settingKey);
                if ($currentValue && Storage::disk('public')->exists($currentValue)) {
                    Storage::disk('public')->delete($currentValue);
                }

                $file = $request->file($inputKey);
                $filename = $prefix . '_' . time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs($storagePath, $filename, 'public');

                TenantSetting::set($settingKey, $path);
            }
        };

        $handleUpload('appearance_logo_light', 'appearance.logo_light', 'remove_logo_light', 'logo_light');
        $handleUpload('appearance_logo_dark', 'appearance.logo_dark', 'remove_logo_dark', 'logo_dark');
        $handleUpload('appearance_logo_mini_light', 'appearance.logo_mini_light', 'remove_logo_mini_light', 'logo_mini_light');
        $handleUpload('appearance_logo_mini_dark', 'appearance.logo_mini_dark', 'remove_logo_mini_dark', 'logo_mini_dark');
        $handleUpload('appearance_favicon', 'appearance.favicon', 'remove_favicon', 'favicon');

        $logoLight = TenantSetting::get('appearance.logo_light', '');
        $logoDark = TenantSetting::get('appearance.logo_dark', '');
        $logoMiniLight = TenantSetting::get('appearance.logo_mini_light', '');
        $logoMiniDark = TenantSetting::get('appearance.logo_mini_dark', '');

        TenantSetting::set('appearance.logo', $logoLight ?: $logoDark ?: '');
        TenantSetting::set('appearance.logo_mini', $logoMiniLight ?: $logoMiniDark ?: '');

        // Redirecionar para a página de configurações mantendo o hash na URL
        $redirectUrl = route('tenant.settings.index', ['slug' => tenant()->subdomain]) . '#appearance';
        return redirect($redirectUrl)
            ->with('success', 'Configurações de aparência atualizadas com sucesso.');
    }
}

