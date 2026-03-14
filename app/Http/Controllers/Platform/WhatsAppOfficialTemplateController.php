<?php

namespace App\Http\Controllers\Platform;

use App\Exceptions\WhatsAppMetaApiException;
use App\Exceptions\WhatsAppMetaConfigurationException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Platform\StoreWhatsAppOfficialTemplateRequest;
use App\Http\Requests\Platform\UpdateWhatsAppOfficialTemplateRequest;
use App\Models\Platform\WhatsAppOfficialTemplate;
use App\Services\Platform\WhatsAppOfficialMessageService;
use App\Services\Platform\WhatsAppOfficialTemplateService;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class WhatsAppOfficialTemplateController extends Controller
{
    public function __construct(
        private readonly WhatsAppOfficialTemplateService $templateService,
        private readonly WhatsAppOfficialMessageService $officialMessageService
    ) {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', WhatsAppOfficialTemplate::class);

        $query = WhatsAppOfficialTemplate::query()
            ->officialProvider()
            ->orderBy('key')
            ->orderByDesc('version');

        if ($request->filled('status')) {
            $query->where('status', (string) $request->input('status'));
        }

        if ($request->filled('key')) {
            $query->where('key', 'like', '%' . $request->input('key') . '%');
        }

        $templates = $query->paginate(20)->withQueryString();

        return view('platform.whatsapp_official_templates.index', [
            'templates' => $templates,
            'filters' => [
                'status' => (string) $request->input('status', ''),
                'key' => (string) $request->input('key', ''),
            ],
            'statusOptions' => [
                WhatsAppOfficialTemplate::STATUS_DRAFT,
                WhatsAppOfficialTemplate::STATUS_PENDING,
                WhatsAppOfficialTemplate::STATUS_APPROVED,
                WhatsAppOfficialTemplate::STATUS_REJECTED,
                WhatsAppOfficialTemplate::STATUS_ARCHIVED,
            ],
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', WhatsAppOfficialTemplate::class);

        return view('platform.whatsapp_official_templates.create', [
            'template' => new WhatsAppOfficialTemplate([
                'provider' => WhatsAppOfficialTemplate::PROVIDER,
                'category' => 'UTILITY',
                'language' => 'pt_BR',
                'status' => WhatsAppOfficialTemplate::STATUS_DRAFT,
                'variables' => [],
            ]),
        ]);
    }

    public function store(StoreWhatsAppOfficialTemplateRequest $request): RedirectResponse
    {
        $this->authorize('create', WhatsAppOfficialTemplate::class);

        try {
            $template = $this->templateService->createTemplate(
                $request->validated(),
                (string) optional(auth()->user())->id
            );

            return redirect()
                ->route('Platform.whatsapp-official-templates.show', $template)
                ->with('success', 'Template oficial criado com sucesso.');
        } catch (DomainException $e) {
            return back()->withInput()->withErrors(['template' => $e->getMessage()]);
        } catch (\Throwable $e) {
            Log::error('Erro ao criar template oficial WhatsApp', [
                'error' => $e->getMessage(),
            ]);

            return back()->withInput()->withErrors([
                'template' => 'Falha ao criar template oficial: ' . $e->getMessage(),
            ]);
        }
    }

    public function show(WhatsAppOfficialTemplate $whatsappOfficialTemplate): View
    {
        $this->authorize('view', $whatsappOfficialTemplate);

        $versions = WhatsAppOfficialTemplate::query()
            ->officialProvider()
            ->byKey($whatsappOfficialTemplate->key)
            ->orderByDesc('version')
            ->get();

        return view('platform.whatsapp_official_templates.show', [
            'template' => $whatsappOfficialTemplate,
            'versions' => $versions,
        ]);
    }

    public function edit(WhatsAppOfficialTemplate $whatsappOfficialTemplate): RedirectResponse|View
    {
        $this->authorize('update', $whatsappOfficialTemplate);

        if (!$whatsappOfficialTemplate->isDirectlyEditable()) {
            $warningMessage = $whatsappOfficialTemplate->requiresVersioningForEdit()
                ? 'Template aprovado na Meta não pode ser editado diretamente. Use "Nova versão".'
                : 'Template com status "' . $whatsappOfficialTemplate->status . '" não permite edição direta.';

            return redirect()
                ->route('Platform.whatsapp-official-templates.show', $whatsappOfficialTemplate)
                ->with('warning', $warningMessage);
        }

        return view('platform.whatsapp_official_templates.edit', [
            'template' => $whatsappOfficialTemplate,
        ]);
    }

    public function update(
        UpdateWhatsAppOfficialTemplateRequest $request,
        WhatsAppOfficialTemplate $whatsappOfficialTemplate
    ): RedirectResponse {
        $this->authorize('update', $whatsappOfficialTemplate);

        try {
            $updated = $this->templateService->updateTemplate(
                $whatsappOfficialTemplate,
                $request->validated(),
                (string) optional(auth()->user())->id
            );

            if ($updated->id !== $whatsappOfficialTemplate->id) {
                return redirect()
                    ->route('Platform.whatsapp-official-templates.edit', $updated)
                    ->with('success', 'Template aprovado não foi editado diretamente. Nova versão criada em draft.');
            }

            return redirect()
                ->route('Platform.whatsapp-official-templates.show', $updated)
                ->with('success', 'Template atualizado com sucesso.');
        } catch (DomainException $e) {
            return back()->withInput()->withErrors(['template' => $e->getMessage()]);
        } catch (\Throwable $e) {
            Log::error('Erro ao atualizar template oficial WhatsApp', [
                'template_id' => $whatsappOfficialTemplate->id,
                'error' => $e->getMessage(),
            ]);

            return back()->withInput()->withErrors([
                'template' => 'Falha ao atualizar template oficial: ' . $e->getMessage(),
            ]);
        }
    }

    public function duplicate(WhatsAppOfficialTemplate $whatsappOfficialTemplate): RedirectResponse
    {
        $this->authorize('duplicate', $whatsappOfficialTemplate);

        try {
            $newVersion = $this->templateService->duplicateVersion(
                $whatsappOfficialTemplate,
                (string) optional(auth()->user())->id
            );

            return redirect()
                ->route('Platform.whatsapp-official-templates.edit', $newVersion)
                ->with('success', 'Nova versão criada em draft com sucesso.');
        } catch (\Throwable $e) {
            Log::error('Erro ao duplicar versão de template oficial WhatsApp', [
                'template_id' => $whatsappOfficialTemplate->id,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors([
                'template' => 'Falha ao gerar nova versão: ' . $e->getMessage(),
            ]);
        }
    }

    public function submitToMeta(WhatsAppOfficialTemplate $whatsappOfficialTemplate): RedirectResponse
    {
        $this->authorize('submitToMeta', $whatsappOfficialTemplate);

        try {
            $this->templateService->submitToMeta(
                $whatsappOfficialTemplate,
                (string) optional(auth()->user())->id
            );

            return redirect()
                ->route('Platform.whatsapp-official-templates.show', $whatsappOfficialTemplate)
                ->with('success', 'Template enviado para a Meta com sucesso.');
        } catch (WhatsAppMetaConfigurationException|DomainException $e) {
            return back()->withErrors(['template' => $e->getMessage()]);
        } catch (WhatsAppMetaApiException $e) {
            Log::warning('wa_official_template_submit_meta_api_error', [
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
            Log::error('Erro ao enviar template oficial para Meta', [
                'template_id' => $whatsappOfficialTemplate->id,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors([
                'template' => 'Falha ao enviar template para a Meta: ' . $e->getMessage(),
            ]);
        }
    }

    public function syncStatus(WhatsAppOfficialTemplate $whatsappOfficialTemplate): RedirectResponse
    {
        $this->authorize('syncStatus', $whatsappOfficialTemplate);

        try {
            $this->templateService->syncStatus(
                $whatsappOfficialTemplate,
                (string) optional(auth()->user())->id
            );

            return redirect()
                ->route('Platform.whatsapp-official-templates.show', $whatsappOfficialTemplate)
                ->with('success', 'Status sincronizado com a Meta com sucesso.');
        } catch (WhatsAppMetaConfigurationException|DomainException $e) {
            return back()->withErrors(['template' => $e->getMessage()]);
        } catch (WhatsAppMetaApiException $e) {
            Log::warning('wa_official_template_sync_meta_api_error', [
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
            Log::error('Erro ao sincronizar status de template oficial', [
                'template_id' => $whatsappOfficialTemplate->id,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors([
                'template' => 'Falha ao sincronizar status: ' . $e->getMessage(),
            ]);
        }
    }

    public function archive(WhatsAppOfficialTemplate $whatsappOfficialTemplate): RedirectResponse
    {
        $this->authorize('archive', $whatsappOfficialTemplate);

        try {
            $this->templateService->archiveTemplate(
                $whatsappOfficialTemplate,
                (string) optional(auth()->user())->id
            );

            return redirect()
                ->route('Platform.whatsapp-official-templates.show', $whatsappOfficialTemplate)
                ->with('success', 'Template arquivado com sucesso.');
        } catch (\Throwable $e) {
            Log::error('Erro ao arquivar template oficial WhatsApp', [
                'template_id' => $whatsappOfficialTemplate->id,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors([
                'template' => 'Falha ao arquivar template: ' . $e->getMessage(),
            ]);
        }
    }

    public function testSend(Request $request, WhatsAppOfficialTemplate $whatsappOfficialTemplate): JsonResponse
    {
        $this->authorize('testSend', $whatsappOfficialTemplate);

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
            $result = $this->officialMessageService->sendManualTest(
                $whatsappOfficialTemplate,
                (string) ($payload['phone'] ?? ''),
                (array) ($payload['variables'] ?? []),
                [
                    'service' => static::class,
                    'event' => 'manual_test',
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
        } catch (\Throwable $e) {
            Log::error('Erro no teste manual de template oficial WhatsApp', [
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
}
