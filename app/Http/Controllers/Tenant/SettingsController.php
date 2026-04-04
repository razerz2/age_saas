<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\TenantSetting;
use App\Models\Tenant\Integrations;
use App\Models\Tenant\Appointment;
use App\Models\Tenant\Doctor;
use App\Models\Tenant\CalendarSyncState;
use App\Models\Platform\Tenant;
use App\Models\Platform\Estado;
use App\Models\Platform\Cidade;
use App\Services\FeatureAccessService;
use App\Services\Tenant\ProfessionalLabelService;
use App\Services\Tenant\NotificationContextBuilder;
use App\Services\Tenant\NotificationTemplateService;
use App\Services\Tenant\TemplateRenderer;
use App\Services\Tenant\WhatsAppBotConfigService;
use App\Services\WhatsApp\TenantGlobalProviderCatalogService;
use App\Services\WhatsApp\TenantEvolutionGlobalInstanceService;
use App\Services\WhatsApp\TenantEvolutionGlobalOperationsService;
use App\Services\WhatsApp\TenantWahaGlobalInstanceService;
use App\Services\WhatsApp\TenantWahaGlobalOperationsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SettingsController extends Controller
{
    private const BRAZIL_COUNTRY_ID = 31;

    /**
     * Exibe a página de configurações
     */
    public function index(Request $request)
    {
        $brazilTimezones = $this->getBrazilTimezones();

        $tenantGlobalProviderCatalog = app(TenantGlobalProviderCatalogService::class);
        $botConfigService = app(WhatsAppBotConfigService::class);
        $botSettings = $botConfigService->getSettings();
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
            'notifications.send_email_to_doctors' => TenantSetting::get('notifications.send_email_to_doctors') === 'true',
            'notifications.send_whatsapp_to_doctors' => TenantSetting::get('notifications.send_whatsapp_to_doctors') === 'true',
            'notifications.whatsapp.provider_mode' => TenantSetting::get(
                'notifications.whatsapp.provider_mode',
                TenantSetting::get('whatsapp.driver', 'global')
            ),
            'notifications.whatsapp.provider' => TenantSetting::get(
                'notifications.whatsapp.provider',
                TenantSetting::get('whatsapp.provider', TenantSetting::get('whatsapp.global_provider', ''))
            ),
            
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
            'whatsapp.global_provider' => TenantSetting::get('whatsapp.global_provider', ''),
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
            'EVOLUTION_BASE_URL' => TenantSetting::get('whatsapp.evolution.base_url', ''),
            'EVOLUTION_API_KEY' => TenantSetting::get('whatsapp.evolution.api_key', ''),
            'EVOLUTION_INSTANCE' => TenantSetting::get('whatsapp.evolution.instance', 'default'),

            // Bot de WhatsApp
            'whatsapp_bot.enabled' => (bool) ($botSettings['enabled'] ?? false),
            'whatsapp_bot.provider_mode' => (string) ($botSettings['provider_mode'] ?? WhatsAppBotConfigService::MODE_SHARED_WITH_NOTIFICATIONS),
            'whatsapp_bot.provider' => (string) ($botSettings['provider'] ?? 'whatsapp_business'),
            'whatsapp_bot.welcome_message' => (string) ($botSettings['welcome_message'] ?? ''),
            'whatsapp_bot.disabled_message' => (string) ($botSettings['disabled_message'] ?? ''),
            'whatsapp_bot.allow_schedule' => (bool) ($botSettings['allow_schedule'] ?? false),
            'whatsapp_bot.allow_view_appointments' => (bool) ($botSettings['allow_view_appointments'] ?? false),
            'whatsapp_bot.allow_cancel_appointments' => (bool) ($botSettings['allow_cancel_appointments'] ?? false),
            'whatsapp_bot.META_ACCESS_TOKEN' => (string) ($botSettings['meta_access_token'] ?? ''),
            'whatsapp_bot.META_PHONE_NUMBER_ID' => (string) ($botSettings['meta_phone_number_id'] ?? ''),
            'whatsapp_bot.META_WABA_ID' => (string) ($botSettings['meta_waba_id'] ?? ''),
            'whatsapp_bot.ZAPI_API_URL' => (string) ($botSettings['zapi_api_url'] ?? 'https://api.z-api.io'),
            'whatsapp_bot.ZAPI_TOKEN' => (string) ($botSettings['zapi_token'] ?? ''),
            'whatsapp_bot.ZAPI_CLIENT_TOKEN' => (string) ($botSettings['zapi_client_token'] ?? ''),
            'whatsapp_bot.ZAPI_INSTANCE_ID' => (string) ($botSettings['zapi_instance_id'] ?? ''),
            'whatsapp_bot.WAHA_BASE_URL' => (string) ($botSettings['waha_base_url'] ?? ''),
            'whatsapp_bot.WAHA_API_KEY' => (string) ($botSettings['waha_api_key'] ?? ''),
            'whatsapp_bot.WAHA_SESSION' => (string) ($botSettings['waha_session'] ?? 'default'),
            'whatsapp_bot.EVOLUTION_BASE_URL' => (string) ($botSettings['evolution_base_url'] ?? ''),
            'whatsapp_bot.EVOLUTION_API_KEY' => (string) ($botSettings['evolution_api_key'] ?? ''),
            'whatsapp_bot.EVOLUTION_INSTANCE' => (string) ($botSettings['evolution_instance'] ?? 'default'),
            'whatsapp_bot.entry_keywords_text' => implode("\n", (array) ($botSettings['entry_keywords'] ?? [])),
            'whatsapp_bot.exit_keywords_text' => implode("\n", (array) ($botSettings['exit_keywords'] ?? [])),
            'whatsapp_bot.messages.welcome' => (string) data_get($botSettings, 'messages.welcome', ''),
            'whatsapp_bot.messages.fallback' => (string) data_get($botSettings, 'messages.fallback', ''),
            'whatsapp_bot.messages.invalid_cpf' => (string) data_get($botSettings, 'messages.invalid_cpf', ''),
            'whatsapp_bot.messages.patient_not_found' => (string) data_get($botSettings, 'messages.patient_not_found', ''),
            'whatsapp_bot.messages.registration_start' => (string) data_get($botSettings, 'messages.registration_start', ''),
            'whatsapp_bot.messages.registration_completed' => (string) data_get($botSettings, 'messages.registration_completed', ''),
            'whatsapp_bot.messages.internal_error' => (string) data_get($botSettings, 'messages.internal_error', ''),
            'whatsapp_bot.messages.no_slots_available' => (string) data_get($botSettings, 'messages.no_slots_available', ''),
            'whatsapp_bot.messages.appointment_created' => (string) data_get($botSettings, 'messages.appointment_created', ''),
            'whatsapp_bot.messages.appointment_canceled' => (string) data_get($botSettings, 'messages.appointment_canceled', ''),
            'whatsapp_bot.messages.back_to_menu' => (string) data_get($botSettings, 'messages.back_to_menu', ''),
            'whatsapp_bot.messages.inactivity_exit' => (string) data_get($botSettings, 'messages.inactivity_exit', ''),
            'whatsapp_bot.session.idle_timeout_minutes' => (int) data_get($botSettings, 'session.idle_timeout_minutes', 30),
            'whatsapp_bot.session.absolute_timeout_minutes' => (int) data_get($botSettings, 'session.absolute_timeout_minutes', 240),
            'whatsapp_bot.session.end_on_inactivity' => (bool) data_get($botSettings, 'session.end_on_inactivity', true),
            'whatsapp_bot.session.clear_context_on_end' => (bool) data_get($botSettings, 'session.clear_context_on_end', true),
            'whatsapp_bot.session.allow_resume_previous' => (bool) data_get($botSettings, 'session.allow_resume_previous', false),
            'whatsapp_bot.session.reset_keywords_text' => implode("\n", (array) data_get($botSettings, 'session.reset_keywords', WhatsAppBotConfigService::DEFAULT_SESSION_RESET_KEYWORDS)),
            'whatsapp_bot.identification.require_cpf_for_intents_text' => implode("\n", (array) data_get($botSettings, 'identification.require_cpf_for_intents', WhatsAppBotConfigService::DEFAULT_REQUIRE_CPF_FOR_INTENTS)),
            'whatsapp_bot.identification.require_valid_cpf' => (bool) data_get($botSettings, 'identification.require_valid_cpf', true),
            'whatsapp_bot.identification.max_attempts' => (int) data_get($botSettings, 'identification.max_attempts', 3),
            'whatsapp_bot.identification.reuse_identified_patient' => (bool) data_get($botSettings, 'identification.reuse_identified_patient', true),
            'whatsapp_bot.identification.lookup_order_text' => implode("\n", (array) data_get($botSettings, 'identification.lookup_order', WhatsAppBotConfigService::DEFAULT_IDENTIFICATION_LOOKUP_ORDER)),
            'whatsapp_bot.menu.max_options' => (int) data_get($botSettings, 'menu.max_options', 6),
            'whatsapp_bot.menu.show_again_after_action' => (bool) data_get($botSettings, 'menu.show_again_after_action', true),
            'whatsapp_bot.menu.return_after_fallback' => (bool) data_get($botSettings, 'menu.return_after_fallback', true),
            'whatsapp_bot.menu.options_json' => json_encode((array) data_get($botSettings, 'menu.options', WhatsAppBotConfigService::DEFAULT_MENU_OPTIONS), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
            
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
            'professional.environment_profile' => TenantSetting::get('professional.environment_profile', ''),
            
            // Aparência
            'appearance.logo' => $appearanceLogoLight,
            'appearance.logo_mini' => $appearanceLogoMiniLight,
            'appearance.logo_light' => $appearanceLogoLight,
            'appearance.logo_dark' => $appearanceLogoDark,
            'appearance.logo_mini_light' => $appearanceLogoMiniLight,
            'appearance.logo_mini_dark' => $appearanceLogoMiniDark,
            'appearance.favicon' => $appearanceFavicon,
        ];

        if (($settings['whatsapp.global_provider'] ?? '') === '') {
            $settings['whatsapp.global_provider'] = $tenantGlobalProviderCatalog->resolveTenantGlobalProvider(null) ?? '';
        }

        $professionalLabelService = app(ProfessionalLabelService::class);
        $settings['professional.environment_profile'] = $professionalLabelService->sanitizeEnvironmentProfile(
            $settings['professional.environment_profile'] ?? '',
            ''
        );
        $professionalEnvironmentProfiles = ProfessionalLabelService::environmentProfileOptions();
        $professionalEnvironmentPresets = ProfessionalLabelService::presets();

        // Buscar integracoes ativas
        $integrations = Integrations::where('is_enabled', true)->get();

        // Cadastros genericos opcionais (feature metadata)
        $googleCalendarIntegration = Integrations::where('key', 'google_calendar')->first();
        $appleCalendarIntegration = Integrations::where('key', 'apple_calendar')->first();

        // Google usa credenciais globais na Platform com fallback para services.google.* (ambiente)
        $hasGoogleCalendarIntegration = has_google_oauth_credentials();
        $hasGoogleCalendarTokenTable = Schema::connection('tenant')->hasTable('google_calendar_tokens');

        // Apple depende da infraestrutura/tabela de tokens por medico
        $hasAppleCalendarIntegration = Schema::connection('tenant')->hasTable('apple_calendar_tokens');

        $doctorRelations = ['user'];
        if ($hasGoogleCalendarTokenTable) {
            $doctorRelations[] = 'googleCalendarToken';
        }
        if ($hasAppleCalendarIntegration) {
            $doctorRelations[] = 'appleCalendarToken';
        }

        $calendarSyncDoctors = Doctor::with($doctorRelations)
            ->whereHas('user', function ($query) {
                $query->where('status', 'active');
            })
            ->orderBy('id')
            ->get();

        if (!$hasGoogleCalendarTokenTable) {
            foreach ($calendarSyncDoctors as $doctor) {
                $doctor->setRelation('googleCalendarToken', null);
            }
        }

        if (!$hasAppleCalendarIntegration) {
            foreach ($calendarSyncDoctors as $doctor) {
                $doctor->setRelation('appleCalendarToken', null);
            }
        }

        $calendarSyncLastSyncByDoctor = collect();
        if (
            Schema::connection('tenant')->hasTable('calendar_sync_state')
            && Schema::connection('tenant')->hasTable('appointments')
        ) {
            $calendarSyncLastSyncByDoctor = CalendarSyncState::query()
                ->join('appointments', 'appointments.id', '=', 'calendar_sync_state.appointment_id')
                ->selectRaw('appointments.doctor_id as doctor_id, MAX(calendar_sync_state.last_sync_at) as last_sync_at')
                ->groupBy('appointments.doctor_id')
                ->pluck('last_sync_at', 'doctor_id');
        }
        // Obter tenant atual para gerar o link de agendamento público
        $currentTenant = Tenant::current();
        $publicBookingUrl = null;
        
        if ($currentTenant) {
            $publicBookingUrl = url('/customer/' . $currentTenant->subdomain . '/agendamento/identificar');
        }

        $editor = $this->buildEditorViewData($currentTenant, $request);
        $localizacao = $currentTenant ? $currentTenant->localizacao : null;
        $showOfficialTemplatesTab = $this->tenantUsesOwnOfficialWhatsApp($settings);
        $showWhatsAppBotTab = $this->hasWhatsAppBotFeature($currentTenant);
        $whatsAppBotEffectiveProvider = $botConfigService->resolveEffectiveProviderConfig();
        $whatsappGlobalProviderOptions = $tenantGlobalProviderCatalog->enabledProviderOptions();
        $whatsappEnabledGlobalProviders = $tenantGlobalProviderCatalog->enabledProviders();
        $tenantWahaGlobalOperations = app(TenantWahaGlobalOperationsService::class);
        $showWahaGlobalTab = $tenantWahaGlobalOperations->shouldShowTab();
        $wahaGlobalStatus = $showWahaGlobalTab
            ? $tenantWahaGlobalOperations->status(false)
            : null;
        $tenantEvolutionGlobalOperations = app(TenantEvolutionGlobalOperationsService::class);
        $showEvolutionGlobalTab = $tenantEvolutionGlobalOperations->shouldShowTab();
        $evolutionGlobalStatus = $showEvolutionGlobalTab
            ? $tenantEvolutionGlobalOperations->status(false)
            : null;

        $initialTab = (string) $request->get('tab', 'clinica');
        if ($initialTab === 'bot-whatsapp' && !$showWhatsAppBotTab) {
            $initialTab = 'clinica';
        }
        if ($initialTab === 'waha' && !$showWahaGlobalTab) {
            $initialTab = 'clinica';
        }
        if ($initialTab === 'evolution' && !$showEvolutionGlobalTab) {
            $initialTab = 'clinica';
        }

        return view('tenant.settings.index', compact(
            'settings', 
            'integrations', 
            'hasGoogleCalendarIntegration', 
            'googleCalendarIntegration', 
            'hasAppleCalendarIntegration',
            'appleCalendarIntegration',
            'calendarSyncDoctors',
            'calendarSyncLastSyncByDoctor',
            'publicBookingUrl',
            'currentTenant',
            'localizacao',
            'editor',
            'showOfficialTemplatesTab',
            'showWhatsAppBotTab',
            'whatsAppBotEffectiveProvider',
            'whatsappGlobalProviderOptions',
            'whatsappEnabledGlobalProviders',
            'showWahaGlobalTab',
            'wahaGlobalStatus',
            'showEvolutionGlobalTab',
            'evolutionGlobalStatus',
            'initialTab',
            'brazilTimezones',
            'professionalEnvironmentProfiles',
            'professionalEnvironmentPresets'
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
            'audience' => 'nullable|string|in:patient,doctor',
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

        $redirect = redirect()->to($this->buildEditorSettingsUrl(
            $validated['channel'],
            $validated['key'],
            (string) ($validated['audience'] ?? 'patient')
        ))
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
            'audience' => 'nullable|string|in:patient,doctor',
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

        return redirect()->to($this->buildEditorSettingsUrl(
            $validated['channel'],
            $validated['key'],
            (string) ($validated['audience'] ?? 'patient')
        ))
            ->with('success', 'Template restaurado para o padrão.');
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
            'audience' => 'nullable|string|in:patient,doctor',
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
            'audience' => (string) ($validated['audience'] ?? 'patient'),
            'channel' => $validated['channel'],
            'key' => $validated['key'],
        ]);
        $request->attributes->set('editor_preview', $preview);

        return $this->index($request);
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
            'pais_id' => self::BRAZIL_COUNTRY_ID,
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
        $brazilTimezones = $this->getBrazilTimezones();

        $request->validate([
            'timezone' => ['required', 'string', Rule::in($brazilTimezones)],
            'date_format' => 'required|string|in:d/m/Y,Y-m-d,m/d/Y',
            'time_format' => 'required|string|in:H:i,h:i A',
            'language' => 'required|string|in:pt_BR,en_US,es_ES',
        ], [
            'timezone.in' => 'Selecione um fuso horário válido do Brasil.',
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
        $tenantGlobalProviderCatalog = app(TenantGlobalProviderCatalogService::class);

        $validator = Validator::make($request->all(), [
            'notifications_appointments_enabled' => 'nullable|boolean',
            'notifications_form_responses_enabled' => 'nullable|boolean',
            'notifications_send_email_to_patients' => 'nullable|boolean',
            'notifications_send_whatsapp_to_patients' => 'nullable|boolean',
            'notifications_send_email_to_doctors' => 'nullable|boolean',
            'notifications_send_whatsapp_to_doctors' => 'nullable|boolean',

            'email_driver' => 'required|in:global,tenancy',
            'email_host' => 'required_if:email_driver,tenancy',
            'email_port' => 'required_if:email_driver,tenancy',
            'email_username' => 'required_if:email_driver,tenancy',
            'email_password' => 'required_if:email_driver,tenancy',
            'email_from_name' => 'nullable|string',
            'email_from_address' => 'nullable|email',

            'whatsapp_driver' => 'required|in:global,tenancy',
            'whatsapp_provider' => 'nullable|in:whatsapp_business,zapi,waha,evolution',
            'whatsapp_global_provider' => 'nullable|string',
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
            'EVOLUTION_BASE_URL' => 'nullable|url',
            'EVOLUTION_API_KEY' => 'nullable|string',
            'EVOLUTION_INSTANCE' => 'nullable|string',

            'whatsapp_bot_provider_mode' => ['required', 'string', Rule::in([
                WhatsAppBotConfigService::MODE_SHARED_WITH_NOTIFICATIONS,
                WhatsAppBotConfigService::MODE_DEDICATED,
            ])],
            'whatsapp_bot_provider' => ['nullable', 'string', Rule::in(WhatsAppBotConfigService::SUPPORTED_PROVIDERS)],
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
            'bot_evolution_base_url' => 'nullable|url',
            'bot_evolution_api_key' => 'nullable|string',
            'bot_evolution_instance' => 'nullable|string',
        ]);

        $validator->after(function ($validator) use ($request, $tenantGlobalProviderCatalog) {
            if ($request->input('whatsapp_driver') === 'global') {
                $enabledGlobalProviders = $tenantGlobalProviderCatalog->enabledProviders();
                if ($enabledGlobalProviders === []) {
                    $validator->errors()->add(
                        'whatsapp_global_provider',
                        'Nenhum provider global de WhatsApp esta habilitado pela Platform.'
                    );
                    return;
                }

                $selectedGlobalProvider = $tenantGlobalProviderCatalog->normalizeProvider(
                    (string) $request->input('whatsapp_global_provider', '')
                );

                if ($selectedGlobalProvider === '') {
                    $validator->errors()->add(
                        'whatsapp_global_provider',
                        'Selecione um provider global de WhatsApp.'
                    );
                    return;
                }

                if (!in_array($selectedGlobalProvider, $enabledGlobalProviders, true)) {
                    $validator->errors()->add(
                        'whatsapp_global_provider',
                        'Provider global invalido para o tenant. Verifique os providers habilitados pela Platform.'
                    );
                }

                return;
            }

            if ($request->input('whatsapp_driver') !== 'tenancy') {
                return;
            }

            $provider = $request->input('whatsapp_provider');
            if (!in_array($provider, ['whatsapp_business', 'zapi', 'waha', 'evolution'], true)) {
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

            if ($provider === 'evolution') {
                if (!$request->filled('EVOLUTION_BASE_URL')) {
                    $validator->errors()->add('EVOLUTION_BASE_URL', 'A Base URL e obrigatoria para o provedor Evolution.');
                }
                if (!$request->filled('EVOLUTION_API_KEY')) {
                    $validator->errors()->add('EVOLUTION_API_KEY', 'A API Key e obrigatoria para o provedor Evolution.');
                }
                if (!$request->filled('EVOLUTION_INSTANCE')) {
                    $validator->errors()->add('EVOLUTION_INSTANCE', 'O nome da instancia e obrigatorio para o provedor Evolution.');
                }
            }

            $botProviderMode = (string) $request->input('whatsapp_bot_provider_mode');
            if ($botProviderMode !== WhatsAppBotConfigService::MODE_DEDICATED) {
                return;
            }

            $botProvider = (string) $request->input('whatsapp_bot_provider');
            if (!in_array($botProvider, WhatsAppBotConfigService::SUPPORTED_PROVIDERS, true)) {
                $validator->errors()->add('whatsapp_bot_provider', 'Selecione um provedor de WhatsApp valido para o bot.');
                return;
            }

            if ($botProvider === 'whatsapp_business') {
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

            if ($botProvider === 'zapi') {
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

            if ($botProvider === 'waha') {
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

            if ($botProvider === 'evolution') {
                if (!$request->filled('bot_evolution_base_url')) {
                    $validator->errors()->add('bot_evolution_base_url', 'A Base URL e obrigatoria para o provedor Evolution.');
                }
                if (!$request->filled('bot_evolution_api_key')) {
                    $validator->errors()->add('bot_evolution_api_key', 'A API Key e obrigatoria para o provedor Evolution.');
                }
                if (!$request->filled('bot_evolution_instance')) {
                    $validator->errors()->add('bot_evolution_instance', 'O nome da instancia e obrigatorio para o provedor Evolution.');
                }
            }
        });

        $validated = $validator->validate();
        $selectedGlobalProvider = null;

        if ($validated['whatsapp_driver'] === 'global') {
            $selectedGlobalProvider = $tenantGlobalProviderCatalog->normalizeProvider(
                (string) ($validated['whatsapp_global_provider'] ?? '')
            );

            if ($selectedGlobalProvider === 'waha') {
                $provisioningResult = app(TenantWahaGlobalInstanceService::class)->ensureProvisionedForCurrentTenant();

                if (empty($provisioningResult['ok'])) {
                    return back()
                        ->withErrors([
                            'whatsapp_global_provider' => (string) ($provisioningResult['message'] ?? 'Nao foi possivel provisionar a sessao WAHA da clinica.'),
                        ])
                        ->withInput();
                }
            }

            if ($selectedGlobalProvider === 'evolution') {
                $provisioningResult = app(TenantEvolutionGlobalInstanceService::class)->ensureProvisionedForCurrentTenant();

                if (empty($provisioningResult['ok'])) {
                    return back()
                        ->withErrors([
                            'whatsapp_global_provider' => (string) ($provisioningResult['message'] ?? 'Nao foi possivel provisionar a instancia Evolution da clinica.'),
                        ])
                        ->withInput();
                }
            }
        }

        TenantSetting::set('notifications.appointments.enabled', $request->has('notifications_appointments_enabled') ? 'true' : 'false');
        TenantSetting::set('notifications.form_responses.enabled', $request->has('notifications_form_responses_enabled') ? 'true' : 'false');

        TenantSetting::set('notifications.send_email_to_patients', $request->has('notifications_send_email_to_patients') ? 'true' : 'false');
        TenantSetting::set('notifications.send_whatsapp_to_patients', $request->has('notifications_send_whatsapp_to_patients') ? 'true' : 'false');
        TenantSetting::set('notifications.send_email_to_doctors', $request->has('notifications_send_email_to_doctors') ? 'true' : 'false');
        TenantSetting::set('notifications.send_whatsapp_to_doctors', $request->has('notifications_send_whatsapp_to_doctors') ? 'true' : 'false');

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
            TenantSetting::set('whatsapp.evolution.base_url', $validated['EVOLUTION_BASE_URL'] ?? '');
            TenantSetting::set('whatsapp.evolution.api_key', $validated['EVOLUTION_API_KEY'] ?? '');
            TenantSetting::set('whatsapp.evolution.instance', $validated['EVOLUTION_INSTANCE'] ?? 'default');
            if (trim((string) TenantSetting::get('whatsapp.global_provider', '')) === '') {
                $fallbackGlobalProvider = $tenantGlobalProviderCatalog->resolveTenantGlobalProvider(null);
                if ($fallbackGlobalProvider !== null) {
                    TenantSetting::set('whatsapp.global_provider', $fallbackGlobalProvider);
                }
            }
        } else {
            $selectedGlobalProvider = $selectedGlobalProvider ?? $tenantGlobalProviderCatalog->normalizeProvider(
                (string) ($validated['whatsapp_global_provider'] ?? '')
            );
            TenantSetting::set('whatsapp.global_provider', $selectedGlobalProvider);
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
            TenantSetting::set('whatsapp.evolution.base_url', '');
            TenantSetting::set('whatsapp.evolution.api_key', '');
            TenantSetting::set('whatsapp.evolution.instance', '');
        }

        TenantSetting::set('notifications.whatsapp.provider_mode', $validated['whatsapp_driver']);
        $notificationsProvider = $validated['whatsapp_driver'] === 'tenancy'
            ? (string) ($validated['whatsapp_provider'] ?? '')
            : (string) ($selectedGlobalProvider ?? '');
        TenantSetting::set('notifications.whatsapp.provider', $notificationsProvider);

        TenantSetting::set('whatsapp_bot.provider_mode', (string) $validated['whatsapp_bot_provider_mode']);
        if ((string) $validated['whatsapp_bot_provider_mode'] === WhatsAppBotConfigService::MODE_DEDICATED) {
            $botProvider = (string) ($validated['whatsapp_bot_provider'] ?? 'whatsapp_business');
            TenantSetting::set('whatsapp_bot.provider', $botProvider);

            if ($botProvider === 'whatsapp_business') {
                TenantSetting::set('whatsapp_bot.meta.access_token', (string) ($validated['bot_meta_access_token'] ?? ''));
                TenantSetting::set('whatsapp_bot.meta.phone_number_id', (string) ($validated['bot_meta_phone_number_id'] ?? ''));
                TenantSetting::set('whatsapp_bot.meta.waba_id', (string) ($validated['bot_meta_waba_id'] ?? ''));
            }

            if ($botProvider === 'zapi') {
                TenantSetting::set('whatsapp_bot.zapi.api_url', (string) ($validated['bot_zapi_api_url'] ?? ''));
                TenantSetting::set('whatsapp_bot.zapi.token', (string) ($validated['bot_zapi_token'] ?? ''));
                TenantSetting::set('whatsapp_bot.zapi.client_token', (string) ($validated['bot_zapi_client_token'] ?? ''));
                TenantSetting::set('whatsapp_bot.zapi.instance_id', (string) ($validated['bot_zapi_instance_id'] ?? ''));
            }

            if ($botProvider === 'waha') {
                TenantSetting::set('whatsapp_bot.waha.base_url', (string) ($validated['bot_waha_base_url'] ?? ''));
                TenantSetting::set('whatsapp_bot.waha.api_key', (string) ($validated['bot_waha_api_key'] ?? ''));
                TenantSetting::set('whatsapp_bot.waha.session', (string) ($validated['bot_waha_session'] ?? 'default'));
            }

            if ($botProvider === 'evolution') {
                TenantSetting::set('whatsapp_bot.evolution.base_url', (string) ($validated['bot_evolution_base_url'] ?? ''));
                TenantSetting::set('whatsapp_bot.evolution.api_key', (string) ($validated['bot_evolution_api_key'] ?? ''));
                TenantSetting::set('whatsapp_bot.evolution.instance', (string) ($validated['bot_evolution_instance'] ?? 'default'));
            }
        }

        return redirect()->route('tenant.settings.index', ['slug' => tenant()->subdomain])
            ->with('success', 'Configurações de notificações atualizadas com sucesso.');
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
            'whatsapp_bot_entry_keywords' => 'nullable|string|max:8000',
            'whatsapp_bot_exit_keywords' => 'nullable|string|max:8000',
            'whatsapp_bot_message_fallback' => 'nullable|string|max:2000',
            'whatsapp_bot_message_invalid_cpf' => 'nullable|string|max:2000',
            'whatsapp_bot_message_patient_not_found' => 'nullable|string|max:2000',
            'whatsapp_bot_message_registration_start' => 'nullable|string|max:2000',
            'whatsapp_bot_message_registration_completed' => 'nullable|string|max:2000',
            'whatsapp_bot_message_internal_error' => 'nullable|string|max:2000',
            'whatsapp_bot_message_no_slots_available' => 'nullable|string|max:2000',
            'whatsapp_bot_message_appointment_created' => 'nullable|string|max:2000',
            'whatsapp_bot_message_appointment_canceled' => 'nullable|string|max:2000',
            'whatsapp_bot_message_back_to_menu' => 'nullable|string|max:2000',
            'whatsapp_bot_message_inactivity_exit' => 'nullable|string|max:2000',
            'whatsapp_bot_session_idle_timeout_minutes' => 'nullable|integer|min:1|max:1440',
            'whatsapp_bot_session_absolute_timeout_minutes' => 'nullable|integer|min:1|max:10080',
            'whatsapp_bot_session_end_on_inactivity' => 'nullable|boolean',
            'whatsapp_bot_session_clear_context_on_end' => 'nullable|boolean',
            'whatsapp_bot_session_allow_resume_previous' => 'nullable|boolean',
            'whatsapp_bot_session_reset_keywords' => 'nullable|string|max:8000',
            'whatsapp_bot_identification_require_cpf_for_intents' => 'nullable|string|max:4000',
            'whatsapp_bot_identification_require_valid_cpf' => 'nullable|boolean',
            'whatsapp_bot_identification_max_attempts' => 'nullable|integer|min:1|max:20',
            'whatsapp_bot_identification_reuse_identified_patient' => 'nullable|boolean',
            'whatsapp_bot_identification_lookup_order' => 'nullable|string|max:4000',
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
            'bot_evolution_base_url' => 'nullable|url',
            'bot_evolution_api_key' => 'nullable|string',
            'bot_evolution_instance' => 'nullable|string',
        ]);

        $validator->after(function ($validator) use ($request) {
            $idleTimeout = (int) $request->input('whatsapp_bot_session_idle_timeout_minutes', 30);
            $absoluteTimeout = (int) $request->input('whatsapp_bot_session_absolute_timeout_minutes', 240);
            if ($absoluteTimeout < $idleTimeout) {
                $validator->errors()->add(
                    'whatsapp_bot_session_absolute_timeout_minutes',
                    'O timeout absoluto deve ser maior ou igual ao timeout de inatividade.'
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

            if ($provider === 'evolution') {
                if (!$request->filled('bot_evolution_base_url')) {
                    $validator->errors()->add('bot_evolution_base_url', 'A Base URL e obrigatoria para o provedor Evolution.');
                }
                if (!$request->filled('bot_evolution_api_key')) {
                    $validator->errors()->add('bot_evolution_api_key', 'A API Key e obrigatoria para o provedor Evolution.');
                }
                if (!$request->filled('bot_evolution_instance')) {
                    $validator->errors()->add('bot_evolution_instance', 'O nome da instancia e obrigatorio para o provedor Evolution.');
                }
            }
        });

        $validated = $validator->validate();

        TenantSetting::set('whatsapp_bot.enabled', $request->has('whatsapp_bot_enabled') ? 'true' : 'false');
        TenantSetting::set('whatsapp_bot.provider_mode', $validated['whatsapp_bot_provider_mode']);
        TenantSetting::set('whatsapp_bot.welcome_message', (string) ($validated['whatsapp_bot_welcome_message'] ?? ''));
        TenantSetting::set('whatsapp_bot.messages.welcome', (string) ($validated['whatsapp_bot_welcome_message'] ?? ''));
        TenantSetting::set('whatsapp_bot.disabled_message', (string) ($validated['whatsapp_bot_disabled_message'] ?? ''));
        TenantSetting::set('whatsapp_bot.messages.fallback', (string) ($validated['whatsapp_bot_message_fallback'] ?? ''));
        TenantSetting::set('whatsapp_bot.messages.invalid_cpf', (string) ($validated['whatsapp_bot_message_invalid_cpf'] ?? ''));
        TenantSetting::set('whatsapp_bot.messages.patient_not_found', (string) ($validated['whatsapp_bot_message_patient_not_found'] ?? ''));
        TenantSetting::set('whatsapp_bot.messages.registration_start', (string) ($validated['whatsapp_bot_message_registration_start'] ?? ''));
        TenantSetting::set('whatsapp_bot.messages.registration_completed', (string) ($validated['whatsapp_bot_message_registration_completed'] ?? ''));
        TenantSetting::set('whatsapp_bot.messages.internal_error', (string) ($validated['whatsapp_bot_message_internal_error'] ?? ''));
        TenantSetting::set('whatsapp_bot.messages.no_slots_available', (string) ($validated['whatsapp_bot_message_no_slots_available'] ?? ''));
        TenantSetting::set('whatsapp_bot.messages.appointment_created', (string) ($validated['whatsapp_bot_message_appointment_created'] ?? ''));
        TenantSetting::set('whatsapp_bot.messages.appointment_canceled', (string) ($validated['whatsapp_bot_message_appointment_canceled'] ?? ''));
        TenantSetting::set('whatsapp_bot.messages.back_to_menu', (string) ($validated['whatsapp_bot_message_back_to_menu'] ?? ''));
        TenantSetting::set('whatsapp_bot.messages.inactivity_exit', (string) ($validated['whatsapp_bot_message_inactivity_exit'] ?? ''));
        TenantSetting::set(
            'whatsapp_bot.entry_keywords',
            json_encode($this->parseKeywordListTextarea((string) ($validated['whatsapp_bot_entry_keywords'] ?? '')), JSON_UNESCAPED_UNICODE)
        );
        TenantSetting::set(
            'whatsapp_bot.exit_keywords',
            json_encode($this->parseKeywordListTextarea((string) ($validated['whatsapp_bot_exit_keywords'] ?? '')), JSON_UNESCAPED_UNICODE)
        );
        TenantSetting::set('whatsapp_bot.session.idle_timeout_minutes', (string) ((int) ($validated['whatsapp_bot_session_idle_timeout_minutes'] ?? 30)));
        TenantSetting::set('whatsapp_bot.session.absolute_timeout_minutes', (string) ((int) ($validated['whatsapp_bot_session_absolute_timeout_minutes'] ?? 240)));
        TenantSetting::set('whatsapp_bot.session.end_on_inactivity', $request->has('whatsapp_bot_session_end_on_inactivity') ? 'true' : 'false');
        TenantSetting::set('whatsapp_bot.session.clear_context_on_end', $request->has('whatsapp_bot_session_clear_context_on_end') ? 'true' : 'false');
        TenantSetting::set('whatsapp_bot.session.allow_resume_previous', $request->has('whatsapp_bot_session_allow_resume_previous') ? 'true' : 'false');
        TenantSetting::set(
            'whatsapp_bot.session.reset_keywords',
            json_encode($this->parseKeywordListTextarea((string) ($validated['whatsapp_bot_session_reset_keywords'] ?? '')), JSON_UNESCAPED_UNICODE)
        );
        TenantSetting::set(
            'whatsapp_bot.identification.require_cpf_for_intents',
            json_encode($this->parseKeywordListTextarea((string) ($validated['whatsapp_bot_identification_require_cpf_for_intents'] ?? '')), JSON_UNESCAPED_UNICODE)
        );
        TenantSetting::set('whatsapp_bot.identification.require_valid_cpf', $request->has('whatsapp_bot_identification_require_valid_cpf') ? 'true' : 'false');
        TenantSetting::set('whatsapp_bot.identification.max_attempts', (string) ((int) ($validated['whatsapp_bot_identification_max_attempts'] ?? 3)));
        TenantSetting::set('whatsapp_bot.identification.reuse_identified_patient', $request->has('whatsapp_bot_identification_reuse_identified_patient') ? 'true' : 'false');
        TenantSetting::set(
            'whatsapp_bot.identification.lookup_order',
            json_encode($this->parseKeywordListTextarea((string) ($validated['whatsapp_bot_identification_lookup_order'] ?? '')), JSON_UNESCAPED_UNICODE)
        );
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

            if ($provider === 'evolution') {
                TenantSetting::set('whatsapp_bot.evolution.base_url', (string) ($validated['bot_evolution_base_url'] ?? ''));
                TenantSetting::set('whatsapp_bot.evolution.api_key', (string) ($validated['bot_evolution_api_key'] ?? ''));
                TenantSetting::set('whatsapp_bot.evolution.instance', (string) ($validated['bot_evolution_instance'] ?? 'default'));
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

        $hasGoogleCredentials = has_google_oauth_credentials();
        $hasAppleInfrastructure = Schema::connection('tenant')->hasTable('apple_calendar_tokens');

        if ($request->has('integrations_google_calendar_enabled')) {
            if (!$hasGoogleCredentials) {
                return redirect()->route('tenant.settings.index', ['slug' => tenant()->subdomain])
                    ->with('error', 'Nao foi possivel habilitar Google Calendar: configure as credenciais globais na Platform > Configuracoes > Integracoes (ou fallback no ambiente).');
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
            if (!$hasAppleInfrastructure) {
                return redirect()->route('tenant.settings.index', ['slug' => tenant()->subdomain])
                    ->with('error', 'Nao foi possivel habilitar Apple Calendar: execute as migrations de token do Apple Calendar no tenant.');
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
            ->with('success', 'Configuracoes de integracoes atualizadas com sucesso.');
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
    public function updateProfessionals(Request $request, ProfessionalLabelService $professionalLabelService)
    {
        $request->validate([
            'professional_customization_enabled' => 'nullable|boolean',
            'professional_label_singular' => 'nullable|string|max:50',
            'professional_label_plural' => 'nullable|string|max:50',
            'professional_registration_label' => 'nullable|string|max:50',
            'professional_environment_profile' => 'nullable|string|max:50',
        ]);

        $customizationEnabled = $request->filled('professional_customization_enabled') || $request->has('professional_customization_enabled');

        if ($customizationEnabled) {
            TenantSetting::enable('professional.customization_enabled');

            $profile = $professionalLabelService->sanitizeEnvironmentProfile(
                $request->input('professional_environment_profile'),
                ProfessionalLabelService::PROFILE_MEDICAL
            );

            TenantSetting::set('professional.environment_profile', $profile);
            TenantSetting::set('professional.label_singular', trim((string) $request->input('professional_label_singular', '')));
            TenantSetting::set('professional.label_plural', trim((string) $request->input('professional_label_plural', '')));
            TenantSetting::set('professional.registration_label', trim((string) $request->input('professional_registration_label', '')));
        } else {
            TenantSetting::disable('professional.customization_enabled');

            TenantSetting::set('professional.environment_profile', '');
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

    private function renderNotificationPreview(
        Tenant $tenant,
        string $channel,
        string $key,
        ?string $subject,
        string $content,
        NotificationContextBuilder $contextBuilder,
        TemplateRenderer $renderer
    ): array {
        $appointmentQuery = Appointment::query()
            ->with([
                'patient',
                'doctor.user',
                'doctor.specialties',
                'calendar.doctor.user',
                'specialty',
                'type',
                'onlineInstructions',
            ])
            ->orderByDesc('starts_at')
            ->orderByDesc('created_at');

        if (str_starts_with($key, 'online_appointment.')) {
            $appointmentQuery->where('appointment_mode', 'online');
        }

        $latestAppointment = $appointmentQuery->first();

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
        if ($key === 'appointment.form_requested.patient') {
            $context = $this->applyAppointmentFormRequestedPreviewFallback($context, $tenant);
        }
        if (str_starts_with($key, 'form.')) {
            $context = $this->applyFormResponsePreviewFallback($context, $tenant);
        }
        if (str_starts_with($key, 'online_appointment.')) {
            $context = $this->applyOnlineAppointmentPreviewFallback($context, $tenant);
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
        $labels = $this->professionalLabelsForTemplateContext();

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
            'online' => [
                'is_online' => false,
                'meeting_link' => '',
                'meeting_app' => '',
                'general_instructions' => '',
                'patient_instructions' => '',
                'instructions_sent' => false,
                'instructions_sent_email_at' => '',
                'instructions_sent_whatsapp_at' => '',
            ],
            'links' => [
                'appointment_confirm' => '',
                'appointment_cancel' => '',
                'appointment_details' => '',
                'online_appointment_details' => '',
                'waitlist_offer' => '',
                'form_response' => '',
                'form_fill' => '',
            ],
            'waitlist' => [
                'offer_expires_at' => '',
                'status' => '',
            ],
            'form' => [
                'id' => '',
                'name' => '',
            ],
            'response' => [
                'id' => '',
                'submitted_at' => '',
            ],
            'labels' => $labels,
        ];
    }

    /**
     * @return array{
     *   professional_singular:string,
     *   professional_plural:string,
     *   professional_registration:string,
     *   professional_singular_lower:string,
     *   professional_plural_lower:string,
     *   professional_registration_lower:string
     * }
     */
    private function professionalLabelsForTemplateContext(): array
    {
        $service = app(ProfessionalLabelService::class);
        $singular = trim((string) $service->singular());
        $plural = trim((string) $service->plural());
        $registration = trim((string) $service->registration());

        if ($singular === '') {
            $singular = 'Médico';
        }
        if ($plural === '') {
            $plural = 'Médicos';
        }
        if ($registration === '') {
            $registration = 'CRM';
        }

        return [
            'professional_singular' => $singular,
            'professional_plural' => $plural,
            'professional_registration' => $registration,
            'professional_singular_lower' => $this->toLower($singular),
            'professional_plural_lower' => $this->toLower($plural),
            'professional_registration_lower' => $this->toLower($registration),
        ];
    }

    private function toLower(string $value): string
    {
        if (function_exists('mb_strtolower')) {
            return mb_strtolower($value, 'UTF-8');
        }

        return strtolower($value);
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

    private function applyFormResponsePreviewFallback(array $context, Tenant $tenant): array
    {
        $formName = data_get($context, 'form.name');
        if (!is_string($formName) || trim($formName) === '') {
            data_set($context, 'form.name', 'Anamnese Inicial');
        }

        $submittedAt = data_get($context, 'response.submitted_at');
        if (!is_string($submittedAt) || trim($submittedAt) === '') {
            data_set($context, 'response.submitted_at', now()->format('d/m/Y H:i'));
        }

        $responseLink = data_get($context, 'links.form_response');
        if (!is_string($responseLink) || trim($responseLink) === '') {
            $slug = (string) ($tenant->subdomain ?? '');
            $fallbackUrl = $slug !== '' ? url('/customer/' . $slug . '/responses/preview') : '#';
            data_set($context, 'links.form_response', $fallbackUrl);
        }

        return $context;
    }

    private function applyAppointmentFormRequestedPreviewFallback(array $context, Tenant $tenant): array
    {
        $formName = data_get($context, 'form.name');
        if (!is_string($formName) || trim($formName) === '') {
            data_set($context, 'form.name', 'Anamnese Inicial');
        }

        $formLink = data_get($context, 'links.form_fill');
        if (!is_string($formLink) || trim($formLink) === '') {
            $slug = (string) ($tenant->subdomain ?? '');
            $fallbackUrl = $slug !== '' ? url('/customer/' . $slug . '/formulario/preview/responder') : '#';
            data_set($context, 'links.form_fill', $fallbackUrl);
        }

        return $context;
    }

    private function applyOnlineAppointmentPreviewFallback(array $context, Tenant $tenant): array
    {
        $isOnline = data_get($context, 'online.is_online');
        if (!is_bool($isOnline)) {
            data_set($context, 'online.is_online', true);
        }

        $meetingApp = data_get($context, 'online.meeting_app');
        if (!is_string($meetingApp) || trim($meetingApp) === '') {
            data_set($context, 'online.meeting_app', 'Google Meet');
        }

        $meetingLink = data_get($context, 'online.meeting_link');
        if (!is_string($meetingLink) || trim($meetingLink) === '') {
            data_set($context, 'online.meeting_link', 'https://meet.google.com/preview-sala-online');
        }

        $instructionsSent = data_get($context, 'online.instructions_sent');
        if (!is_bool($instructionsSent)) {
            data_set($context, 'online.instructions_sent', true);
        }

        $emailSentAt = data_get($context, 'online.instructions_sent_email_at');
        if (!is_string($emailSentAt) || trim($emailSentAt) === '') {
            data_set($context, 'online.instructions_sent_email_at', now()->subMinutes(30)->format('d/m/Y H:i'));
        }

        $whatsappSentAt = data_get($context, 'online.instructions_sent_whatsapp_at');
        if (!is_string($whatsappSentAt) || trim($whatsappSentAt) === '') {
            data_set($context, 'online.instructions_sent_whatsapp_at', now()->subMinutes(25)->format('d/m/Y H:i'));
        }

        $onlineDetails = data_get($context, 'links.online_appointment_details');
        if (!is_string($onlineDetails) || trim($onlineDetails) === '') {
            $slug = (string) ($tenant->subdomain ?? '');
            $fallbackUrl = $slug !== '' ? url('/customer/' . $slug . '/consultas-online/preview') : '#';
            data_set($context, 'links.online_appointment_details', $fallbackUrl);
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

    private function buildEditorSettingsUrl(string $channel, string $key, string $audience = 'patient'): string
    {
        $audience = in_array($audience, ['patient', 'doctor'], true) ? $audience : 'patient';

        return route('tenant.settings.index', [
            'slug' => tenant()->subdomain,
            'tab' => 'editor',
            'audience' => $audience,
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

        $professionalLabelService = app(ProfessionalLabelService::class);
        $professionalSingular = trim((string) $professionalLabelService->singular());
        if ($professionalSingular === '') {
            $professionalSingular = 'Médico';
        }

        $allKeys = $service->listKeys();
        $audiences = ['patient', 'doctor'];
        $requestedAudience = strtolower((string) $request->input('audience', $request->query('audience', 'patient')));
        $audience = in_array($requestedAudience, $audiences, true) ? $requestedAudience : 'patient';
        $keys = array_values(array_filter(
            $allKeys,
            static fn (array $item): bool => (string) ($item['audience'] ?? 'patient') === $audience
        ));

        if ($keys === [] && $audience !== 'patient') {
            $audience = 'patient';
            $keys = array_values(array_filter(
                $allKeys,
                static fn (array $item): bool => (string) ($item['audience'] ?? 'patient') === 'patient'
            ));
        }

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
            'audiences' => $audiences,
            'current_audience' => $audience,
            'channels' => $channels,
            'keys' => $keys,
            'current_channel' => $channel,
            'current_key' => $key,
            'default_template' => $defaultTemplate,
            'effective_template' => $effectiveTemplate,
            'is_custom' => $isCustom,
            'subject_required' => $subjectRequired,
            'preview' => $preview,
            'professional_singular' => $professionalSingular,
            'variables' => $this->notificationTemplateVariables($audience),
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

    /**
     * @return array<int, string>
     */
    private function parseKeywordListTextarea(string $value): array
    {
        $lines = preg_split('/\R/u', $value) ?: [];
        $keywords = [];

        foreach ($lines as $line) {
            $keyword = trim((string) $line);
            if ($keyword === '') {
                continue;
            }

            if (!in_array($keyword, $keywords, true)) {
                $keywords[] = $keyword;
            }
        }

        return $keywords;
    }

    /**
     * @return array<int, array{id:string,label:string,enabled:bool,order:int,requires_identification:bool}>
     */
    private function parseMenuOptionsJsonTextarea(string $value): array
    {
        $trimmed = trim($value);
        if ($trimmed === '') {
            return WhatsAppBotConfigService::DEFAULT_MENU_OPTIONS;
        }

        $decoded = json_decode($trimmed, true);
        if (!is_array($decoded)) {
            throw new \InvalidArgumentException('As opções do menu devem estar em JSON válido.');
        }

        $normalized = [];
        foreach ($decoded as $item) {
            if (!is_array($item)) {
                continue;
            }

            $id = $this->normalizeBotMenuOptionId((string) ($item['id'] ?? ''));
            if ($id === '') {
                continue;
            }

            $label = trim((string) ($item['label'] ?? ''));
            if ($label === '') {
                $label = match ($id) {
                    'schedule' => 'Agendar consulta',
                    'view_appointments' => 'Ver meus agendamentos',
                    'cancel_appointments' => 'Cancelar agendamento',
                    default => 'Opção',
                };
            }

            $enabled = filter_var($item['enabled'] ?? true, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);
            $requiresIdentification = filter_var($item['requires_identification'] ?? true, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);

            $normalized[$id] = [
                'id' => $id,
                'label' => $label,
                'enabled' => $enabled ?? true,
                'order' => max(1, (int) ($item['order'] ?? 99)),
                'requires_identification' => $requiresIdentification ?? true,
            ];
        }

        if ($normalized === []) {
            throw new \InvalidArgumentException('As opções do menu não possuem itens válidos.');
        }

        $normalizedList = array_values($normalized);
        usort($normalizedList, static function (array $left, array $right): int {
            $orderCompare = ((int) ($left['order'] ?? 0)) <=> ((int) ($right['order'] ?? 0));
            if ($orderCompare !== 0) {
                return $orderCompare;
            }

            return strcmp((string) ($left['id'] ?? ''), (string) ($right['id'] ?? ''));
        });

        return $normalizedList;
    }

    private function normalizeBotMenuOptionId(string $id): string
    {
        $normalized = strtolower(trim($id));

        return match ($normalized) {
            'schedule' => 'schedule',
            'view_appointments' => 'view_appointments',
            'cancel_appointments', 'cancel_appointment' => 'cancel_appointments',
            default => '',
        };
    }

    /**
     * @return array<int, string>
     */
    private function getBrazilTimezones(): array
    {
        $timezones = array_values(array_filter(
            (array) config('timezones.brazil', []),
            static fn ($value): bool => is_string($value) && $value !== ''
        ));

        return $timezones !== [] ? $timezones : ['America/Sao_Paulo'];
    }

    private function notificationTemplateVariables(string $audience = 'patient'): array
    {
        $groups = [
            'CLINIC' => [
                ['key' => '{{clinic.name}}', 'description' => 'Nome da clínica'],
                ['key' => '{{clinic.phone}}', 'description' => 'Telefone da clínica'],
                ['key' => '{{clinic.email}}', 'description' => 'E-mail da clínica'],
                ['key' => '{{clinic.address}}', 'description' => 'Endereço da clínica'],
                ['key' => '{{clinic.slug}}', 'description' => 'Identificador da clínica'],
            ],
            'PATIENT' => [
                ['key' => '{{patient.name}}', 'description' => 'Nome do paciente'],
                ['key' => '{{patient.phone}}', 'description' => 'Telefone do paciente'],
                ['key' => '{{patient.email}}', 'description' => 'E-mail do paciente'],
            ],
            'DOCTOR / PROFESSIONAL' => [
                ['key' => '{{doctor.name}}', 'description' => 'Nome do profissional (perfil interno doctor)'],
                ['key' => '{{doctor.specialty}}', 'description' => 'Especialidade do profissional'],
                ['key' => '{{doctor.phone}}', 'description' => 'Telefone do profissional'],
                ['key' => '{{doctor.email}}', 'description' => 'E-mail do profissional'],
                ['key' => '{{professional.name}}', 'description' => 'Nome do profissional'],
                ['key' => '{{professional.specialty}}', 'description' => 'Especialidade do profissional'],
                ['key' => '{{professional.phone}}', 'description' => 'Telefone do profissional'],
                ['key' => '{{professional.email}}', 'description' => 'E-mail do profissional'],
            ],
            'LABELS' => [
                ['key' => '{{labels.professional_singular}}', 'description' => 'Rótulo singular atual do profissional'],
                ['key' => '{{labels.professional_plural}}', 'description' => 'Rótulo plural atual do profissional'],
                ['key' => '{{labels.professional_registration}}', 'description' => 'Rótulo atual do registro profissional (CRM/CRP/CRO/CREFITO/Conselho)'],
                ['key' => '{{labels.professional_singular_lower}}', 'description' => 'Rótulo singular atual em minúsculo'],
                ['key' => '{{labels.professional_plural_lower}}', 'description' => 'Rótulo plural atual em minúsculo'],
                ['key' => '{{labels.professional_registration_lower}}', 'description' => 'Rótulo de registro em minúsculo'],
            ],
            'APPOINTMENT' => [
                ['key' => '{{appointment.date}}', 'description' => 'Data da consulta'],
                ['key' => '{{appointment.time}}', 'description' => 'Hora da consulta'],
                ['key' => '{{appointment.datetime}}', 'description' => 'Data e hora da consulta'],
                ['key' => '{{appointment.starts_at}}', 'description' => 'Início da consulta'],
                ['key' => '{{appointment.ends_at}}', 'description' => 'Fim da consulta'],
                ['key' => '{{appointment.type}}', 'description' => 'Tipo de consulta'],
                ['key' => '{{appointment.mode}}', 'description' => 'Modalidade da consulta'],
                ['key' => '{{appointment.status}}', 'description' => 'Status da consulta'],
                ['key' => '{{appointment.confirmation_expires_at}}', 'description' => 'Prazo de confirmação da consulta'],
            ],
            'LINKS' => [
                ['key' => '{{links.appointment_confirm}}', 'description' => 'Link para confirmar consulta'],
                ['key' => '{{links.appointment_cancel}}', 'description' => 'Link para cancelar consulta'],
                ['key' => '{{links.appointment_details}}', 'description' => 'Link com detalhes da consulta'],
                ['key' => '{{links.online_appointment_details}}', 'description' => 'Link interno dos detalhes da consulta online'],
                ['key' => '{{links.waitlist_offer}}', 'description' => 'Link da oferta da lista de espera'],
                ['key' => '{{links.form_response}}', 'description' => 'Link interno para resposta de formulário'],
                ['key' => '{{links.form_fill}}', 'description' => 'Link público para preenchimento do formulário do agendamento'],
            ],
            'ONLINE APPOINTMENT' => [
                ['key' => '{{online.is_online}}', 'description' => 'Indica se a consulta é online (true/false)'],
                ['key' => '{{online.meeting_link}}', 'description' => 'Link da reunião online'],
                ['key' => '{{online.meeting_app}}', 'description' => 'Aplicativo da reunião online'],
                ['key' => '{{online.general_instructions}}', 'description' => 'Instruções gerais da consulta online'],
                ['key' => '{{online.patient_instructions}}', 'description' => 'Observações ao paciente da consulta online'],
                ['key' => '{{online.instructions_sent}}', 'description' => 'Indica se instruções já foram enviadas'],
                ['key' => '{{online.instructions_sent_email_at}}', 'description' => 'Data/hora do último envio por e-mail'],
                ['key' => '{{online.instructions_sent_whatsapp_at}}', 'description' => 'Data/hora do último envio por WhatsApp'],
            ],
            'WAITLIST' => [
                ['key' => '{{waitlist.offer_expires_at}}', 'description' => 'Validade da oferta da lista de espera'],
                ['key' => '{{waitlist.status}}', 'description' => 'Status da lista de espera'],
            ],
            'FORM / RESPONSE' => [
                ['key' => '{{form.id}}', 'description' => 'ID do formulário'],
                ['key' => '{{form.name}}', 'description' => 'Nome do formulário'],
                ['key' => '{{response.id}}', 'description' => 'ID da resposta'],
                ['key' => '{{response.submitted_at}}', 'description' => 'Data/hora de envio da resposta'],
            ],
        ];

        if ($audience === 'doctor') {
            return [
                'CLINIC' => $groups['CLINIC'],
                'DOCTOR / PROFESSIONAL' => $groups['DOCTOR / PROFESSIONAL'],
                'PATIENT' => $groups['PATIENT'],
                'APPOINTMENT' => $groups['APPOINTMENT'],
                'LINKS' => $groups['LINKS'],
                'ONLINE APPOINTMENT' => $groups['ONLINE APPOINTMENT'],
                'WAITLIST' => $groups['WAITLIST'],
                'FORM / RESPONSE' => $groups['FORM / RESPONSE'],
                'LABELS' => $groups['LABELS'],
            ];
        }

        return $groups;
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
