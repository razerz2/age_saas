<?php

namespace App\Services\Platform;

use App\Models\Platform\WhatsAppOfficialTemplate;
use App\Models\Platform\WhatsAppOfficialTemplateBinding;
use App\Support\WhatsAppOfficialTenantEventCatalog;
use Illuminate\Support\Facades\Schema;

class WhatsAppOfficialTemplateResolver
{
    private ?bool $bindingsTableAvailable = null;

    public function resolveApprovedByKey(string $key): ?WhatsAppOfficialTemplate
    {
        $inputKey = trim($key);
        if ($inputKey === '') {
            return null;
        }

        $scopeContext = $this->resolveScopeByEventKey($inputKey);
        $canonicalKey = $scopeContext['event_key'] ?? $inputKey;
        $keyCandidates = $this->equivalentKeyCandidates($canonicalKey);

        if ($scopeContext !== null && $this->isBindingsTableAvailable()) {
            $bindings = WhatsAppOfficialTemplateBinding::query()
                ->byScope((string) $scopeContext['scope'])
                ->whereIn('event_key', $keyCandidates)
                ->with('officialTemplate')
                ->get();

            foreach ($bindings as $binding) {
                $template = $binding->officialTemplate;
                if (
                    $template
                    && (string) $template->provider === WhatsAppOfficialTemplate::PROVIDER
                    && (string) $template->status === WhatsAppOfficialTemplate::STATUS_APPROVED
                    && $template->tenant_id === null
                    && $this->keysAreEquivalent((string) $template->key, $canonicalKey)
                ) {
                    return $template;
                }
            }

            if ($bindings->isNotEmpty()) {
                // If a binding exists but is inconsistent/ineligible, do not fallback silently.
                return null;
            }
        }

        return WhatsAppOfficialTemplate::query()
            ->officialProvider()
            ->forPlatformBaseline()
            ->whereIn('key', $keyCandidates)
            ->approved()
            ->orderByDesc('version')
            ->first();
    }

    public function resolveMetaTemplateNameByKey(string $key): ?string
    {
        $template = $this->resolveApprovedByKey($key);
        return $template?->meta_template_name;
    }

    /**
     * @return array{scope: string, event_key: string}|null
     */
    private function resolveScopeByEventKey(string $key): ?array
    {
        $normalizedInput = $this->normalizeComparableKey($key);
        if ($normalizedInput === '') {
            return null;
        }

        $groups = WhatsAppOfficialTenantEventCatalog::groupedByDomain();

        foreach ([WhatsAppOfficialTemplateBinding::SCOPE_PLATFORM, WhatsAppOfficialTemplateBinding::SCOPE_TENANT] as $scope) {
            $events = (array) ($groups[$scope] ?? []);
            foreach ($events as $event) {
                $eventKey = (string) ($event['key'] ?? '');
                if ($this->normalizeComparableKey($eventKey) === $normalizedInput) {
                    return [
                        'scope' => $scope,
                        'event_key' => $eventKey,
                    ];
                }
            }
        }

        return null;
    }

    private function isBindingsTableAvailable(): bool
    {
        if ($this->bindingsTableAvailable !== null) {
            return $this->bindingsTableAvailable;
        }

        $this->bindingsTableAvailable = Schema::hasTable('whatsapp_official_template_bindings');

        return $this->bindingsTableAvailable;
    }

    /**
     * @return array<int, string>
     */
    private function equivalentKeyCandidates(string $key): array
    {
        $trimmed = trim($key);
        if ($trimmed === '') {
            return [];
        }

        $underscore = str_replace('.', '_', $trimmed);
        $dot = str_replace('_', '.', $trimmed);

        return array_values(array_unique([$trimmed, $underscore, $dot]));
    }

    private function keysAreEquivalent(string $left, string $right): bool
    {
        $normalizedLeft = $this->normalizeComparableKey($left);
        if ($normalizedLeft === '') {
            return false;
        }

        return $normalizedLeft === $this->normalizeComparableKey($right);
    }

    private function normalizeComparableKey(string $value): string
    {
        $normalized = strtolower(trim($value));
        if ($normalized === '') {
            return '';
        }

        $normalized = str_replace(['.', '-', ' '], '_', $normalized);
        $normalized = preg_replace('/_+/', '_', $normalized) ?? $normalized;

        return trim($normalized, '_');
    }
}
