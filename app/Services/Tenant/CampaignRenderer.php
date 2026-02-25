<?php

namespace App\Services\Tenant;

use App\Models\Tenant\Campaign;

class CampaignRenderer
{
    public function __construct(
        private readonly TemplateRenderer $templateRenderer
    ) {
    }

    /**
     * @param array<string,mixed> $vars
     * @return array<string,mixed>
     */
    public function renderChannel(Campaign $campaign, string $channel, array $vars = []): array
    {
        $channel = strtolower(trim($channel));
        $content = is_array($campaign->content_json) ? $campaign->content_json : [];

        return match ($channel) {
            'email' => $this->renderEmail($content, $vars),
            'whatsapp' => $this->renderWhatsapp($content, $vars),
            default => [],
        };
    }

    /**
     * @param array<string,mixed> $content
     * @param array<string,mixed> $vars
     * @return array<string,mixed>
     */
    private function renderEmail(array $content, array $vars): array
    {
        $emailContent = data_get($content, 'email');
        $emailContent = is_array($emailContent) ? $emailContent : [];

        $subject = $this->renderText((string) ($emailContent['subject'] ?? ''), $vars);
        $bodyHtml = $this->renderText((string) ($emailContent['body_html'] ?? ''), $vars);
        $bodyText = $this->renderText((string) ($emailContent['body_text'] ?? ''), $vars);

        $attachments = $emailContent['attachments'] ?? [];
        if (!is_array($attachments)) {
            $attachments = [];
        }

        return [
            'subject' => $subject,
            'body_html' => $bodyHtml,
            'body_text' => $bodyText,
            'message' => $bodyHtml !== '' ? $bodyHtml : $bodyText,
            'attachments' => $attachments,
        ];
    }

    /**
     * @param array<string,mixed> $content
     * @param array<string,mixed> $vars
     * @return array<string,mixed>
     */
    private function renderWhatsapp(array $content, array $vars): array
    {
        $whatsappContent = data_get($content, 'whatsapp');
        $whatsappContent = is_array($whatsappContent) ? $whatsappContent : [];

        $messageType = strtolower(trim((string) ($whatsappContent['message_type'] ?? 'text')));
        $provider = strtolower(trim((string) ($whatsappContent['provider'] ?? 'waha')));

        $media = $whatsappContent['media'] ?? [];
        $media = is_array($media) ? $media : [];

        return [
            'provider' => $provider !== '' ? $provider : 'waha',
            'message_type' => in_array($messageType, ['text', 'media'], true) ? $messageType : 'text',
            'text' => $this->renderText((string) ($whatsappContent['text'] ?? ''), $vars),
            'media' => [
                'kind' => strtolower(trim((string) ($media['kind'] ?? 'document'))),
                'source' => strtolower(trim((string) ($media['source'] ?? 'url'))),
                'url' => $this->renderText((string) ($media['url'] ?? ''), $vars),
                'asset_id' => $media['asset_id'] ?? null,
                'caption' => $this->renderText((string) ($media['caption'] ?? ''), $vars),
            ],
        ];
    }

    /**
     * Renderiza placeholders e remove tokens não resolvidos.
     *
     * @param array<string,mixed> $vars
     */
    private function renderText(string $template, array $vars): string
    {
        $rendered = $this->templateRenderer->render($template, $vars);
        $rendered = preg_replace('/\{\{\s*([A-Za-z0-9_.-]+)\s*\}\}/', '', $rendered);

        return trim((string) ($rendered ?? ''));
    }
}
