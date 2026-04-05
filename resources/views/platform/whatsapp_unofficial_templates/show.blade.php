@extends('layouts.freedash.app')
@section('title', 'WhatsApp Não Oficial - Template Interno Platform')

@php
    $requiredVariables = is_array($requiredVariables ?? null) ? $requiredVariables : [];
    $fakeValues = is_array($fakeValues ?? null) ? $fakeValues : [];
@endphp

@section('content')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-7 align-self-center">
                <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">
                    WhatsApp Não Oficial - Template Interno Platform
                </h4>
                <div class="d-flex align-items-center">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb m-0 p-0">
                            <li class="breadcrumb-item"><a href="{{ route('Platform.dashboard') }}" class="text-muted">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('Platform.whatsapp-unofficial-templates.index') }}" class="text-muted">WhatsApp Não Oficial</a></li>
                            <li class="breadcrumb-item text-muted active" aria-current="page">Template Interno Platform</li>
                        </ol>
                    </nav>
                </div>
            </div>
            <div class="col-5 align-self-center">
                <div class="customize-input float-end d-flex gap-2 justify-content-end">
                    @can('testSend', $template)
                        <button type="button" class="btn btn-success shadow-sm" data-bs-toggle="modal" data-bs-target="#manualUnofficialTestModal">
                            <i class="fas fa-vial me-1"></i> Testar mensagem
                        </button>
                    @endcan
                    <a href="{{ route('Platform.whatsapp-unofficial-templates.edit', $template) }}" class="btn btn-warning text-white shadow-sm">
                        <i class="fas fa-edit me-1"></i> Editar
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="alert alert-info">
            Este template pertence ao domínio <strong>WhatsApp Não Oficial</strong> e não depende de cadastro/aprovação na Meta.
            A mensagem final é renderizada internamente e enviada pelo provider não oficial ativo (WAHA/Z-API).
        </div>

        <div class="card shadow-sm border-0 mb-3">
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong>Key:</strong>
                        <div>{{ $template->key }}</div>
                    </div>
                    <div class="col-md-4">
                        <strong>Nome/Título:</strong>
                        <div>{{ $template->title }}</div>
                    </div>
                    <div class="col-md-4">
                        <strong>Categoria:</strong>
                        <div>{{ $template->category }}</div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong>Status:</strong>
                        <div>
                            <span class="badge bg-{{ $template->is_active ? 'success' : 'secondary' }}">
                                {{ $template->is_active ? 'ativo' : 'inativo' }}
                            </span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <strong>Criado em:</strong>
                        <div>{{ $template->created_at?->format('d/m/Y H:i') ?? '-' }}</div>
                    </div>
                    <div class="col-md-4">
                        <strong>Atualizado em:</strong>
                        <div>{{ $template->updated_at?->format('d/m/Y H:i') ?? '-' }}</div>
                    </div>
                </div>

                <div class="mb-3">
                    <strong>Corpo da mensagem:</strong>
                    <pre class="border rounded p-3 mt-2">{{ $template->body }}</pre>
                </div>

                <div class="mb-3">
                    <strong>Variáveis declaradas (JSON):</strong>
                    <pre class="border rounded p-3 mt-2">{{ json_encode($template->variables ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                </div>

                <div class="mb-3">
                    <strong>Variáveis obrigatórias para teste:</strong>
                    @if($requiredVariables === [])
                        <div class="text-muted mt-1">Nenhuma variável obrigatória detectada neste template.</div>
                    @else
                        <div class="mt-2 d-flex flex-wrap gap-1">
                            @foreach($requiredVariables as $variable)
                                <span class="badge bg-light text-dark border">{{ $variable }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="text-end">
                    <form method="POST" action="{{ route('Platform.whatsapp-unofficial-templates.toggle', $template) }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-{{ $template->is_active ? 'secondary' : 'success' }}">
                            <i class="fas fa-power-off me-1"></i>
                            {{ $template->is_active ? 'Inativar' : 'Ativar' }}
                        </button>
                    </form>
                    <a href="{{ route('Platform.whatsapp-unofficial-templates.index') }}" class="btn btn-outline-primary">
                        Voltar
                    </a>
                </div>
            </div>
        </div>
    </div>

    @can('testSend', $template)
        <div class="modal fade" id="manualUnofficialTestModal" tabindex="-1" aria-labelledby="manualUnofficialTestModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="manualUnofficialTestModalLabel">
                            Teste Manual - {{ $template->key }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                    </div>
                    <div class="modal-body">
                        <div id="manual-unofficial-feedback" class="alert d-none"></div>

                        <div class="alert alert-secondary">
                            O preview e o envio usam a mesma engine de renderização do runtime não oficial.
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="manual-unofficial-phone">Telefone de destino</label>
                            <input type="text" class="form-control" id="manual-unofficial-phone" placeholder="Ex: 5511999999999">
                            <small class="text-muted">Use formato internacional com DDI.</small>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="mb-0">Variáveis</h6>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="manual-unofficial-fill-fake-btn">
                                <i class="fas fa-wand-magic-sparkles me-1"></i> Preencher com dados fictícios
                            </button>
                        </div>
                        <div id="manual-unofficial-vars" class="border rounded p-3 bg-light mb-3"></div>

                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="mb-0">Preview renderizado</h6>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="manual-unofficial-preview-btn">
                                Atualizar preview
                            </button>
                        </div>
                        <pre id="manual-unofficial-preview" class="border rounded p-3 bg-white text-wrap"></pre>
                        <div id="manual-unofficial-missing" class="text-danger small mt-2 d-none"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-success" id="manual-unofficial-send-btn" {{ $template->is_active ? '' : 'disabled' }}>
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
        const requiredVariables = @json($requiredVariables);
        const fakeValues = @json($fakeValues);
        const templateIsActive = @json((bool) $template->is_active);
        const previewEndpoint = @json(route('Platform.whatsapp-unofficial-templates.preview', $template));
        const sendEndpoint = @json(route('Platform.whatsapp-unofficial-templates.test-send', $template));
        const csrfToken = @json(csrf_token());

        const feedbackEl = document.getElementById('manual-unofficial-feedback');
        const varsContainer = document.getElementById('manual-unofficial-vars');
        const fillFakeBtn = document.getElementById('manual-unofficial-fill-fake-btn');
        const previewBtn = document.getElementById('manual-unofficial-preview-btn');
        const sendBtn = document.getElementById('manual-unofficial-send-btn');
        const phoneInput = document.getElementById('manual-unofficial-phone');
        const previewOutput = document.getElementById('manual-unofficial-preview');
        const missingEl = document.getElementById('manual-unofficial-missing');

        if (!varsContainer || !previewBtn || !sendBtn || !previewOutput) {
            return;
        }

        function escapeHtml(value) {
            return String(value ?? '')
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        }

        function clearFeedback() {
            feedbackEl.className = 'alert d-none';
            feedbackEl.textContent = '';
        }

        function showFeedback(type, message) {
            feedbackEl.className = 'alert';
            feedbackEl.classList.add(type === 'success' ? 'alert-success' : 'alert-danger');
            feedbackEl.textContent = message;
        }

        function renderVariableInputs() {
            if (!Array.isArray(requiredVariables) || requiredVariables.length === 0) {
                varsContainer.innerHTML = '<div class="text-muted">Este template não possui variáveis obrigatórias.</div>';
                return;
            }

            varsContainer.innerHTML = requiredVariables.map((variable, index) => {
                const id = 'manual-unofficial-var-' + index;
                return `
                    <div class="mb-2">
                        <label class="form-label mb-1" for="${id}">${escapeHtml(variable)}</label>
                        <input type="text" class="form-control manual-unofficial-var-input" id="${id}" data-variable="${escapeHtml(variable)}" />
                    </div>
                `;
            }).join('');
        }

        function collectVariables() {
            const inputs = varsContainer.querySelectorAll('.manual-unofficial-var-input');
            const variables = {};

            inputs.forEach((input) => {
                const key = (input.getAttribute('data-variable') || '').trim();
                if (!key) {
                    return;
                }

                variables[key] = (input.value || '').trim();
            });

            return variables;
        }

        function fillFakeData() {
            const inputs = varsContainer.querySelectorAll('.manual-unofficial-var-input');
            inputs.forEach((input) => {
                const key = (input.getAttribute('data-variable') || '').trim();
                if (!key) {
                    return;
                }

                input.value = fakeValues[key] || ('valor_teste_' + key.replaceAll('.', '_'));
                input.classList.remove('is-invalid');
            });
        }

        function setMissingVariables(missing) {
            if (!Array.isArray(missing) || missing.length === 0) {
                missingEl.classList.add('d-none');
                missingEl.textContent = '';
                return;
            }

            missingEl.classList.remove('d-none');
            missingEl.textContent = 'Variáveis obrigatórias ausentes: ' + missing.join(', ');

            const inputs = varsContainer.querySelectorAll('.manual-unofficial-var-input');
            inputs.forEach((input) => {
                const key = (input.getAttribute('data-variable') || '').trim();
                if (missing.includes(key)) {
                    input.classList.add('is-invalid');
                } else {
                    input.classList.remove('is-invalid');
                }
            });
        }

        async function renderPreview(fillMissingWithFake = false) {
            clearFeedback();

            try {
                const response = await fetch(previewEndpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({
                        variables: collectVariables(),
                        fill_missing_with_fake: fillMissingWithFake,
                    }),
                });

                const data = await response.json().catch(() => ({}));
                if (!response.ok || !data.success) {
                    const message = data.message || 'Falha ao gerar preview.';
                    showFeedback('error', message);
                    if (data.missing_variables) {
                        setMissingVariables(data.missing_variables);
                    }
                    return false;
                }

                previewOutput.textContent = String(data.preview || '');
                setMissingVariables(Array.isArray(data.missing_variables) ? data.missing_variables : []);
                return true;
            } catch (error) {
                showFeedback('error', 'Erro de comunicação ao gerar preview.');
                return false;
            }
        }

        async function sendManualTest() {
            clearFeedback();
            const phone = (phoneInput.value || '').trim();
            if (!phone) {
                phoneInput.classList.add('is-invalid');
                showFeedback('error', 'Informe o telefone de destino.');
                return;
            }
            phoneInput.classList.remove('is-invalid');

            const previewOk = await renderPreview(false);
            if (!previewOk) {
                return;
            }

            const original = sendBtn.innerHTML;
            sendBtn.disabled = true;
            sendBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Enviando...';

            try {
                const response = await fetch(sendEndpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({
                        phone: phone,
                        variables: collectVariables(),
                    }),
                });

                const data = await response.json().catch(() => ({}));
                if (response.ok && data.success) {
                    showFeedback('success', data.message || 'Teste enviado com sucesso.');
                    return;
                }

                setMissingVariables(Array.isArray(data.missing_variables) ? data.missing_variables : []);
                showFeedback('error', data.message || 'Falha ao enviar teste manual.');
            } catch (error) {
                showFeedback('error', 'Erro de comunicação ao enviar teste manual.');
            } finally {
                sendBtn.disabled = false;
                sendBtn.innerHTML = original;
            }
        }

        renderVariableInputs();
        if (templateIsActive) {
            if (requiredVariables.length > 0) {
                fillFakeData();
            }
            renderPreview(true);
        } else {
            previewBtn.disabled = true;
            fillFakeBtn.disabled = true;
        }

        fillFakeBtn?.addEventListener('click', function () {
            fillFakeData();
            renderPreview(false);
        });
        previewBtn.addEventListener('click', function () {
            renderPreview(false);
        });
        sendBtn.addEventListener('click', sendManualTest);
    })();
</script>
@endpush
@endcan
