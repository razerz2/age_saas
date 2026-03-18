<?php

namespace App\Services\Platform;

use App\Models\Platform\WhatsAppOfficialTemplate;
use App\Models\Platform\WhatsAppOfficialTenantTemplate;

class WhatsAppOfficialTenantTemplateResolver
{
    public function resolveMapping(
        string $tenantId,
        string $eventKey,
        ?string $language = 'pt_BR',
        bool $allowLanguageFallback = true
    ): ?WhatsAppOfficialTenantTemplate {
        $tenantId = trim($tenantId);
        $eventKey = trim($eventKey);
        $language = $language !== null ? trim($language) : null;

        if ($tenantId === '' || $eventKey === '') {
            return null;
        }

        $baseQuery = WhatsAppOfficialTenantTemplate::query()
            ->forTenant($tenantId)
            ->forEvent($eventKey)
            ->active()
            ->whereHas('officialTemplate', function ($query): void {
                $query
                    ->where('provider', WhatsAppOfficialTemplate::PROVIDER)
                    ->where('status', WhatsAppOfficialTemplate::STATUS_APPROVED);
            })
            ->with('officialTemplate');

        if ($language !== null && $language !== '') {
            $exact = (clone $baseQuery)
                ->where('language', $language)
                ->orderByDesc('updated_at')
                ->first();

            if ($exact) {
                return $exact;
            }

            if ($allowLanguageFallback && $language !== 'pt_BR') {
                $ptBr = (clone $baseQuery)
                    ->where('language', 'pt_BR')
                    ->orderByDesc('updated_at')
                    ->first();

                if ($ptBr) {
                    return $ptBr;
                }
            }
        }

        if ($allowLanguageFallback) {
            return (clone $baseQuery)
                ->orderByDesc('updated_at')
                ->first();
        }

        return null;
    }

    public function resolveOfficialTemplate(
        string $tenantId,
        string $eventKey,
        ?string $language = 'pt_BR',
        bool $allowLanguageFallback = true
    ): ?WhatsAppOfficialTemplate {
        $mapping = $this->resolveMapping($tenantId, $eventKey, $language, $allowLanguageFallback);
        if (!$mapping) {
            return null;
        }

        $template = $mapping->officialTemplate;
        if (!$template) {
            return null;
        }

        if (
            (string) $template->provider !== WhatsAppOfficialTemplate::PROVIDER
            || (string) $template->status !== WhatsAppOfficialTemplate::STATUS_APPROVED
        ) {
            return null;
        }

        return $template;
    }

    /**
     * @return array{
     *   source: string,
     *   mapping_id: ?string,
     *   template_id: ?string,
     *   template: ?WhatsAppOfficialTemplate
     * }
     */
    public function resolveWithContext(
        string $tenantId,
        string $eventKey,
        ?string $language = 'pt_BR',
        bool $allowLanguageFallback = true
    ): array {
        $mapping = $this->resolveMapping($tenantId, $eventKey, $language, $allowLanguageFallback);
        $template = $mapping?->officialTemplate;

        return [
            'source' => $mapping ? 'tenant_mapping' : 'not_found',
            'mapping_id' => $mapping?->id ? (string) $mapping->id : null,
            'template_id' => $template?->id ? (string) $template->id : null,
            'template' => $template,
        ];
    }
}

