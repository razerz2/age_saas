<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\TenantSetting;
use App\Models\Tenant\Integrations;
use App\Models\Tenant\Appointment;
use App\Models\Platform\Tenant;
use App\Models\Platform\Estado;
use App\Models\Platform\Cidade;
use App\Services\FeatureAccessService;
use App\Services\Tenant\NotificationContextBuilder;
use App\Services\Tenant\NotificationTemplateService;
use App\Services\Tenant\TemplateRenderer;
use App\Services\Tenant\WhatsAppBotConfigService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SettingsController extends Controller
{
    private const BRAZIL_COUNTRY_ID = 31;

    /**
     * Exibe a pÃ¡gina de configuraÃ§Ãµes
     */
    public function index(Request $request)
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
            'appointments.confirmation.enabled' => tenant_setting_bool('appointments.confirmation.enabled', false),
            'appointments.confirmation.ttl_minutes' => tenant_setting_int('appointments.confirmation.ttl_minutes', 30),
            'appointments.waitlist.enabled' => tenant_setting_bool('appointments.waitlist.enabled', false),
            'appointments.waitlist.offer_ttl_minutes' => tenant_setting_int('appointments.waitlist.offer_ttl_minutes', 15),
            'appointments.waitlist.allow_when_confirmed' => tenant_setting_bool('appointments.waitlist.allow_when_confirmed', true),
            'appointments.waitlist.max_per_slot' => tenant_setting_nullable_int('appointments.waitlist.max_per_slot', null),
            
            // CalendÃ¡rio
            'calendar.default_start_time' => TenantSetting::get('calendar.default_start_time', '08:00'),
            'calendar.default_end_time' => TenantSetting::get('calendar.default_end_time', '18:00'),
            'calendar.default_weekdays' => TenantSetting::get('calendar.default_weekdays', '1,2,3,4,5'), // Segunda a Sexta
            'calendar.show_weekends' => TenantSetting::isEnabled('calendar.show_weekends'),
            
            // NotificaÃ§Ãµes
            // Verifica explicitamente se o valor Ã© 'true' para garantir que desabilitados retornem false
            'notifications.appointments.enabled' => TenantSetting::get('notifications.appointments.enabled') === 'true',
            'notifications.form_responses.enabled' => TenantSetting::get('notifications.form_responses.enabled') === 'true',
            // Para notificaÃ§Ãµes aos pacientes, verifica explicitamente se Ã© 'true' (opt-in)
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
            'META_WABA_ID' => TenantSetting::get('whatsapp.meta.waba_id', ''),
            'ZAPI_API_URL' => TenantSetting::get('whatsapp.zapi.api_url', 'https://api.z-api.io'),
            'ZAPI_TOKEN' => TenantSetting::get('whatsapp.zapi.token', ''),
            'ZAPI_CLIENT_TOKEN' => TenantSetting::get('whatsapp.zapi.client_token', ''),
            'ZAPI_INSTANCE_ID' => TenantSetting::get('whatsapp.zapi.instance_id', ''),
            'WAHA_BASE_URL' => TenantSetting::get('whatsapp.waha.base_url', ''),
            'WAHA_API_KEY' => TenantSetting::get('whatsapp.waha.api_key', ''),
            'WAHA_SESSION' => TenantSetting::get('whatsapp.waha.session', 'default'),

            // Bot de WhatsApp
            'whatsapp_bot.enabled' => TenantSetting::get('whatsapp_bot.enabled', 'false') === 'true',
            'whatsapp_bot.provider_mode' => TenantSetting::get('whatsapp_bot.provider_mode', WhatsAppBotConfigService::MODE_SHARED_WITH_NOTIFICATIONS),
            'whatsapp_bot.provider' => TenantSetting::get('whatsapp_bot.provider', 'whatsapp_business'),
            'whatsapp_bot.welcome_message' => TenantSetting::get('whatsapp_bot.welcome_message', ''),
            'whatsapp_bot.disabled_message' => TenantSetting::get('whatsapp_bot.disabled_message', ''),
            'whatsapp_bot.allow_schedule' => TenantSetting::get('whatsapp_bot.allow_schedule', 'false') === 'true',
            'whatsapp_bot.allow_view_appointments' => TenantSetting::get('whatsapp_bot.allow_view_appointments', 'false') === 'true',
            'whatsapp_bot.allow_cancel_appointments' => TenantSetting::get('whatsapp_bot.allow_cancel_appointments', 'false') === 'true',
            'whatsapp_bot.META_ACCESS_TOKEN' => TenantSetting::get('whatsapp_bot.meta.access_token', ''),
            'whatsapp_bot.META_PHONE_NUMBER_ID' => TenantSetting::get('whatsapp_bot.meta.phone_number_id', ''),
            'whatsapp_bot.META_WABA_ID' => TenantSetting::get('whatsapp_bot.meta.waba_id', ''),
            'whatsapp_bot.ZAPI_API_URL' => TenantSetting::get('whatsapp_bot.zapi.api_url', 'https://api.z-api.io'),
            'whatsapp_bot.ZAPI_TOKEN' => TenantSetting::get('whatsapp_bot.zapi.token', ''),
            'whatsapp_bot.ZAPI_CLIENT_TOKEN' => TenantSetting::get('whatsapp_bot.zapi.client_token', ''),
            'whatsapp_bot.ZAPI_INSTANCE_ID' => TenantSetting::get('whatsapp_bot.zapi.instance_id', ''),
            'whatsapp_bot.WAHA_BASE_URL' => TenantSetting::get('whatsapp_bot.waha.base_url', ''),
            'whatsapp_bot.WAHA_API_KEY' => TenantSetting::get('whatsapp_bot.waha.api_key', ''),
            'whatsapp_bot.WAHA_SESSION' => TenantSetting::get('whatsapp_bot.waha.session', 'default'),
            
            // IntegraÃ§Ãµes
            'integrations.google_calendar.enabled' => TenantSetting::isEnabled('integrations.google_calendar.enabled'),
            'integrations.google_calendar.auto_sync' => TenantSetting::isEnabled('integrations.google_calendar.auto_sync'),
            'integrations.apple_calendar.enabled' => TenantSetting::isEnabled('integrations.apple_calendar.enabled'),
            'integrations.apple_calendar.auto_sync' => TenantSetting::isEnabled('integrations.apple_calendar.auto_sync'),
            
            // Profissionais
            'professional.customization_enabled' => TenantSetting::get('professional.customization_enabled') === 'true',
            'professional.label_singular' => TenantSetting::get('professional.label_singular', ''),
            'professional.label_plural' => TenantSetting::get('professional.label_plural', ''),
            'professional.registration_label' => TenantSetting::get('professional.registration_label', ''),
            
            // AparÃªncia
            'appearance.logo' => $appearanceLogoLight,
            'appearance.logo_mini' => $appearanceLogoMiniLight,
            'appearance.logo_light' => $appearanceLogoLight,
            'appearance.logo_dark' => $appearanceLogoDark,
            'appearance.logo_mini_light' => $appearanceLogoMiniLight,
            'appearance.logo_mini_dark' => $appearanceLogoMiniDark,
            'appearance.favicon' => $appearanceFavicon,
        ];

        // Buscar integraÃ§Ãµes ativas
        $integrations = Integrations::where('is_enabled', true)->get();
        
        // Verificar se Google Calendar estÃ¡ cadastrado e configurado
        $googleCalendarIntegration = Integrations::where('key', 'google_calendar')->first();
        $appleCalendarIntegration = Integrations::where('key', 'apple_calendar')->first();
        
        // Considera vÃ¡lida se existe, estÃ¡ habilitada e tem config nÃ£o vazio
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

        // Considera vÃ¡lida se existe, estÃ¡ habilitada e tem config nÃ£o vazio
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

        // Obter tenant atual para gerar o link de agendamento pÃºblico
        $currentTenant = Tenant::current();
        $publicBookingUrl = null;
        
        if ($currentTenant) {
            $publicBookingUrl = url('/customer/' . $currentTenant->subdomain . '/agendamento/identificar');
        }

        $botConfigService = app(WhatsAppBotConfigService::class);
        $editor = $this->buildEditorViewData($currentTenant, $request);
        $localizacao = $currentTenant ? $currentTenant->localizacao : null;
        $showOfficialTemplatesTab = $this->tenantUsesOwnOfficialWhatsApp($settings);
        $showWhatsAppBotTab = $this->hasWhatsAppBotFeature($currentTenant);
        $whatsAppBotEffectiveProvider = $botConfigService->resolveEffectiveProviderConfig();

        $initialTab = (string) $request->get('tab', 'clinica');
        if ($initialTab === 'bot-whatsapp' && !$showWhatsAppBotTab) {
            $initialTab = 'clinica';
        }

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
            'editor',
            'showOfficialTemplatesTab',
            'showWhatsAppBotTab',
            'whatsAppBotEffectiveProvider',
            'initialTab'
        ));
    }

    /**
     * Salva override do template de notificacao para tenant/canal/key.
     */
    public function updateNotificationTemplate(
        Request $request,
        NotificationTemplateService $templateService,
        NotificationContextBuilder $contextBuilder,
        TemplateRenderer $renderer
    )
    {
        $validated = $request->validate([
            'channel' => 'required|string|in:email,whatsapp',
            'key' => 'required|string|max:255',
            'subject' => 'nullable|string|max:255',
            'content' => 'required|string|min:1',
        ]);

        $tenant = Tenant::current();
        if (!$tenant) {
            return redirect()
                ->route('tenant.settings.index', ['slug' => request()->route('slug')])
                ->with('error', 'Tenant nao encontrado.');
        }

        $defaultTemplate = $templateService->getDefaultTemplate($validated['channel'], $validated['key']);
        $subject = $validated['subject'] ?? null;
        if ($validated['channel'] === 'email' && !empty($defaultTemplate['subject']) && trim((string) $subject) === '') {
            return back()
                ->withErrors(['subject' => 'O assunto e obrigatorio para templates de email.'])
                ->withInput();
        }

        $unknownPlaceholders = [];
        try {
            $preview = $this->renderNotificationPreview(
                $tenant,
                $validated['channel'],
                $validated['key'],
                $subject,
                $validated['content'],
                $contextBuilder,
                $renderer
            );
            $unknownPlaceholders = is_array($preview['unknown_placeholders'] ?? null)
                ? $preview['unknown_placeholders']
                : [];
        } catch (\Throwable $e) {
            \Log::warning('Falha ao validar placeholders no editor (save).', [
                'tenant_id' => (string) $tenant->id,
                'channel' => $validated['channel'],
                'key' => $validated['key'],
                'error' => $e->getMessage(),
            ]);
        }

        $templateService->saveOverride(
            (string) $tenant->id,
            $validated['channel'],
            $validated['key'],
            $subject,
            $validated['content']
        );

        $redirect = redirect()->to($this->buildEditorSettingsUrl($validated['channel'], $validated['key']))
            ->with('success', 'Template salvo.');

        if (is_array($unknownPlaceholders) && $unknownPlaceholders !== []) {
            $redirect->with('editor_unknown_placeholders', array_values(array_unique($unknownPlaceholders)));
        }

        return $redirect;
    }

    /**
     * Remove override para voltar ao template padrao do sistema.
     */
    public function restoreNotificationTemplate(Request $request, NotificationTemplateService $templateService)
    {
        $validated = $request->validate([
            'channel' => 'required|string|in:email,whatsapp',
            'key' => 'required|string|max:255',
        ]);

        $tenant = Tenant::current();
        if (!$tenant) {
            return redirect()
                ->route('tenant.settings.index', ['slug' => request()->route('slug')])
                ->with('error', 'Tenant nao encontrado.');
        }

        $templateService->restoreDefault((string) $tenant->id, $validated['channel'], $validated['key']);

        return redirect()->to($this->buildEditorSettingsUrl($validated['channel'], $validated['key']))
            ->with('success', 'Template restaurado para o padrÃ£o.');
    }

    /**
     * Gera preview renderizado sem salvar override.
     */
    public function previewNotificationTemplate(
        Request $request,
        NotificationTemplateService $templateService,
        NotificationContextBuilder $contextBuilder,
        TemplateRenderer $renderer
    ) {
        $validated = $request->validate([
            'channel' => 'required|string|in:email,whatsapp',
            'key' => 'required|string|max:255',
            'subject' => 'nullable|string|max:255',
            'content' => 'required|string|min:1',
        ]);

        $tenant = Tenant::current();
        if (!$tenant) {
            return redirect()
                ->route('tenant.settings.index', ['slug' => request()->route('slug')])
                ->with('error', 'Tenant nao encontrado.');
        }

        $defaultTemplate = $templateService->getDefaultTemplate($validated['channel'], $validated['key']);
        $subject = $validated['subject'] ?? null;
        if ($validated['channel'] === 'email' && !empty($defaultTemplate['subject']) && trim((string) $subject) === '') {
            return back()
                ->withErrors(['subject' => 'O assunto e obrigatorio para templates de email.'])
                ->withInput();
        }

        try {
            $preview = $this->renderNotificationPreview(
                $tenant,
                $validated['channel'],
                $validated['key'],
                $subject,
                $validated['content'],
                $contextBuilder,
                $renderer
            );
        } catch (\Throwable $e) {
            \Log::warning('Falha ao gerar preview de template.', [
                'tenant_id' => (string) $tenant->id,
                'channel' => $validated['channel'],
                'key' => $validated['key'],
                'error' => $e->getMessage(),
            ]);

            $preview = [
                'channel' => $validated['channel'],
                'key' => $validated['key'],
                'context_source' => 'mock',
                'context_warning' => 'Nao foi possivel montar o contexto de preview. Exibindo texto sem substituicoes adicionais.',
                'unknown_placeholders' => [],
                'subject_input' => $subject,
                'content_input' => $validated['content'],
                'subject_rendered' => $subject,
                'content_rendered' => $validated['content'],
            ];
        }

        $request->merge([
            'tab' => 'editor',
            'channel' => $validated['channel'],
            'key' => $validated['key'],
        ]);
        $request->attributes->set('editor_preview', $preview);

        return $this->index($request);
    }

    /**
     * Atualiza as informaÃ§Ãµes de cadastro do tenant
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
            'pais_id' => self::BRAZIL_COUNTRY_ID,
            'estado_id' => $request->estado_id,
            'cidade_id' => $request->cidade_id,
        ]);

        return redirect()->to(route('tenant.settings.index', ['slug' => tenant()->subdomain]) . '#registration')
            ->with('success', 'InformaÃ§Ãµes de cadastro atualizadas com sucesso.');
    }

    /**
     * Exibe a pÃ¡gina dedicada do link de agendamento pÃºblico
     * Esta pÃ¡gina nÃ£o requer acesso ao mÃ³dulo de configuraÃ§Ãµes
     */
    public function publicBookingLink()
    {
        // Obter tenant atual para gerar o link de agendamento pÃºblico
        $currentTenant = Tenant::current();
        $publicBookingUrl = null;
        
        if ($currentTenant) {
            $publicBookingUrl = url('/customer/' . $currentTenant->subdomain . '/agendamento/identificar');
        }

        return view('tenant.settings.public-booking-link', compact('publicBookingUrl'));
    }

    /**
     * Atualiza as configuraÃ§Ãµes gerais
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
            ->with('success', 'ConfiguraÃ§Ãµes gerais atualizadas com sucesso.');
    }

    /**
     * Atualiza as configuraÃ§Ãµes de agendamentos
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
            'appointments_confirmation_enabled' => 'boolean',
            'appointments_confirmation_ttl_minutes' => 'nullable|integer|min:1|max:1440',
            'appointments_waitlist_enabled' => 'boolean',
            'appointments_waitlist_offer_ttl_minutes' => 'nullable|integer|min:1|max:1440',
            'appointments_waitlist_allow_when_confirmed' => 'boolean',
            'appointments_waitlist_max_per_slot' => 'nullable|integer|min:1',
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
        TenantSetting::set(
            'appointments.confirmation.enabled',
            $request->has('appointments_confirmation_enabled') ? 'true' : 'false'
        );
        TenantSetting::set(
            'appointments.confirmation.ttl_minutes',
            (string) ($request->appointments_confirmation_ttl_minutes ?? 30)
        );
        TenantSetting::set(
            'appointments.waitlist.enabled',
            $request->has('appointments_waitlist_enabled') ? 'true' : 'false'
        );
        TenantSetting::set(
            'appointments.waitlist.offer_ttl_minutes',
            (string) ($request->appointments_waitlist_offer_ttl_minutes ?? 15)
        );
        TenantSetting::set(
            'appointments.waitlist.allow_when_confirmed',
            $request->has('appointments_waitlist_allow_when_confirmed') ? 'true' : 'false'
        );

        $maxPerSlot = $request->appointments_waitlist_max_per_slot;
        TenantSetting::set(
            'appointments.waitlist.max_per_slot',
            ($maxPerSlot === null || $maxPerSlot === '') ? null : (string) $maxPerSlot
        );

        return redirect()->route('tenant.settings.index', ['slug' => tenant()->subdomain])
            ->with('success', 'ConfiguraÃ§Ãµes de agendamentos atualizadas com sucesso.');
    }

    /**
     * Atualiza as configuraÃ§Ãµes de calendÃ¡rio
     */
    public function updateCalendar(Request $request)
    {
        $request->validate([
            'calendar_default_start_time' => 'required|date_format:H:i',
            'calendar_default_end_time' => 'required|date_format:H:i',
            'calendar_default_weekdays' => 'required|string',
            'calendar_show_weekends' => 'boolean',
        ]);

        // Valida se o horÃ¡rio de tÃ©rmino Ã© depois do inÃ­cio
        if (strtotime($request->calendar_default_end_time) <= strtotime($request->calendar_default_start_time)) {
            return redirect()->route('tenant.settings.index', ['slug' => tenant()->subdomain])
                ->with('error', 'O horÃ¡rio de tÃ©rmino deve ser posterior ao horÃ¡rio de inÃ­cio.');
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
            ->with('success', 'ConfiguraÃ§Ãµes de calendÃ¡rio atualizadas com sucesso.');
    }

    /**
     * Atualiza as configuraÃ§Ãµes de notificaÃ§Ãµes
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
            'META_WABA_ID' => 'nullable|string',
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
                if (!$request->filled('META_WABA_ID')) {
                    $validator->errors()->add('META_WABA_ID', 'O WABA ID e obrigatorio para o provedor Meta.');
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
            TenantSetting::set('whatsapp.meta.waba_id', $validated['META_WABA_ID'] ?? '');
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
            TenantSetting::set('whatsapp.meta.waba_id', '');
            TenantSetting::set('whatsapp.zapi.api_url', '');
            TenantSetting::set('whatsapp.zapi.token', '');
            TenantSetting::set('whatsapp.zapi.client_token', '');
            TenantSetting::set('whatsapp.zapi.instance_id', '');
            TenantSetting::set('whatsapp.waha.base_url', '');
            TenantSetting::set('whatsapp.waha.api_key', '');
            TenantSetting::set('whatsapp.waha.session', '');
        }

        return redirect()->route('tenant.settings.index', ['slug' => tenant()->subdomain])
            ->with('success', 'ConfiguraÃ§Ãµes de notificaÃ§Ãµes atualizadas com sucesso.');
    }

    /**
     * Atualiza as configuracoes iniciais do Bot de WhatsApp.
     */
    public function updateWhatsAppBot(Request $request)
    {
        $tenant = Tenant::current();
        if (!$this->hasWhatsAppBotFeature($tenant)) {
            abort(403, "A funcionalidade 'Bot de WhatsApp' nao esta disponivel no seu plano.");
        }

        $validator = Validator::make($request->all(), [
            'whatsapp_bot_enabled' => 'nullable|boolean',
            'whatsapp_bot_provider_mode' => ['required', 'string', Rule::in([
                WhatsAppBotConfigService::MODE_SHARED_WITH_NOTIFICATIONS,
                WhatsAppBotConfigService::MODE_DEDICATED,
            ])],
            'whatsapp_bot_provider' => ['nullable', 'string', Rule::in(WhatsAppBotConfigService::SUPPORTED_PROVIDERS)],
            'whatsapp_bot_welcome_message' => 'nullable|string|max:2000',
            'whatsapp_bot_disabled_message' => 'nullable|string|max:500',
            'whatsapp_bot_allow_schedule' => 'nullable|boolean',
            'whatsapp_bot_allow_view_appointments' => 'nullable|boolean',
            'whatsapp_bot_allow_cancel_appointments' => 'nullable|boolean',
            'bot_meta_access_token' => 'nullable|string',
            'bot_meta_phone_number_id' => 'nullable|string',
            'bot_meta_waba_id' => 'nullable|string',
            'bot_zapi_api_url' => 'nullable|url',
            'bot_zapi_token' => 'nullable|string',
            'bot_zapi_client_token' => 'nullable|string',
            'bot_zapi_instance_id' => 'nullable|string',
            'bot_waha_base_url' => 'nullable|url',
            'bot_waha_api_key' => 'nullable|string',
            'bot_waha_session' => 'nullable|string',
        ]);

        $validator->after(function ($validator) use ($request) {
            $botEnabled = $request->has('whatsapp_bot_enabled');
            if ($botEnabled && !$request->filled('whatsapp_bot_welcome_message')) {
                $validator->errors()->add(
                    'whatsapp_bot_welcome_message',
                    'A mensagem inicial e obrigatoria quando o bot estiver habilitado.'
                );
            }

            $providerMode = (string) $request->input('whatsapp_bot_provider_mode');
            if ($providerMode !== WhatsAppBotConfigService::MODE_DEDICATED) {
                return;
            }

            $provider = (string) $request->input('whatsapp_bot_provider');
            if (!in_array($provider, WhatsAppBotConfigService::SUPPORTED_PROVIDERS, true)) {
                $validator->errors()->add('whatsapp_bot_provider', 'Selecione um provedor de WhatsApp valido para o bot.');
                return;
            }

            if ($provider === 'whatsapp_business') {
                if (!$request->filled('bot_meta_access_token')) {
                    $validator->errors()->add('bot_meta_access_token', 'O Access Token e obrigatorio para o provedor Meta.');
                }
                if (!$request->filled('bot_meta_phone_number_id')) {
                    $validator->errors()->add('bot_meta_phone_number_id', 'O Phone Number ID e obrigatorio para o provedor Meta.');
                }
                if (!$request->filled('bot_meta_waba_id')) {
                    $validator->errors()->add('bot_meta_waba_id', 'O WABA ID e obrigatorio para o provedor Meta.');
                }
            }

            if ($provider === 'zapi') {
                if (!$request->filled('bot_zapi_api_url')) {
                    $validator->errors()->add('bot_zapi_api_url', 'A API URL e obrigatoria para o provedor Z-API.');
                }
                if (!$request->filled('bot_zapi_token')) {
                    $validator->errors()->add('bot_zapi_token', 'O Token e obrigatorio para o provedor Z-API.');
                }
                if (!$request->filled('bot_zapi_client_token')) {
                    $validator->errors()->add('bot_zapi_client_token', 'O Client Token e obrigatorio para o provedor Z-API.');
                }
                if (!$request->filled('bot_zapi_instance_id')) {
                    $validator->errors()->add('bot_zapi_instance_id', 'O Instance ID e obrigatorio para o provedor Z-API.');
                }
            }

            if ($provider === 'waha') {
                if (!$request->filled('bot_waha_base_url')) {
                    $validator->errors()->add('bot_waha_base_url', 'A Base URL e obrigatoria para o provedor WAHA.');
                }
                if (!$request->filled('bot_waha_api_key')) {
                    $validator->errors()->add('bot_waha_api_key', 'A API Key e obrigatoria para o provedor WAHA.');
                }
                if (!$request->filled('bot_waha_session')) {
                    $validator->errors()->add('bot_waha_session', 'O nome da sessao e obrigatorio para o provedor WAHA.');
                }
            }
        });

        $validated = $validator->validate();

        TenantSetting::set('whatsapp_bot.enabled', $request->has('whatsapp_bot_enabled') ? 'true' : 'false');
        TenantSetting::set('whatsapp_bot.provider_mode', $validated['whatsapp_bot_provider_mode']);
        TenantSetting::set('whatsapp_bot.welcome_message', (string) ($validated['whatsapp_bot_welcome_message'] ?? ''));
        TenantSetting::set('whatsapp_bot.disabled_message', (string) ($validated['whatsapp_bot_disabled_message'] ?? ''));
        TenantSetting::set('whatsapp_bot.allow_schedule', $request->has('whatsapp_bot_allow_schedule') ? 'true' : 'false');
        TenantSetting::set('whatsapp_bot.allow_view_appointments', $request->has('whatsapp_bot_allow_view_appointments') ? 'true' : 'false');
        TenantSetting::set('whatsapp_bot.allow_cancel_appointments', $request->has('whatsapp_bot_allow_cancel_appointments') ? 'true' : 'false');

        if ($validated['whatsapp_bot_provider_mode'] === WhatsAppBotConfigService::MODE_DEDICATED) {
            $provider = (string) $validated['whatsapp_bot_provider'];
            TenantSetting::set('whatsapp_bot.provider', $provider);

            if ($provider === 'whatsapp_business') {
                TenantSetting::set('whatsapp_bot.meta.access_token', (string) ($validated['bot_meta_access_token'] ?? ''));
                TenantSetting::set('whatsapp_bot.meta.phone_number_id', (string) ($validated['bot_meta_phone_number_id'] ?? ''));
                TenantSetting::set('whatsapp_bot.meta.waba_id', (string) ($validated['bot_meta_waba_id'] ?? ''));
            }

            if ($provider === 'zapi') {
                TenantSetting::set('whatsapp_bot.zapi.api_url', (string) ($validated['bot_zapi_api_url'] ?? ''));
                TenantSetting::set('whatsapp_bot.zapi.token', (string) ($validated['bot_zapi_token'] ?? ''));
                TenantSetting::set('whatsapp_bot.zapi.client_token', (string) ($validated['bot_zapi_client_token'] ?? ''));
                TenantSetting::set('whatsapp_bot.zapi.instance_id', (string) ($validated['bot_zapi_instance_id'] ?? ''));
            }

            if ($provider === 'waha') {
                TenantSetting::set('whatsapp_bot.waha.base_url', (string) ($validated['bot_waha_base_url'] ?? ''));
                TenantSetting::set('whatsapp_bot.waha.api_key', (string) ($validated['bot_waha_api_key'] ?? ''));
                TenantSetting::set('whatsapp_bot.waha.session', (string) ($validated['bot_waha_session'] ?? 'default'));
            }
        }

        return redirect()->route('tenant.settings.index', [
            'slug' => tenant()->subdomain,
            'tab' => 'bot-whatsapp',
        ])->with('success', 'Configuracoes do Bot de WhatsApp atualizadas com sucesso.');
    }

    /**
     * Atualiza as configuracoes de integracoes
     */
    public function updateIntegrations(Request $request)
    {
        $request->validate([
            'integrations_google_calendar_enabled' => 'boolean',
            'integrations_google_calendar_auto_sync' => 'boolean',
            'integrations_apple_calendar_enabled' => 'boolean',
            'integrations_apple_calendar_auto_sync' => 'boolean',
        ]);

        // Verificar se Google Calendar estÃ¡ cadastrado antes de permitir habilitar
        $googleCalendarIntegration = Integrations::where('key', 'google_calendar')->first();
        $appleCalendarIntegration = Integrations::where('key', 'apple_calendar')->first();

        if ($request->has('integrations_google_calendar_enabled')) {
            if (!$googleCalendarIntegration || !$googleCalendarIntegration->is_enabled || empty($googleCalendarIntegration->config)) {
                return redirect()->route('tenant.settings.index', ['slug' => tenant()->subdomain])
                    ->with('error', 'NÃ£o Ã© possÃ­vel habilitar o Google Calendar. Cadastre primeiro a integraÃ§Ã£o em IntegraÃ§Ãµes com a chave "google_calendar" e configure a API.');
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
                    ->with('error', 'NÃ£o Ã© possÃ­vel habilitar o Apple Calendar. Cadastre primeiro a integraÃ§Ã£o em IntegraÃ§Ãµes com a chave "apple_calendar" e configure a API.');
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
            ->with('success', 'ConfiguraÃ§Ãµes de integraÃ§Ãµes atualizadas com sucesso.');
    }

    /**
     * Atualiza as configuraÃ§Ãµes de mÃ³dulos padrÃ£o por perfil de usuÃ¡rio
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

        // Salvar mÃ³dulos padrÃ£o para usuÃ¡rio comum
        // O formulÃ¡rio envia como user_defaults[modules_common_user][], entÃ£o acessamos via dot notation
        $commonUserModules = $request->input('user_defaults.modules_common_user', []);
        // Se nÃ£o vier nada, garantir que seja array vazio
        if (empty($commonUserModules)) {
            $commonUserModules = [];
        }
        TenantSetting::set('user_defaults.modules_common_user', json_encode($commonUserModules));

        // Salvar mÃ³dulos padrÃ£o para mÃ©dico
        $doctorModules = $request->input('user_defaults.modules_doctor', []);
        // Se nÃ£o vier nada, garantir que seja array vazio
        if (empty($doctorModules)) {
            $doctorModules = [];
        }
        TenantSetting::set('user_defaults.modules_doctor', json_encode($doctorModules));

        return redirect()->route('tenant.settings.index', ['slug' => tenant()->subdomain])
            ->with('success', 'ConfiguraÃ§Ãµes de usuÃ¡rios e permissÃµes atualizadas com sucesso.');
    }

    /**
     * Atualiza as configuraÃ§Ãµes de profissionais
     */
    public function updateProfessionals(Request $request)
    {
        $request->validate([
            'professional_customization_enabled' => 'nullable|boolean',
            'professional_label_singular' => 'nullable|string|max:50',
            'professional_label_plural' => 'nullable|string|max:50',
            'professional_registration_label' => 'nullable|string|max:50',
        ]);

        // Habilitar/desabilitar personalizaÃ§Ã£o
        // Checkbox nÃ£o marcado nÃ£o Ã© enviado no request, entÃ£o verificamos explicitamente
        if ($request->filled('professional_customization_enabled') || $request->has('professional_customization_enabled')) {
            TenantSetting::enable('professional.customization_enabled');
            
            // Salvar rÃ³tulos globais quando personalizaÃ§Ã£o estÃ¡ habilitada
            TenantSetting::set('professional.label_singular', $request->professional_label_singular ?? '');
            TenantSetting::set('professional.label_plural', $request->professional_label_plural ?? '');
            TenantSetting::set('professional.registration_label', $request->professional_registration_label ?? '');
        } else {
            TenantSetting::disable('professional.customization_enabled');
            
            // Limpar rÃ³tulos quando desabilitado
            TenantSetting::set('professional.label_singular', '');
            TenantSetting::set('professional.label_plural', '');
            TenantSetting::set('professional.registration_label', '');
        }

        return redirect()->route('tenant.settings.index', ['slug' => tenant()->subdomain])
            ->with('success', 'ConfiguraÃ§Ãµes de profissionais atualizadas com sucesso.');
    }

    /**
     * Atualiza as configuraÃ§Ãµes de aparÃªncia (logo e favicon)
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

        // Obter tenant atual para criar diretÃ³rio especÃ­fico
        $currentTenant = Tenant::current();
        $tenantId = $currentTenant ? $currentTenant->id : 'default';
        $storagePath = 'tenant/' . $tenantId . '/branding';
        
        // Garantir que o diretÃ³rio existe
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

        // Redirecionar para a pÃ¡gina de configuraÃ§Ãµes mantendo o hash na URL
        $redirectUrl = route('tenant.settings.index', ['slug' => tenant()->subdomain]) . '#appearance';
        return redirect($redirectUrl)
            ->with('success', 'ConfiguraÃ§Ãµes de aparÃªncia atualizadas com sucesso.');
    }

    private function renderNotificationPreview(
        Tenant $tenant,
        string $channel,
        string $key,
        ?string $subject,
        string $content,
        NotificationContextBuilder $contextBuilder,
        TemplateRenderer $renderer
    ): array {
        $latestAppointment = Appointment::query()
            ->with([
                'patient',
                'doctor.user',
                'doctor.specialties',
                'calendar.doctor.user',
                'specialty',
                'type',
            ])
            ->orderByDesc('starts_at')
            ->orderByDesc('created_at')
            ->first();

        $contextWarning = null;
        if ($latestAppointment) {
            try {
                $context = $contextBuilder->buildForAppointment($latestAppointment);
                $contextSource = 'appointment';
            } catch (\Throwable $e) {
                $context = $this->buildMockEditorPreviewContext($tenant);
                $contextSource = 'mock';
                $contextWarning = 'Falha ao montar contexto real do ultimo agendamento. Preview usando contexto basico.';
            }
        } else {
            $context = $this->buildMockEditorPreviewContext($tenant);
            $contextSource = 'mock';
            $contextWarning = 'Nenhum agendamento encontrado. Preview usando contexto basico (dados da clinica + campos vazios).';
        }

        if (str_starts_with($key, 'waitlist.')) {
            $context = $this->applyWaitlistPreviewFallback($context, $tenant);
        }

        $unknownPlaceholders = $this->detectUnknownPlaceholders(
            $channel === 'email' ? $subject : null,
            $content,
            $context,
            $renderer
        );

        return [
            'channel' => $channel,
            'key' => $key,
            'context_source' => $contextSource,
            'context_warning' => $contextWarning,
            'unknown_placeholders' => $unknownPlaceholders,
            'subject_input' => $subject,
            'content_input' => $content,
            'subject_rendered' => $channel === 'email' && $subject !== null
                ? $renderer->render($subject, $context)
                : null,
            'content_rendered' => $renderer->render($content, $context),
        ];
    }

    private function buildMockEditorPreviewContext(Tenant $tenant): array
    {
        $tenant->loadMissing(['localizacao.cidade', 'localizacao.estado']);

        $street = trim((string) ($tenant->localizacao?->endereco ?? ''));
        $number = trim((string) ($tenant->localizacao?->n_endereco ?? ''));
        $district = trim((string) ($tenant->localizacao?->bairro ?? ''));
        $city = trim((string) ($tenant->localizacao?->cidade?->nome_cidade ?? ''));
        $state = trim((string) ($tenant->localizacao?->estado?->uf ?? ''));
        $zip = trim((string) ($tenant->localizacao?->cep ?? ''));

        $addressParts = array_values(array_filter([
            trim($street . ($number !== '' ? ', ' . $number : '')),
            $district,
            trim($city . ($state !== '' ? '/' . $state : '')),
            $zip,
        ], static fn ($value) => $value !== ''));

        return [
            'clinic' => [
                'name' => (string) ($tenant->trade_name ?: $tenant->legal_name ?: ''),
                'phone' => (string) ($tenant->phone ?? ''),
                'email' => (string) ($tenant->email ?? ''),
                'address' => implode(' - ', $addressParts),
                'slug' => (string) ($tenant->subdomain ?? ''),
            ],
            'patient' => [
                'name' => '',
                'phone' => '',
                'email' => '',
            ],
            'doctor' => [
                'name' => '',
                'specialty' => '',
            ],
            'professional' => [
                'name' => '',
                'specialty' => '',
            ],
            'appointment' => [
                'date' => '',
                'time' => '',
                'datetime' => '',
                'starts_at' => '',
                'ends_at' => '',
                'type' => '',
                'mode' => '',
                'status' => '',
                'confirmation_expires_at' => '',
            ],
            'links' => [
                'appointment_confirm' => '',
                'appointment_cancel' => '',
                'appointment_details' => '',
                'waitlist_offer' => '',
            ],
            'waitlist' => [
                'offer_expires_at' => '',
                'status' => '',
            ],
        ];
    }

    private function applyWaitlistPreviewFallback(array $context, Tenant $tenant): array
    {
        $offerTtlMinutes = max(1, tenant_setting_int('appointments.waitlist.offer_ttl_minutes', 15));
        $offerExpires = now()->addMinutes($offerTtlMinutes)->format('d/m/Y H:i');

        $offerExpiresAt = data_get($context, 'waitlist.offer_expires_at');
        if (!is_string($offerExpiresAt) || trim($offerExpiresAt) === '') {
            data_set($context, 'waitlist.offer_expires_at', $offerExpires);
        }

        $waitlistStatus = data_get($context, 'waitlist.status');
        if (!is_string($waitlistStatus) || trim($waitlistStatus) === '') {
            data_set($context, 'waitlist.status', 'OFFERED');
        }

        $waitlistOfferLink = data_get($context, 'links.waitlist_offer');
        if (!is_string($waitlistOfferLink) || trim($waitlistOfferLink) === '') {
            $slug = (string) ($tenant->subdomain ?? '');
            $fallbackUrl = $slug !== '' ? url('/customer/' . $slug . '/agendamento/oferta/preview') : '#';
            data_set($context, 'links.waitlist_offer', $fallbackUrl);
        }

        return $context;
    }

    /**
     * @return list<string>
     */
    private function detectUnknownPlaceholders(
        ?string $subject,
        string $content,
        array $context,
        TemplateRenderer $renderer
    ): array {
        $placeholders = $renderer->extractPlaceholders($content);
        if ($subject !== null && $subject !== '') {
            $placeholders = array_merge($placeholders, $renderer->extractPlaceholders($subject));
        }

        if ($placeholders === []) {
            return [];
        }

        $missing = new \stdClass();
        $unknown = [];

        foreach ($placeholders as $placeholder) {
            $value = data_get($context, $placeholder, $missing);
            if ($value === $missing) {
                $unknown[] = (string) $placeholder;
            }
        }

        return array_values(array_unique($unknown));
    }

    private function buildEditorSettingsUrl(string $channel, string $key): string
    {
        return route('tenant.settings.index', [
            'slug' => tenant()->subdomain,
            'tab' => 'editor',
            'channel' => $channel,
            'key' => $key,
        ]);
    }

    private function buildEditorViewData(?Tenant $currentTenant, Request $request): array
    {
        $service = app(NotificationTemplateService::class);
        $channels = array_values(array_filter(
            (array) config('notification_templates.channels', ['email', 'whatsapp']),
            static fn ($item) => in_array($item, ['email', 'whatsapp'], true)
        ));

        if ($channels === []) {
            $channels = ['email', 'whatsapp'];
        }

        $keys = $service->listKeys();
        $requestedChannel = strtolower((string) $request->input('channel', $request->query('channel', 'email')));
        $channel = in_array($requestedChannel, $channels, true) ? $requestedChannel : $channels[0];
        $requestedKey = (string) $request->input('key', $request->query('key', ''));
        $key = $this->resolveEditorKey($keys, $channel, $requestedKey);

        $defaultTemplate = null;
        $effectiveTemplate = null;
        $isCustom = false;
        $subjectRequired = false;
        $preview = null;

        if ($currentTenant && $key) {
            try {
                $defaultTemplate = $service->getDefaultTemplate($channel, $key);
                $effectiveTemplate = $service->getEffectiveTemplate((string) $currentTenant->id, $channel, $key);
                $isCustom = $service->getOverride((string) $currentTenant->id, $channel, $key) !== null;
                $subjectRequired = $channel === 'email' && !empty(trim((string) ($defaultTemplate['subject'] ?? '')));
            } catch (\Illuminate\Validation\ValidationException) {
                $defaultTemplate = null;
                $effectiveTemplate = null;
                $isCustom = false;
                $subjectRequired = false;
            }
        }

        $previewPayload = $request->attributes->get('editor_preview');
        if (is_array($previewPayload)) {
            $preview = $previewPayload;
            if (
                ($previewPayload['channel'] ?? null) === $channel
                && ($previewPayload['key'] ?? null) === $key
                && is_array($effectiveTemplate)
            ) {
                $effectiveTemplate['subject'] = $previewPayload['subject_input'] ?? $effectiveTemplate['subject'] ?? null;
                $effectiveTemplate['content'] = $previewPayload['content_input'] ?? $effectiveTemplate['content'] ?? '';
            }
        }

        return [
            'channels' => $channels,
            'keys' => $keys,
            'current_channel' => $channel,
            'current_key' => $key,
            'default_template' => $defaultTemplate,
            'effective_template' => $effectiveTemplate,
            'is_custom' => $isCustom,
            'subject_required' => $subjectRequired,
            'preview' => $preview,
            'variables' => $this->notificationTemplateVariables(),
        ];
    }

    private function resolveEditorKey(array $keys, string $channel, string $requestedKey): ?string
    {
        foreach ($keys as $item) {
            $itemKey = (string) ($item['key'] ?? '');
            $itemChannels = (array) ($item['channels'] ?? []);
            if ($itemKey === $requestedKey && in_array($channel, $itemChannels, true)) {
                return $itemKey;
            }
        }

        foreach ($keys as $item) {
            $itemKey = (string) ($item['key'] ?? '');
            $itemChannels = (array) ($item['channels'] ?? []);
            if ($itemKey !== '' && in_array($channel, $itemChannels, true)) {
                return $itemKey;
            }
        }

        return null;
    }

    private function notificationTemplateVariables(): array
    {
        return [
            'CLINIC' => [
                ['key' => '{{clinic.name}}', 'description' => 'Nome da clÃ­nica'],
                ['key' => '{{clinic.phone}}', 'description' => 'Telefone da clÃ­nica'],
                ['key' => '{{clinic.email}}', 'description' => 'E-mail da clÃ­nica'],
                ['key' => '{{clinic.address}}', 'description' => 'EndereÃ§o da clÃ­nica'],
                ['key' => '{{clinic.slug}}', 'description' => 'Identificador da clÃ­nica'],
            ],
            'PATIENT' => [
                ['key' => '{{patient.name}}', 'description' => 'Nome do paciente'],
                ['key' => '{{patient.phone}}', 'description' => 'Telefone do paciente'],
                ['key' => '{{patient.email}}', 'description' => 'E-mail do paciente'],
            ],
            'DOCTOR / PROFESSIONAL' => [
                ['key' => '{{doctor.name}}', 'description' => 'Nome do mÃ©dico'],
                ['key' => '{{doctor.specialty}}', 'description' => 'Especialidade do mÃ©dico'],
                ['key' => '{{professional.name}}', 'description' => 'Nome do profissional'],
                ['key' => '{{professional.specialty}}', 'description' => 'Especialidade do profissional'],
            ],
            'APPOINTMENT' => [
                ['key' => '{{appointment.date}}', 'description' => 'Data da consulta'],
                ['key' => '{{appointment.time}}', 'description' => 'Hora da consulta'],
                ['key' => '{{appointment.datetime}}', 'description' => 'Data e hora da consulta'],
                ['key' => '{{appointment.starts_at}}', 'description' => 'InÃ­cio da consulta'],
                ['key' => '{{appointment.ends_at}}', 'description' => 'Fim da consulta'],
                ['key' => '{{appointment.type}}', 'description' => 'Tipo de consulta'],
                ['key' => '{{appointment.mode}}', 'description' => 'Modalidade da consulta'],
                ['key' => '{{appointment.status}}', 'description' => 'Status da consulta'],
                ['key' => '{{appointment.confirmation_expires_at}}', 'description' => 'Prazo de confirmaÃ§Ã£o da consulta'],
            ],
            'LINKS' => [
                ['key' => '{{links.appointment_confirm}}', 'description' => 'Link para confirmar consulta'],
                ['key' => '{{links.appointment_cancel}}', 'description' => 'Link para cancelar consulta'],
                ['key' => '{{links.appointment_details}}', 'description' => 'Link com detalhes da consulta'],
                ['key' => '{{links.waitlist_offer}}', 'description' => 'Link da oferta da lista de espera'],
            ],
            'WAITLIST' => [
                ['key' => '{{waitlist.offer_expires_at}}', 'description' => 'Validade da oferta da lista de espera'],
                ['key' => '{{waitlist.status}}', 'description' => 'Status da lista de espera'],
            ],
        ];
    }

    private function hasWhatsAppBotFeature(?Tenant $tenant = null): bool
    {
        return app(FeatureAccessService::class)
            ->hasFeature(WhatsAppBotConfigService::FEATURE_NAME, $tenant ?? Tenant::current());
    }

    /**
     * @param array<string, mixed> $settings
     */
    private function tenantUsesOwnOfficialWhatsApp(array $settings): bool
    {
        $driver = strtolower(trim((string) ($settings['whatsapp.driver'] ?? 'global')));
        $provider = strtolower(trim((string) ($settings['WHATSAPP_PROVIDER'] ?? '')));

        return $driver === 'tenancy' && in_array($provider, ['whatsapp_business', 'business'], true);
    }
}


