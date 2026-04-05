<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Http\Requests\Platform\StorePlatformEmailTemplateRequest;
use App\Http\Requests\Platform\UpdatePlatformEmailTemplateRequest;
use App\Models\Platform\NotificationTemplate;
use App\Models\Platform\PlatformEmailTemplate;
use App\Services\Platform\EmailTemplateTestSendService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class PlatformEmailTemplateController extends Controller
{
    public function __construct(
        private readonly EmailTemplateTestSendService $testSendService
    ) {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', PlatformEmailTemplate::class);

        $query = PlatformEmailTemplate::query()->orderBy('name');

        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . (string) $request->input('name') . '%');
        }

        if ($request->filled('enabled')) {
            $query->where('enabled', (string) $request->input('enabled') === '1');
        }

        return view('platform.platform_email_templates.index', [
            'templates' => $query->paginate(20)->withQueryString(),
            'filters' => [
                'name' => (string) $request->input('name', ''),
                'enabled' => (string) $request->input('enabled', ''),
            ],
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', PlatformEmailTemplate::class);

        return view('platform.platform_email_templates.create', [
            'template' => new PlatformEmailTemplate([
                'enabled' => true,
            ]),
        ]);
    }

    public function store(StorePlatformEmailTemplateRequest $request): RedirectResponse
    {
        $this->authorize('create', PlatformEmailTemplate::class);

        $payload = $request->validated();
        $template = PlatformEmailTemplate::query()->create([
            'name' => (string) $payload['name'],
            'display_name' => (string) $payload['display_name'],
            'channel' => NotificationTemplate::CHANNEL_EMAIL,
            'scope' => NotificationTemplate::SCOPE_PLATFORM,
            'subject' => (string) $payload['subject'],
            'body' => (string) $payload['body'],
            'default_subject' => (string) $payload['subject'],
            'default_body' => (string) $payload['body'],
            'variables' => [],
            'enabled' => (bool) ($payload['enabled'] ?? true),
        ]);

        return redirect()
            ->route('Platform.platform-email-templates.edit', $template)
            ->with('success', 'Template de Email Platform criado com sucesso.');
    }

    public function show(PlatformEmailTemplate $platformEmailTemplate): View
    {
        $this->authorize('view', $platformEmailTemplate);

        return view('platform.platform_email_templates.show', [
            'template' => $platformEmailTemplate,
        ]);
    }

    public function edit(PlatformEmailTemplate $platformEmailTemplate): View
    {
        $this->authorize('update', $platformEmailTemplate);

        return view('platform.platform_email_templates.edit', [
            'template' => $platformEmailTemplate,
        ]);
    }

    public function update(
        UpdatePlatformEmailTemplateRequest $request,
        PlatformEmailTemplate $platformEmailTemplate
    ): RedirectResponse {
        $this->authorize('update', $platformEmailTemplate);

        $platformEmailTemplate->update($request->validated());

        return redirect()
            ->route('Platform.platform-email-templates.edit', $platformEmailTemplate)
            ->with('success', 'Template de Email Platform atualizado com sucesso.');
    }

    public function restore(PlatformEmailTemplate $platformEmailTemplate): RedirectResponse
    {
        $this->authorize('restore', $platformEmailTemplate);

        $platformEmailTemplate->update([
            'subject' => $platformEmailTemplate->default_subject,
            'body' => $platformEmailTemplate->default_body,
        ]);

        return redirect()
            ->route('Platform.platform-email-templates.index')
            ->with('success', 'Template de Email Platform restaurado para o padrão.');
    }

    public function toggle(PlatformEmailTemplate $platformEmailTemplate): RedirectResponse
    {
        $this->authorize('toggle', $platformEmailTemplate);

        $platformEmailTemplate->update([
            'enabled' => !$platformEmailTemplate->enabled,
        ]);

        return redirect()
            ->route('Platform.platform-email-templates.index')
            ->with(
                'success',
                $platformEmailTemplate->enabled
                    ? 'Template de Email Platform ativado com sucesso.'
                    : 'Template de Email Platform inativado com sucesso.'
            );
    }

    public function testSend(Request $request, PlatformEmailTemplate $platformEmailTemplate): RedirectResponse
    {
        $this->authorize('update', $platformEmailTemplate);

        $validated = $request->validate([
            'destination_email' => ['required', 'email'],
            'test_send_modal' => ['nullable', 'string'],
        ]);

        try {
            $this->testSendService->send(
                $platformEmailTemplate,
                (string) $validated['destination_email']
            );

            return redirect()
                ->route('Platform.platform-email-templates.show', $platformEmailTemplate)
                ->with('success', 'Email de teste enviado com sucesso.');
        } catch (Throwable $e) {
            report($e);

            return redirect()
                ->route('Platform.platform-email-templates.show', $platformEmailTemplate)
                ->withInput()
                ->withErrors([
                    'destination_email' => 'Não foi possível enviar o e-mail de teste. Tente novamente.',
                ]);
        }
    }
}
