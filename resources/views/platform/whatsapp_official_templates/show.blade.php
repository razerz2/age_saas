@extends('layouts.freedash.app')
@section('title', 'Detalhes do Template WhatsApp Oficial')

@php
    $badgeMap = [
        'draft' => 'secondary',
        'pending' => 'warning',
        'approved' => 'success',
        'rejected' => 'danger',
        'archived' => 'dark',
    ];
    $eventDescriptions = [
        'invoice.created' => 'Fatura criada',
        'invoice.upcoming_due' => 'Lembrete de vencimento',
        'invoice.overdue' => 'Fatura vencida',
        'tenant.suspended_due_to_overdue' => 'Suspensao por inadimplencia',
        'security.2fa_code' => 'Codigo de verificacao (2FA)',
        'tenant.welcome' => 'Boas-vindas ao tenant',
        'subscription.created' => 'Assinatura criada',
        'subscription.recovery_started' => 'Recovery de assinatura iniciado',
        'credentials.resent' => 'Reenvio de credenciais',
    ];
    $isApprovedForManualTest = $template->status === \App\Models\Platform\WhatsAppOfficialTemplate::STATUS_APPROVED;
    $isAuthenticationTemplate = in_array(strtoupper((string) $template->category), ['SECURITY', 'AUTHENTICATION'], true);
    $remoteBodyParamsExpected = 0;
    $remoteButtonParamsExpected = 0;
    $remoteButtonRequirements = [];
    if (is_array($template->meta_response) && is_array($template->meta_response['data'] ?? null)) {
        $targetName = strtolower(trim((string) $template->meta_template_name));
        $targetLanguage = strtolower(trim((string) $template->language));
        $matchedRemoteTemplate = null;

        foreach ((array) $template->meta_response['data'] as $remoteItem) {
            if (!is_array($remoteItem)) {
                continue;
            }

            $remoteName = strtolower(trim((string) ($remoteItem['name'] ?? '')));
            $remoteLanguage = strtolower(trim((string) ($remoteItem['language'] ?? $remoteItem['locale'] ?? '')));
            if ($remoteName !== $targetName || $remoteLanguage !== $targetLanguage) {
                continue;
            }

            $matchedRemoteTemplate = $remoteItem;
            break;
        }

        if (is_array($matchedRemoteTemplate)) {
            foreach ((array) ($matchedRemoteTemplate['components'] ?? []) as $component) {
                if (!is_array($component)) {
                    continue;
                }

                $componentType = strtoupper((string) ($component['type'] ?? ''));
                if ($componentType === 'BODY') {
                    preg_match_all('/\{\{(\d+)\}\}/', (string) ($component['text'] ?? ''), $matches);
                    $placeholders = array_values(array_unique($matches[1] ?? []));
                    $remoteBodyParamsExpected = count($placeholders);
                    continue;
                }

                if ($componentType !== 'BUTTONS') {
                    continue;
                }

                foreach ((array) ($component['buttons'] ?? []) as $buttonIndex => $button) {
                    if (!is_array($button)) {
                        continue;
                    }

                    $subType = strtolower(trim((string) ($button['sub_type'] ?? $button['type'] ?? 'url')));
                    $buttonPlaceholders = [];
                    foreach (['url', 'text', 'payload'] as $field) {
                        $fieldValue = trim((string) ($button[$field] ?? ''));
                        if ($fieldValue === '') {
                            continue;
                        }

                        preg_match_all('/\{\{(\d+)\}\}/', $fieldValue, $buttonMatches);
                        $buttonPlaceholders = array_merge($buttonPlaceholders, (array) ($buttonMatches[1] ?? []));
                    }

                    $buttonParamsExpected = count(array_unique($buttonPlaceholders));
                    if ($buttonParamsExpected <= 0) {
                        $exampleValues = [];
                        $example = $button['example'] ?? null;
                        if (is_array($example)) {
                            array_walk_recursive($example, static function ($value) use (&$exampleValues): void {
                                if (is_scalar($value)) {
                                    $normalized = trim((string) $value);
                                    if ($normalized !== '') {
                                        $exampleValues[] = $normalized;
                                    }
                                }
                            });
                        } elseif (is_scalar($example)) {
                            $normalized = trim((string) $example);
                            if ($normalized !== '') {
                                $exampleValues[] = $normalized;
                            }
                        }

                        $buttonParamsExpected = count($exampleValues);
                    }

                    if ($buttonParamsExpected <= 0) {
                        continue;
                    }

                    $remoteButtonParamsExpected += $buttonParamsExpected;
                    $remoteButtonRequirements[] = [
                        'index' => (string) ($button['index'] ?? $buttonIndex),
                        'sub_type' => $subType !== '' ? $subType : 'url',
                        'params' => $buttonParamsExpected,
                    ];
                }
            }
        }
    }
    $variableMap = is_array($template->variables) ? $template->variables : [];
    $sampleMap = is_array($template->sample_variables) ? $template->sample_variables : [];
    uksort($variableMap, static fn ($a, $b) => (int) $a <=> (int) $b);
    $orderedVariables = [];
    foreach ($variableMap as $placeholder => $variableName) {
        $orderedVariables[] = [
            'placeholder' => (string) $placeholder,
            'name' => (string) $variableName,
            'sample' => (string) ($sampleMap[(string) $placeholder] ?? ''),
        ];
    }
@endphp

@section('content')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-7 align-self-center">
                <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">Detalhes do Template WhatsApp Oficial</h4>
                <div class="d-flex align-items-center">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb m-0 p-0">
                            <li class="breadcrumb-item"><a href="{{ route('Platform.dashboard') }}" class="text-muted">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('Platform.whatsapp-official-templates.index') }}" class="text-muted">Templates Oficiais</a></li>
                            <li class="breadcrumb-item text-muted active" aria-current="page">Detalhes</li>
                        </ol>
                    </nav>
                </div>
            </div>
            <div class="col-5 align-self-center text-end">
                <a href="{{ route('Platform.whatsapp-official-templates.index') }}" class="btn btn-outline-secondary">Voltar</a>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('warning'))
            <div class="alert alert-warning">{{ session('warning') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="alert alert-info">
            Este modulo e exclusivo para templates oficiais da Platform (Meta Cloud API).
            Templates operacionais de clinica devem ser gerenciados no Tenant em Settings > Editor.
        </div>
        <div class="alert alert-warning">
            Para envio oficial em runtime, o template precisa estar em <strong>APPROVED</strong> e o provider ativo deve ser
            <code>whatsapp_business</code>. Status atual diferente de approved gera skip de envio com log tecnico.
        </div>

        <div class="card shadow-sm border-0 mb-3">
            <div class="card-body">
                <div class="d-flex gap-2 flex-wrap mb-3">
                    @if($template->isDirectlyEditable())
                        <a href="{{ route('Platform.whatsapp-official-templates.edit', $template) }}" class="btn btn-warning text-white">
                            <i class="fas fa-edit me-1"></i>
                            {{ $template->status === 'rejected' ? 'Ajustar Rejeitado' : 'Editar Draft' }}
                        </a>
                    @endif

                    <form action="{{ route('Platform.whatsapp-official-templates.duplicate', $template) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-secondary">
                            <i class="fas fa-copy me-1"></i> Nova Versão
                        </button>
                    </form>

                    <form action="{{ route('Platform.whatsapp-official-templates.submit', $template) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane me-1"></i> Enviar para Meta
                        </button>
                    </form>

                    <form action="{{ route('Platform.whatsapp-official-templates.sync', $template) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-info text-white">
                            <i class="fas fa-rotate me-1"></i> Sincronizar Status
                        </button>
                    </form>

                    @can('testSend', $template)
                        <button type="button"
                            class="btn {{ $isApprovedForManualTest ? 'btn-success' : 'btn-outline-secondary' }}"
                            data-bs-toggle="modal"
                            data-bs-target="#manualTemplateTestModal">
                            <i class="fas fa-vial me-1"></i> Testar template
                        </button>
                    @endcan

                    @if($template->status !== 'archived')
                        <form action="{{ route('Platform.whatsapp-official-templates.archive', $template) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-dark"
                                onclick="return confirm('Arquivar este template?')">
                                <i class="fas fa-box-archive me-1"></i> Arquivar
                            </button>
                        </form>
                    @endif
                </div>

                <div class="row">
                    <div class="col-md-6 mb-2"><strong>Key interna:</strong> {{ $template->key }}</div>
                    <div class="col-md-6 mb-2"><strong>Nome Meta:</strong> {{ $template->meta_template_name }}</div>
                    <div class="col-md-12 mb-2"><strong>Evento SaaS:</strong> {{ $eventDescriptions[$template->key] ?? '-' }}</div>
                    <div class="col-md-3 mb-2"><strong>Provider:</strong> {{ $template->provider }}</div>
                    <div class="col-md-3 mb-2"><strong>Categoria:</strong> {{ $template->category }}</div>
                    <div class="col-md-3 mb-2"><strong>Idioma:</strong> {{ $template->language }}</div>
                    <div class="col-md-3 mb-2"><strong>Versão:</strong> {{ $template->version }}</div>
                    <div class="col-md-3 mb-2">
                        <strong>Status:</strong>
                        <span class="badge bg-{{ $badgeMap[$template->status] ?? 'secondary' }}">{{ strtoupper($template->status) }}</span>
                    </div>
                    <div class="col-md-3 mb-2"><strong>Última sincronização:</strong> {{ $template->last_synced_at?->format('d/m/Y H:i:s') ?? '-' }}</div>
                    <div class="col-md-3 mb-2"><strong>Meta template ID:</strong> {{ $template->meta_template_id ?: '-' }}</div>
                    <div class="col-md-3 mb-2"><strong>Meta WABA ID:</strong> {{ $template->meta_waba_id ?: '-' }}</div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="card shadow-sm border-0 mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Conteúdo</h5>
                        @if($template->header_text)
                            <p><strong>Header:</strong><br>{{ $template->header_text }}</p>
                        @endif
                        <p><strong>Body:</strong></p>
                        <pre class="p-2 bg-light border rounded">{{ $template->body_text }}</pre>
                        @if($template->footer_text)
                            <p><strong>Footer:</strong><br>{{ $template->footer_text }}</p>
                        @endif
                    </div>
                </div>

                <div class="card shadow-sm border-0 mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Resposta técnica Meta (resumida)</h5>
                        <pre class="p-2 bg-light border rounded">{{ json_encode($template->meta_response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?: '{}' }}</pre>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm border-0 mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Variáveis</h5>
                        <pre class="p-2 bg-light border rounded">{{ json_encode($template->variables, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?: '{}' }}</pre>
                    </div>
                </div>

                <div class="card shadow-sm border-0 mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Exemplos de Variáveis</h5>
                        <pre class="p-2 bg-light border rounded">{{ json_encode($template->sample_variables, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?: '{}' }}</pre>
                    </div>
                </div>

                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="card-title">Versões da key</h5>
                        <ul class="list-group">
                            @foreach($versions as $versionTemplate)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <a href="{{ route('Platform.whatsapp-official-templates.show', $versionTemplate) }}">
                                        v{{ $versionTemplate->version }}
                                    </a>
                                    <span class="badge bg-{{ $badgeMap[$versionTemplate->status] ?? 'secondary' }}">
                                        {{ strtoupper($versionTemplate->status) }}
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @can('testSend', $template)
    <div class="modal fade" id="manualTemplateTestModal" tabindex="-1" aria-labelledby="manualTemplateTestModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="manualTemplateTestModalLabel">Teste Manual - {{ $template->key }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <div id="manual-template-test-feedback" class="alert d-none"></div>

                    @if(!$isApprovedForManualTest)
                        <div class="alert alert-warning">
                            Este template esta em <strong>{{ strtoupper($template->status) }}</strong>.
                            O teste manual so e permitido para status <strong>APPROVED</strong>.
                        </div>
                    @else
                        <div class="alert alert-info">
                            Preencha as variaveis e envie o teste para um numero valido.
                        </div>
                        @if($isAuthenticationTemplate)
                            <div class="alert alert-secondary">
                                Template AUTHENTICATION: o envio de teste segue o schema remoto aprovado da Meta.
                                @if($remoteBodyParamsExpected > 0 || $remoteButtonParamsExpected > 0)
                                    O remoto exige {{ $remoteBodyParamsExpected }} parametro(s) no BODY
                                    e {{ $remoteButtonParamsExpected }} parametro(s) em BUTTONS.
                                    @if($remoteButtonRequirements !== [])
                                        <div class="small mt-2">
                                            @foreach($remoteButtonRequirements as $buttonRequirement)
                                                <div>
                                                    BUTTON index {{ $buttonRequirement['index'] }} ({{ strtoupper($buttonRequirement['sub_type']) }}):
                                                    {{ $buttonRequirement['params'] }} parametro(s).
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                @else
                                    O remoto nao exige parametros dinamicos de BODY/BUTTONS.
                                @endif
                            </div>
                        @endif
                    @endif

                    <div class="mb-3">
                        <label for="manual-template-test-phone" class="form-label">Numero de destino</label>
                        <input type="text" class="form-control" id="manual-template-test-phone" placeholder="Ex: 5511999999999">
                        <small class="text-muted">Use formato internacional com DDI.</small>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0">Variaveis do template</h6>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="manual-template-fill-fake-btn">
                            <i class="fas fa-wand-magic-sparkles me-1"></i> Preencher com dados ficticios
                        </button>
                    </div>

                    <div id="manual-template-variables-container" class="border rounded p-3 bg-light"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-success" id="manual-template-send-btn" {{ !$isApprovedForManualTest ? 'disabled' : '' }}>
                        <i class="fas fa-paper-plane me-1"></i> Enviar teste
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endcan

    @include('layouts.freedash.footer')
@endsection

@can('testSend', $template)
@push('scripts')
<script>
    (function () {
        const isApproved = @json($isApprovedForManualTest);
        const isAuthenticationTemplate = @json($isAuthenticationTemplate);
        const authRemoteBodyParamsExpected = @json($remoteBodyParamsExpected);
        const authRemoteButtonParamsExpected = @json($remoteButtonParamsExpected);
        const authRemoteTotalParamsExpected = authRemoteBodyParamsExpected + authRemoteButtonParamsExpected;
        const variables = @json($orderedVariables);
        const endpoint = @json(route('Platform.whatsapp-official-templates.test-send', $template));
        const csrfToken = @json(csrf_token());

        const feedbackEl = document.getElementById('manual-template-test-feedback');
        const variablesContainer = document.getElementById('manual-template-variables-container');
        const fillFakeBtn = document.getElementById('manual-template-fill-fake-btn');
        const sendBtn = document.getElementById('manual-template-send-btn');
        const phoneInput = document.getElementById('manual-template-test-phone');

        if (!variablesContainer || !sendBtn || !phoneInput) {
            return;
        }

        function clearFeedback() {
            feedbackEl.classList.add('d-none');
            feedbackEl.className = 'alert d-none';
            feedbackEl.textContent = '';
        }

        function showFeedback(type, message) {
            feedbackEl.className = 'alert';
            feedbackEl.classList.add(type === 'success' ? 'alert-success' : 'alert-danger');
            feedbackEl.textContent = message;
        }

        function variableInputId(name, index) {
            return 'manual-var-' + index + '-' + name.replace(/[^a-zA-Z0-9_]/g, '_');
        }

        function renderVariables() {
            if (!Array.isArray(variables) || variables.length === 0) {
                variablesContainer.innerHTML = '<div class="text-muted">Este template nao possui variaveis mapeadas.</div>';
                return;
            }

            const html = variables.map((item, index) => {
                const inputId = variableInputId(item.name, index);
                const placeholderLabel = item.placeholder ? ('{' + '{' + item.placeholder + '}' + '}') : '-';
                const sample = item.sample || '';
                return `
                    <div class="mb-3">
                        <label class="form-label" for="${inputId}">
                            ${placeholderLabel} - ${item.name}
                        </label>
                        <input type="text" class="form-control manual-template-variable-input"
                            id="${inputId}" data-variable-name="${item.name}" data-placeholder="${item.placeholder || ''}"
                            value="${sample.replace(/"/g, '&quot;')}">
                    </div>
                `;
            }).join('');

            variablesContainer.innerHTML = html;
        }

        function randomFrom(list) {
            return list[Math.floor(Math.random() * list.length)];
        }

        function randomMoney(min, max) {
            const value = (Math.random() * (max - min) + min).toFixed(2);
            return 'R$ ' + value.replace('.', ',');
        }

        function futureDate(daysAhead) {
            const d = new Date();
            d.setDate(d.getDate() + daysAhead);
            const day = String(d.getDate()).padStart(2, '0');
            const month = String(d.getMonth() + 1).padStart(2, '0');
            const year = d.getFullYear();
            return `${day}/${month}/${year}`;
        }

        function fakeValueFor(name) {
            const key = String(name || '').toLowerCase();
            if (key === 'customer_name') return randomFrom(['Rafael Souza', 'Marina Lima', 'Carlos Pereira', 'Julia Costa']);
            if (key === 'tenant_name') return randomFrom(['Clinica Boa Vida', 'Instituto Viva', 'Centro Med Prime']);
            if (key === 'invoice_amount') return randomMoney(89, 799);
            if (key === 'due_date') return futureDate(7);
            if (key === 'payment_link') return 'https://app.allsync.com.br/faturas/pagar/teste123';
            if (key === 'code') return String(Math.floor(100000 + Math.random() * 900000));
            if (key === 'expires_in_minutes') return String(randomFrom([5, 10, 15]));
            if (key === 'plan_name') return randomFrom(['Plano Starter', 'Plano Pro', 'Plano Premium']);
            if (key === 'plan_amount') return randomMoney(99, 499);
            if (key === 'login_url') return 'https://app.allsync.com.br/platform/login';
            if (key === 'delivery_channel') return randomFrom(['whatsapp', 'email', 'sistema']);
            return 'Teste ' + name;
        }

        function fillWithFakeData() {
            const inputs = variablesContainer.querySelectorAll('.manual-template-variable-input');
            inputs.forEach((input) => {
                const variableName = input.getAttribute('data-variable-name') || '';
                input.value = fakeValueFor(variableName);
                input.classList.remove('is-invalid');
            });
        }

        function collectVariables() {
            const payload = {};
            const missing = [];
            const inputs = variablesContainer.querySelectorAll('.manual-template-variable-input');

            inputs.forEach((input) => {
                const variableName = input.getAttribute('data-variable-name') || '';
                const value = (input.value || '').trim();
                if (!value && !isAuthenticationTemplate) {
                    input.classList.add('is-invalid');
                    missing.push(variableName);
                    return;
                }
                input.classList.remove('is-invalid');
                if (value !== '') {
                    payload[variableName] = value;
                }
            });

            return { payload, missing };
        }

        async function sendManualTest() {
            clearFeedback();

            if (!isApproved) {
                showFeedback('error', 'Envio bloqueado: este template precisa estar com status APPROVED.');
                return;
            }

            const phone = (phoneInput.value || '').trim();
            if (!phone) {
                phoneInput.classList.add('is-invalid');
                showFeedback('error', 'Informe o numero de destino.');
                return;
            }
            phoneInput.classList.remove('is-invalid');

            const collected = collectVariables();
            const payload = collected.payload;

            if (!isAuthenticationTemplate && collected.missing.length > 0) {
                showFeedback('error', 'Preencha todas as variaveis obrigatorias do template.');
                return;
            }

            if (isAuthenticationTemplate && authRemoteTotalParamsExpected > 0 && Object.keys(payload).length === 0) {
                showFeedback('error', 'Este template AUTHENTICATION exige parametros dinamicos no schema remoto (BODY/BUTTONS). Preencha ao menos a variavel de codigo.');
                return;
            }

            const originalText = sendBtn.innerHTML;
            sendBtn.disabled = true;
            sendBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Enviando...';

            try {
                const response = await fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({
                        phone: phone,
                        variables: payload,
                    }),
                });

                const data = await response.json().catch(() => ({}));
                if (response.ok && data.success) {
                    showFeedback('success', data.message || 'Teste enviado com sucesso.');
                    return;
                }

                let message = data.message || 'Falha ao enviar teste do template.';
                if (data.errors) {
                    const firstErrorField = Object.keys(data.errors)[0];
                    const firstError = Array.isArray(data.errors[firstErrorField]) ? data.errors[firstErrorField][0] : null;
                    if (firstError) {
                        message += ' ' + firstError;
                    }
                }
                showFeedback('error', message);
            } catch (error) {
                showFeedback('error', 'Erro de comunicacao ao enviar teste do template.');
            } finally {
                sendBtn.disabled = !isApproved;
                sendBtn.innerHTML = originalText;
            }
        }

        renderVariables();
        if (fillFakeBtn) {
            fillFakeBtn.addEventListener('click', fillWithFakeData);
        }
        sendBtn.addEventListener('click', sendManualTest);
    })();
</script>
@endpush
@endcan
