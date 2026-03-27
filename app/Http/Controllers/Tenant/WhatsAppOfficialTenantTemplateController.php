<?php

namespace App\Http\Controllers\Tenant;

use App\Exceptions\WhatsAppMetaApiException;
use App\Exceptions\WhatsAppMetaConfigurationException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Platform\StoreWhatsAppOfficialTemplateRequest;
use App\Http\Requests\Platform\UpdateWhatsAppOfficialTemplateRequest;
use App\Models\Platform\WhatsAppOfficialTemplate;
use App\Models\Platform\WhatsAppOfficialTenantTemplate;
use App\Services\Platform\WhatsAppOfficialMessageService;
use App\Services\Platform\WhatsAppOfficialTemplateService;
use App\Services\Tenant\TenantWhatsAppConfigService;
use App\Support\WhatsAppOfficialTenantEventCatalog;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class WhatsAppOfficialTenantTemplateController extends Controller
{
    public function __construct(
        private readonly TenantWhatsAppConfigService $tenantWhatsAppConfigService
    ) {
    }

    public function index(Request $request): View
    {
        $tenantId = $this->currentTenantId();
        $this->tenantWhatsAppConfigService->applyRuntimeConfig();

        $query = WhatsAppOfficialTenantTemplate::query()
            ->forTenant($tenantId)
            ->whereIn('event_key', $this->tenantEventKeys())
            ->with('officialTemplate')
            ->whereHas('officialTemplate', function ($builder) use ($tenantId): void {
                $builder
                    ->officialProvider()
                    ->forTenant($tenantId);
            })
            ->orderBy('event_key')
            ->orderByDesc('updated_at');

        if ($request->filled('key')) {
            $query->where('event_key', 'like', '%' . trim((string) $request->input('key')) . '%');
        }

        if ($request->filled('status')) {
            $status = trim((string) $request->input('status'));
            $query->whereHas('officialTemplate', function ($builder) use ($status): void {
                $builder->where('status', $status);
            });
        }

        if ($request->filled('language')) {
            $language = trim((string) $request->input('language'));
            $query->where('language', $language);
        }

        return view('tenant.whatsapp_official_tenant_templates.index', [
            'mappings' => $query->paginate(20)->withQueryString(),
            'eventLabels' => $this->tenantEventLabels(),
            'statusOptions' => [
                WhatsAppOfficialTemplate::STATUS_DRAFT,
                WhatsAppOfficialTemplate::STATUS_PENDING,
                WhatsAppOfficialTemplate::STATUS_APPROVED,
                WhatsAppOfficialTemplate::STATUS_REJECTED,
                WhatsAppOfficialTemplate::STATUS_ARCHIVED,
            ],
            'languageOptions' => $this->languageOptions($tenantId),
            'filters' => [
                'key' => (string) $request->input('key', ''),
                'status' => (string) $request->input('status', ''),
                'language' => (string) $request->input('language', ''),
            ],
        ]);
    }

    public function create(): View
    {
        return view('tenant.whatsapp_official_tenant_templates.create', [
            'template' => new WhatsAppOfficialTemplate([
                'provider' => WhatsAppOfficialTemplate::PROVIDER,
                'category' => 'UTILITY',
                'language' => 'pt_BR',
                'status' => WhatsAppOfficialTemplate::STATUS_DRAFT,
                'variables' => [],
                'sample_variables' => [],
            ]),
            'eventLabels' => $this->tenantEventLabels(),
            'allowedKeys' => $this->tenantEventKeys(),
        ]);
    }

    public function store(StoreWhatsAppOfficialTemplateRequest $request): RedirectResponse
    {
        $tenantId = $this->currentTenantId();
        $this->tenantWhatsAppConfigService->applyRuntimeConfig();

        $validated = $request->validated();
        $validated['tenant_id'] = $tenantId;
        $validated['provider'] = WhatsAppOfficialTemplate::PROVIDER;
        $validated['category'] = 'UTILITY';
        $validated['language'] = 'pt_BR';
        $validated['meta_template_name'] = $this->canonicalTenantMetaTemplateName((string) ($validated['key'] ?? ''));
        $this->assertTenantBaselinePayload($validated);

        try {
            $template = $this->templateService()->createTemplate(
                $validated,
                (string) optional(auth()->user())->id
            );

            $this->upsertTenantMapping($tenantId, $template);

            return redirect()
                ->route('tenant.settings.whatsapp-official-tenant-templates.show', [
                    'slug' => tenant()->subdomain,
                    'whatsappOfficialTemplate' => $template->id,
                ])
                ->with('success', 'Template oficial criado com sucesso.');
        } catch (DomainException $e) {
            return back()->withInput()->withErrors(['template' => $e->getMessage()]);
        }
    }

    public function show(WhatsAppOfficialTemplate $whatsappOfficialTemplate): View
    {
        $tenantId = $this->currentTenantId();
        $this->assertTenantOfficialScope($whatsappOfficialTemplate, $tenantId);

        $versions = WhatsAppOfficialTemplate::query()
            ->officialProvider()
            ->forTenant($tenantId)
            ->where('key', $whatsappOfficialTemplate->key)
            ->orderByDesc('version')
            ->get();

        return view('tenant.whatsapp_official_tenant_templates.show', [
            'template' => $whatsappOfficialTemplate,
            'versions' => $versions,
            'eventLabels' => $this->tenantEventLabels(),
        ]);
    }

    public function edit(WhatsAppOfficialTemplate $whatsappOfficialTemplate): RedirectResponse|View
    {
        $tenantId = $this->currentTenantId();
        $this->assertTenantOfficialScope($whatsappOfficialTemplate, $tenantId);

        if (!$whatsappOfficialTemplate->isDirectlyEditable()) {
            $warningMessage = $whatsappOfficialTemplate->requiresVersioningForEdit()
                ? 'Template aprovado na Meta nao pode ser editado diretamente. Crie nova versao.'
                : 'Template com status atual nao permite edicao direta.';

            return redirect()
                ->route('tenant.settings.whatsapp-official-tenant-templates.show', [
                    'slug' => tenant()->subdomain,
                    'whatsappOfficialTemplate' => $whatsappOfficialTemplate->id,
                ])
                ->with('warning', $warningMessage);
        }

        return view('tenant.whatsapp_official_tenant_templates.edit', [
            'template' => $whatsappOfficialTemplate,
            'eventLabels' => $this->tenantEventLabels(),
            'allowedKeys' => $this->tenantEventKeys(),
        ]);
    }

    public function update(
        UpdateWhatsAppOfficialTemplateRequest $request,
        WhatsAppOfficialTemplate $whatsappOfficialTemplate
    ): RedirectResponse {
        $tenantId = $this->currentTenantId();
        $this->tenantWhatsAppConfigService->applyRuntimeConfig();
        $this->assertTenantOfficialScope($whatsappOfficialTemplate, $tenantId);

        $validated = $request->validated();
        $validated['tenant_id'] = $tenantId;
        $validated['provider'] = WhatsAppOfficialTemplate::PROVIDER;
        $validated['category'] = 'UTILITY';
        $validated['language'] = 'pt_BR';
        $validated['meta_template_name'] = $this->canonicalTenantMetaTemplateName((string) ($validated['key'] ?? ''));
        $this->assertTenantBaselinePayload($validated);

        try {
            $updated = $this->templateService()->updateTemplate(
                $whatsappOfficialTemplate,
                $validated,
                (string) optional(auth()->user())->id
            );

            $targetTemplate = $updated->id !== $whatsappOfficialTemplate->id ? $updated : $whatsappOfficialTemplate;
            $this->upsertTenantMapping($tenantId, $targetTemplate);

            return redirect()
                ->route('tenant.settings.whatsapp-official-tenant-templates.show', [
                    'slug' => tenant()->subdomain,
                    'whatsappOfficialTemplate' => $targetTemplate->id,
                ])
                ->with('success', 'Template oficial atualizado com sucesso.');
        } catch (DomainException $e) {
            return back()->withInput()->withErrors(['template' => $e->getMessage()]);
        }
    }

    public function submitToMeta(WhatsAppOfficialTemplate $whatsappOfficialTemplate): RedirectResponse
    {
        $tenantId = $this->currentTenantId();
        $this->tenantWhatsAppConfigService->applyRuntimeConfig();
        $this->assertTenantOfficialScope($whatsappOfficialTemplate, $tenantId);

        try {
            $this->templateService()->submitToMeta(
                $whatsappOfficialTemplate,
                (string) optional(auth()->user())->id
            );

            return back()->with('success', 'Template enviado para Meta com sucesso.');
        } catch (WhatsAppMetaConfigurationException|DomainException $e) {
            return back()->withErrors(['template' => $e->getMessage()]);
        } catch (WhatsAppMetaApiException $e) {
            Log::warning('tenant_official_template_submit_meta_api_error', [
                'tenant_id' => $tenantId,
                'template_id' => (string) $whatsappOfficialTemplate->id,
                'http_status' => $e->httpStatus(),
                'meta_error' => $e->metaError(),
                'response_summary' => $e->responseSummary(),
            ]);

            return back()->withErrors([
                'template' => 'Erro de API Meta ao enviar template: ' . $e->userSafeMessage(),
            ]);
        } catch (\Throwable $e) {
            Log::error('tenant_official_template_submit_error', [
                'tenant_id' => $tenantId,
                'template_id' => (string) $whatsappOfficialTemplate->id,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors(['template' => 'Falha ao enviar template para a Meta.']);
        }
    }

    public function republishToMeta(WhatsAppOfficialTemplate $whatsappOfficialTemplate): RedirectResponse
    {
        $tenantId = $this->currentTenantId();
        $this->tenantWhatsAppConfigService->applyRuntimeConfig();
        $this->assertTenantOfficialScope($whatsappOfficialTemplate, $tenantId);

        $lockKey = 'tenant_wa_official_template_republish:' . $tenantId . ':' . (string) $whatsappOfficialTemplate->id;
        $lock = Cache::lock($lockKey, 20);
        if (!$lock->get()) {
            return back()->with('warning', 'Ja existe uma publicacao em andamento para este template.');
        }

        try {
            $republishedTemplate = $this->templateService()->republishAsNewTemplate(
                $whatsappOfficialTemplate,
                (string) optional(auth()->user())->id
            );

            $this->upsertTenantMapping($tenantId, $republishedTemplate);

            return redirect()
                ->route('tenant.settings.whatsapp-official-tenant-templates.show', [
                    'slug' => tenant()->subdomain,
                    'whatsappOfficialTemplate' => $republishedTemplate->id,
                ])
                ->with('success', 'Template republicado na Meta com sucesso.');
        } catch (WhatsAppMetaConfigurationException|DomainException $e) {
            return back()->withErrors(['template' => $e->getMessage()]);
        } catch (WhatsAppMetaApiException $e) {
            Log::warning('tenant_official_template_republish_meta_api_error', [
                'tenant_id' => $tenantId,
                'template_id' => (string) $whatsappOfficialTemplate->id,
                'http_status' => $e->httpStatus(),
                'meta_error' => $e->metaError(),
                'response_summary' => $e->responseSummary(),
            ]);

            return back()->withErrors([
                'template' => 'Erro de API Meta ao republicar template: ' . $e->userSafeMessage(),
            ]);
        } catch (\Throwable $e) {
            Log::error('tenant_official_template_republish_error', [
                'tenant_id' => $tenantId,
                'template_id' => (string) $whatsappOfficialTemplate->id,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors(['template' => 'Falha ao republicar template na Meta.']);
        } finally {
            $lock->release();
        }
    }

    public function syncStatus(WhatsAppOfficialTemplate $whatsappOfficialTemplate): RedirectResponse
    {
        $tenantId = $this->currentTenantId();
        $this->tenantWhatsAppConfigService->applyRuntimeConfig();
        $this->assertTenantOfficialScope($whatsappOfficialTemplate, $tenantId);

        try {
            $this->templateService()->syncStatus(
                $whatsappOfficialTemplate,
                (string) optional(auth()->user())->id
            );

            return back()->with('success', 'Status sincronizado com a Meta com sucesso.');
        } catch (WhatsAppMetaConfigurationException|DomainException $e) {
            return back()->withErrors(['template' => $e->getMessage()]);
        } catch (WhatsAppMetaApiException $e) {
            Log::warning('tenant_official_template_sync_meta_api_error', [
                'tenant_id' => $tenantId,
                'template_id' => (string) $whatsappOfficialTemplate->id,
                'http_status' => $e->httpStatus(),
                'meta_error' => $e->metaError(),
                'response_summary' => $e->responseSummary(),
            ]);

            return back()->withErrors([
                'template' => 'Erro de API Meta ao sincronizar status: ' . $e->userSafeMessage(),
            ]);
        } catch (\Throwable $e) {
            Log::error('tenant_official_template_sync_error', [
                'tenant_id' => $tenantId,
                'template_id' => (string) $whatsappOfficialTemplate->id,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors(['template' => 'Falha ao sincronizar status do template.']);
        }
    }

    public function testSend(Request $request, WhatsAppOfficialTemplate $whatsappOfficialTemplate): JsonResponse
    {
        $tenantId = $this->currentTenantId();
        $this->tenantWhatsAppConfigService->applyRuntimeConfig();
        $this->assertTenantOfficialScope($whatsappOfficialTemplate, $tenantId);

        $validator = Validator::make($request->all(), [
            'phone' => ['required', 'string', 'min:10', 'max:25', 'regex:/^[0-9+\-\s\(\)]+$/'],
            'variables' => ['nullable', 'array'],
            'variables.*' => ['nullable', 'string', 'max:500'],
        ], [
            'phone.required' => 'Informe o numero de destino.',
            'phone.regex' => 'Numero de destino invalido.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Falha de validacao no teste manual.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $payload = $validator->validated();
            $result = $this->officialMessageService()->sendManualTest(
                $whatsappOfficialTemplate,
                (string) ($payload['phone'] ?? ''),
                (array) ($payload['variables'] ?? []),
                [
                    'service' => static::class,
                    'event' => 'manual_test_tenant_template',
                    'actor_id' => (string) optional(auth()->user())->id,
                    'tenant_id' => $tenantId,
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Teste de template enviado com sucesso.',
                'http_status' => $result['http_status'] ?? null,
            ]);
        } catch (DomainException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (WhatsAppMetaApiException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->userSafeMessage(),
                'http_status' => $e->httpStatus(),
                'meta_error' => $e->metaError(),
            ], 422);
        } catch (WhatsAppMetaConfigurationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('tenant_official_template_manual_test_error', [
                'tenant_id' => $tenantId,
                'template_id' => (string) $whatsappOfficialTemplate->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro interno ao executar teste de template.',
            ], 500);
        }
    }

    private function currentTenantId(): string
    {
        $tenant = tenant();
        if (!$tenant || trim((string) $tenant->id) === '') {
            abort(404);
        }

        return trim((string) $tenant->id);
    }

    private function assertTenantOfficialScope(WhatsAppOfficialTemplate $template, string $tenantId): void
    {
        if (
            (string) $template->provider !== WhatsAppOfficialTemplate::PROVIDER
            || !in_array((string) $template->key, $this->tenantEventKeys(), true)
            || (string) $template->tenant_id !== $tenantId
        ) {
            abort(404);
        }
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function assertTenantBaselinePayload(array $payload): void
    {
        $key = trim((string) ($payload['key'] ?? ''));
        if (!in_array($key, $this->tenantEventKeys(), true)) {
            throw new DomainException('A key informada nao pertence ao baseline oficial tenant.');
        }

        $provider = trim((string) ($payload['provider'] ?? ''));
        if ($provider !== WhatsAppOfficialTemplate::PROVIDER) {
            throw new DomainException('Provider invalido para baseline oficial tenant.');
        }

        $category = strtoupper(trim((string) ($payload['category'] ?? '')));
        if ($category !== 'UTILITY') {
            throw new DomainException('No baseline oficial tenant, a categoria deve ser UTILITY.');
        }

        $language = trim((string) ($payload['language'] ?? ''));
        if ($language !== 'pt_BR') {
            throw new DomainException('No baseline oficial tenant, o idioma padrao deve ser pt_BR.');
        }

        $payloadTenantId = trim((string) ($payload['tenant_id'] ?? ''));
        if ($payloadTenantId === '') {
            throw new DomainException('Escopo tenant obrigatorio para templates oficiais do tenant.');
        }
    }

    /**
     * @return array<int, string>
     */
    private function tenantEventKeys(): array
    {
        $events = WhatsAppOfficialTenantEventCatalog::groupedByDomain()['tenant'] ?? [];

        return array_values(array_map(
            static fn (array $event): string => (string) ($event['key'] ?? ''),
            array_filter($events, static fn (array $event): bool => trim((string) ($event['key'] ?? '')) !== '')
        ));
    }

    /**
     * @return array<string, string>
     */
    private function tenantEventLabels(): array
    {
        $labels = [];
        $events = WhatsAppOfficialTenantEventCatalog::groupedByDomain()['tenant'] ?? [];
        foreach ($events as $event) {
            $key = (string) ($event['key'] ?? '');
            if ($key === '') {
                continue;
            }

            $labels[$key] = (string) ($event['label'] ?? $key);
        }

        return $labels;
    }

    /**
     * @return array<int, string>
     */
    private function languageOptions(string $tenantId): array
    {
        return WhatsAppOfficialTemplate::query()
            ->officialProvider()
            ->forTenant($tenantId)
            ->whereIn('key', $this->tenantEventKeys())
            ->select('language')
            ->distinct()
            ->orderBy('language')
            ->pluck('language')
            ->filter(fn (?string $value): bool => trim((string) $value) !== '')
            ->values()
            ->all();
    }

    private function canonicalTenantMetaTemplateName(string $key): string
    {
        $normalizedKey = strtolower(str_replace(['.', '-', ' '], '_', trim($key)));
        return 'tenant_' . $normalizedKey;
    }

    private function templateService(): WhatsAppOfficialTemplateService
    {
        return app(WhatsAppOfficialTemplateService::class);
    }

    private function officialMessageService(): WhatsAppOfficialMessageService
    {
        return app(WhatsAppOfficialMessageService::class);
    }

    private function upsertTenantMapping(string $tenantId, WhatsAppOfficialTemplate $template): void
    {
        $actorId = $this->normalizeActorId((string) optional(auth()->user())->id);

        $mapping = WhatsAppOfficialTenantTemplate::query()
            ->forTenant($tenantId)
            ->forEvent((string) $template->key)
            ->where('language', (string) $template->language)
            ->first();

        if (!$mapping) {
            $mapping = new WhatsAppOfficialTenantTemplate([
                'tenant_id' => $tenantId,
                'event_key' => (string) $template->key,
                'language' => (string) $template->language,
            ]);
            if ($actorId !== null) {
                $mapping->created_by = $actorId;
            }
        }

        $mapping->whatsapp_official_template_id = (string) $template->id;
        $mapping->is_active = true;
        if ($actorId !== null) {
            $mapping->updated_by = $actorId;
        }
        $mapping->save();
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
}

