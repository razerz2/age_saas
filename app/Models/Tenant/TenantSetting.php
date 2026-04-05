<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class TenantSetting extends Model
{
    use HasFactory;

    protected $connection = 'tenant';
    protected $table = 'tenant_settings';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'key',
        'value',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    /**
     * Obtém um valor de configuração
     */
    public static function get(string $key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Define um valor de configuração
     */
    public static function set(string $key, $value): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }

    /**
     * Verifica se uma configuração está habilitada
     * Por padrão retorna true (notificações habilitadas por padrão)
     */
    public static function isEnabled(string $key): bool
    {
        $value = static::get($key);
        // Se não existe, retorna true (habilitado por padrão)
        if ($value === null) {
            return true;
        }
        return $value === 'true';
    }

    /**
     * Habilita uma configuração
     */
    public static function enable(string $key): void
    {
        static::set($key, 'true');
    }

    /**
     * Desabilita uma configuração
     */
    public static function disable(string $key): void
    {
        static::set($key, 'false');
    }

    /**
     * Obtém todas as configurações como array associativo
     */
    public static function getAll(): array
    {
        $settings = static::all();
        $result = [];
        
        foreach ($settings as $setting) {
            $result[$setting->key] = $setting->value;
        }
        
        return $result;
    }

    /**
     * Obtém as configurações do provedor de email
     */
    public static function emailProvider(): array
    {
        $settings = self::getAll();

        return [
            'driver' => $settings['email.driver'] ?? 'global',
            'host' => $settings['email.host'] ?? null,
            'port' => $settings['email.port'] ?? null,
            'username' => $settings['email.username'] ?? null,
            'password' => $settings['email.password'] ?? null,
            'from_name' => $settings['email.from_name'] ?? config('mail.from.name'),
            'from_address' => $settings['email.from_address'] ?? config('mail.from.address'),
        ];
    }

    /**
     * Obtém as configurações do provedor de WhatsApp
     */
    public static function whatsappProvider(): array
    {
        $settings = self::getAll();

        return [
            'driver' => $settings['whatsapp.driver'] ?? 'global',
            'global_provider' => $settings['whatsapp.global_provider'] ?? null,
            'provider' => $settings['whatsapp.provider'] ?? 'whatsapp_business',
            'meta_access_token' => $settings['whatsapp.meta.access_token'] ?? null,
            'meta_phone_number_id' => $settings['whatsapp.meta.phone_number_id'] ?? null,
            'meta_waba_id' => $settings['whatsapp.meta.waba_id'] ?? null,
            'zapi_api_url' => $settings['whatsapp.zapi.api_url'] ?? null,
            'zapi_token' => $settings['whatsapp.zapi.token'] ?? null,
            'zapi_client_token' => $settings['whatsapp.zapi.client_token'] ?? null,
            'zapi_instance_id' => $settings['whatsapp.zapi.instance_id'] ?? null,
            'waha_base_url' => $settings['whatsapp.waha.base_url'] ?? null,
            'waha_api_key' => $settings['whatsapp.waha.api_key'] ?? null,
            'waha_session' => $settings['whatsapp.waha.session'] ?? 'default',
            'evolution_base_url' => $settings['whatsapp.evolution.base_url'] ?? null,
            'evolution_api_key' => $settings['whatsapp.evolution.api_key'] ?? null,
            'evolution_instance' => $settings['whatsapp.evolution.instance'] ?? 'default',
            'api_url' => $settings['whatsapp.api_url'] ?? null,
            'api_token' => $settings['whatsapp.api_token'] ?? null,
            'sender' => $settings['whatsapp.sender'] ?? null,
        ];
    }

    /**
     * Obtém as configurações de email para campanhas.
     */
    public static function campaignEmailConfig(): array
    {
        $settings = self::getAll();
        $mode = strtolower(trim((string) ($settings['campaigns.email.mode'] ?? 'notifications')));

        if (!in_array($mode, ['notifications', 'custom'], true)) {
            $mode = 'notifications';
        }

        return [
            'mode' => $mode,
            'driver' => $settings['campaigns.email.driver'] ?? 'smtp',
            'host' => $settings['campaigns.email.host'] ?? null,
            'port' => $settings['campaigns.email.port'] ?? null,
            'username' => $settings['campaigns.email.username'] ?? null,
            'password' => $settings['campaigns.email.password'] ?? null,
            'encryption' => $settings['campaigns.email.encryption'] ?? null,
            'from_name' => $settings['campaigns.email.from_name'] ?? null,
            'from_address' => $settings['campaigns.email.from_address'] ?? null,
        ];
    }

    /**
     * Obtém as configurações de WhatsApp para campanhas.
     */
    public static function campaignWhatsAppConfig(): array
    {
        $settings = self::getAll();
        $mode = strtolower(trim((string) ($settings['campaigns.whatsapp.mode'] ?? 'notifications')));

        if (!in_array($mode, ['notifications', 'custom'], true)) {
            $mode = 'notifications';
        }

        return [
            'mode' => $mode,
            'driver' => $settings['campaigns.whatsapp.driver'] ?? 'tenancy',
            'provider' => $settings['campaigns.whatsapp.provider'] ?? 'whatsapp_business',
            'meta_access_token' => $settings['campaigns.whatsapp.meta.access_token'] ?? null,
            'meta_phone_number_id' => $settings['campaigns.whatsapp.meta.phone_number_id'] ?? null,
            'zapi_api_url' => $settings['campaigns.whatsapp.zapi.api_url'] ?? null,
            'zapi_token' => $settings['campaigns.whatsapp.zapi.token'] ?? null,
            'zapi_client_token' => $settings['campaigns.whatsapp.zapi.client_token'] ?? null,
            'zapi_instance_id' => $settings['campaigns.whatsapp.zapi.instance_id'] ?? null,
            'waha_base_url' => $settings['campaigns.whatsapp.waha.base_url'] ?? null,
            'waha_api_key' => $settings['campaigns.whatsapp.waha.api_key'] ?? null,
            'waha_session' => $settings['campaigns.whatsapp.waha.session'] ?? 'default',
            'evolution_base_url' => $settings['campaigns.whatsapp.evolution.base_url'] ?? null,
            'evolution_api_key' => $settings['campaigns.whatsapp.evolution.api_key'] ?? null,
            'evolution_instance' => $settings['campaigns.whatsapp.evolution.instance'] ?? 'default',
        ];
    }

    /**
     * Obtem as configuracoes do bot de WhatsApp.
     */
    public static function whatsappBotProvider(): array
    {
        $settings = self::getAll();

        return [
            'enabled' => ($settings['whatsapp_bot.enabled'] ?? 'false') === 'true',
            'provider_mode' => $settings['whatsapp_bot.provider_mode'] ?? 'shared_with_notifications',
            'provider' => $settings['whatsapp_bot.provider'] ?? 'whatsapp_business',
            'welcome_message' => $settings['whatsapp_bot.welcome_message'] ?? '',
            'disabled_message' => $settings['whatsapp_bot.disabled_message'] ?? '',
            'allow_schedule' => ($settings['whatsapp_bot.allow_schedule'] ?? 'false') === 'true',
            'allow_view_appointments' => ($settings['whatsapp_bot.allow_view_appointments'] ?? 'false') === 'true',
            'allow_cancel_appointments' => ($settings['whatsapp_bot.allow_cancel_appointments'] ?? 'false') === 'true',
            'meta_access_token' => $settings['whatsapp_bot.meta.access_token'] ?? null,
            'meta_phone_number_id' => $settings['whatsapp_bot.meta.phone_number_id'] ?? null,
            'meta_waba_id' => $settings['whatsapp_bot.meta.waba_id'] ?? null,
            'zapi_api_url' => $settings['whatsapp_bot.zapi.api_url'] ?? 'https://api.z-api.io',
            'zapi_token' => $settings['whatsapp_bot.zapi.token'] ?? null,
            'zapi_client_token' => $settings['whatsapp_bot.zapi.client_token'] ?? null,
            'zapi_instance_id' => $settings['whatsapp_bot.zapi.instance_id'] ?? null,
            'waha_base_url' => $settings['whatsapp_bot.waha.base_url'] ?? null,
            'waha_api_key' => $settings['whatsapp_bot.waha.api_key'] ?? null,
            'waha_session' => $settings['whatsapp_bot.waha.session'] ?? 'default',
            'evolution_base_url' => $settings['whatsapp_bot.evolution.base_url'] ?? null,
            'evolution_api_key' => $settings['whatsapp_bot.evolution.api_key'] ?? null,
            'evolution_instance' => $settings['whatsapp_bot.evolution.instance'] ?? 'default',
            'entry_keywords' => self::parseStringListSetting($settings['whatsapp_bot.entry_keywords'] ?? null),
            'exit_keywords' => self::parseStringListSetting($settings['whatsapp_bot.exit_keywords'] ?? null),
        ];
    }

    /**
     * Alias para compatibilidade com fluxos que esperam getWhatsAppConfig().
     *
     * @return array<string, mixed>
     */
    public static function getWhatsAppConfig(): array
    {
        return self::whatsappProvider();
    }

    /**
     * @param mixed $value
     * @return array<int, string>
     */
    private static function parseStringListSetting(mixed $value): array
    {
        if (is_array($value)) {
            return array_values(array_filter(array_map(static fn ($item): string => trim((string) $item), $value), static fn (string $item): bool => $item !== ''));
        }

        if (!is_string($value) || trim($value) === '') {
            return [];
        }

        $decoded = json_decode($value, true);
        if (is_array($decoded)) {
            return array_values(array_filter(array_map(static fn ($item): string => trim((string) $item), $decoded), static fn (string $item): bool => $item !== ''));
        }

        $lines = preg_split('/\R/u', $value) ?: [];

        return array_values(array_filter(array_map(static fn ($item): string => trim((string) $item), $lines), static fn (string $item): bool => $item !== ''));
    }
}
