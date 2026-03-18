@extends('layouts.freedash.app')
@section('title', 'WhatsApp Oficial - Template Oficial Tenant')

@php
    $statusBadge = [
        'approved' => 'success',
        'pending' => 'warning',
        'draft' => 'secondary',
        'rejected' => 'danger',
        'archived' => 'dark',
    ];
    $isApprovedForManualTest = $template->status === \App\Models\Platform\WhatsAppOfficialTemplate::STATUS_APPROVED;
    $isAuthenticationTemplate = in_array(strtoupper((string) $template->category), ['SECURITY', 'AUTHENTICATION'], true);
    $matchedRemoteTemplate = null;
    $remoteBodyParamsExpected = 0;
    $remoteBodyPlaceholderOrder = [];
    $remoteButtonParamsExpected = 0;
    $remoteButtonRequirements = [];
    if (is_array($template->meta_response) && is_array($template->meta_response['data'] ?? null)) {
        $targetName = strtolower(trim((string) $template->meta_template_name));
        $targetLanguage = strtolower(trim((string) $template->language));

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
                    $placeholders = [];
                    foreach ((array) ($matches[1] ?? []) as $placeholder) {
                        $normalizedPlaceholder = trim((string) $placeholder);
                        if ($normalizedPlaceholder === '' || in_array($normalizedPlaceholder, $placeholders, true)) {
                            continue;
                        }
                        $placeholders[] = $normalizedPlaceholder;
                    }
                    $remoteBodyPlaceholderOrder = $placeholders;
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
    $remoteTemplateFound = is_array($matchedRemoteTemplate);
    $remoteTemplateStatus = $remoteTemplateFound
        ? strtoupper(trim((string) ($matchedRemoteTemplate['status'] ?? 'PENDING')))
        : null;
    $isMetaApprovedSnapshot = $remoteTemplateFound && $remoteTemplateStatus === 'APPROVED';
    $isReadyForManualTest = $isApprovedForManualTest && $isMetaApprovedSnapshot;
    $manualTestBlockedReason = null;
    if (!$isApprovedForManualTest) {
        $manualTestBlockedReason = 'Template local precisa estar com status APPROVED para liberar teste manual.';
    } elseif (!$remoteTemplateFound) {
        $manualTestBlockedReason = 'Template nao localizado no snapshot atual da Meta para o nome/idioma configurados. Execute sincronizacao para confirmar o nome canonico aprovado.';
    } elseif (!$isMetaApprovedSnapshot) {
        $manualTestBlockedReason = 'Template encontrado na Meta com status ' . $remoteTemplateStatus . '. Aguarde APPROVED.';
    }
    $hasKnownRemoteReference = !empty($template->meta_template_id)
        || !empty($template->meta_waba_id)
        || !empty($template->last_synced_at)
        || is_array($template->meta_response);
    $isRepublishCandidate = $template->status !== \App\Models\Platform\WhatsAppOfficialTemplate::STATUS_DRAFT
        && $hasKnownRemoteReference
        && !$remoteTemplateFound;
    $variableMap = is_array($template->variables) ? $template->variables : [];
    $sampleMap = is_array($template->sample_variables) ? $template->sample_variables : [];
    uksort($variableMap, static fn ($a, $b) => (int) $a <=> (int) $b);
    uksort($sampleMap, static fn ($a, $b) => (int) $a <=> (int) $b);
    $localVariableValues = array_values(array_unique(array_values($variableMap)));
    $localSampleValues = array_values($sampleMap);
    $sampleByVariableName = [];
    foreach ($variableMap as $placeholder => $variableName) {
        $normalizedVarName = trim((string) $variableName);
        if ($normalizedVarName === '' || isset($sampleByVariableName[$normalizedVarName])) {
            continue;
        }
        $sampleByVariableName[$normalizedVarName] = (string) ($sampleMap[(string) $placeholder] ?? '');
    }

    $semanticCandidatesByKey = [
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
    ];

    $semanticSlots = $semanticCandidatesByKey[(string) $template->key] ?? [];
    $remoteBodyText = '';
    if (is_array($matchedRemoteTemplate)) {
        foreach ((array) ($matchedRemoteTemplate['components'] ?? []) as $component) {
            if (!is_array($component)) {
                continue;
            }
            if (strtoupper((string) ($component['type'] ?? '')) === 'BODY') {
                $remoteBodyText = (string) ($component['text'] ?? '');
                break;
            }
        }
    }
    $orderedVariablesLocal = [];
    foreach ($variableMap as $placeholder => $variableName) {
        $orderedVariablesLocal[] = [
            'placeholder' => (string) $placeholder,
            'name' => (string) $variableName,
            'sample' => (string) ($sampleMap[(string) $placeholder] ?? ''),
        ];
    }
    $orderedVariables = $orderedVariablesLocal;
    $isUtilityTemplate = !$isAuthenticationTemplate;
    $isRemoteSchemaApplied = false;
    if ($isUtilityTemplate && $remoteTemplateFound && $remoteBodyPlaceholderOrder !== []) {
        $remoteOrderedVariables = [];
        $usedVariableNames = [];
        foreach ($remoteBodyPlaceholderOrder as $index => $remotePlaceholder) {
            $resolvedName = '';

            foreach ((array) ($semanticSlots[$index] ?? []) as $candidateName) {
                $normalizedCandidate = trim((string) $candidateName);
                if (
                    $normalizedCandidate !== ''
                    && in_array($normalizedCandidate, $localVariableValues, true)
                    && !in_array($normalizedCandidate, $usedVariableNames, true)
                ) {
                    $resolvedName = $normalizedCandidate;
                    break;
                }
            }

            if ($resolvedName === '') {
                $candidateByPlaceholder = trim((string) ($variableMap[(string) $remotePlaceholder] ?? ''));
                if ($candidateByPlaceholder !== '' && !in_array($candidateByPlaceholder, $usedVariableNames, true)) {
                    $resolvedName = $candidateByPlaceholder;
                }
            }

            if ($resolvedName === '' && isset($localVariableValues[$index])) {
                $fallbackByOrder = trim((string) $localVariableValues[$index]);
                if ($fallbackByOrder !== '' && !in_array($fallbackByOrder, $usedVariableNames, true)) {
                    $resolvedName = $fallbackByOrder;
                }
            }

            if ($resolvedName === '') {
                foreach ($localVariableValues as $fallbackVarName) {
                    if (!in_array($fallbackVarName, $usedVariableNames, true)) {
                        $resolvedName = $fallbackVarName;
                        break;
                    }
                }
            }

            if ($resolvedName === '') {
                continue;
            }

            $usedVariableNames[] = $resolvedName;

            $resolvedSample = (string) (
                $sampleByVariableName[$resolvedName]
                ?? $sampleMap[(string) $remotePlaceholder]
                ?? ($localSampleValues[$index] ?? '')
            );
            $remoteOrderedVariables[] = [
                'placeholder' => (string) $remotePlaceholder,
                'name' => $resolvedName,
                'sample' => $resolvedSample,
            ];
        }

        if ($remoteOrderedVariables !== []) {
            $orderedVariables = $remoteOrderedVariables;
            $isRemoteSchemaApplied = true;
        }
    }
    $localVariableCount = count($orderedVariablesLocal);
    $effectiveVariableCount = count($orderedVariables);
    $hasSchemaDivergence = $isUtilityTemplate
        && $remoteTemplateFound
        && $remoteBodyParamsExpected > 0
        && $localVariableCount !== $remoteBodyParamsExpected;
@endphp

@section('content')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-7 align-self-center">
                <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">WhatsApp Oficial - Template Oficial Tenant</h4>
                <div class="d-flex align-items-center">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb m-0 p-0">
                            <li class="breadcrumb-item"><a href="{{ route('Platform.dashboard') }}" class="text-muted">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('Platform.whatsapp-official-tenant-templates.index') }}" class="text-muted">Templates Oficiais Tenant</a></li>
                            <li class="breadcrumb-item text-muted active" aria-current="page">{{ $template->key }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
            <div class="col-5 align-self-center text-end">
                @can('update', \App\Models\Platform\WhatsAppOfficialTenantTemplate::class)
                    <a href="{{ route('Platform.whatsapp-official-tenant-templates.edit', $template) }}" class="btn btn-outline-warning">
                        <i class="fas fa-edit me-1"></i> Editar
                    </a>
                @endcan
                <a href="{{ route('Platform.whatsapp-official-tenant-templates.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Voltar
                </a>
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
            Baseline oficial tenant. Este teste usa template Meta oficial e provider whatsapp_business.
        </div>
        <div class="alert alert-warning">
            Envio de teste no oficial requer template existente e <strong>APPROVED</strong> na Meta para este nome/idioma.
        </div>
        @if($isRepublishCandidate)
            <div class="alert alert-warning">
                O vinculo remoto anterior nao foi localizado na Meta. Use <strong>Publicar novamente na Meta</strong> para recriar o template remoto com base no cadastro local atual, preservando este registro.
            </div>
        @endif

        <div class="d-flex gap-2 flex-wrap mb-3">
            @can('submitToMeta', \App\Models\Platform\WhatsAppOfficialTenantTemplate::class)
                @if($isRepublishCandidate)
                    <form action="{{ route('Platform.whatsapp-official-tenant-templates.republish', $template) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit"
                            class="btn btn-warning"
                            onclick="return confirm('Esta acao vai recriar o template na Meta usando o cadastro local atual, sem apagar o registro local. Deseja continuar?');">
                            <i class="fas fa-rotate me-1"></i> Publicar novamente na Meta
                        </button>
                    </form>
                @else
                    <form action="{{ route('Platform.whatsapp-official-tenant-templates.submit', $template) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane me-1"></i> Enviar para Meta
                        </button>
                    </form>
                @endif
            @endcan

            @can('syncStatus', \App\Models\Platform\WhatsAppOfficialTenantTemplate::class)
                <form action="{{ route('Platform.whatsapp-official-tenant-templates.sync', $template) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-info text-white">
                        <i class="fas fa-rotate me-1"></i> Sincronizar status na Meta
                    </button>
                </form>
            @endcan

            @can('testSend', \App\Models\Platform\WhatsAppOfficialTenantTemplate::class)
                <button type="button"
                    class="btn {{ $isReadyForManualTest ? 'btn-success' : 'btn-outline-secondary' }}"
                    data-bs-toggle="modal"
                    data-bs-target="#manualTemplateTestModal">
                    <i class="fas fa-vial me-1"></i> Testar template
                </button>
            @endcan
        </div>

        <div class="card shadow-sm border-0 mb-3">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <strong>Key</strong>
                        <div><code>{{ $template->key }}</code></div>
                    </div>
                    <div class="col-md-4">
                        <strong>Evento</strong>
                        <div>{{ $eventLabels[$template->key] ?? '-' }}</div>
                    </div>
                    <div class="col-md-4">
                        <strong>Nome Meta</strong>
                        <div>{{ $template->meta_template_name }}</div>
                    </div>
                    <div class="col-md-3">
                        <strong>Provider</strong>
                        <div>{{ $template->provider }}</div>
                    </div>
                    <div class="col-md-3">
                        <strong>Categoria</strong>
                        <div>{{ $template->category }}</div>
                    </div>
                    <div class="col-md-2">
                        <strong>Idioma</strong>
                        <div>{{ $template->language }}</div>
                    </div>
                    <div class="col-md-2">
                        <strong>Status</strong>
                        <div>
                            <span class="badge bg-{{ $statusBadge[(string) $template->status] ?? 'secondary' }}">
                                {{ strtoupper((string) $template->status) }}
                            </span>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <strong>Versao</strong>
                        <div>v{{ (int) $template->version }}</div>
                    </div>
                    <div class="col-md-4">
                        <strong>Ultima sincronizacao</strong>
                        <div>{{ $template->last_synced_at?->format('d/m/Y H:i:s') ?? '-' }}</div>
                    </div>
                    <div class="col-md-4">
                        <strong>Meta template ID</strong>
                        <div>{{ $template->meta_template_id ?: '-' }}</div>
                    </div>
                    <div class="col-md-4">
                        <strong>Meta WABA ID</strong>
                        <div>{{ $template->meta_waba_id ?: '-' }}</div>
                    </div>
                    <div class="col-md-4">
                        <strong>Status remoto Meta</strong>
                        <div>{{ $remoteTemplateStatus ?: 'NAO ENCONTRADO' }}</div>
                    </div>
                    <div class="col-md-4">
                        <strong>Apto para teste</strong>
                        <div>
                            <span class="badge bg-{{ $isReadyForManualTest ? 'success' : 'secondary' }}">
                                {{ $isReadyForManualTest ? 'SIM' : 'NAO' }}
                            </span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <strong>Disponibilidade remota</strong>
                        <div>{{ $remoteTemplateFound ? 'Template localizado na Meta' : 'Template nao localizado na Meta' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0 mb-3">
            <div class="card-header bg-light"><strong>Body</strong></div>
            <div class="card-body">
                <pre class="bg-light border rounded p-2 mb-0" style="white-space: pre-wrap;">{{ $template->body_text ?: '-' }}</pre>
            </div>
        </div>

        <div class="card shadow-sm border-0 mb-3">
            <div class="card-header bg-light"><strong>Variables</strong></div>
            <div class="card-body">
                <pre class="bg-light border rounded p-2 mb-0">{{ json_encode($template->variables ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
            </div>
        </div>

        <div class="card shadow-sm border-0 mb-3">
            <div class="card-header bg-light"><strong>Sample Variables</strong></div>
            <div class="card-body">
                <pre class="bg-light border rounded p-2 mb-0">{{ json_encode($template->sample_variables ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-light"><strong>Historico de versoes</strong></div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-striped table-bordered align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Versao</th>
                                <th>Status</th>
                                <th>Meta Name</th>
                                <th>Ultima sincronizacao</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($versions as $version)
                                <tr>
                                    <td>v{{ (int) $version->version }}</td>
                                    <td>
                                        <span class="badge bg-{{ $statusBadge[(string) $version->status] ?? 'secondary' }}">
                                            {{ strtoupper((string) $version->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $version->meta_template_name }}</td>
                                    <td>{{ $version->last_synced_at?->format('d/m/Y H:i') ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @can('testSend', \App\Models\Platform\WhatsAppOfficialTenantTemplate::class)
    <div class="modal fade" id="manualTemplateTestModal" tabindex="-1" aria-labelledby="manualTemplateTestModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="manualTemplateTestModalLabel">Teste Manual - {{ $template->key }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <div id="manual-template-test-feedback" class="alert d-none"></div>

                    @if(!$isReadyForManualTest)
                        <div class="alert alert-warning">
                            {{ $manualTestBlockedReason }}
                        </div>
                    @else
                        <div class="alert alert-info">
                            Preencha as variaveis e envie o teste para um numero valido.
                        </div>
                        @if($isUtilityTemplate && $remoteTemplateFound && $isRemoteSchemaApplied)
                            <div class="alert alert-secondary">
                                O envio de teste segue o schema remoto aprovado da Meta.
                                Serão enviados {{ $effectiveVariableCount }} parametro(s) de BODY na ordem remota.
                                @if($remoteBodyText !== '')
                                    <div class="mt-2">
                                        <strong>Body remoto efetivo:</strong>
                                        <pre class="bg-light border rounded p-2 mt-1 mb-0" style="white-space: pre-wrap;">{{ $remoteBodyText }}</pre>
                                    </div>
                                @endif
                                @if($hasSchemaDivergence)
                                    <div class="mt-2">
                                        Divergencia detectada: local={{ $localVariableCount }} parametro(s) e remoto={{ $remoteBodyParamsExpected }}.
                                        Campos excedentes locais não serão enviados neste teste.
                                    </div>
                                @endif
                            </div>
                        @endif
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
                        <h6 class="mb-0">{{ $isRemoteSchemaApplied ? 'Variaveis efetivas para envio (schema remoto)' : 'Variaveis do template' }}</h6>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="manual-template-fill-fake-btn">
                            <i class="fas fa-wand-magic-sparkles me-1"></i> Preencher com dados ficticios
                        </button>
                    </div>

                    <div id="manual-template-variables-container" class="border rounded p-3 bg-light"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-success" id="manual-template-send-btn" {{ !$isReadyForManualTest ? 'disabled' : '' }}>
                        <i class="fas fa-paper-plane me-1"></i> Enviar teste
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endcan

    @include('layouts.freedash.footer')
@endsection

@can('testSend', \App\Models\Platform\WhatsAppOfficialTenantTemplate::class)
@push('scripts')
<script>
    (function () {
        const canManualTest = @json($isReadyForManualTest);
        const manualTestBlockedReason = @json($manualTestBlockedReason);
        const isAuthenticationTemplate = @json($isAuthenticationTemplate);
        const authRemoteBodyParamsExpected = @json($remoteBodyParamsExpected);
        const authRemoteButtonParamsExpected = @json($remoteButtonParamsExpected);
        const authRemoteTotalParamsExpected = authRemoteBodyParamsExpected + authRemoteButtonParamsExpected;
        const variables = @json($orderedVariables);
        const endpoint = @json(route('Platform.whatsapp-official-tenant-templates.test-send', $template));
        const csrfToken = @json(csrf_token());

        const feedbackEl = document.getElementById('manual-template-test-feedback');
        const variablesContainer = document.getElementById('manual-template-variables-container');
        const fillFakeBtn = document.getElementById('manual-template-fill-fake-btn');
        const sendBtn = document.getElementById('manual-template-send-btn');
        const phoneInput = document.getElementById('manual-template-test-phone');

        if (!variablesContainer || !sendBtn || !phoneInput || !feedbackEl) {
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
            if (key === 'appointment_date') return futureDate(3);
            if (key === 'appointment_time') return randomFrom(['09:00', '14:30', '18:00']);
            if (key === 'appointment_details_link') return 'https://app.allsync.com.br/agendamento/detalhes/abc123';
            if (key === 'appointment_confirm_link') return 'https://app.allsync.com.br/agendamento/confirmar/abc123';
            if (key === 'patient_name') return randomFrom(['Ana Souza', 'Pedro Lima', 'Carla Nunes']);
            if (key === 'waitlist_offer_link') return 'https://app.allsync.com.br/waitlist/oferta/abc123';
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

            if (!canManualTest) {
                showFeedback('error', manualTestBlockedReason || 'Envio bloqueado: template ainda nao esta apto para teste na Meta.');
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
                sendBtn.disabled = !canManualTest;
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
