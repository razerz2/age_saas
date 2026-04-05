<?php

namespace App\Services;

use App\Services\Providers\ProviderConfigResolver;
use App\Models\Tenant\TenantSetting;
use App\Services\WhatsApp\TenantGlobalProviderCatalogService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Services\WhatsApp\PhoneNormalizer;

class WhatsappTenantService
{
    /**
     * Envia mensagem WhatsApp usando API do tenant ou global
     */
    public static function send($phone, $message, ?array $providerOverride = null): bool
    {
        try {
            $provider = is_array($providerOverride) && $providerOverride !== []
                ? $providerOverride
                : TenantSetting::whatsappProvider();
            $resolver = new ProviderConfigResolver();

            if (($provider['driver'] ?? 'global') === 'tenancy') {
                if (!empty($provider['provider'])) {
                    $tenantProvider = strtolower(trim((string) $provider['provider']));
                    if ($tenantProvider === '') {
                        $tenantProvider = 'whatsapp_business';
                    }

                    config([
                        'services.whatsapp.force_runtime_provider' => true,
                        'services.whatsapp.runtime_provider' => $tenantProvider,
                        'services.whatsapp.provider' => $tenantProvider,
                        'services.whatsapp.business.api_url' => config('services.whatsapp.business.api_url', 'https://graph.facebook.com/v22.0'),
                        'services.whatsapp.business.token' => $provider['meta_access_token'] ?? '',
                        'services.whatsapp.business.phone_id' => $provider['meta_phone_number_id'] ?? '',
                        'services.whatsapp.business.waba_id' => $provider['meta_waba_id'] ?? '',
                        'services.whatsapp.zapi.api_url' => $provider['zapi_api_url'] ?? 'https://api.z-api.io',
                        'services.whatsapp.zapi.token' => $provider['zapi_token'] ?? '',
                        'services.whatsapp.zapi.client_token' => $provider['zapi_client_token'] ?? '',
                        'services.whatsapp.zapi.instance_id' => $provider['zapi_instance_id'] ?? '',
                    ]);

                    $resolver->applyUnofficialRuntimeConfigs($provider);

                    $whatsappService = new WhatsAppService();
                    return $whatsappService->sendMessage($phone, $message);
                }

                // Fallback legado para tenants com configuracao antiga
                if (!empty($provider['api_url']) && !empty($provider['api_token'])) {
                    $normalizedPhone = PhoneNormalizer::normalizeE164($phone);
                    $response = Http::asJson()->post($provider['api_url'], [
                        'token' => $provider['api_token'],
                        'from' => $provider['sender'],
                        'to' => $normalizedPhone !== '' ? $normalizedPhone : $phone,
                        'body' => $message,
                    ]);

                    Log::info('WhatsApp enviado (tenant legado)', [
                        'to' => $phone,
                        'status' => $response->status(),
                        'body' => $response->json(),
                    ]);

                    return $response->successful();
                }
            }

            $globalProvider = self::resolveTenantGlobalProvider($provider);
            if ($globalProvider === null) {
                Log::warning('Tenant global WhatsApp provider is invalid or disabled; send blocked', [
                    'tenant_id' => \App\Models\Platform\Tenant::current()?->id,
                    'driver' => $provider['driver'] ?? null,
                    'global_provider' => $provider['global_provider'] ?? null,
                ]);

                return false;
            }

            $globalMetaApiUrl = self::resolveGlobalWhatsAppMetaValue(
                ['WHATSAPP_META_BASE_URL', 'WHATSAPP_BUSINESS_API_URL', 'WHATSAPP_API_URL'],
                (string) config('services.whatsapp.business.api_url', 'https://graph.facebook.com/v22.0')
            );
            $globalMetaToken = self::resolveGlobalWhatsAppMetaValue(
                ['WHATSAPP_META_TOKEN', 'WHATSAPP_BUSINESS_TOKEN', 'META_ACCESS_TOKEN', 'BOT_META_ACCESS_TOKEN', 'bot_meta_access_token'],
                (string) config('services.whatsapp.business.token', config('services.whatsapp.token', ''))
            );
            $globalMetaPhoneId = self::resolveGlobalWhatsAppMetaValue(
                ['WHATSAPP_META_PHONE_NUMBER_ID', 'WHATSAPP_BUSINESS_PHONE_ID', 'META_PHONE_NUMBER_ID', 'BOT_META_PHONE_NUMBER_ID', 'bot_meta_phone_number_id'],
                (string) config('services.whatsapp.business.phone_id', config('services.whatsapp.phone_id', ''))
            );
            $globalMetaWabaId = self::resolveGlobalWhatsAppMetaValue(
                ['WHATSAPP_META_WABA_ID', 'WHATSAPP_BUSINESS_ACCOUNT_ID', 'META_WABA_ID', 'BOT_META_WABA_ID', 'bot_meta_waba_id'],
                (string) config('services.whatsapp.business.waba_id', '')
            );

            config([
                'services.whatsapp.force_runtime_provider' => true,
                'services.whatsapp.runtime_provider' => $globalProvider,
                'services.whatsapp.provider' => $globalProvider,
                'services.whatsapp.business.api_url' => $globalMetaApiUrl,
                'services.whatsapp.business.token' => $globalMetaToken,
                'services.whatsapp.business.phone_id' => $globalMetaPhoneId,
                'services.whatsapp.business.waba_id' => $globalMetaWabaId,
            ]);
            $resolver->applyUnofficialRuntimeConfigs($provider);

            // GLOBAL PROVIDER - usa WhatsAppService padrao
            $whatsappService = new WhatsAppService();
            return $whatsappService->sendMessage($phone, $message);
        } catch (\Throwable $e) {
            Log::error('Erro ao enviar mensagem WhatsApp', [
                'to' => $phone,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * @param array<string, mixed> $tenantProvider
     */
    private static function resolveTenantGlobalProvider(array $tenantProvider): ?string
    {
        $catalog = app(TenantGlobalProviderCatalogService::class);
        return $catalog->resolveTenantGlobalProvider(
            (string) ($tenantProvider['global_provider'] ?? '')
        );
    }

    private static function resolveGlobalWhatsAppMetaValue(array $keys, string $fallback = ''): string
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
}
