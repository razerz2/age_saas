<?php

namespace App\Http\Controllers\Platform;

use App\Exceptions\WhatsAppMetaApiException;
use App\Exceptions\WhatsAppMetaConfigurationException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Platform\StoreWhatsAppOfficialTemplateRequest;
use App\Http\Requests\Platform\UpdateWhatsAppOfficialTemplateRequest;
use App\Models\Platform\WhatsAppOfficialTemplate;
use App\Models\Platform\WhatsAppOfficialTenantTemplate;
use App\Services\Platform\WhatsAppOfficialMessageService;
use App\Services\Platform\WhatsAppOfficialTemplateService;
use App\Services\WhatsApp\MetaCloudTemplateApiService;
use App\Support\WhatsAppOfficialTenantEventCatalog;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class WhatsAppOfficialTenantTemplateController extends Controller
{
    public function __construct(
        private readonly WhatsAppOfficialTemplateService $templateService,
        private readonly WhatsAppOfficialMessageService $officialMessageService,
        private readonly MetaCloudTemplateApiService $metaTemplateApiService
    ) {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', WhatsAppOfficialTenantTemplate::class);

        $query = WhatsAppOfficialTemplate::query()
            ->officialProvider()
            ->forPlatformBaseline()
            ->whereIn('key', $this->tenantEventKeys())
            ->orderBy('key')
            ->orderByDesc('version');

        if ($request->filled('key')) {
            $query->where('key', 'like', '%' . (string) $request->input('key') . '%');
        }

        if ($request->filled('status')) {
            $query->where('status', (string) $request->input('status'));
        }

        if ($request->filled('language')) {
            $query->where('language', (string) $request->input('language'));
        }

        return view('platform.whatsapp_official_tenant_templates.index', [
            'templates' => $query->paginate(20)->withQueryString(),
            'filters' => [
                'key' => (string) $request->input('key', ''),
                'status' => (string) $request->input('status', ''),
                'language' => (string) $request->input('language', ''),
            ],
            'eventLabels' => $this->tenantEventLabels(),
            'statusOptions' => [
                WhatsAppOfficialTemplate::STATUS_DRAFT,
                WhatsAppOfficialTemplate::STATUS_PENDING,
                WhatsAppOfficialTemplate::STATUS_APPROVED,
                WhatsAppOfficialTemplate::STATUS_REJECTED,
                WhatsAppOfficialTemplate::STATUS_ARCHIVED,
            ],
            'languageOptions' => $this->languageOptions(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', WhatsAppOfficialTenantTemplate::class);

        return view('platform.whatsapp_official_tenant_templates.create', [
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
        $this->authorize('create', WhatsAppOfficialTenantTemplate::class);

        $validated = $request->validated();
        $validated['meta_template_name'] = $this->canonicalTenantMetaTemplateName((string) ($validated['key'] ?? ''));
        $this->assertTenantBaselinePayload($validated);

        try {
            $template = $this->templateService->createTemplate(
                $validated,
                (string) optional(auth()->user())->id
            );

            return redirect()
                ->route('Platform.whatsapp-official-tenant-templates.show', $template)
                ->with('success', 'Template oficial tenant criado com sucesso.');
        } catch (DomainException $e) {
            return back()->withInput()->withErrors(['template' => $e->getMessage()]);
        }
    }

    public function show(WhatsAppOfficialTemplate $whatsappOfficialTemplate): View
    {
        $this->authorize('viewAny', WhatsAppOfficialTenantTemplate::class);
        $this->assertTenantOfficialScope($whatsappOfficialTemplate);

        $versions = WhatsAppOfficialTemplate::query()
            ->officialProvider()
            ->forPlatformBaseline()
            ->where('key', $whatsappOfficialTemplate->key)
            ->orderByDesc('version')
            ->get();

        return view('platform.whatsapp_official_tenant_templates.show', [
            'template' => $whatsappOfficialTemplate,
            'versions' => $versions,
            'eventLabels' => $this->tenantEventLabels(),
        ]);
    }

    public function edit(WhatsAppOfficialTemplate $whatsappOfficialTemplate): RedirectResponse|View
    {
        $this->authorize('update', WhatsAppOfficialTenantTemplate::class);
        $this->assertTenantOfficialScope($whatsappOfficialTemplate);

        if (!$whatsappOfficialTemplate->isDirectlyEditable()) {
            $warningMessage = $whatsappOfficialTemplate->requiresVersioningForEdit()
                ? 'Template aprovado na Meta não pode ser editado diretamente. Use nova versão.'
                : 'Template com status "' . $whatsappOfficialTemplate->status . '" não permite edição direta.';

            return redirect()
                ->route('Platform.whatsapp-official-tenant-templates.show', $whatsappOfficialTemplate)
                ->with('warning', $warningMessage);
        }

        return view('platform.whatsapp_official_tenant_templates.edit', [
            'template' => $whatsappOfficialTemplate,
            'eventLabels' => $this->tenantEventLabels(),
            'allowedKeys' => $this->tenantEventKeys(),
        ]);
    }

    public function update(
        UpdateWhatsAppOfficialTemplateRequest $request,
        WhatsAppOfficialTemplate $whatsappOfficialTemplate
    ): RedirectResponse {
        $this->authorize('update', WhatsAppOfficialTenantTemplate::class);
        $this->assertTenantOfficialScope($whatsappOfficialTemplate);

        $validated = $request->validated();
        $validated['meta_template_name'] = $this->canonicalTenantMetaTemplateName((string) ($validated['key'] ?? ''));
        $this->assertTenantBaselinePayload($validated);

        try {
            $updated = $this->templateService->updateTemplate(
                $whatsappOfficialTemplate,
                $validated,
                (string) optional(auth()->user())->id
            );

            $routeTarget = $updated->id !== $whatsappOfficialTemplate->id ? $updated : $whatsappOfficialTemplate;

            return redirect()
                ->route('Platform.whatsapp-official-tenant-templates.show', $routeTarget)
                ->with('success', 'Template oficial tenant atualizado com sucesso.');
        } catch (DomainException $e) {
            return back()->withInput()->withErrors(['template' => $e->getMessage()]);
        }
    }

    public function syncStatus(WhatsAppOfficialTemplate $whatsappOfficialTemplate): RedirectResponse
    {
        $this->authorize('syncStatus', WhatsAppOfficialTenantTemplate::class);
        $this->assertTenantOfficialScope($whatsappOfficialTemplate);
        $whatsappOfficialTemplate = $this->ensureCanonicalTenantMetaTemplateName($whatsappOfficialTemplate);

        try {
            $syncedTemplate = $this->syncStatusWithTenantNameFallback($whatsappOfficialTemplate);
            $remoteTemplate = $this->findRemoteTemplateSnapshot($syncedTemplate);

            if ($remoteTemplate === null) {
                return redirect()
                    ->route('Platform.whatsapp-official-tenant-templates.show', $whatsappOfficialTemplate)
                    ->with('warning', 'Sincronizacao concluida, mas nenhum template remoto foi localizado para os nomes consultados.');
            }

            return redirect()
                ->route('Platform.whatsapp-official-tenant-templates.show', $whatsappOfficialTemplate)
                ->with('success', 'Status sincronizado com a Meta com sucesso.');
        } catch (WhatsAppMetaConfigurationException|DomainException $e) {
            return back()->withErrors(['template' => $e->getMessage()]);
        } catch (WhatsAppMetaApiException $e) {
            Log::warning('wa_official_tenant_template_sync_meta_api_error', [
                'template_id' => (string) $whatsappOfficialTemplate->id,
                'key' => (string) $whatsappOfficialTemplate->key,
                'meta_template_name' => (string) $whatsappOfficialTemplate->meta_template_name,
                'http_status' => $e->httpStatus(),
                'meta_error' => $e->metaError(),
                'response_summary' => $e->responseSummary(),
            ]);

            return back()->withErrors([
                'template' => 'Erro de API Meta ao sincronizar status: ' . $e->userSafeMessage(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Erro ao sincronizar status de template oficial tenant', [
                'template_id' => (string) $whatsappOfficialTemplate->id,
                'key' => (string) $whatsappOfficialTemplate->key,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors([
                'template' => 'Falha ao sincronizar status: ' . $e->getMessage(),
            ]);
        }
    }

    public function submitToMeta(WhatsAppOfficialTemplate $whatsappOfficialTemplate): RedirectResponse
    {
        $this->authorize('submitToMeta', WhatsAppOfficialTenantTemplate::class);
        $this->assertTenantOfficialScope($whatsappOfficialTemplate);
        $whatsappOfficialTemplate = $this->ensureCanonicalTenantMetaTemplateName($whatsappOfficialTemplate);

        try {
            $this->templateService->submitToMeta(
                $whatsappOfficialTemplate,
                (string) optional(auth()->user())->id
            );

            return redirect()
                ->route('Platform.whatsapp-official-tenant-templates.show', $whatsappOfficialTemplate)
                ->with('success', 'Template oficial tenant enviado para a Meta com sucesso.');
        } catch (WhatsAppMetaConfigurationException|DomainException $e) {
            return back()->withErrors(['template' => $e->getMessage()]);
        } catch (WhatsAppMetaApiException $e) {
            Log::warning('wa_official_tenant_template_submit_meta_api_error', [
                'template_id' => (string) $whatsappOfficialTemplate->id,
                'key' => (string) $whatsappOfficialTemplate->key,
                'meta_template_name' => (string) $whatsappOfficialTemplate->meta_template_name,
                'http_status' => $e->httpStatus(),
                'meta_error' => $e->metaError(),
                'response_summary' => $e->responseSummary(),
            ]);

            return back()->withErrors([
                'template' => 'Erro de API Meta ao enviar template: ' . $e->userSafeMessage(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Erro ao enviar template oficial tenant para Meta', [
                'template_id' => (string) $whatsappOfficialTemplate->id,
                'key' => (string) $whatsappOfficialTemplate->key,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors([
                'template' => 'Falha ao enviar template para a Meta: ' . $e->getMessage(),
            ]);
        }
    }

    public function republishToMeta(WhatsAppOfficialTemplate $whatsappOfficialTemplate): RedirectResponse
    {
        $this->authorize('submitToMeta', WhatsAppOfficialTenantTemplate::class);
        $this->assertTenantOfficialScope($whatsappOfficialTemplate);
        $whatsappOfficialTemplate = $this->ensureCanonicalTenantMetaTemplateName($whatsappOfficialTemplate);

        $lockKey = 'wa_official_tenant_template_republish:' . (string) $whatsappOfficialTemplate->id;
        $lock = Cache::lock($lockKey, 20);
        if (!$lock->get()) {
            return redirect()
                ->route('Platform.whatsapp-official-tenant-templates.show', $whatsappOfficialTemplate)
                ->with('warning', 'Ja existe uma publicacao em andamento para este template. Aguarde alguns segundos e tente novamente.');
        }

        try {
            $template = WhatsAppOfficialTemplate::query()->findOrFail($whatsappOfficialTemplate->id);
            $template = $this->ensureCanonicalTenantMetaTemplateName($template);
            $syncedTemplate = $this->syncStatusWithTenantNameFallback($template);
            $remoteTemplate = $this->findRemoteTemplateSnapshot($syncedTemplate);

            if ($remoteTemplate !== null) {
                $remoteStatus = strtoupper(trim((string) ($remoteTemplate['status'] ?? 'PENDING')));

                return redirect()
                    ->route('Platform.whatsapp-official-tenant-templates.show', $syncedTemplate)
                    ->with('warning', 'O template remoto ainda existe na Meta (status ' . $remoteStatus . '). Use a sincronizacao normal.');
            }

            $republishedTemplate = $this->templateService->republishAsNewTemplate(
                $syncedTemplate,
                (string) optional(auth()->user())->id
            );

            return redirect()
                ->route('Platform.whatsapp-official-tenant-templates.show', $republishedTemplate)
                ->with('success', 'Template oficial tenant publicado novamente na Meta com sucesso.');
        } catch (WhatsAppMetaConfigurationException|DomainException $e) {
            return back()->withErrors(['template' => $e->getMessage()]);
        } catch (WhatsAppMetaApiException $e) {
            Log::warning('wa_official_tenant_template_republish_meta_api_error', [
                'template_id' => (string) $whatsappOfficialTemplate->id,
                'key' => (string) $whatsappOfficialTemplate->key,
                'meta_template_name' => (string) $whatsappOfficialTemplate->meta_template_name,
                'http_status' => $e->httpStatus(),
                'meta_error' => $e->metaError(),
                'response_summary' => $e->responseSummary(),
            ]);

            if ($this->isMetaNameConflict($e)) {
                return back()->withErrors([
                    'template' => 'Conflito de nome na Meta: ja existe template com este nome/idioma. Ajuste o Nome Meta e tente novamente.',
                ]);
            }

            return back()->withErrors([
                'template' => 'Erro de API Meta ao publicar novamente: ' . $e->userSafeMessage(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Erro ao republicar template oficial tenant na Meta', [
                'template_id' => (string) $whatsappOfficialTemplate->id,
                'key' => (string) $whatsappOfficialTemplate->key,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors([
                'template' => 'Falha ao publicar novamente na Meta: ' . $e->getMessage(),
            ]);
        } finally {
            $lock->release();
        }
    }

    public function testSend(Request $request, WhatsAppOfficialTemplate $whatsappOfficialTemplate): JsonResponse
    {
        $this->authorize('testSend', WhatsAppOfficialTenantTemplate::class);
        $this->assertTenantOfficialScope($whatsappOfficialTemplate);
        $whatsappOfficialTemplate = $this->ensureCanonicalTenantMetaTemplateName($whatsappOfficialTemplate);

        $validator = Validator::make($request->all(), [
            'phone' => ['required', 'string', 'min:10', 'max:25', 'regex:/^[0-9+\-\s\(\)]+$/'],
            'variables' => ['nullable', 'array'],
            'variables.*' => ['nullable', 'string', 'max:500'],
        ], [
            'phone.required' => 'Informe o numero de destino.',
            'phone.regex' => 'Numero de destino invalido.',
            'variables.array' => 'Formato de variaveis invalido para o teste.',
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
            $syncedTemplate = $this->syncAndAssertTemplateReadyForManualTest($whatsappOfficialTemplate);
            $remoteTemplate = $this->findRemoteTemplateSnapshot($syncedTemplate);
            $templateForSend = $this->prepareTemplateForManualTestUsingRemoteSchema($syncedTemplate, $remoteTemplate);

            $result = $this->officialMessageService->sendManualTest(
                $templateForSend,
                (string) ($payload['phone'] ?? ''),
                (array) ($payload['variables'] ?? []),
                [
                    'service' => static::class,
                    'event' => 'manual_test_tenant_baseline',
                    'actor_id' => (string) optional(auth()->user())->id,
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
            Log::error('Erro no teste manual de template oficial tenant', [
                'template_id' => (string) $whatsappOfficialTemplate->id,
                'key' => (string) $whatsappOfficialTemplate->key,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro interno ao executar teste de template.',
            ], 500);
        }
    }

    private function syncAndAssertTemplateReadyForManualTest(
        WhatsAppOfficialTemplate $template
    ): WhatsAppOfficialTemplate {
        $syncedTemplate = $this->syncStatusWithTenantNameFallback($template);

        $remoteTemplate = $this->findRemoteTemplateSnapshot($syncedTemplate);
        if ($remoteTemplate === null) {
            throw new DomainException(
                'Template nao foi localizado na Meta para os nomes consultados '
                . '(' . $syncedTemplate->meta_template_name . ', idioma ' . $syncedTemplate->language . '). '
                . 'Sincronize novamente e confirme o nome canonicamente aprovado na Meta antes de testar.'
            );
        }

        $remoteStatus = strtoupper(trim((string) ($remoteTemplate['status'] ?? 'PENDING')));
        if ($remoteStatus !== 'APPROVED') {
            throw new DomainException(
                'Template encontrado na Meta com status '
                . $remoteStatus
                . '. Aguarde APPROVED para liberar teste manual.'
            );
        }

        if ($syncedTemplate->status !== WhatsAppOfficialTemplate::STATUS_APPROVED) {
            throw new DomainException(
                'Template local nao esta apto para teste apos sincronizacao. '
                . 'Sincronize novamente e confirme status APPROVED.'
            );
        }

        return $syncedTemplate;
    }

    private function prepareTemplateForManualTestUsingRemoteSchema(
        WhatsAppOfficialTemplate $template,
        ?array $remoteTemplate
    ): WhatsAppOfficialTemplate {
        if (!is_array($remoteTemplate)) {
            return $template;
        }

        $category = strtoupper(trim((string) $template->category));
        if (in_array($category, ['SECURITY', 'AUTHENTICATION'], true)) {
            // AUTHENTICATION ja usa schema remoto no service oficial.
            return $template;
        }

        $alignedVariableMap = $this->resolveAlignedUtilityVariableMap($template, $remoteTemplate);
        if ($alignedVariableMap === null) {
            return $template;
        }

        $templateForSend = clone $template;
        $templateForSend->setAttribute('variables', $alignedVariableMap);

        return $templateForSend;
    }

    /**
     * @return array<string, string>|null
     */
    private function resolveAlignedUtilityVariableMap(
        WhatsAppOfficialTemplate $template,
        array $remoteTemplate
    ): ?array {
        $remotePlaceholders = $this->extractRemoteBodyPlaceholderOrder($remoteTemplate);
        if ($remotePlaceholders === []) {
            return null;
        }

        $localVariableMap = $this->normalizeVariableMap((array) $template->variables);
        if ($localVariableMap === []) {
            throw new DomainException('Template sem mapeamento de variaveis local para montar parametros do body remoto.');
        }

        $localNamesByOrder = array_values(array_unique(array_values($localVariableMap)));
        $semanticSlots = $this->tenantSemanticVariableCandidatesByKey((string) $template->key);
        $remoteBodyText = $this->extractRemoteBodyText($remoteTemplate);
        $aligned = [];
        $usedVariableNames = [];
        foreach ($remotePlaceholders as $placeholder) {
            $slotIndex = count($aligned);
            $variableName = $this->resolveSemanticVariableNameForRemoteSlot(
                $slotIndex,
                $placeholder,
                $template,
                $remoteBodyText,
                $semanticSlots,
                $localVariableMap,
                $localNamesByOrder,
                $usedVariableNames
            );

            if ($variableName === null || trim($variableName) === '') {
                throw new DomainException(
                    'Nao foi possivel mapear variavel local para placeholder remoto {{' . $placeholder . '}}.'
                );
            }

            $usedVariableNames[] = $variableName;
            $aligned[(string) (count($aligned) + 1)] = trim($variableName);
        }

        return $aligned;
    }

    /**
     * @param array<int, array<int, string>> $semanticSlots
     * @param array<string, string> $localVariableMap
     * @param array<int, string> $localNamesByOrder
     * @param array<int, string> $usedVariableNames
     */
    private function resolveSemanticVariableNameForRemoteSlot(
        int $slotIndex,
        string $remotePlaceholder,
        WhatsAppOfficialTemplate $template,
        string $remoteBodyText,
        array $semanticSlots,
        array $localVariableMap,
        array $localNamesByOrder,
        array $usedVariableNames
    ): ?string {
        $availableByName = array_values(array_filter(
            $localNamesByOrder,
            static fn (string $name): bool => trim($name) !== ''
        ));

        // 1) Regra semantica explicita por key/slot.
        foreach ((array) ($semanticSlots[$slotIndex] ?? []) as $candidateName) {
            $normalizedCandidate = trim((string) $candidateName);
            if (
                $normalizedCandidate !== ''
                && in_array($normalizedCandidate, $availableByName, true)
                && !in_array($normalizedCandidate, $usedVariableNames, true)
            ) {
                return $normalizedCandidate;
            }
        }

        // 2) Inferencia por contexto do template remoto.
        $contextInferred = $this->inferVariableNameFromRemoteContext(
            $remoteBodyText,
            $remotePlaceholder,
            $availableByName,
            $usedVariableNames
        );
        if ($contextInferred !== null) {
            return $contextInferred;
        }

        // 3) Match por mesmo indice numerico local.
        $candidateByLocalPlaceholder = trim((string) ($localVariableMap[$remotePlaceholder] ?? ''));
        if (
            $candidateByLocalPlaceholder !== ''
            && !in_array($candidateByLocalPlaceholder, $usedVariableNames, true)
        ) {
            return $candidateByLocalPlaceholder;
        }

        // 4) Fallback por ordem local.
        foreach ($availableByName as $candidateName) {
            if (!in_array($candidateName, $usedVariableNames, true)) {
                return $candidateName;
            }
        }

        return null;
    }

    private function syncStatusWithTenantNameFallback(WhatsAppOfficialTemplate $template): WhatsAppOfficialTemplate
    {
        $actorId = (string) optional(auth()->user())->id;
        $syncedTemplate = $this->templateService->syncStatus($template, $actorId);
        $canonicalName = $this->canonicalTenantMetaTemplateName((string) $syncedTemplate->key);

        if ($this->findRemoteTemplateSnapshot($syncedTemplate) !== null) {
            return $syncedTemplate;
        }

        $language = trim((string) $syncedTemplate->language);
        foreach ($this->tenantMetaTemplateNameCandidates($syncedTemplate) as $candidateName) {
            if (strcasecmp($candidateName, (string) $syncedTemplate->meta_template_name) === 0) {
                continue;
            }

            if (strcasecmp($candidateName, $canonicalName) !== 0) {
                continue;
            }

            $response = $this->metaTemplateApiService->fetchTemplateByNameAndLanguage($candidateName, $language);
            $remoteTemplate = $this->findRemoteTemplateByNameAndLanguage(
                (array) ($response['data'] ?? []),
                $candidateName,
                $language
            );

            if ($remoteTemplate === null) {
                continue;
            }

            $resolvedRemoteName = strtolower(trim((string) ($remoteTemplate['name'] ?? $candidateName)));
            if (strcasecmp($resolvedRemoteName, $canonicalName) !== 0) {
                Log::warning('wa_official_tenant_template_sync_non_canonical_remote_name_ignored', [
                    'template_id' => (string) $syncedTemplate->id,
                    'key' => (string) $syncedTemplate->key,
                    'expected_meta_template_name' => $canonicalName,
                    'remote_name' => $resolvedRemoteName,
                ]);
                continue;
            }

            $remoteStatus = strtoupper(trim((string) ($remoteTemplate['status'] ?? 'PENDING')));

            $syncedTemplate->meta_template_name = $canonicalName;
            $syncedTemplate->meta_template_id = (string) ($remoteTemplate['id'] ?? $syncedTemplate->meta_template_id);
            $syncedTemplate->meta_waba_id = $this->metaTemplateApiService->getWabaId();
            $syncedTemplate->meta_response = $response;
            $syncedTemplate->status = $this->mapMetaStatusToLocal($remoteStatus);
            $syncedTemplate->last_synced_at = Carbon::now();
            $syncedTemplate->save();

            Log::info('wa_official_tenant_template_sync_name_fallback_resolved', [
                'template_id' => (string) $syncedTemplate->id,
                'key' => (string) $syncedTemplate->key,
                'old_meta_template_name' => (string) $template->meta_template_name,
                'resolved_meta_template_name' => (string) $syncedTemplate->meta_template_name,
                'remote_status' => $remoteStatus,
            ]);

            return $syncedTemplate;
        }

        return $syncedTemplate;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function assertTenantBaselinePayload(array $payload): void
    {
        $key = trim((string) ($payload['key'] ?? ''));
        if (!in_array($key, $this->tenantEventKeys(), true)) {
            throw new DomainException('A key informada não pertence ao baseline oficial tenant.');
        }

        $provider = trim((string) ($payload['provider'] ?? ''));
        if ($provider !== WhatsAppOfficialTemplate::PROVIDER) {
            throw new DomainException('Provider inválido para baseline oficial tenant.');
        }

        $category = strtoupper(trim((string) ($payload['category'] ?? '')));
        if ($category !== 'UTILITY') {
            throw new DomainException('No baseline oficial tenant, a categoria deve ser UTILITY.');
        }

        $language = trim((string) ($payload['language'] ?? ''));
        if ($language !== 'pt_BR') {
            throw new DomainException('No baseline oficial tenant, o idioma padrão deve ser pt_BR.');
        }
    }

    private function assertTenantOfficialScope(WhatsAppOfficialTemplate $template): void
    {
        if (
            (string) $template->provider !== WhatsAppOfficialTemplate::PROVIDER
            || !in_array((string) $template->key, $this->tenantEventKeys(), true)
            || $template->tenant_id !== null
        ) {
            abort(404);
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
    private function languageOptions(): array
    {
        return WhatsAppOfficialTemplate::query()
            ->officialProvider()
            ->forPlatformBaseline()
            ->whereIn('key', $this->tenantEventKeys())
            ->select('language')
            ->distinct()
            ->orderBy('language')
            ->pluck('language')
            ->filter(fn (?string $value): bool => trim((string) $value) !== '')
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>|null
     */
    private function findRemoteTemplateSnapshot(WhatsAppOfficialTemplate $template): ?array
    {
        $response = $template->meta_response;
        if (!is_array($response)) {
            return null;
        }

        $rows = (array) ($response['data'] ?? []);
        if ($rows === []) {
            return null;
        }

        $name = strtolower(trim((string) $template->meta_template_name));
        $language = strtolower(trim((string) $template->language));

        return $this->findRemoteTemplateByNameAndLanguage($rows, $name, $language);
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     * @return array<string, mixed>|null
     */
    private function findRemoteTemplateByNameAndLanguage(array $rows, string $name, string $language): ?array
    {
        $targetName = strtolower(trim($name));
        $targetLanguage = strtolower(trim($language));

        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $rowName = strtolower(trim((string) ($row['name'] ?? '')));
            $rowLanguage = strtolower(trim((string) ($row['language'] ?? $row['locale'] ?? '')));
            if ($rowName === $targetName && $rowLanguage === $targetLanguage) {
                return $row;
            }
        }

        return null;
    }

    private function ensureCanonicalTenantMetaTemplateName(WhatsAppOfficialTemplate $template): WhatsAppOfficialTemplate
    {
        $canonicalName = $this->canonicalTenantMetaTemplateName((string) $template->key);
        $currentName = strtolower(trim((string) $template->meta_template_name));

        if ($currentName === $canonicalName) {
            return $template;
        }

        $template->meta_template_name = $canonicalName;
        $actorId = $this->normalizeActorId((string) optional(auth()->user())->id);
        if ($actorId !== null) {
            $template->updated_by = $actorId;
        }
        $template->save();

        Log::info('wa_official_tenant_template_meta_name_normalized', [
            'template_id' => (string) $template->id,
            'key' => (string) $template->key,
            'old_meta_template_name' => $currentName,
            'normalized_meta_template_name' => $canonicalName,
        ]);

        return $template->fresh() ?? $template;
    }

    private function canonicalTenantMetaTemplateName(string $key): string
    {
        $normalizedKey = strtolower(str_replace(['.', '-', ' '], '_', trim($key)));
        return 'tenant_' . $normalizedKey;
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
     * @return array<int, string>
     */
    private function tenantMetaTemplateNameCandidates(WhatsAppOfficialTemplate $template): array
    {
        $canonical = $this->canonicalTenantMetaTemplateName((string) $template->key);
        $current = strtolower(trim((string) $template->meta_template_name));
        $withoutTenantPrefix = str_starts_with($current, 'tenant_')
            ? trim(substr($current, strlen('tenant_')))
            : $current;

        $candidates = [$canonical, $current, $withoutTenantPrefix];

        $filtered = array_values(array_filter(array_map('trim', $candidates), static fn (string $value): bool => $value !== ''));
        return array_values(array_unique($filtered));
    }

    /**
     * @return array<int, array<int, string>>
     */
    private function tenantSemanticVariableCandidatesByKey(string $key): array
    {
        return match (trim($key)) {
            'appointment.pending_confirmation' => [
                ['patient_name', 'customer_name'],
                ['appointment_date', 'appointment_datetime'],
                ['appointment_confirm_link', 'appointment_details_link', 'appointment_link', 'links.appointment_confirm'],
            ],
            'appointment.confirmed' => [
                ['patient_name', 'customer_name'],
                ['appointment_date', 'appointment_datetime'],
                ['appointment_details_link', 'appointment_manage_link', 'appointment_confirm_link', 'appointment_link', 'links.appointment_details'],
            ],
            'appointment.canceled' => [
                ['patient_name', 'customer_name'],
                ['appointment_date', 'appointment_datetime'],
                ['appointment_new_link', 'appointment_reschedule_link', 'appointment_details_link', 'appointment_link'],
            ],
            'appointment.expired' => [
                ['patient_name', 'customer_name'],
                ['appointment_date', 'appointment_datetime'],
                ['appointment_new_link', 'appointment_reschedule_link', 'appointment_details_link', 'appointment_link'],
            ],
            'waitlist.joined' => [
                ['patient_name', 'customer_name'],
            ],
            'waitlist.offered' => [
                ['patient_name', 'customer_name'],
                ['appointment_date', 'appointment_datetime'],
                ['waitlist_offer_link', 'appointment_confirm_link', 'appointment_link', 'links.waitlist_offer'],
            ],
            'appointment.created.doctor' => [
                ['doctor_name', 'professional_name'],
                ['patient_name', 'customer_name'],
                ['appointment_date', 'appointment_datetime'],
                ['appointment_time'],
                ['appointment_details_link', 'appointment_manage_link', 'appointment_link', 'links.appointment_details'],
            ],
            'appointment.confirmed.doctor' => [
                ['doctor_name', 'professional_name'],
                ['patient_name', 'customer_name'],
                ['appointment_date', 'appointment_datetime'],
                ['appointment_time'],
                ['appointment_details_link', 'appointment_manage_link', 'appointment_link', 'links.appointment_details'],
            ],
            'appointment.canceled.doctor' => [
                ['doctor_name', 'professional_name'],
                ['patient_name', 'customer_name'],
                ['appointment_date', 'appointment_datetime'],
                ['appointment_time'],
            ],
            'appointment.rescheduled.doctor' => [
                ['doctor_name', 'professional_name'],
                ['patient_name', 'customer_name'],
                ['appointment_date', 'appointment_datetime'],
                ['appointment_time'],
                ['appointment_details_link', 'appointment_manage_link', 'appointment_link', 'links.appointment_details'],
            ],
            'waitlist.offered.doctor' => [
                ['doctor_name', 'professional_name'],
                ['patient_name', 'customer_name'],
                ['appointment_date', 'appointment_datetime'],
                ['waitlist_offer_link', 'appointment_confirm_link', 'appointment_link', 'links.waitlist_offer'],
            ],
            'waitlist.accepted.doctor' => [
                ['doctor_name', 'professional_name'],
                ['patient_name', 'customer_name'],
                ['appointment_date', 'appointment_datetime'],
                ['appointment_time'],
            ],
            'form.response_submitted.doctor' => [
                ['doctor_name', 'professional_name'],
                ['patient_name', 'customer_name'],
                ['form_name'],
                ['response_submitted_at', 'submitted_at'],
                ['form_response_link', 'response_link', 'links.form_response'],
            ],
            'online_appointment.updated.doctor' => [
                ['doctor_name', 'professional_name'],
                ['patient_name', 'customer_name'],
                ['appointment_date', 'appointment_datetime'],
                ['appointment_time'],
                ['meeting_app', 'online.meeting_app'],
                ['meeting_link', 'online.meeting_link'],
                ['online_appointment_details_link', 'appointment_details_link', 'links.online_appointment_details'],
            ],
            'online_appointment.instructions_sent.doctor' => [
                ['doctor_name', 'professional_name'],
                ['patient_name', 'customer_name'],
                ['appointment_date', 'appointment_datetime'],
                ['appointment_time'],
                ['instructions_sent_email_at', 'online.instructions_sent_email_at'],
                ['instructions_sent_whatsapp_at', 'online.instructions_sent_whatsapp_at'],
                ['online_appointment_details_link', 'appointment_details_link', 'links.online_appointment_details'],
            ],
            'online_appointment.form_response_submitted.doctor' => [
                ['doctor_name', 'professional_name'],
                ['patient_name', 'customer_name'],
                ['form_name'],
                ['appointment_datetime', 'appointment_date'],
                ['response_submitted_at', 'submitted_at'],
                ['form_response_link', 'response_link', 'links.form_response'],
                ['online_appointment_details_link', 'appointment_details_link', 'links.online_appointment_details'],
            ],
            default => [],
        };
    }

    private function mapMetaStatusToLocal(string $status): string
    {
        return match ($status) {
            'APPROVED' => WhatsAppOfficialTemplate::STATUS_APPROVED,
            'REJECTED' => WhatsAppOfficialTemplate::STATUS_REJECTED,
            'ARCHIVED', 'PAUSED', 'DISABLED' => WhatsAppOfficialTemplate::STATUS_ARCHIVED,
            'PENDING', 'IN_REVIEW', 'PENDING_REVIEW' => WhatsAppOfficialTemplate::STATUS_PENDING,
            default => WhatsAppOfficialTemplate::STATUS_PENDING,
        };
    }

    private function isMetaNameConflict(WhatsAppMetaApiException $exception): bool
    {
        $metaError = $exception->metaError();
        $message = strtolower(trim((string) ($metaError['message'] ?? '')));
        $details = strtolower(trim((string) ($metaError['details'] ?? '')));
        $summary = strtolower(trim((string) $exception->responseSummary()));

        $haystack = trim($message . ' ' . $details . ' ' . $summary);

        return str_contains($haystack, 'already exists')
            || str_contains($haystack, 'name already')
            || str_contains($haystack, 'duplicate');
    }

    /**
     * @param array<string, mixed> $remoteTemplate
     * @return array<int, string>
     */
    private function extractRemoteBodyPlaceholderOrder(array $remoteTemplate): array
    {
        $components = (array) ($remoteTemplate['components'] ?? []);
        foreach ($components as $component) {
            if (!is_array($component)) {
                continue;
            }

            $type = strtoupper(trim((string) ($component['type'] ?? '')));
            if ($type !== 'BODY') {
                continue;
            }

            $text = (string) ($component['text'] ?? '');
            preg_match_all('/\{\{(\d+)\}\}/', $text, $matches);
            $ordered = [];
            foreach ((array) ($matches[1] ?? []) as $placeholder) {
                $normalized = trim((string) $placeholder);
                if ($normalized === '' || in_array($normalized, $ordered, true)) {
                    continue;
                }

                $ordered[] = $normalized;
            }

            return $ordered;
        }

        return [];
    }

    /**
     * @param array<string, mixed> $remoteTemplate
     */
    private function extractRemoteBodyText(array $remoteTemplate): string
    {
        $components = (array) ($remoteTemplate['components'] ?? []);
        foreach ($components as $component) {
            if (!is_array($component)) {
                continue;
            }

            $type = strtoupper(trim((string) ($component['type'] ?? '')));
            if ($type === 'BODY') {
                return (string) ($component['text'] ?? '');
            }
        }

        return '';
    }

    /**
     * @param array<int, string> $availableByName
     * @param array<int, string> $usedVariableNames
     */
    private function inferVariableNameFromRemoteContext(
        string $remoteBodyText,
        string $remotePlaceholder,
        array $availableByName,
        array $usedVariableNames
    ): ?string {
        $normalizedContext = $this->normalizeSemanticText($remoteBodyText);
        $normalizedPlaceholder = trim($remotePlaceholder);
        if ($normalizedContext === '' || $normalizedPlaceholder === '') {
            return null;
        }

        $contextWindow = '';
        if (preg_match('/(.{0,60})\\{\\{' . preg_quote($normalizedPlaceholder, '/') . '\\}\\}(.{0,60})/iu', $normalizedContext, $matches) === 1) {
            $contextWindow = trim((string) (($matches[1] ?? '') . ' ' . ($matches[2] ?? '')));
        }

        if ($contextWindow === '') {
            return null;
        }

        $candidateGroups = [];
        if (str_contains($contextWindow, 'data') || str_contains($contextWindow, 'date') || str_contains($contextWindow, 'dia')) {
            $candidateGroups[] = ['appointment_date', 'appointment_datetime', 'date'];
        }
        if (str_contains($contextWindow, 'horario') || str_contains($contextWindow, 'hora') || str_contains($contextWindow, 'time')) {
            $candidateGroups[] = ['appointment_time', 'time'];
        }
        if (
            str_contains($contextWindow, 'link')
            || str_contains($contextWindow, 'acesse')
            || str_contains($contextWindow, 'url')
            || str_contains($contextWindow, 'detalh')
            || str_contains($contextWindow, 'confirm')
            || str_contains($contextWindow, 'cancel')
            || str_contains($contextWindow, 'oferta')
        ) {
            $candidateGroups[] = ['link', 'url', 'confirm', 'cancel', 'details', 'offer', 'manage'];
        }
        if (str_contains($contextWindow, 'ola') || str_contains($contextWindow, 'nome')) {
            $candidateGroups[] = ['patient_name', 'customer_name', 'name'];
        }

        foreach ($candidateGroups as $patterns) {
            $match = $this->pickVariableByPattern($availableByName, $usedVariableNames, (array) $patterns);
            if ($match !== null) {
                return $match;
            }
        }

        return null;
    }

    /**
     * @param array<int, string> $availableByName
     * @param array<int, string> $usedVariableNames
     * @param array<int, string> $patterns
     */
    private function pickVariableByPattern(
        array $availableByName,
        array $usedVariableNames,
        array $patterns
    ): ?string {
        $normalizedPatterns = array_values(array_filter(array_map(
            fn (string $pattern): string => $this->normalizeSemanticText($pattern),
            $patterns
        ), static fn (string $pattern): bool => $pattern !== ''));

        if ($normalizedPatterns === []) {
            return null;
        }

        foreach ($availableByName as $candidateName) {
            if (in_array($candidateName, $usedVariableNames, true)) {
                continue;
            }

            $normalizedCandidate = $this->normalizeSemanticText($candidateName);
            foreach ($normalizedPatterns as $pattern) {
                if (str_contains($normalizedCandidate, $pattern)) {
                    return $candidateName;
                }
            }
        }

        return null;
    }

    private function normalizeSemanticText(string $text): string
    {
        $value = mb_strtolower(trim($text), 'UTF-8');
        if ($value === '') {
            return '';
        }

        $transliterated = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
        if (is_string($transliterated) && trim($transliterated) !== '') {
            $value = $transliterated;
        }

        return preg_replace('/\s+/', ' ', $value) ?? $value;
    }

    /**
     * @param array<string, mixed> $map
     * @return array<string, string>
     */
    private function normalizeVariableMap(array $map): array
    {
        $normalized = [];
        foreach ($map as $placeholder => $variableName) {
            if (!is_string($placeholder) && !is_int($placeholder)) {
                continue;
            }

            if (!is_string($variableName) && !is_int($variableName)) {
                continue;
            }

            $placeholderKey = trim((string) $placeholder);
            $name = trim((string) $variableName);
            if ($placeholderKey === '' || $name === '') {
                continue;
            }

            $normalized[$placeholderKey] = $name;
        }

        uksort($normalized, static fn (string $a, string $b): int => (int) $a <=> (int) $b);
        return $normalized;
    }
}
