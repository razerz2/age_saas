<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Http\Requests\Platform\StoreTenantEmailTemplateRequest;
use App\Http\Requests\Platform\UpdateTenantEmailTemplateRequest;
use App\Models\Platform\NotificationTemplate;
use App\Models\Platform\TenantEmailTemplate;
use App\Services\Platform\EmailTemplateTestSendService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class TenantEmailTemplateController extends Controller
{
    public function __construct(
        private readonly EmailTemplateTestSendService $testSendService
    ) {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', TenantEmailTemplate::class);

        $query = TenantEmailTemplate::query()->orderBy('name');

        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . (string) $request->input('name') . '%');
        }

        if ($request->filled('enabled')) {
            $query->where('enabled', (string) $request->input('enabled') === '1');
        }

        return view('platform.tenant_email_templates.index', [
            'templates' => $query->paginate(20)->withQueryString(),
            'filters' => [
                'name' => (string) $request->input('name', ''),
                'enabled' => (string) $request->input('enabled', ''),
            ],
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', TenantEmailTemplate::class);

        return view('platform.tenant_email_templates.create', [
            'template' => new TenantEmailTemplate([
                'enabled' => true,
            ]),
        ]);
    }

    public function store(StoreTenantEmailTemplateRequest $request): RedirectResponse
    {
        $this->authorize('create', TenantEmailTemplate::class);

        $payload = $request->validated();
        $template = TenantEmailTemplate::query()->create([
            'name' => (string) $payload['name'],
            'display_name' => (string) $payload['display_name'],
            'channel' => NotificationTemplate::CHANNEL_EMAIL,
            'scope' => NotificationTemplate::SCOPE_TENANT,
            'subject' => (string) $payload['subject'],
            'body' => (string) $payload['body'],
            'default_subject' => (string) $payload['subject'],
            'default_body' => (string) $payload['body'],
            'variables' => [],
            'enabled' => (bool) ($payload['enabled'] ?? true),
        ]);

        return redirect()
            ->route('Platform.tenant-email-templates.edit', $template)
            ->with('success', 'Template de Email Tenant criado com sucesso.');
    }

    public function show(TenantEmailTemplate $tenantEmailTemplate): View
    {
        $this->authorize('view', $tenantEmailTemplate);

        return view('platform.tenant_email_templates.show', [
            'template' => $tenantEmailTemplate,
        ]);
    }

    public function edit(TenantEmailTemplate $tenantEmailTemplate): View
    {
        $this->authorize('update', $tenantEmailTemplate);

        return view('platform.tenant_email_templates.edit', [
            'template' => $tenantEmailTemplate,
        ]);
    }

    public function update(
        UpdateTenantEmailTemplateRequest $request,
        TenantEmailTemplate $tenantEmailTemplate
    ): RedirectResponse {
        $this->authorize('update', $tenantEmailTemplate);

        $tenantEmailTemplate->update($request->validated());

        return redirect()
            ->route('Platform.tenant-email-templates.edit', $tenantEmailTemplate)
            ->with('success', 'Template de Email Tenant atualizado com sucesso.');
    }

    public function restore(TenantEmailTemplate $tenantEmailTemplate): RedirectResponse
    {
        $this->authorize('restore', $tenantEmailTemplate);

        $tenantEmailTemplate->update([
            'subject' => $tenantEmailTemplate->default_subject,
            'body' => $tenantEmailTemplate->default_body,
        ]);

        return redirect()
            ->route('Platform.tenant-email-templates.index')
            ->with('success', 'Template de Email Tenant restaurado para o padrão.');
    }

    public function toggle(TenantEmailTemplate $tenantEmailTemplate): RedirectResponse
    {
        $this->authorize('toggle', $tenantEmailTemplate);

        $tenantEmailTemplate->update([
            'enabled' => !$tenantEmailTemplate->enabled,
        ]);

        return redirect()
            ->route('Platform.tenant-email-templates.index')
            ->with(
                'success',
                $tenantEmailTemplate->enabled
                    ? 'Template de Email Tenant ativado com sucesso.'
                    : 'Template de Email Tenant inativado com sucesso.'
            );
    }

    public function testSend(Request $request, TenantEmailTemplate $tenantEmailTemplate): RedirectResponse
    {
        $this->authorize('update', $tenantEmailTemplate);

        $validated = $request->validate([
            'destination_email' => ['required', 'email'],
            'test_send_modal' => ['nullable', 'string'],
        ]);

        try {
            $this->testSendService->send(
                $tenantEmailTemplate,
                (string) $validated['destination_email']
            );

            return redirect()
                ->route('Platform.tenant-email-templates.show', $tenantEmailTemplate)
                ->with('success', 'Email de teste enviado com sucesso.');
        } catch (Throwable $e) {
            report($e);

            return redirect()
                ->route('Platform.tenant-email-templates.show', $tenantEmailTemplate)
                ->withInput()
                ->withErrors([
                    'destination_email' => 'Não foi possível enviar o e-mail de teste. Tente novamente.',
                ]);
        }
    }
}
