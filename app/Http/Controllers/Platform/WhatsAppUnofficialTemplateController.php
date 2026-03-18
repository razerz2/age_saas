<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Http\Requests\Platform\StoreWhatsAppUnofficialTemplateRequest;
use App\Http\Requests\Platform\UpdateWhatsAppUnofficialTemplateRequest;
use App\Models\Platform\WhatsAppUnofficialTemplate;
use App\Services\Platform\WhatsAppUnofficialTemplateManualTestService;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Throwable;

class WhatsAppUnofficialTemplateController extends Controller
{
    public function __construct(
        private readonly WhatsAppUnofficialTemplateManualTestService $manualTestService
    ) {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', WhatsAppUnofficialTemplate::class);

        $query = WhatsAppUnofficialTemplate::query()->orderBy('category')->orderBy('key');

        if ($request->filled('key')) {
            $query->where('key', 'like', '%' . $request->input('key') . '%');
        }

        if ($request->filled('category')) {
            $query->where('category', 'like', '%' . $request->input('category') . '%');
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', (string) $request->input('is_active') === '1');
        }

        return view('platform.whatsapp_unofficial_templates.index', [
            'templates' => $query->paginate(20)->withQueryString(),
            'filters' => [
                'key' => (string) $request->input('key', ''),
                'category' => (string) $request->input('category', ''),
                'is_active' => (string) $request->input('is_active', ''),
            ],
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', WhatsAppUnofficialTemplate::class);

        return view('platform.whatsapp_unofficial_templates.create', [
            'template' => new WhatsAppUnofficialTemplate([
                'is_active' => true,
                'variables' => [],
            ]),
        ]);
    }

    public function store(StoreWhatsAppUnofficialTemplateRequest $request): RedirectResponse
    {
        $this->authorize('create', WhatsAppUnofficialTemplate::class);

        $template = WhatsAppUnofficialTemplate::query()->create($request->validated());

        return redirect()
            ->route('Platform.whatsapp-unofficial-templates.show', $template)
            ->with('success', 'Template interno nao oficial criado com sucesso.');
    }

    public function show(WhatsAppUnofficialTemplate $whatsappUnofficialTemplate): View
    {
        $this->authorize('view', $whatsappUnofficialTemplate);
        $descriptor = $this->manualTestService->describeTemplate($whatsappUnofficialTemplate);

        return view('platform.whatsapp_unofficial_templates.show', [
            'template' => $whatsappUnofficialTemplate,
            'requiredVariables' => $descriptor['required_variables'] ?? [],
            'fakeValues' => $descriptor['fake_values'] ?? [],
        ]);
    }

    public function edit(WhatsAppUnofficialTemplate $whatsappUnofficialTemplate): View
    {
        $this->authorize('update', $whatsappUnofficialTemplate);

        return view('platform.whatsapp_unofficial_templates.edit', [
            'template' => $whatsappUnofficialTemplate,
        ]);
    }

    public function update(
        UpdateWhatsAppUnofficialTemplateRequest $request,
        WhatsAppUnofficialTemplate $whatsappUnofficialTemplate
    ): RedirectResponse {
        $this->authorize('update', $whatsappUnofficialTemplate);

        $whatsappUnofficialTemplate->update($request->validated());

        return redirect()
            ->route('Platform.whatsapp-unofficial-templates.show', $whatsappUnofficialTemplate)
            ->with('success', 'Template interno nao oficial atualizado com sucesso.');
    }

    public function toggle(WhatsAppUnofficialTemplate $whatsappUnofficialTemplate): RedirectResponse
    {
        $this->authorize('toggle', $whatsappUnofficialTemplate);

        $whatsappUnofficialTemplate->update([
            'is_active' => !$whatsappUnofficialTemplate->is_active,
        ]);

        return redirect()
            ->route('Platform.whatsapp-unofficial-templates.index')
            ->with(
                'success',
                $whatsappUnofficialTemplate->is_active
                    ? 'Template ativado com sucesso.'
                    : 'Template inativado com sucesso.'
            );
    }

    public function preview(WhatsAppUnofficialTemplate $whatsappUnofficialTemplate, Request $request): JsonResponse
    {
        $this->authorize('preview', $whatsappUnofficialTemplate);

        $validator = Validator::make($request->all(), [
            'variables' => ['nullable', 'array'],
            'variables.*' => ['nullable', 'string', 'max:500'],
            'fill_missing_with_fake' => ['nullable', 'boolean'],
        ], [
            'variables.array' => 'Formato de variaveis invalido para preview.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Falha de validacao no preview.',
                'errors' => $validator->errors(),
            ], 422);
        }

        if (!$whatsappUnofficialTemplate->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Template inativo. Ative o template para gerar preview operacional.',
            ], 422);
        }

        try {
            $payload = $validator->validated();
            $provider = $this->manualTestService->activeProvider();
            $preview = $this->manualTestService->preview(
                $whatsappUnofficialTemplate,
                (array) ($payload['variables'] ?? []),
                filter_var($payload['fill_missing_with_fake'] ?? false, FILTER_VALIDATE_BOOLEAN)
            );

            Log::info('wa_unofficial_manual_test_preview', [
                'template_key' => (string) $whatsappUnofficialTemplate->key,
                'template_scope' => 'platform_unofficial',
                'provider' => $provider,
                'preview_source' => $preview['preview_source'] ?? 'tenant_template_renderer',
                'missing_variables' => $preview['missing_variables'] ?? [],
                'result' => 'success',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Preview renderizado com sucesso.',
                'preview' => $preview['rendered_message'] ?? '',
                'provider' => $provider,
                'missing_variables' => $preview['missing_variables'] ?? [],
                'required_variables' => $preview['required_variables'] ?? [],
                'resolved_variables' => $preview['resolved_variables'] ?? [],
                'preview_source' => $preview['preview_source'] ?? 'tenant_template_renderer',
            ]);
        } catch (Throwable $e) {
            Log::warning('wa_unofficial_manual_test_preview_failed', [
                'template_key' => (string) $whatsappUnofficialTemplate->key,
                'template_scope' => 'platform_unofficial',
                'provider' => $this->manualTestService->activeProvider(),
                'result' => 'error',
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro interno ao gerar preview do template.',
            ], 500);
        }
    }

    public function testSend(WhatsAppUnofficialTemplate $whatsappUnofficialTemplate, Request $request): JsonResponse
    {
        $this->authorize('testSend', $whatsappUnofficialTemplate);

        $validator = Validator::make($request->all(), [
            'phone' => ['required', 'string', 'min:10', 'max:25', 'regex:/^[0-9+\-\s\(\)]+$/'],
            'variables' => ['nullable', 'array'],
            'variables.*' => ['nullable', 'string', 'max:500'],
            'fill_missing_with_fake' => ['nullable', 'boolean'],
        ], [
            'phone.required' => 'Informe o numero de destino.',
            'phone.regex' => 'Numero de destino invalido.',
            'variables.array' => 'Formato de variaveis invalido para envio.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Falha de validacao no teste manual.',
                'errors' => $validator->errors(),
            ], 422);
        }

        if (!$whatsappUnofficialTemplate->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Template inativo. Ative o template antes de enviar teste manual.',
            ], 422);
        }

        try {
            $payload = $validator->validated();
            $preview = $this->manualTestService->preview(
                $whatsappUnofficialTemplate,
                (array) ($payload['variables'] ?? []),
                filter_var($payload['fill_missing_with_fake'] ?? false, FILTER_VALIDATE_BOOLEAN)
            );

            if (($preview['missing_variables'] ?? []) !== []) {
                return response()->json([
                    'success' => false,
                    'message' => 'Variaveis obrigatorias ausentes: ' . implode(', ', (array) $preview['missing_variables']),
                    'missing_variables' => $preview['missing_variables'],
                    'preview' => $preview['rendered_message'] ?? '',
                ], 422);
            }

            $result = $this->manualTestService->send(
                $whatsappUnofficialTemplate,
                (string) ($payload['phone'] ?? ''),
                (array) ($preview['resolved_variables'] ?? []),
                [
                    'service' => static::class,
                    'event' => 'manual_test',
                    'actor_id' => (string) optional(auth()->user())->id,
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Teste manual enviado com sucesso.',
                'provider' => $result['provider'] ?? null,
                'preview_source' => $result['preview_source'] ?? 'tenant_template_renderer',
            ]);
        } catch (DomainException $e) {
            Log::warning('wa_unofficial_manual_test_send_failed', [
                'template_key' => (string) $whatsappUnofficialTemplate->key,
                'template_scope' => 'platform_unofficial',
                'provider' => $this->manualTestService->activeProvider(),
                'to_masked' => $this->maskPhone((string) ($payload['phone'] ?? '')),
                'result' => 'error',
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (Throwable $e) {
            Log::error('wa_unofficial_manual_test_send_error', [
                'template_key' => (string) $whatsappUnofficialTemplate->key,
                'template_scope' => 'platform_unofficial',
                'provider' => $this->manualTestService->activeProvider(),
                'to_masked' => $this->maskPhone((string) ($payload['phone'] ?? '')),
                'result' => 'error',
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro interno ao executar teste manual do template.',
            ], 500);
        }
    }

    private function maskPhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';
        if ($digits === '') {
            return '***';
        }

        if (strlen($digits) <= 4) {
            return str_repeat('*', strlen($digits));
        }

        return str_repeat('*', strlen($digits) - 4) . substr($digits, -4);
    }
}
