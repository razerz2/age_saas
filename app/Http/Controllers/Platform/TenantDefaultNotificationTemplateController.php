<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Http\Requests\Platform\StoreTenantDefaultNotificationTemplateRequest;
use App\Http\Requests\Platform\UpdateTenantDefaultNotificationTemplateRequest;
use App\Models\Platform\TenantDefaultNotificationTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TenantDefaultNotificationTemplateController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', TenantDefaultNotificationTemplate::class);

        $query = TenantDefaultNotificationTemplate::query()->orderBy('key');

        if ($request->filled('channel')) {
            $query->where('channel', (string) $request->input('channel'));
        }

        if ($request->filled('category')) {
            $query->where('category', (string) $request->input('category'));
        }

        if ($request->filled('key')) {
            $query->where('key', 'like', '%' . $request->input('key') . '%');
        }

        return view('platform.tenant_default_notification_templates.index', [
            'templates' => $query->paginate(20)->withQueryString(),
            'filters' => [
                'channel' => (string) $request->input('channel', ''),
                'category' => (string) $request->input('category', ''),
                'key' => (string) $request->input('key', ''),
            ],
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', TenantDefaultNotificationTemplate::class);

        return view('platform.tenant_default_notification_templates.create', [
            'template' => new TenantDefaultNotificationTemplate([
                'channel' => 'whatsapp',
                'language' => 'pt_BR',
                'is_active' => true,
                'variables' => [],
            ]),
        ]);
    }

    public function store(StoreTenantDefaultNotificationTemplateRequest $request): RedirectResponse
    {
        $this->authorize('create', TenantDefaultNotificationTemplate::class);

        $validated = $request->validated();

        $request->validate([
            'key' => [
                Rule::unique('tenant_default_notification_templates', 'key')
                    ->where(fn ($query) => $query->where('channel', $validated['channel'])),
            ],
        ]);

        $template = TenantDefaultNotificationTemplate::query()->create($validated);

        return redirect()
            ->route('Platform.tenant-default-notification-templates.edit', $template)
            ->with('success', 'Template padrão do Tenant criado com sucesso.');
    }

    public function edit(TenantDefaultNotificationTemplate $tenantDefaultTemplate): View
    {
        $this->authorize('update', $tenantDefaultTemplate);

        return view('platform.tenant_default_notification_templates.edit', [
            'template' => $tenantDefaultTemplate,
        ]);
    }

    public function update(
        UpdateTenantDefaultNotificationTemplateRequest $request,
        TenantDefaultNotificationTemplate $tenantDefaultTemplate
    ): RedirectResponse {
        $this->authorize('update', $tenantDefaultTemplate);

        $validated = $request->validated();

        $request->validate([
            'key' => [
                Rule::unique('tenant_default_notification_templates', 'key')
                    ->ignore($tenantDefaultTemplate->id)
                    ->where(fn ($query) => $query->where('channel', $validated['channel'])),
            ],
        ]);

        $tenantDefaultTemplate->update($validated);

        return redirect()
            ->route('Platform.tenant-default-notification-templates.index')
            ->with('success', 'Template padrão do Tenant atualizado com sucesso.');
    }
}
