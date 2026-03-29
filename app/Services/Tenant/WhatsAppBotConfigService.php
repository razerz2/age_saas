<?php

namespace App\Services\Tenant;

use App\Models\Tenant\TenantSetting;
use App\Services\Providers\ProviderConfigResolver;
use App\Services\WhatsApp\TenantGlobalProviderCatalogService;

class WhatsAppBotConfigService
{
    public const FEATURE_NAME = 'whatsapp_bot';
    public const MODE_SHARED_WITH_NOTIFICATIONS = 'shared_with_notifications';
    public const MODE_DEDICATED = 'dedicated';
    public const DEFAULT_EXIT_MESSAGE = 'Atendimento encerrado. Quando quiser, envie uma mensagem para comecar novamente.';
    public const DEFAULT_WELCOME_MESSAGE = 'Ola! Posso ajudar com seu atendimento.';
    public const DEFAULT_INTERNAL_ERROR_MESSAGE = 'Ocorreu um problema ao processar sua solicitacao. Tente novamente ou digite 0 para voltar ao menu.';
    public const DEFAULT_NO_SLOTS_MESSAGE = 'Nao ha horarios disponiveis no momento.';
    public const DEFAULT_APPOINTMENT_CREATED_MESSAGE = 'Agendamento realizado com sucesso.';
    public const DEFAULT_APPOINTMENT_CANCELED_MESSAGE = 'Agendamento cancelado com sucesso.';
    public const DEFAULT_BACK_TO_MENU_MESSAGE = 'Voltando ao menu principal.';
    public const DEFAULT_PATIENT_NOT_FOUND_MESSAGE = 'Nao localizei cadastro para este CPF.';
    public const DEFAULT_REGISTRATION_START_MESSAGE = 'Vamos criar seu cadastro.';
    public const DEFAULT_REGISTRATION_COMPLETED_MESSAGE = 'Cadastro criado com sucesso.';
    public const DEFAULT_INVALID_CPF_MESSAGE = 'CPF invalido. Envie no formato 000.000.000-00 ou apenas numeros.';
    public const DEFAULT_FALLBACK_MESSAGE = 'Nao entendi. Escolha uma opcao:';
    public const DEFAULT_INACTIVITY_EXIT_MESSAGE = 'Sessao encerrada por inatividade. Quando quiser, envie uma mensagem para comecar novamente.';

    /**
     * @var array<int, string>
     */
    public const DEFAULT_SESSION_RESET_KEYWORDS = ['menu', 'inicio', 'reiniciar', '0'];

    /**
     * @var array<int, string>
     */
    public const DEFAULT_REQUIRE_CPF_FOR_INTENTS = ['schedule', 'view_appointments'];

    /**
     * @var array<int, string>
     */
    public const DEFAULT_IDENTIFICATION_LOOKUP_ORDER = ['cpf', 'phone'];

    /**
     * @var array<int, array{id:string,label:string,enabled:bool,order:int,requires_identification:bool}>
     */
    public const DEFAULT_MENU_OPTIONS = [
        [
            'id' => 'schedule',
            'label' => 'Agendar consulta',
            'enabled' => true,
            'order' => 1,
            'requires_identification' => true,
        ],
        [
            'id' => 'view_appointments',
            'label' => 'Ver meus agendamentos',
            'enabled' => true,
            'order' => 2,
            'requires_identification' => true,
        ],
        [
            'id' => 'cancel_appointments',
            'label' => 'Cancelar agendamento',
            'enabled' => true,
            'order' => 3,
            'requires_identification' => true,
        ],
    ];

    /**
     * @var array<int, string>
     */
    public const DEFAULT_ENTRY_KEYWORDS = [
        'Oi',
        'oi',
        'Ola',
        'ola',
        'Boa tarde',
        'boa tarde',
        'Bom dia',
        'bom dia',
        'Boa noite',
        'boa noite',
        'menu',
        'iniciar',
        'inicio',
        'comecar',
    ];

    /**
     * @var array<int, string>
     */
    public const DEFAULT_EXIT_KEYWORDS = [
        'sair',
        'Sair',
        'encerrar',
        'Encerrar',
        'finalizar',
        'Finalizar',
        'tchau',
        'Tchau',
        'obrigado',
        'obrigada',
        'ate mais',
        'fim',
        'parar',
    ];

    /**
     * @var array<int, string>
     */
    public const SUPPORTED_PROVIDERS = ['whatsapp_business', 'zapi', 'waha', 'evolution'];

    /**
     * @return array<string, mixed>
     */
    public function getSettings(): array
    {
        $settings = TenantSetting::whatsappBotProvider();
        $settings['entry_keywords'] = $this->resolveEntryKeywords($settings);
        $settings['exit_keywords'] = $this->resolveExitKeywords($settings);
        $settings['messages'] = $this->resolveMessages($settings);
        $settings['welcome_message'] = (string) ($settings['messages']['welcome'] ?? '');
        $settings['session'] = $this->resolveSessionSettings();
        $settings['identification'] = $this->resolveIdentificationSettings();
        $settings['menu'] = $this->resolveMenuSettings();

        return $settings;
    }

    /**
     * @param array<string, mixed> $settings
     * @return array<int, string>
     */
    public function resolveEntryKeywords(array $settings): array
    {
        return $this->normalizeKeywordList(
            $settings['entry_keywords'] ?? null,
            self::DEFAULT_ENTRY_KEYWORDS
        );
    }

    /**
     * @param array<string, mixed> $settings
     * @return array<int, string>
     */
    public function resolveExitKeywords(array $settings): array
    {
        return $this->normalizeKeywordList(
            $settings['exit_keywords'] ?? null,
            self::DEFAULT_EXIT_KEYWORDS
        );
    }

    /**
     * @param array<string, mixed> $settings
     * @return array<string, string>
     */
    public function resolveMessages(array $settings): array
    {
        $legacyWelcome = trim((string) ($settings['welcome_message'] ?? ''));
        $welcomeFallback = $legacyWelcome !== '' ? $legacyWelcome : self::DEFAULT_WELCOME_MESSAGE;

        return [
            'welcome' => $this->resolveMessageValue('welcome', $welcomeFallback),
            'fallback' => $this->resolveMessageValue('fallback', self::DEFAULT_FALLBACK_MESSAGE),
            'invalid_cpf' => $this->resolveMessageValue('invalid_cpf', self::DEFAULT_INVALID_CPF_MESSAGE),
            'patient_not_found' => $this->resolveMessageValue('patient_not_found', self::DEFAULT_PATIENT_NOT_FOUND_MESSAGE),
            'registration_start' => $this->resolveMessageValue('registration_start', self::DEFAULT_REGISTRATION_START_MESSAGE),
            'registration_completed' => $this->resolveMessageValue('registration_completed', self::DEFAULT_REGISTRATION_COMPLETED_MESSAGE),
            'internal_error' => $this->resolveMessageValue('internal_error', self::DEFAULT_INTERNAL_ERROR_MESSAGE),
            'no_slots_available' => $this->resolveMessageValue('no_slots_available', self::DEFAULT_NO_SLOTS_MESSAGE),
            'appointment_created' => $this->resolveMessageValue('appointment_created', self::DEFAULT_APPOINTMENT_CREATED_MESSAGE),
            'appointment_canceled' => $this->resolveMessageValue('appointment_canceled', self::DEFAULT_APPOINTMENT_CANCELED_MESSAGE),
            'back_to_menu' => $this->resolveMessageValue('back_to_menu', self::DEFAULT_BACK_TO_MENU_MESSAGE),
            'inactivity_exit' => $this->resolveMessageValue('inactivity_exit', self::DEFAULT_INACTIVITY_EXIT_MESSAGE),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function resolveSessionSettings(): array
    {
        $idleTimeout = max(1, $this->resolveIntSetting('whatsapp_bot.session.idle_timeout_minutes', 30));
        $absoluteTimeout = max($idleTimeout, $this->resolveIntSetting('whatsapp_bot.session.absolute_timeout_minutes', 240));
        $resetKeywords = $this->parseStringListSetting(tenant_setting('whatsapp_bot.session.reset_keywords', []));

        if ($resetKeywords === []) {
            $resetKeywords = self::DEFAULT_SESSION_RESET_KEYWORDS;
        }

        return [
            'idle_timeout_minutes' => $idleTimeout,
            'absolute_timeout_minutes' => $absoluteTimeout,
            'end_on_inactivity' => tenant_setting_bool('whatsapp_bot.session.end_on_inactivity', true),
            'clear_context_on_end' => tenant_setting_bool('whatsapp_bot.session.clear_context_on_end', true),
            'allow_resume_previous' => tenant_setting_bool('whatsapp_bot.session.allow_resume_previous', false),
            'reset_keywords' => $resetKeywords,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function resolveIdentificationSettings(): array
    {
        $requireCpfForIntents = $this->parseStringListSetting(tenant_setting('whatsapp_bot.identification.require_cpf_for_intents', []));
        if ($requireCpfForIntents === []) {
            $requireCpfForIntents = self::DEFAULT_REQUIRE_CPF_FOR_INTENTS;
        }

        $lookupOrder = $this->parseStringListSetting(tenant_setting('whatsapp_bot.identification.lookup_order', []));
        if ($lookupOrder === []) {
            $lookupOrder = self::DEFAULT_IDENTIFICATION_LOOKUP_ORDER;
        }

        $lookupOrder = array_values(array_filter(array_map(
            static fn (string $item): string => trim(strtolower($item)),
            $lookupOrder
        ), static fn (string $item): bool => in_array($item, ['cpf', 'phone'], true)));
        if ($lookupOrder === []) {
            $lookupOrder = self::DEFAULT_IDENTIFICATION_LOOKUP_ORDER;
        }

        return [
            'require_cpf_for_intents' => array_values(array_unique($requireCpfForIntents)),
            'require_valid_cpf' => tenant_setting_bool('whatsapp_bot.identification.require_valid_cpf', true),
            'max_attempts' => max(1, $this->resolveIntSetting('whatsapp_bot.identification.max_attempts', 3)),
            'reuse_identified_patient' => tenant_setting_bool('whatsapp_bot.identification.reuse_identified_patient', true),
            'lookup_order' => $lookupOrder,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function resolveMenuSettings(): array
    {
        $maxOptions = max(1, $this->resolveIntSetting('whatsapp_bot.menu.max_options', 6));
        $menuOptionsRaw = tenant_setting('whatsapp_bot.menu.options', []);
        $menuOptions = $this->normalizeMenuOptions($menuOptionsRaw);

        if ($menuOptions === []) {
            $menuOptions = self::DEFAULT_MENU_OPTIONS;
        }

        return [
            'max_options' => $maxOptions,
            'options' => $menuOptions,
            'show_again_after_action' => tenant_setting_bool('whatsapp_bot.menu.show_again_after_action', true),
            'return_after_fallback' => tenant_setting_bool('whatsapp_bot.menu.return_after_fallback', true),
        ];
    }

    /**
     * Resolve o provider efetivo que o bot deve usar.
     *
     * @return array<string, mixed>
     */
    public function resolveEffectiveProviderConfig(?array $settings = null): array
    {
        $botSettings = $settings ?? $this->getSettings();
        $mode = $this->normalizeProviderMode((string) ($botSettings['provider_mode'] ?? self::MODE_SHARED_WITH_NOTIFICATIONS));

        if ($mode === self::MODE_SHARED_WITH_NOTIFICATIONS) {
            return $this->resolveSharedWithNotificationsConfig();
        }

        return [
            'mode' => self::MODE_DEDICATED,
            'source' => 'bot',
            'provider' => $this->normalizeProvider((string) ($botSettings['provider'] ?? 'whatsapp_business')),
            'meta_access_token' => (string) ($botSettings['meta_access_token'] ?? ''),
            'meta_phone_number_id' => (string) ($botSettings['meta_phone_number_id'] ?? ''),
            'meta_waba_id' => (string) ($botSettings['meta_waba_id'] ?? ''),
            'zapi_api_url' => (string) ($botSettings['zapi_api_url'] ?? ''),
            'zapi_token' => (string) ($botSettings['zapi_token'] ?? ''),
            'zapi_client_token' => (string) ($botSettings['zapi_client_token'] ?? ''),
            'zapi_instance_id' => (string) ($botSettings['zapi_instance_id'] ?? ''),
            'waha_base_url' => (string) ($botSettings['waha_base_url'] ?? ''),
            'waha_api_key' => (string) ($botSettings['waha_api_key'] ?? ''),
            'waha_session' => (string) ($botSettings['waha_session'] ?? 'default'),
            'evolution_base_url' => (string) ($botSettings['evolution_base_url'] ?? ''),
            'evolution_api_key' => (string) ($botSettings['evolution_api_key'] ?? ''),
            'evolution_instance' => (string) ($botSettings['evolution_instance'] ?? 'default'),
        ];
    }

    public function normalizeProviderMode(string $mode): string
    {
        return in_array($mode, [self::MODE_SHARED_WITH_NOTIFICATIONS, self::MODE_DEDICATED], true)
            ? $mode
            : self::MODE_SHARED_WITH_NOTIFICATIONS;
    }

    public function normalizeProvider(string $provider): string
    {
        $normalized = strtolower(trim($provider));

        return in_array($normalized, self::SUPPORTED_PROVIDERS, true)
            ? $normalized
            : 'whatsapp_business';
    }

    public function providerLabel(string $provider): string
    {
        return match ($this->normalizeProvider($provider)) {
            'zapi' => 'Z-API',
            'waha' => 'WAHA',
            'evolution' => 'Evolution API',
            default => 'WhatsApp Business (Meta)',
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function resolveSharedWithNotificationsConfig(): array
    {
        $notificationConfig = TenantSetting::whatsappProvider();
        $driver = strtolower(trim((string) ($notificationConfig['driver'] ?? 'global')));

        if ($driver === 'tenancy') {
            return [
                'mode' => self::MODE_SHARED_WITH_NOTIFICATIONS,
                'source' => 'notifications',
                'provider' => $this->normalizeProvider((string) ($notificationConfig['provider'] ?? 'whatsapp_business')),
                'meta_access_token' => (string) ($notificationConfig['meta_access_token'] ?? ''),
                'meta_phone_number_id' => (string) ($notificationConfig['meta_phone_number_id'] ?? ''),
                'meta_waba_id' => (string) ($notificationConfig['meta_waba_id'] ?? ''),
                'zapi_api_url' => (string) ($notificationConfig['zapi_api_url'] ?? ''),
                'zapi_token' => (string) ($notificationConfig['zapi_token'] ?? ''),
                'zapi_client_token' => (string) ($notificationConfig['zapi_client_token'] ?? ''),
                'zapi_instance_id' => (string) ($notificationConfig['zapi_instance_id'] ?? ''),
                'waha_base_url' => (string) ($notificationConfig['waha_base_url'] ?? ''),
                'waha_api_key' => (string) ($notificationConfig['waha_api_key'] ?? ''),
                'waha_session' => (string) ($notificationConfig['waha_session'] ?? 'default'),
            ];
        }

        $tenantGlobalProviderCatalog = app(TenantGlobalProviderCatalogService::class);
        $globalProvider = $tenantGlobalProviderCatalog->resolveTenantGlobalProvider(
            (string) ($notificationConfig['global_provider'] ?? '')
        );

        $resolvedWahaConfig = app(ProviderConfigResolver::class)->resolveWahaConfig($notificationConfig);
        $resolvedEvolutionConfig = app(ProviderConfigResolver::class)->resolveEvolutionConfig($notificationConfig);

        return [
            'mode' => self::MODE_SHARED_WITH_NOTIFICATIONS,
            'source' => 'notifications',
            'provider' => $globalProvider !== null
                ? $this->normalizeProvider($globalProvider)
                : 'whatsapp_business',
            'meta_access_token' => $this->resolveGlobalValue(
                ['WHATSAPP_META_TOKEN', 'WHATSAPP_BUSINESS_TOKEN', 'META_ACCESS_TOKEN', 'BOT_META_ACCESS_TOKEN', 'bot_meta_access_token'],
                (string) config('services.whatsapp.business.token', config('services.whatsapp.token', ''))
            ),
            'meta_phone_number_id' => $this->resolveGlobalValue(
                ['WHATSAPP_META_PHONE_NUMBER_ID', 'WHATSAPP_BUSINESS_PHONE_ID', 'META_PHONE_NUMBER_ID', 'BOT_META_PHONE_NUMBER_ID', 'bot_meta_phone_number_id'],
                (string) config('services.whatsapp.business.phone_id', config('services.whatsapp.phone_id', ''))
            ),
            'meta_waba_id' => $this->resolveGlobalValue(
                ['WHATSAPP_META_WABA_ID', 'WHATSAPP_BUSINESS_ACCOUNT_ID', 'META_WABA_ID', 'BOT_META_WABA_ID', 'bot_meta_waba_id'],
                (string) config('services.whatsapp.business.waba_id', '')
            ),
            'zapi_api_url' => (string) config('services.whatsapp.zapi.api_url', 'https://api.z-api.io'),
            'zapi_token' => (string) config('services.whatsapp.zapi.token', ''),
            'zapi_client_token' => (string) config('services.whatsapp.zapi.client_token', ''),
            'zapi_instance_id' => (string) config('services.whatsapp.zapi.instance_id', ''),
            'waha_base_url' => (string) ($resolvedWahaConfig['base_url'] ?? ''),
            'waha_api_key' => (string) ($resolvedWahaConfig['api_key'] ?? ''),
            'waha_session' => (string) ($resolvedWahaConfig['session'] ?? 'default'),
            'evolution_base_url' => (string) ($resolvedEvolutionConfig['base_url'] ?? ''),
            'evolution_api_key' => (string) ($resolvedEvolutionConfig['api_key'] ?? ''),
            'evolution_instance' => (string) ($resolvedEvolutionConfig['instance'] ?? 'default'),
        ];
    }

    private function resolveGlobalValue(array $keys, string $fallback = ''): string
    {
        foreach ($keys as $key) {
            $value = function_exists('sysconfig')
                ? (string) sysconfig((string) $key, '')
                : '';

            $value = trim($value);
            if ($value !== '') {
                return $value;
            }
        }

        return trim($fallback);
    }

    private function resolveMessageValue(string $key, string $fallback): string
    {
        $value = trim((string) tenant_setting('whatsapp_bot.messages.' . $key, ''));
        if ($value !== '') {
            return $value;
        }

        return $fallback;
    }

    private function resolveIntSetting(string $key, int $fallback): int
    {
        return tenant_setting_int($key, $fallback);
    }

    /**
     * @param mixed $value
     * @return array<int, string>
     */
    private function parseStringListSetting(mixed $value): array
    {
        $items = [];

        if (is_array($value)) {
            $items = $value;
        } elseif (is_string($value)) {
            $decoded = json_decode($value, true);
            if (is_array($decoded)) {
                $items = $decoded;
            } else {
                $items = preg_split('/\R/u', $value) ?: [];
            }
        }

        $normalized = [];
        foreach ($items as $item) {
            $text = trim((string) $item);
            if ($text === '') {
                continue;
            }

            if (!in_array($text, $normalized, true)) {
                $normalized[] = $text;
            }
        }

        return $normalized;
    }

    /**
     * @param mixed $value
     * @return array<int, array{id:string,label:string,enabled:bool,order:int,requires_identification:bool}>
     */
    private function normalizeMenuOptions(mixed $value): array
    {
        $items = [];

        if (is_array($value)) {
            $items = $value;
        } elseif (is_string($value)) {
            $decoded = json_decode($value, true);
            if (is_array($decoded)) {
                $items = $decoded;
            }
        }

        $defaultById = [];
        foreach (self::DEFAULT_MENU_OPTIONS as $defaultItem) {
            $defaultById[(string) $defaultItem['id']] = $defaultItem;
        }

        $normalized = [];
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            $id = $this->normalizeMenuOptionId((string) ($item['id'] ?? ''));
            if ($id === '' || !isset($defaultById[$id])) {
                continue;
            }

            $default = $defaultById[$id];
            $label = trim((string) ($item['label'] ?? ''));
            if ($label === '') {
                $label = (string) $default['label'];
            }

            $normalized[] = [
                'id' => $id,
                'label' => $label,
                'enabled' => filter_var($item['enabled'] ?? $default['enabled'], FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? (bool) $default['enabled'],
                'order' => max(1, (int) ($item['order'] ?? $default['order'])),
                'requires_identification' => filter_var($item['requires_identification'] ?? $default['requires_identification'], FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? (bool) $default['requires_identification'],
            ];
        }

        if ($normalized === []) {
            return [];
        }

        usort($normalized, static function (array $left, array $right): int {
            $orderCompare = ((int) ($left['order'] ?? 0)) <=> ((int) ($right['order'] ?? 0));
            if ($orderCompare !== 0) {
                return $orderCompare;
            }

            return strcmp((string) ($left['id'] ?? ''), (string) ($right['id'] ?? ''));
        });

        $unique = [];
        foreach ($normalized as $item) {
            $id = (string) ($item['id'] ?? '');
            if ($id === '' || isset($unique[$id])) {
                continue;
            }
            $unique[$id] = $item;
        }

        return array_values($unique);
    }

    private function normalizeMenuOptionId(string $id): string
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
     * @param mixed $value
     * @param array<int, string> $fallback
     * @return array<int, string>
     */
    private function normalizeKeywordList(mixed $value, array $fallback): array
    {
        $items = [];

        if (is_array($value)) {
            $items = $value;
        } elseif (is_string($value)) {
            $decoded = json_decode($value, true);
            if (is_array($decoded)) {
                $items = $decoded;
            } else {
                $items = preg_split('/\R/u', $value) ?: [];
            }
        }

        $normalized = [];
        foreach ($items as $item) {
            $text = trim((string) $item);
            if ($text === '') {
                continue;
            }

            if (!in_array($text, $normalized, true)) {
                $normalized[] = $text;
            }
        }

        if ($normalized === []) {
            return $fallback;
        }

        return $normalized;
    }
}
