<?php

namespace App\Services\Tenant;

use App\Models\Platform\TenantDefaultNotificationTemplate;
use App\Models\Platform\WhatsAppUnofficialTemplate;

class WhatsAppUnofficialTemplateResolutionService
{
    public const SCOPE_TENANT_ONLY = 'tenant_only';
    public const SCOPE_TENANT_THEN_PLATFORM = 'tenant_then_platform';

    public function __construct(
        private readonly NotificationTemplateService $notificationTemplateService
    ) {
    }

    /**
     * Resolve template nao oficial de WhatsApp por key para um tenant.
     *
     * Hierarquia:
     * 1) notification_templates do tenant (customizacao ou baseline ja copiado)
     * 2) fallback opcional para catalogo da Platform (whatsapp_unofficial_templates)
     *
     * @return array{
     *   key:string,
     *   channel:string,
     *   title:?string,
     *   category:?string,
     *   subject:?string,
     *   content:string,
     *   variables:array<int, string>,
     *   source:string,
     *   scope:string,
     *   used_platform_fallback:bool,
     *   is_active:bool
     * }|null
     */
    public function resolve(string $tenantId, string $key, string $scope = self::SCOPE_TENANT_ONLY): ?array
    {
        $tenantId = trim($tenantId);
        $key = trim($key);

        if ($tenantId === '' || $key === '') {
            return null;
        }

        $scope = $this->normalizeScope($scope);
        $tenantTemplate = $this->notificationTemplateService->getOverride($tenantId, 'whatsapp', $key);
        if ($tenantTemplate !== null) {
            $baseline = TenantDefaultNotificationTemplate::query()
                ->where('channel', 'whatsapp')
                ->where('key', $key)
                ->orderByDesc('is_active')
                ->orderByDesc('updated_at')
                ->first();

            $source = 'tenant_custom';
            if ($baseline !== null && $this->isSameAsBaseline($tenantTemplate->content, $baseline->content)) {
                $source = 'tenant_baseline';
            }

            return [
                'key' => $key,
                'channel' => 'whatsapp',
                'title' => $baseline?->title,
                'category' => $baseline?->category,
                'subject' => null,
                'content' => (string) $tenantTemplate->content,
                'variables' => is_array($baseline?->variables) ? array_values($baseline->variables) : [],
                'source' => $source,
                'scope' => 'tenant',
                'used_platform_fallback' => false,
                'is_active' => true,
            ];
        }

        if ($scope !== self::SCOPE_TENANT_THEN_PLATFORM) {
            return null;
        }

        $platformTemplate = WhatsAppUnofficialTemplate::query()
            ->active()
            ->where('key', $key)
            ->first();

        if ($platformTemplate === null) {
            return null;
        }

        return [
            'key' => (string) $platformTemplate->key,
            'channel' => 'whatsapp',
            'title' => (string) $platformTemplate->title,
            'category' => (string) $platformTemplate->category,
            'subject' => null,
            'content' => (string) $platformTemplate->body,
            'variables' => is_array($platformTemplate->variables) ? array_values($platformTemplate->variables) : [],
            'source' => 'platform_unofficial_catalog',
            'scope' => 'platform',
            'used_platform_fallback' => true,
            'is_active' => (bool) $platformTemplate->is_active,
        ];
    }

    private function normalizeScope(string $scope): string
    {
        $scope = trim($scope);
        if ($scope === self::SCOPE_TENANT_THEN_PLATFORM) {
            return $scope;
        }

        return self::SCOPE_TENANT_ONLY;
    }

    private function isSameAsBaseline(?string $tenantContent, ?string $baselineContent): bool
    {
        return trim((string) $tenantContent) === trim((string) $baselineContent);
    }
}
