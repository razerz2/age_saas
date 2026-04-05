<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreCampaignTemplateRequest;
use App\Http\Requests\Tenant\UpdateCampaignTemplateRequest;
use App\Models\Platform\WhatsAppOfficialTemplate;
use App\Models\Tenant\CampaignTemplate;
use App\Services\Tenant\CampaignTemplateProviderResolver;
use App\Support\Tenant\CampaignTemplateVariableCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;

class CampaignTemplateController extends Controller
{
    private const OFFICIAL_BLOCK_MESSAGE = 'O tenant está usando WhatsApp Oficial para campanhas. Gerencie os templates no catálogo oficial sincronizado com a Meta.';

    public function __construct(
        private readonly CampaignTemplateProviderResolver $providerResolver,
        private readonly CampaignTemplateVariableCatalog $variableCatalog
    ) {
    }

    public function index(): View
    {
        $provider = $this->providerResolver->resolveWhatsAppProvider();
        $isOfficialMode = $this->providerResolver->isOfficialWhatsApp();
        $officialTemplates = null;
        $campaignTemplates = null;

        if ($isOfficialMode) {
            $officialTemplates = WhatsAppOfficialTemplate::query()
                ->officialProvider()
                ->forTenant($this->currentTenantId())
                ->approved()
                ->orderBy('key')
                ->orderByDesc('version')
                ->paginate(20);
        } else {
            $campaignTemplates = CampaignTemplate::query()
                ->forWhatsApp()
                ->unofficial()
                ->orderByDesc('updated_at')
                ->paginate(20);
        }

        return view('tenant.campaign_templates.index', [
            'provider' => $provider,
            'isOfficialMode' => $isOfficialMode,
            'officialTemplates' => $officialTemplates,
            'campaignTemplates' => $campaignTemplates,
            'officialManagementUrl' => $this->officialManagementUrl(),
        ]);
    }

    public function create(): RedirectResponse|View
    {
        if ($this->providerResolver->isOfficialWhatsApp()) {
            return $this->redirectToIndexWithWarning();
        }

        return view('tenant.campaign_templates.create', [
            'campaignTemplate' => new CampaignTemplate([
                'is_active' => true,
            ]),
            'availableVariables' => $this->variableCatalog->all(),
        ]);
    }

    public function store(StoreCampaignTemplateRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $template = CampaignTemplate::query()->create($this->buildPayload($validated));

        return redirect()
            ->route('tenant.campaign-templates.edit', [
                'slug' => $this->resolveSlug($request),
                'campaignTemplate' => $template->id,
            ])
            ->with('success', 'Template de campanha criado com sucesso.');
    }

    public function edit(CampaignTemplate $campaignTemplate): RedirectResponse|View
    {
        if ($this->providerResolver->isOfficialWhatsApp()) {
            return $this->redirectToIndexWithWarning();
        }

        return view('tenant.campaign_templates.edit', [
            'campaignTemplate' => $campaignTemplate,
            'availableVariables' => $this->variableCatalog->all(),
        ]);
    }

    public function update(
        UpdateCampaignTemplateRequest $request,
        CampaignTemplate $campaignTemplate
    ): RedirectResponse {
        $validated = $request->validated();
        $campaignTemplate->update($this->buildPayload($validated));

        return redirect()
            ->route('tenant.campaign-templates.edit', [
                'slug' => $this->resolveSlug($request),
                'campaignTemplate' => $campaignTemplate->id,
            ])
            ->with('success', 'Template de campanha atualizado com sucesso.');
    }

    /**
     * @param array<string, mixed> $validated
     * @return array<string, mixed>
     */
    private function buildPayload(array $validated): array
    {
        $variables = $this->sanitizeVariables($validated['variables_json'] ?? []);

        return [
            'name' => trim((string) ($validated['name'] ?? '')),
            'channel' => 'whatsapp',
            'provider_type' => 'unofficial',
            'template_key' => null,
            'title' => null,
            'content' => trim((string) ($validated['content'] ?? '')),
            'variables_json' => $variables === [] ? null : $variables,
            'is_active' => (bool) ($validated['is_active'] ?? true),
        ];
    }

    /**
     * @param array<int, mixed> $variables
     * @return array<int, string>
     */
    private function sanitizeVariables(array $variables): array
    {
        $normalized = [];

        foreach ($variables as $variable) {
            $value = trim((string) $variable);
            if ($value === '') {
                continue;
            }

            if (!in_array($value, $normalized, true)) {
                $normalized[] = $value;
            }
        }

        return $normalized;
    }

    private function officialManagementUrl(): ?string
    {
        if (!Route::has('tenant.settings.whatsapp-official-tenant-templates.index')) {
            return null;
        }

        return workspace_route('tenant.settings.whatsapp-official-tenant-templates.index');
    }

    private function redirectToIndexWithWarning(): RedirectResponse
    {
        return redirect()
            ->route('tenant.campaign-templates.index', ['slug' => $this->resolveSlug()])
            ->with('warning', self::OFFICIAL_BLOCK_MESSAGE);
    }

    private function resolveSlug(?Request $request = null): string
    {
        $slug = trim((string) ($request?->route('slug') ?? tenant()?->subdomain ?? ''));

        return $slug;
    }

    private function currentTenantId(): string
    {
        return trim((string) (tenant()?->id ?? ''));
    }
}
