<?php

namespace App\Services\Tenant;

use App\Models\Tenant\Asset;
use App\Models\Tenant\Campaign;
use App\Models\Tenant\CampaignRecipient;
use App\Models\Tenant\CampaignRun;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class CampaignDeliveryService
{
    public function __construct(
        private readonly CampaignRenderer $renderer,
        private readonly EmailSender $emailSender,
        private readonly WhatsAppSender $whatsAppSender,
        private readonly NotificationDeliveryLogger $deliveryLogger
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

            throw new RuntimeException('Canal não suportado para envio da campanha.');
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
            throw new RuntimeException('Assunto de email não configurado para esta campanha.');
        }

        if ($message === '') {
            throw new RuntimeException('Conteúdo de email não configurado para esta campanha.');
        }

        $attachments = $this->resolveEmailAttachments($payload['attachments'] ?? []);
        $tenantId = $this->resolveTenantId();

        $sent = $this->emailSender->sendCampaign(
            $tenantId,
            $destination,
            $subject,
            $message,
            $attachments,
            $meta
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
        $messageType = strtolower(trim((string) ($payload['message_type'] ?? 'text')));

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
                    throw new RuntimeException('Mídia do WhatsApp sem URL configurada.');
                }

                $sent = $this->whatsAppSender->sendMediaFromUrl(
                    $this->resolveTenantId(),
                    $destination,
                    $url,
                    $caption !== '' ? $caption : null,
                    $meta
                );

                return [
                    'success' => $sent,
                    'error_message' => $sent ? null : 'Falha ao enviar mídia via WhatsApp.',
                ];
            }

            if ($source === 'upload') {
                $assetId = $this->normalizeNullableInt($media['asset_id'] ?? null);
                $meta['asset_id'] = $assetId;

                if (!$assetId) {
                    throw new RuntimeException('Asset de mídia não encontrado para envio via upload.');
                }

                $asset = Asset::query()->find($assetId);
                if (!$asset) {
                    throw new RuntimeException('Asset de mídia não encontrado para envio via upload.');
                }

                $publicUrl = $this->resolvePublicUrl($asset);
                if ($publicUrl === null) {
                    throw new RuntimeException('Mídia via upload requer URL pública do asset para envio no provedor atual.');
                }

                $sent = $this->whatsAppSender->sendMediaFromUrl(
                    $this->resolveTenantId(),
                    $destination,
                    $publicUrl,
                    $caption !== '' ? $caption : null,
                    $meta
                );

                return [
                    'success' => $sent,
                    'error_message' => $sent ? null : 'Falha ao enviar mídia via WhatsApp.',
                ];
            }

            throw new RuntimeException('Source de mídia WhatsApp inválido.');
        }

        $text = trim((string) ($payload['text'] ?? ''));
        if ($text === '') {
            throw new RuntimeException('Mensagem de WhatsApp não configurada para esta campanha.');
        }

        $sent = $this->whatsAppSender->send($this->resolveTenantId(), $destination, $text, $meta);

        return [
            'success' => $sent,
            'error_message' => $sent ? null : 'Falha ao enviar mensagem WhatsApp da campanha.',
        ];
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
}
