<?php

namespace App\Services\Tenant;

use App\Models\Platform\WhatsAppOfficialTemplate;
use App\Models\Tenant\Campaign;
use App\Models\Tenant\CampaignTemplate;
use Illuminate\Support\Arr;

class CampaignRenderer
{
    public function __construct(
        private readonly TemplateRenderer $templateRenderer,
        private readonly CampaignTemplateProviderResolver $providerResolver
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

        $provider = $this->normalizeProvider((string) ($whatsappContent['provider'] ?? ''));
        $compositionMode = $this->normalizeCompositionMode((string) ($whatsappContent['composition_mode'] ?? ''), $provider);

        if ($compositionMode === 'template') {
            $templateType = strtolower(trim((string) ($whatsappContent['template_type'] ?? '')));
            if ($templateType === '') {
                $templateType = $provider === 'whatsapp_business' ? 'official' : 'unofficial';
            }

            if ($templateType === 'official') {
                return $this->renderOfficialWhatsAppTemplate($provider, $whatsappContent, $vars);
            }

            return $this->renderUnofficialWhatsAppTemplate($provider, $whatsappContent, $vars);
        }

        return $this->renderManualWhatsapp($provider, $whatsappContent, $vars);
    }

    /**
     * @param array<string,mixed> $whatsappContent
     * @param array<string,mixed> $vars
     * @return array<string,mixed>
     */
    private function renderManualWhatsapp(string $provider, array $whatsappContent, array $vars): array
    {
        $messageType = strtolower(trim((string) ($whatsappContent['message_type'] ?? 'text')));
        $media = $whatsappContent['media'] ?? [];
        $media = is_array($media) ? $media : [];

        return [
            'provider' => $provider,
            'composition_mode' => 'manual',
            'template_type' => null,
            'template_resolution_status' => 'legacy_manual',
            'render_error' => null,
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
     * @param array<string,mixed> $whatsappContent
     * @param array<string,mixed> $vars
     * @return array<string,mixed>
     */
    private function renderUnofficialWhatsAppTemplate(string $provider, array $whatsappContent, array $vars): array
    {
        $templateId = $this->normalizeNullableInt($whatsappContent['template_id'] ?? null);
        $template = null;
        $resolutionStatus = 'resolved';
        $renderError = null;

        if ($templateId !== null) {
            $template = CampaignTemplate::query()
                ->forWhatsApp()
                ->unofficial()
                ->find($templateId);
        }

        if ($templateId === null) {
            $resolutionStatus = 'missing_template_id';
            $renderError = 'Template nÃ£o oficial da campanha nÃ£o foi selecionado.';
        } elseif (!$template) {
            $resolutionStatus = 'template_not_found';
            $renderError = 'Template nÃ£o oficial da campanha nÃ£o estÃ¡ disponÃ­vel.';
        } elseif ($template->is_active !== true) {
            $resolutionStatus = 'template_inactive';
            $renderError = 'Template nÃ£o oficial da campanha estÃ¡ inativo.';
        }

        $renderedContent = $template
            ? $this->renderText((string) $template->content, $vars)
            : '';

        if ($renderError === null && trim($renderedContent) === '') {
            $resolutionStatus = 'template_render_empty';
            $renderError = 'ConteÃºdo do template nÃ£o oficial nÃ£o pÃ´de ser renderizado.';
        }

        return [
            'provider' => $provider,
            'composition_mode' => 'template',
            'template_type' => 'unofficial',
            'template_id' => $templateId,
            'template_name' => $template?->name,
            'template_is_active' => $template?->is_active === true,
            'template_content' => $template?->content,
            'template_resolution_status' => $resolutionStatus,
            'render_error' => $renderError,
            'message_type' => 'text',
            'text' => $renderedContent,
            'media' => [],
        ];
    }

    /**
     * @param array<string,mixed> $whatsappContent
     * @param array<string,mixed> $vars
     * @return array<string,mixed>
     */
    private function renderOfficialWhatsAppTemplate(string $provider, array $whatsappContent, array $vars): array
    {
        $officialTemplateId = trim((string) ($whatsappContent['official_template_id'] ?? ''));
        $template = null;
        $tenantId = trim((string) (tenant()?->id ?? ''));
        $resolutionStatus = 'resolved';
        $renderError = null;

        if ($officialTemplateId !== '' && $tenantId !== '') {
            $template = WhatsAppOfficialTemplate::query()
                ->officialProvider()
                ->forTenant($tenantId)
                ->find($officialTemplateId);
        }

        if ($officialTemplateId === '') {
            $resolutionStatus = 'missing_template_id';
            $renderError = 'Template oficial da campanha nÃ£o foi selecionado.';
        } elseif ($tenantId === '') {
            $resolutionStatus = 'missing_tenant';
            $renderError = 'NÃ£o foi possÃ­vel validar o template oficial para o tenant atual.';
        } elseif (!$template) {
            $resolutionStatus = 'template_not_found';
            $renderError = 'Template oficial da campanha nÃ£o estÃ¡ disponÃ­vel para este tenant.';
        } elseif (strtolower((string) $template->status) !== WhatsAppOfficialTemplate::STATUS_APPROVED) {
            $resolutionStatus = 'template_not_approved';
            $renderError = 'Template oficial da campanha nÃ£o estÃ¡ aprovado no momento.';
        }

        return [
            'provider' => $provider,
            'composition_mode' => 'template',
            'template_type' => 'official',
            'official_template_id' => $officialTemplateId !== '' ? $officialTemplateId : null,
            'official_template' => $template ? [
                'id' => (string) $template->id,
                'key' => (string) $template->key,
                'meta_template_name' => (string) $template->meta_template_name,
                'language' => (string) $template->language,
                'category' => (string) $template->category,
                'version' => (int) $template->version,
                'status' => (string) $template->status,
            ] : null,
            'official_variables' => $this->normalizeOfficialVariables($vars),
            'template_resolution_status' => $resolutionStatus,
            'render_error' => $renderError,
            'message_type' => 'template',
            'text' => '',
            'media' => [],
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

    private function normalizeProvider(string $provider): string
    {
        $normalized = strtolower(trim($provider));
        $normalized = match ($normalized) {
            'whatsapp-business', 'business', 'meta' => 'whatsapp_business',
            'waha_core', 'waha-core', 'waha_gateway', 'waha-gateway',
            'whatsapp_waha', 'whatsapp-waha', 'whatsapp_gateway', 'whatsapp-gateway' => 'waha',
            'evolution_api', 'evolution-api', 'evolutionapi',
            'evo_api', 'evo-api', 'whatsapp_evolution', 'whatsapp-evolution' => 'evolution',
            default => $normalized,
        };

        if (in_array($normalized, ['whatsapp_business', 'zapi', 'waha', 'evolution'], true)) {
            return $normalized;
        }

        return $this->providerResolver->resolveWhatsAppProvider();
    }

    private function normalizeCompositionMode(string $mode, string $provider): string
    {
        $mode = strtolower(trim($mode));
        if (in_array($mode, ['manual', 'template'], true)) {
            return $mode;
        }

        return $provider === 'whatsapp_business' ? 'template' : 'manual';
    }

    /**
     * @param array<string,mixed> $vars
     * @return array<string,string>
     */
    private function normalizeOfficialVariables(array $vars): array
    {
        $flat = Arr::dot($vars);
        $normalized = [];

        foreach ($flat as $key => $value) {
            if (!is_string($key) && !is_int($key)) {
                continue;
            }

            $name = trim((string) $key);
            if ($name === '') {
                continue;
            }

            $stringValue = $this->stringifyValue($value);
            if ($stringValue === null) {
                continue;
            }

            $normalized[$name] = $stringValue;
            $alias = str_replace('.', '_', $name);
            if ($alias !== $name && !array_key_exists($alias, $normalized)) {
                $normalized[$alias] = $stringValue;
            }
        }

        return $normalized;
    }

    private function stringifyValue(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_scalar($value)) {
            return trim((string) $value);
        }

        return null;
    }

    private function normalizeNullableInt(mixed $value): ?int
    {
        if ($value === null) {
            return null;
        }

        if (is_int($value)) {
            return $value > 0 ? $value : null;
        }

        $raw = trim((string) $value);
        if ($raw === '' || !ctype_digit($raw)) {
            return null;
        }

        $normalized = (int) $raw;
        return $normalized > 0 ? $normalized : null;
    }
}
