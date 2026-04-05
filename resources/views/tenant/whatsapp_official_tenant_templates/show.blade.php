@extends('layouts.tailadmin.app')

@section('title', 'Template Oficial')

@php
    $statusBadge = [
        'approved' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300',
        'pending' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300',
        'draft' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200',
        'rejected' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300',
        'archived' => 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-200',
    ];
    $variables = is_array($template->variables) ? $template->variables : [];
    uksort($variables, static fn ($a, $b) => (int) $a <=> (int) $b);
    $sampleVariables = is_array($template->sample_variables) ? $template->sample_variables : [];
@endphp

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Template Oficial</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $eventLabels[$template->key] ?? $template->key }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ workspace_route('tenant.settings.whatsapp-official-tenant-templates.edit', $template->id) }}"
               class="inline-flex items-center rounded-lg border border-yellow-300 px-4 py-2 text-sm font-medium text-yellow-700 hover:bg-yellow-50 dark:border-yellow-700 dark:text-yellow-300 dark:hover:bg-yellow-900/20">
                Editar
            </a>
            <a href="{{ workspace_route('tenant.settings.whatsapp-official-tenant-templates.index') }}"
               class="inline-flex items-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700">
                Voltar
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 dark:border-green-800 dark:bg-green-900/20 dark:text-green-300">
            {{ session('success') }}
        </div>
    @endif

    @if(session('warning'))
        <div class="rounded-lg border border-yellow-200 bg-yellow-50 px-4 py-3 text-sm text-yellow-800 dark:border-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-300">
            {{ session('warning') }}
        </div>
    @endif

    @if($errors->any())
        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-800 dark:bg-red-900/20 dark:text-red-300">
            <ul class="list-disc pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="flex flex-wrap gap-2">
        <form method="POST" action="{{ workspace_route('tenant.settings.whatsapp-official-tenant-templates.submit', $template->id) }}">
            @csrf
            <button type="submit" class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                Enviar para Meta
            </button>
        </form>
        <form method="POST" action="{{ workspace_route('tenant.settings.whatsapp-official-tenant-templates.sync', $template->id) }}">
            @csrf
            <button type="submit" class="inline-flex items-center rounded-lg border border-blue-300 px-4 py-2 text-sm font-medium text-blue-700 hover:bg-blue-50 dark:border-blue-700 dark:text-blue-300 dark:hover:bg-blue-900/20">
                Sincronizar status
            </button>
        </form>
        <form method="POST" action="{{ workspace_route('tenant.settings.whatsapp-official-tenant-templates.republish', $template->id) }}">
            @csrf
            <button type="submit" class="inline-flex items-center rounded-lg border border-amber-300 px-4 py-2 text-sm font-medium text-amber-700 hover:bg-amber-50 dark:border-amber-700 dark:text-amber-300 dark:hover:bg-amber-900/20">
                Republicar
            </button>
        </form>
        <button type="button" data-open-manual-test
                class="inline-flex items-center rounded-lg border border-green-300 px-4 py-2 text-sm font-medium text-green-700 hover:bg-green-50 dark:border-green-700 dark:text-green-300 dark:hover:bg-green-900/20">
            Testar envio
        </button>
    </div>

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="text-xs uppercase text-gray-500 dark:text-gray-400">Evento</div>
            <div class="mt-1 font-medium text-gray-900 dark:text-white">{{ $template->key }}</div>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="text-xs uppercase text-gray-500 dark:text-gray-400">Nome Meta</div>
            <div class="mt-1 font-medium text-gray-900 dark:text-white">{{ $template->meta_template_name }}</div>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="text-xs uppercase text-gray-500 dark:text-gray-400">Status</div>
            <div class="mt-1">
                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium {{ $statusBadge[(string) $template->status] ?? 'bg-gray-100 text-gray-700' }}">
                    {{ strtoupper((string) $template->status) }}
                </span>
            </div>
        </div>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <h2 class="mb-3 text-base font-semibold text-gray-900 dark:text-white">Body</h2>
        <pre class="whitespace-pre-wrap rounded-lg border border-gray-200 bg-gray-50 p-3 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-900/40 dark:text-gray-200">{{ $template->body_text ?: '-' }}</pre>
    </div>

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <h2 class="mb-3 text-base font-semibold text-gray-900 dark:text-white">Variables</h2>
            <pre class="overflow-auto rounded-lg border border-gray-200 bg-gray-50 p-3 text-xs text-gray-700 dark:border-gray-700 dark:bg-gray-900/40 dark:text-gray-200">{{ json_encode($variables, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <h2 class="mb-3 text-base font-semibold text-gray-900 dark:text-white">Sample Variables</h2>
            <pre class="overflow-auto rounded-lg border border-gray-200 bg-gray-50 p-3 text-xs text-gray-700 dark:border-gray-700 dark:bg-gray-900/40 dark:text-gray-200">{{ json_encode($sampleVariables, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
        </div>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <h2 class="mb-3 text-base font-semibold text-gray-900 dark:text-white">Histórico de versões</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900/40">
                    <tr>
                        <th class="px-3 py-2 text-left font-semibold text-gray-600 dark:text-gray-300">Versão</th>
                        <th class="px-3 py-2 text-left font-semibold text-gray-600 dark:text-gray-300">Status</th>
                        <th class="px-3 py-2 text-left font-semibold text-gray-600 dark:text-gray-300">Meta Name</th>
                        <th class="px-3 py-2 text-left font-semibold text-gray-600 dark:text-gray-300">Atualizado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($versions as $version)
                    <tr>
                        <td class="px-3 py-2 text-gray-700 dark:text-gray-200">v{{ (int) $version->version }}</td>
                        <td class="px-3 py-2">
                            <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium {{ $statusBadge[(string) $version->status] ?? 'bg-gray-100 text-gray-700' }}">
                                {{ strtoupper((string) $version->status) }}
                            </span>
                        </td>
                        <td class="px-3 py-2 text-gray-700 dark:text-gray-200">{{ $version->meta_template_name }}</td>
                        <td class="px-3 py-2 text-gray-700 dark:text-gray-200">{{ $version->updated_at?->format('d/m/Y H:i') ?? '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60 p-4" id="manual-test-modal">
    <div class="w-full max-w-2xl rounded-xl border border-gray-200 bg-white p-5 shadow-xl dark:border-gray-700 dark:bg-gray-800">
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Teste Manual de Envio</h3>
            <button type="button" data-close-manual-test class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">Fechar</button>
        </div>

        <div id="manual-test-feedback" class="mb-3 hidden rounded-lg border px-3 py-2 text-sm"></div>

        <div class="space-y-3">
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Número de destino</label>
                <input type="text" id="manual-test-phone" class="w-full rounded-lg border border-gray-300 px-3 py-2 dark:border-gray-600 dark:bg-gray-700 dark:text-white" placeholder="Ex: 5511999999999">
            </div>

            <div class="space-y-3" id="manual-test-vars">
                @forelse($variables as $placeholder => $variableName)
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ '{' . '{' . $placeholder . '}' . '}' }} - {{ $variableName }}
                        </label>
                        <input type="text"
                               class="manual-test-var w-full rounded-lg border border-gray-300 px-3 py-2 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                               data-variable-name="{{ $variableName }}"
                               value="{{ $sampleVariables[(string) $placeholder] ?? '' }}">
                    </div>
                @empty
                    <p class="text-sm text-gray-500 dark:text-gray-400">Este template não possui variáveis mapeadas.</p>
                @endforelse
            </div>

            <div class="pt-2">
                <button type="button" id="manual-test-send-btn"
                        class="inline-flex items-center rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700">
                    Enviar teste
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        const modal = document.getElementById('manual-test-modal');
        const openBtn = document.querySelector('[data-open-manual-test]');
        const closeBtn = document.querySelector('[data-close-manual-test]');
        const sendBtn = document.getElementById('manual-test-send-btn');
        const feedback = document.getElementById('manual-test-feedback');
        const phoneInput = document.getElementById('manual-test-phone');
        const csrfToken = @json(csrf_token());
        const endpoint = @json(workspace_route('tenant.settings.whatsapp-official-tenant-templates.test-send', $template->id));

        if (!modal || !openBtn || !closeBtn || !sendBtn || !phoneInput || !feedback) {
            return;
        }

        const showFeedback = (ok, message) => {
            feedback.classList.remove('hidden', 'border-green-200', 'bg-green-50', 'text-green-700', 'border-red-200', 'bg-red-50', 'text-red-700');
            if (ok) {
                feedback.classList.add('border-green-200', 'bg-green-50', 'text-green-700');
            } else {
                feedback.classList.add('border-red-200', 'bg-red-50', 'text-red-700');
            }
            feedback.textContent = message;
        };

        const collectVariables = () => {
            const payload = {};
            document.querySelectorAll('.manual-test-var').forEach((input) => {
                const name = (input.getAttribute('data-variable-name') || '').trim();
                const value = (input.value || '').trim();
                if (name !== '' && value !== '') {
                    payload[name] = value;
                }
            });
            return payload;
        };

        openBtn.addEventListener('click', () => {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            feedback.classList.add('hidden');
        });

        closeBtn.addEventListener('click', () => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        });

        sendBtn.addEventListener('click', async () => {
            const phone = (phoneInput.value || '').trim();
            if (!phone) {
                showFeedback(false, 'Informe o número de destino.');
                return;
            }

            sendBtn.disabled = true;
            const oldText = sendBtn.textContent;
            sendBtn.textContent = 'Enviando...';

            try {
                const response = await fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({
                        phone,
                        variables: collectVariables(),
                    }),
                });

                const data = await response.json().catch(() => ({}));
                if (response.ok && data.success) {
                    showFeedback(true, data.message || 'Teste enviado com sucesso.');
                    return;
                }

                showFeedback(false, data.message || 'Falha ao enviar teste.');
            } catch (error) {
                showFeedback(false, 'Erro de comunicação ao enviar teste.');
            } finally {
                sendBtn.disabled = false;
                sendBtn.textContent = oldText;
            }
        });
    })();
</script>
@endpush

