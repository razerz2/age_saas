<?php

namespace App\Services\Tenant;

use App\Models\Platform\WhatsAppOfficialTemplate;
use App\Models\Tenant\Asset;
use App\Models\Tenant\Campaign;
use App\Models\Tenant\CampaignRecipient;
use App\Models\Tenant\CampaignRun;
use App\Models\Tenant\TenantSetting;
use App\Services\Platform\WhatsAppOfficialMessageService;
use App\Services\Providers\ProviderConfigResolver;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class CampaignDeliveryService
{
    public function __construct(
        private readonly CampaignRenderer $renderer,
        private readonly EmailSender $emailSender,
        private readonly WhatsAppSender $whatsAppSender,
        private readonly NotificationDeliveryLogger $deliveryLogger,
        private readonly WhatsAppOfficialMessageService $officialWhatsAppMessageService,
        private readonly ProviderConfigResolver $providerConfigResolver,
        private readonly TenantWhatsAppConfigService $tenantWhatsAppConfigService,
        private readonly CampaignTemplateProviderResolver $campaignTemplateProviderResolver
    ) {
    }

    /**
     * @param array<string,mixed> $vars
     * @param array<string,mixed> $meta
     * @return array{success:bool,error_message:?string}
     */
    public function sendTest(
        Campaign $campaign,
        string $channel,
        string $destination,
        array $vars = [],
        array $meta = []
    ): array {
        $channel = strtolower(trim($channel));
        $meta = array_merge($meta, [
            'key' => 'campaign_test',
            'campaign_id' => (int) $campaign->id,
            'destination' => $destination,
            'origin' => 'campaign_test',
            'channel' => $channel,
        ]);

        return $this->sendByChannel($campaign, $channel, $destination, $vars, $meta);
    }

    /**
     * @return array{success:bool,error_message:?string}
     */
    public function sendRecipient(
        Campaign $campaign,
        CampaignRun $run,
        CampaignRecipient $recipient
    ): array {
        $channel = strtolower(trim((string) $recipient->channel));
        $destination = trim((string) $recipient->destination);
        $vars = is_array($recipient->vars_json) ? $recipient->vars_json : [];

        $meta = [
            'key' => 'campaign:' . (int) $campaign->id,
            'campaign_id' => (int) $campaign->id,
            'campaign_run_id' => (int) $run->id,
            'campaign_recipient_id' => (int) $recipient->id,
            'destination' => $destination,
            'origin' => 'campaign_run',
            'channel' => $channel,
            'run_id' => (int) $run->id,
        ];

        return $this->sendByChannel($campaign, $channel, $destination, $vars, $meta);
    }

    /**
     * @param array<string,mixed> $vars
     * @param array<string,mixed> $meta
     * @return array{success:bool,error_message:?string}
     */
    private function sendByChannel(
        Campaign $campaign,
        string $channel,
        string $destination,
        array $vars,
        array $meta
    ): array {
        try {
            if ($channel === 'email') {
                return $this->sendEmail($campaign, $destination, $vars, $meta);
            }

            if ($channel === 'whatsapp') {
                return $this->sendWhatsApp($campaign, $destination, $vars, $meta);
            }

            throw new RuntimeException('Canal nÃ£o suportado para envio da campanha.');
        } catch (Throwable $e) {
            $this->logError($channel, $meta, $destination, $e, null, '');

            return [
                'success' => false,
                'error_message' => $e->getMessage(),
            ];
        }
    }

    /**
     * @param array<string,mixed> $vars
     * @param array<string,mixed> $meta
     * @return array{success:bool,error_message:?string}
     */
    private function sendEmail(Campaign $campaign, string $destination, array $vars, array $meta): array
    {
        $payload = $this->renderer->renderChannel($campaign, 'email', $vars);
        $subject = trim((string) ($payload['subject'] ?? ''));
        $message = trim((string) ($payload['message'] ?? ''));

        if ($subject === '') {
            throw new RuntimeException('Assunto de email nÃ£o configurado para esta campanha.');
        }

        if ($message === '') {
            throw new RuntimeException('ConteÃºdo de email nÃ£o configurado para esta campanha.');
        }

        $attachments = $this->resolveEmailAttachments($payload['attachments'] ?? []);
        $tenantId = $this->resolveTenantId();
        $emailProviderOverride = $this->resolveCampaignEmailProviderOverride();

        $sent = $this->emailSender->sendCampaign(
            $tenantId,
            $destination,
            $subject,
            $message,
            $attachments,
            $meta,
            $emailProviderOverride
        );

        return [
            'success' => $sent,
            'error_message' => $sent ? null : 'Falha ao enviar email da campanha.',
        ];
    }

    /**
     * @param array<string,mixed> $vars
     * @param array<string,mixed> $meta
     * @return array{success:bool,error_message:?string}
     */
    private function sendWhatsApp(Campaign $campaign, string $destination, array $vars, array $meta): array
    {
        $payload = $this->renderer->renderChannel($campaign, 'whatsapp', $vars);
        $compositionMode = strtolower(trim((string) ($payload['composition_mode'] ?? 'manual')));
        $templateType = strtolower(trim((string) ($payload['template_type'] ?? '')));
        $messageType = strtolower(trim((string) ($payload['message_type'] ?? 'text')));
        $templateResolutionStatus = strtolower(trim((string) ($payload['template_resolution_status'] ?? '')));
        $renderError = trim((string) ($payload['render_error'] ?? ''));
        $whatsAppProviderOverride = $this->resolveCampaignWhatsAppProviderOverride();
        $isOfficialProvider = $this->campaignTemplateProviderResolver->isOfficialWhatsApp();

        $meta['composition_mode'] = $compositionMode;
        if ($templateType !== '') {
            $meta['template_type'] = $templateType;
        }
        if ($templateResolutionStatus !== '') {
            $meta['template_resolution_status'] = $templateResolutionStatus;
        }
        if ($renderError !== '') {
            throw new RuntimeException($renderError);
        }

        if ($compositionMode === 'template') {
            if ($templateType === 'official') {
                if (!$isOfficialProvider) {
                    throw new RuntimeException('Template oficial sÃ³ pode ser usado quando o provider efetivo de campanhas Ã© WhatsApp Oficial.');
                }

                return $this->sendOfficialWhatsAppTemplate($destination, $payload, $meta);
            }

            if ($templateType !== 'unofficial') {
                throw new RuntimeException('Tipo de template do WhatsApp invÃ¡lido para envio da campanha.');
            }

            if ($isOfficialProvider) {
                throw new RuntimeException('Campanhas com WhatsApp Oficial exigem template oficial aprovado pela Meta.');
            }

            $templateId = $this->normalizeNullableInt($payload['template_id'] ?? null);
            if (!$templateId) {
                throw new RuntimeException('Template nÃ£o oficial da campanha nÃ£o foi selecionado.');
            }

            $templateIsActive = (bool) ($payload['template_is_active'] ?? false);
            if (!$templateIsActive) {
                throw new RuntimeException('Template nÃ£o oficial da campanha estÃ¡ inativo ou indisponÃ­vel.');
            }

            $text = trim((string) ($payload['text'] ?? ''));
            if ($text === '') {
                throw new RuntimeException('ConteÃºdo do template nÃ£o oficial nÃ£o pÃ´de ser renderizado.');
            }

            $meta['template_id'] = $templateId;
            $meta['template_name'] = (string) ($payload['template_name'] ?? '');

            $sent = $this->whatsAppSender->send(
                $this->resolveTenantId(),
                $destination,
                $text,
                $meta,
                $whatsAppProviderOverride
            );

            return [
                'success' => $sent,
                'error_message' => $sent ? null : 'Falha ao enviar template nÃ£o oficial via WhatsApp.',
            ];
        }

        if ($isOfficialProvider) {
            throw new RuntimeException('Campanhas com WhatsApp Oficial exigem template aprovado pela Meta.');
        }

        if ($messageType === 'media') {
            $media = is_array($payload['media'] ?? null) ? $payload['media'] : [];
            $source = strtolower(trim((string) ($media['source'] ?? '')));
            $caption = trim((string) ($media['caption'] ?? ''));
            $mediaKind = strtolower(trim((string) ($media['kind'] ?? 'document')));

            $meta['media_source'] = $source;
            $meta['media_kind'] = $mediaKind;

            if ($source === 'url') {
                $url = trim((string) ($media['url'] ?? ''));
                if ($url === '') {
                    throw new RuntimeException('MÃ­dia do WhatsApp sem URL configurada.');
                }

                $sent = $this->whatsAppSender->sendMediaFromUrl(
                    $this->resolveTenantId(),
                    $destination,
                    $url,
                    $caption !== '' ? $caption : null,
                    $meta,
                    $whatsAppProviderOverride
                );

                return [
                    'success' => $sent,
                    'error_message' => $sent ? null : 'Falha ao enviar mÃ­dia via WhatsApp.',
                ];
            }

            if ($source === 'upload') {
                $assetId = $this->normalizeNullableInt($media['asset_id'] ?? null);
                $meta['asset_id'] = $assetId;

                if (!$assetId) {
                    throw new RuntimeException('Asset de mÃ­dia nÃ£o encontrado para envio via upload.');
                }

                $asset = Asset::query()->find($assetId);
                if (!$asset) {
                    throw new RuntimeException('Asset de mÃ­dia nÃ£o encontrado para envio via upload.');
                }

                $publicUrl = $this->resolvePublicUrl($asset);
                if ($publicUrl === null) {
                    throw new RuntimeException('MÃ­dia via upload requer URL pÃºblica do asset para envio no provedor atual.');
                }

                $sent = $this->whatsAppSender->sendMediaFromUrl(
                    $this->resolveTenantId(),
                    $destination,
                    $publicUrl,
                    $caption !== '' ? $caption : null,
                    $meta,
                    $whatsAppProviderOverride
                );

                return [
                    'success' => $sent,
                    'error_message' => $sent ? null : 'Falha ao enviar mÃ­dia via WhatsApp.',
                ];
            }

            throw new RuntimeException('Source de mÃ­dia WhatsApp invÃ¡lido.');
        }

        $text = trim((string) ($payload['text'] ?? ''));
        if ($text === '') {
            throw new RuntimeException('Mensagem de WhatsApp nÃ£o configurada para esta campanha.');
        }

        $sent = $this->whatsAppSender->send(
            $this->resolveTenantId(),
            $destination,
            $text,
            $meta,
            $whatsAppProviderOverride
        );

        return [
            'success' => $sent,
            'error_message' => $sent ? null : 'Falha ao enviar mensagem WhatsApp da campanha.',
        ];
    }

    /**
     * @param array<string,mixed> $payload
     * @param array<string,mixed> $meta
     * @return array{success:bool,error_message:?string}
     */
    private function sendOfficialWhatsAppTemplate(string $destination, array $payload, array $meta): array
    {
        $officialTemplateId = trim((string) ($payload['official_template_id'] ?? ''));
        if ($officialTemplateId === '') {
            return [
                'success' => false,
                'error_message' => 'Selecione um template oficial aprovado para enviar a campanha.',
            ];
        }

        $tenantId = $this->resolveTenantId();
        if ($tenantId === '') {
            return [
                'success' => false,
                'error_message' => 'Tenant invÃ¡lido para envio de template oficial.',
            ];
        }

        $template = WhatsAppOfficialTemplate::query()
            ->officialProvider()
            ->forTenant($tenantId)
            ->find($officialTemplateId);

        if (!$template) {
            return [
                'success' => false,
                'error_message' => 'Template oficial não encontrado para este tenant.',
            ];
        }

        if (strtolower(trim((string) $template->status)) !== WhatsAppOfficialTemplate::STATUS_APPROVED) {
            return [
                'success' => false,
                'error_message' => 'Template oficial selecionado não está aprovado no momento.',
            ];
        }

        $meta['official_template_id'] = (string) $template->id;
        $meta['official_template_key'] = (string) $template->key;
        $meta['official_template_name'] = (string) $template->meta_template_name;
        $meta['official_template_language'] = (string) $template->language;

        $variables = is_array($payload['official_variables'] ?? null)
            ? $payload['official_variables']
            : [];

        try {
            $this->applyCampaignWhatsAppRuntimeConfig();

            $result = $this->officialWhatsAppMessageService->sendManualTest(
                $template,
                $destination,
                $variables,
                $meta
            );

            $httpStatus = is_numeric($result['http_status'] ?? null) ? (int) $result['http_status'] : null;
            if ($httpStatus !== null) {
                $meta['http_status'] = $httpStatus;
            }

            $summary = trim((string) ($result['response_summary'] ?? ''));
            $auditMessage = $summary !== ''
                ? $summary
                : ('Template oficial enviado: ' . (string) $template->key);

            $this->deliveryLogger->logSuccess(
                $tenantId,
                'whatsapp',
                (string) ($meta['key'] ?? 'campaign'),
                'whatsapp:whatsapp_business',
                $destination,
                null,
                $auditMessage,
                $meta
            );

            return [
                'success' => true,
                'error_message' => null,
            ];
        } catch (Throwable $exception) {
            $this->deliveryLogger->logError(
                $tenantId,
                'whatsapp',
                (string) ($meta['key'] ?? 'campaign'),
                'whatsapp:whatsapp_business',
                $destination,
                null,
                'Falha no envio de template oficial da campanha.',
                $exception,
                $meta
            );

            return [
                'success' => false,
                'error_message' => $exception->getMessage(),
            ];
        }
    }

    /**
     * @param mixed $attachments
     * @return array<int,array{path:string,filename?:string,mime?:string}>
     */
    private function resolveEmailAttachments(mixed $attachments): array
    {
        if (!is_array($attachments)) {
            return [];
        }

        $resolved = [];
        foreach ($attachments as $attachment) {
            if (!is_array($attachment)) {
                continue;
            }

            $source = strtolower(trim((string) ($attachment['source'] ?? '')));
            if ($source !== 'upload') {
                continue;
            }

            $assetId = $this->normalizeNullableInt($attachment['asset_id'] ?? null);
            if (!$assetId) {
                continue;
            }

            $asset = Asset::query()->find($assetId);
            if (!$asset) {
                continue;
            }

            $disk = trim((string) $asset->disk);
            $path = trim((string) $asset->path);
            if ($disk === '' || $path === '' || !Storage::disk($disk)->exists($path)) {
                continue;
            }

            $resolved[] = [
                'path' => Storage::disk($disk)->path($path),
                'filename' => trim((string) ($attachment['filename'] ?? $asset->filename)),
                'mime' => trim((string) ($attachment['mime'] ?? $asset->mime)),
            ];
        }

        return $resolved;
    }

    private function resolvePublicUrl(Asset $asset): ?string
    {
        $disk = trim((string) ($asset->disk ?? ''));
        $path = trim((string) ($asset->path ?? ''));
        if ($disk === '' || $path === '') {
            return null;
        }

        try {
            if ($disk === 'public') {
                return Storage::disk($disk)->url($path);
            }

            if (config('filesystems.disks.' . $disk . '.driver') === 's3') {
                return Storage::disk($disk)->temporaryUrl($path, now()->addMinutes(20));
            }
        } catch (Throwable) {
            return null;
        }

        return null;
    }

    /**
     * @param array<string,mixed> $meta
     */
    private function logError(
        string $channel,
        array $meta,
        string $destination,
        Throwable $error,
        ?string $subject,
        string $message
    ): void {
        $this->deliveryLogger->logError(
            $this->resolveTenantId(),
            $channel,
            (string) ($meta['key'] ?? 'campaign'),
            $channel === 'email' ? 'mail:campaign' : 'whatsapp:campaign',
            $destination,
            $subject,
            $message !== '' ? $message : 'Campaign dispatch error',
            $error,
            $meta
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    private function resolveCampaignEmailProviderOverride(): ?array
    {
        $config = TenantSetting::campaignEmailConfig();
        $mode = strtolower(trim((string) ($config['mode'] ?? 'notifications')));

        if ($mode !== 'custom') {
            return null;
        }

        $fromName = trim((string) ($config['from_name'] ?? ''));
        if ($fromName === '') {
            $fromName = (string) config('mail.from.name', '');
        }

        return [
            'driver' => 'tenancy',
            'host' => (string) ($config['host'] ?? ''),
            'port' => (string) ($config['port'] ?? ''),
            'username' => (string) ($config['username'] ?? ''),
            'password' => (string) ($config['password'] ?? ''),
            'encryption' => (string) ($config['encryption'] ?? ''),
            'from_name' => $fromName,
            'from_address' => (string) ($config['from_address'] ?? ''),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function resolveCampaignWhatsAppProviderOverride(): ?array
    {
        $config = TenantSetting::campaignWhatsAppConfig();
        $mode = strtolower(trim((string) ($config['mode'] ?? 'notifications')));

        if ($mode !== 'custom') {
            return null;
        }

        return [
            'driver' => 'tenancy',
            'provider' => (string) ($config['provider'] ?? 'whatsapp_business'),
            'meta_access_token' => (string) ($config['meta_access_token'] ?? ''),
            'meta_phone_number_id' => (string) ($config['meta_phone_number_id'] ?? ''),
            'zapi_api_url' => (string) ($config['zapi_api_url'] ?? ''),
            'zapi_token' => (string) ($config['zapi_token'] ?? ''),
            'zapi_client_token' => (string) ($config['zapi_client_token'] ?? ''),
            'zapi_instance_id' => (string) ($config['zapi_instance_id'] ?? ''),
            'waha_base_url' => (string) ($config['waha_base_url'] ?? ''),
            'waha_api_key' => (string) ($config['waha_api_key'] ?? ''),
            'waha_session' => (string) ($config['waha_session'] ?? 'default'),
            'evolution_base_url' => (string) ($config['evolution_base_url'] ?? ''),
            'evolution_api_key' => (string) ($config['evolution_api_key'] ?? ''),
            'evolution_instance' => (string) ($config['evolution_instance'] ?? 'default'),
        ];
    }

    private function applyCampaignWhatsAppRuntimeConfig(): void
    {
        $config = TenantSetting::campaignWhatsAppConfig();
        $mode = strtolower(trim((string) ($config['mode'] ?? 'notifications')));

        if ($mode !== 'custom') {
            $this->tenantWhatsAppConfigService->applyRuntimeConfig();
            return;
        }

        $provider = $this->normalizeProvider((string) ($config['provider'] ?? 'whatsapp_business'));
        $runtimeConfig = [
            'driver' => 'tenancy',
            'provider' => $provider,
            'meta_access_token' => (string) ($config['meta_access_token'] ?? ''),
            'meta_phone_number_id' => (string) ($config['meta_phone_number_id'] ?? ''),
            'meta_waba_id' => (string) TenantSetting::get('campaigns.whatsapp.meta.waba_id', ''),
            'zapi_api_url' => (string) ($config['zapi_api_url'] ?? ''),
            'zapi_token' => (string) ($config['zapi_token'] ?? ''),
            'zapi_client_token' => (string) ($config['zapi_client_token'] ?? ''),
            'zapi_instance_id' => (string) ($config['zapi_instance_id'] ?? ''),
            'waha_base_url' => (string) ($config['waha_base_url'] ?? ''),
            'waha_api_key' => (string) ($config['waha_api_key'] ?? ''),
            'waha_session' => (string) ($config['waha_session'] ?? 'default'),
            'evolution_base_url' => (string) ($config['evolution_base_url'] ?? ''),
            'evolution_api_key' => (string) ($config['evolution_api_key'] ?? ''),
            'evolution_instance' => (string) ($config['evolution_instance'] ?? 'default'),
        ];

        config([
            'services.whatsapp.force_runtime_provider' => true,
            'services.whatsapp.runtime_provider' => $provider,
            'services.whatsapp.provider' => $provider,
            'services.whatsapp.business.api_url' => (string) config('services.whatsapp.business.api_url', 'https://graph.facebook.com/v22.0'),
            'services.whatsapp.business.token' => (string) ($runtimeConfig['meta_access_token'] ?? ''),
            'services.whatsapp.business.phone_id' => (string) ($runtimeConfig['meta_phone_number_id'] ?? ''),
            'services.whatsapp.business.waba_id' => (string) ($runtimeConfig['meta_waba_id'] ?? ''),
            'services.whatsapp.zapi.api_url' => (string) ($runtimeConfig['zapi_api_url'] ?? ''),
            'services.whatsapp.zapi.token' => (string) ($runtimeConfig['zapi_token'] ?? ''),
            'services.whatsapp.zapi.client_token' => (string) ($runtimeConfig['zapi_client_token'] ?? ''),
            'services.whatsapp.zapi.instance_id' => (string) ($runtimeConfig['zapi_instance_id'] ?? ''),
        ]);

        $this->providerConfigResolver->applyUnofficialRuntimeConfigs($runtimeConfig);
    }

    private function resolveTenantId(): string
    {
        $tenantId = tenant()?->id;
        if (is_string($tenantId) && trim($tenantId) !== '') {
            return trim($tenantId);
        }

        return '';
    }

    private function normalizeNullableInt(mixed $value): ?int
    {
        if ($value === null) {
            return null;
        }

        $raw = trim((string) $value);
        if ($raw === '' || !ctype_digit($raw)) {
            return null;
        }

        return (int) $raw;
    }

    private function normalizeProvider(string $provider): string
    {
        $provider = strtolower(trim($provider));

        return match ($provider) {
            'whatsapp-business', 'business', 'meta' => 'whatsapp_business',
            'waha_core', 'waha-core', 'waha_gateway', 'waha-gateway',
            'whatsapp_waha', 'whatsapp-waha', 'whatsapp_gateway', 'whatsapp-gateway' => 'waha',
            'evolution_api', 'evolution-api', 'evolutionapi',
            'evo_api', 'evo-api', 'whatsapp_evolution', 'whatsapp-evolution' => 'evolution',
            default => in_array($provider, ['whatsapp_business', 'zapi', 'waha', 'evolution'], true)
                ? $provider
                : 'whatsapp_business',
        };
    }
}

