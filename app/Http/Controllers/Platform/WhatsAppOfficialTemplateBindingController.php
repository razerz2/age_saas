<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Platform\WhatsAppOfficialTemplate;
use App\Models\Platform\WhatsAppOfficialTemplateBinding;
use App\Models\Platform\WhatsAppOfficialTenantTemplate;
use App\Support\WhatsAppOfficialTenantEventCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class WhatsAppOfficialTemplateBindingController extends Controller
{
    public function platformIndex(): View
    {
        $this->authorize('manageBindings', WhatsAppOfficialTemplate::class);

        return $this->renderScopePage(
            WhatsAppOfficialTemplateBinding::SCOPE_PLATFORM,
            'WhatsApp Oficial - Vínculos Platform',
            'Vínculos Oficiais Platform',
            'Platform.whatsapp-official-templates.bindings.index',
            'Platform.whatsapp-official-templates.bindings.upsert',
            'Platform.whatsapp-official-templates.index',
            'Defina qual template oficial Platform está ativo para cada evento/key operacional.'
        );
    }

    public function tenantIndex(): View
    {
        $this->authorize('manageBindings', WhatsAppOfficialTenantTemplate::class);

        return $this->renderScopePage(
            WhatsAppOfficialTemplateBinding::SCOPE_TENANT,
            'WhatsApp Oficial - Vínculos Tenant',
            'Vínculos Oficiais Tenant',
            'Platform.whatsapp-official-tenant-templates.bindings.index',
            'Platform.whatsapp-official-tenant-templates.bindings.upsert',
            'Platform.whatsapp-official-tenant-templates.index',
            'Defina qual template oficial Tenant (baseline) está ativo para cada evento/key clínico.'
        );
    }

    public function upsertPlatform(Request $request): RedirectResponse
    {
        $this->authorize('manageBindings', WhatsAppOfficialTemplate::class);

        return $this->upsertForScope(
            $request,
            WhatsAppOfficialTemplateBinding::SCOPE_PLATFORM,
            'Platform.whatsapp-official-templates.bindings.index',
            'Vínculo oficial Platform atualizado com sucesso.'
        );
    }

    public function upsertTenant(Request $request): RedirectResponse
    {
        $this->authorize('manageBindings', WhatsAppOfficialTenantTemplate::class);

        return $this->upsertForScope(
            $request,
            WhatsAppOfficialTemplateBinding::SCOPE_TENANT,
            'Platform.whatsapp-official-tenant-templates.bindings.index',
            'Vínculo oficial Tenant atualizado com sucesso.'
        );
    }

    private function renderScopePage(
        string $scope,
        string $pageTitle,
        string $breadcrumbLabel,
        string $indexRouteName,
        string $upsertRouteName,
        string $backRouteName,
        string $introMessage
    ): View {
        $events = $this->eventsByScope($scope);
        $eventKeys = array_map(static fn (array $event): string => (string) ($event['key'] ?? ''), $events);
        $normalizedEventLookup = $this->normalizedEventLookup($eventKeys);
        $eventKeyCandidates = $this->expandEquivalentKeys($eventKeys);

        $bindingsStorageReady = Schema::hasTable('whatsapp_official_template_bindings');
        $bindingsByEvent = collect();
        if ($bindingsStorageReady) {
            $bindings = WhatsAppOfficialTemplateBinding::query()
                ->byScope($scope)
                ->whereIn('event_key', $eventKeyCandidates)
                ->with('officialTemplate')
                ->get()
                ->values();

            $bindingsByEvent = $this->mapBindingsByCanonicalEventKey(
                $bindings,
                $normalizedEventLookup
            );
        }

        $templates = WhatsAppOfficialTemplate::query()
            ->officialProvider()
            ->where('status', WhatsAppOfficialTemplate::STATUS_APPROVED)
            ->whereIn('key', $eventKeyCandidates)
            ->orderBy('key')
            ->orderByDesc('version')
            ->get();

        $templatesByEvent = $this->groupTemplatesByCanonicalEventKey(
            $templates,
            $normalizedEventLookup
        );

        return view('platform.whatsapp_official_template_bindings.index', [
            'scope' => $scope,
            'pageTitle' => $pageTitle,
            'breadcrumbLabel' => $breadcrumbLabel,
            'indexRouteName' => $indexRouteName,
            'upsertRouteName' => $upsertRouteName,
            'backRouteName' => $backRouteName,
            'introMessage' => $introMessage,
            'bindingsStorageReady' => $bindingsStorageReady,
            'events' => $events,
            'bindingsByEvent' => $bindingsByEvent,
            'templatesByEvent' => $templatesByEvent,
        ]);
    }

    private function upsertForScope(
        Request $request,
        string $scope,
        string $indexRouteName,
        string $successMessage
    ): RedirectResponse {
        if (!Schema::hasTable('whatsapp_official_template_bindings')) {
            return back()->withErrors([
                'template' => 'Estrutura de vínculos oficiais indisponível. Execute as migrations pendentes e tente novamente.',
            ]);
        }

        $allowedKeys = array_map(
            static fn (array $event): string => (string) ($event['key'] ?? ''),
            $this->eventsByScope($scope)
        );
        $normalizedEventLookup = $this->normalizedEventLookup($allowedKeys);

        $payload = $request->validate([
            'event_key' => ['required', 'string'],
            'whatsapp_official_template_id' => ['required', 'uuid', Rule::exists('whatsapp_official_templates', 'id')],
        ]);

        $requestedEventKey = trim((string) ($payload['event_key'] ?? ''));
        $eventKey = $this->resolveCanonicalEventKey($requestedEventKey, $normalizedEventLookup);
        if ($eventKey === null) {
            return back()->withErrors([
                'event_key' => 'Evento inválido para este escopo de vínculo oficial.',
            ]);
        }

        $templateId = trim((string) ($payload['whatsapp_official_template_id'] ?? ''));

        $template = WhatsAppOfficialTemplate::query()->find($templateId);
        if (!$template) {
            return back()->withErrors(['template' => 'Template oficial não encontrado.']);
        }

        if ((string) $template->provider !== WhatsAppOfficialTemplate::PROVIDER) {
            return back()->withErrors(['template' => 'Apenas templates oficiais (provider whatsapp_business) podem ser vinculados.']);
        }

        if ((string) $template->status !== WhatsAppOfficialTemplate::STATUS_APPROVED) {
            return back()->withErrors(['template' => 'Somente templates APPROVED podem ser definidos como vínculo oficial ativo.']);
        }

        if (!$this->keysAreEquivalent((string) $template->key, $eventKey)) {
            return back()->withErrors(['template' => 'Template selecionado não corresponde ao evento informado.']);
        }

        $actorId = $this->normalizeActorId((string) optional(auth()->user())->id);
        $bindingCandidates = WhatsAppOfficialTemplateBinding::query()
            ->byScope($scope)
            ->whereIn('event_key', $this->equivalentKeyCandidates($eventKey))
            ->with('officialTemplate')
            ->get()
            ->values();
        $binding = $this->pickPreferredBinding($bindingCandidates, $eventKey);

        $previousTemplateId = $binding?->whatsapp_official_template_id
            ? (string) $binding->whatsapp_official_template_id
            : null;

        if (!$binding) {
            $binding = new WhatsAppOfficialTemplateBinding([
                'scope' => $scope,
                'event_key' => $eventKey,
            ]);
            if ($actorId !== null) {
                $binding->created_by = $actorId;
            }
        }

        $binding->whatsapp_official_template_id = (string) $template->id;
        $binding->provider = (string) $template->provider;
        $binding->language = (string) $template->language;
        if ($actorId !== null) {
            $binding->updated_by = $actorId;
        }
        $binding->save();

        Log::info('wa_official_template_binding_updated', [
            'scope' => $scope,
            'event_key' => $eventKey,
            'requested_event_key' => $requestedEventKey,
            'previous_template_id' => $previousTemplateId,
            'new_template_id' => (string) $template->id,
            'template_meta_name' => (string) $template->meta_template_name,
            'template_status' => (string) $template->status,
        ]);

        return redirect()
            ->route($indexRouteName)
            ->with('success', $successMessage);
    }

    /**
     * @return array<int, array{key: string, label: string, domain: string}>
     */
    private function eventsByScope(string $scope): array
    {
        return array_values(WhatsAppOfficialTenantEventCatalog::groupedByDomain()[$scope] ?? []);
    }

    private function normalizeActorId(?string $actorId): ?string
    {
        $value = strtolower(trim((string) $actorId));
        if ($value === '') {
            return null;
        }

        if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $value) !== 1) {
            return null;
        }

        return $value;
    }

    /**
     * @param array<int, string> $eventKeys
     * @return array<string, string>
     */
    private function normalizedEventLookup(array $eventKeys): array
    {
        $lookup = [];
        foreach ($eventKeys as $eventKey) {
            $normalized = $this->normalizeComparableKey($eventKey);
            if ($normalized === '' || array_key_exists($normalized, $lookup)) {
                continue;
            }

            $lookup[$normalized] = $eventKey;
        }

        return $lookup;
    }

    /**
     * @param array<int, string> $keys
     * @return array<int, string>
     */
    private function expandEquivalentKeys(array $keys): array
    {
        $expanded = [];
        foreach ($keys as $key) {
            $expanded = array_merge($expanded, $this->equivalentKeyCandidates($key));
        }

        return array_values(array_unique(array_filter($expanded, static fn (string $value): bool => trim($value) !== '')));
    }

    /**
     * @param Collection<int, WhatsAppOfficialTemplateBinding> $bindings
     * @param array<string, string> $normalizedEventLookup
     * @return Collection<string, WhatsAppOfficialTemplateBinding>
     */
    private function mapBindingsByCanonicalEventKey(Collection $bindings, array $normalizedEventLookup): Collection
    {
        $mapped = collect();
        foreach ($bindings as $binding) {
            $canonicalEventKey = $this->resolveCanonicalEventKey((string) $binding->event_key, $normalizedEventLookup);
            if ($canonicalEventKey === null) {
                continue;
            }

            $current = $mapped->get($canonicalEventKey);
            if ($current === null) {
                $mapped->put($canonicalEventKey, $binding);
                continue;
            }

            $currentIsCanonical = trim((string) $current->event_key) === $canonicalEventKey;
            $candidateIsCanonical = trim((string) $binding->event_key) === $canonicalEventKey;
            if ($candidateIsCanonical && !$currentIsCanonical) {
                $mapped->put($canonicalEventKey, $binding);
                continue;
            }

            if ($candidateIsCanonical === $currentIsCanonical) {
                $candidateUpdatedAt = $binding->updated_at?->getTimestamp() ?? 0;
                $currentUpdatedAt = $current->updated_at?->getTimestamp() ?? 0;
                if ($candidateUpdatedAt >= $currentUpdatedAt) {
                    $mapped->put($canonicalEventKey, $binding);
                }
            }
        }

        return $mapped;
    }

    /**
     * @param Collection<int, WhatsAppOfficialTemplate> $templates
     * @param array<string, string> $normalizedEventLookup
     * @return Collection<string, Collection<int, WhatsAppOfficialTemplate>>
     */
    private function groupTemplatesByCanonicalEventKey(Collection $templates, array $normalizedEventLookup): Collection
    {
        $grouped = [];
        foreach ($templates as $template) {
            $canonicalEventKey = $this->resolveCanonicalEventKey((string) $template->key, $normalizedEventLookup);
            if ($canonicalEventKey === null) {
                continue;
            }

            $grouped[$canonicalEventKey] ??= collect();
            $grouped[$canonicalEventKey]->push($template);
        }

        return collect($grouped);
    }

    /**
     * @param array<string, string> $normalizedEventLookup
     */
    private function resolveCanonicalEventKey(string $key, array $normalizedEventLookup): ?string
    {
        $normalized = $this->normalizeComparableKey($key);
        if ($normalized === '') {
            return null;
        }

        return $normalizedEventLookup[$normalized] ?? null;
    }

    /**
     * @param Collection<int, WhatsAppOfficialTemplateBinding> $bindings
     */
    private function pickPreferredBinding(Collection $bindings, string $canonicalEventKey): ?WhatsAppOfficialTemplateBinding
    {
        if ($bindings->isEmpty()) {
            return null;
        }

        $direct = $bindings->first(
            static fn (WhatsAppOfficialTemplateBinding $binding): bool => trim((string) $binding->event_key) === $canonicalEventKey
        );
        if ($direct instanceof WhatsAppOfficialTemplateBinding) {
            return $direct;
        }

        return $bindings
            ->sortByDesc(static fn (WhatsAppOfficialTemplateBinding $binding): int => $binding->updated_at?->getTimestamp() ?? 0)
            ->first();
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
        return $this->normalizeComparableKey($left) !== ''
            && $this->normalizeComparableKey($left) === $this->normalizeComparableKey($right);
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
