@php
    $isEdit = isset($template) && $template->exists;
    $templateStatus = (string) old('status', $template->status ?? 'draft');
    $variablesValue = old('variables');
    if ($variablesValue === null) {
        $variablesValue = !empty($template->variables ?? null)
            ? json_encode($template->variables, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
            : "{}";
    } elseif (is_array($variablesValue)) {
        $variablesValue = json_encode($variablesValue, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    $buttonsValue = old('buttons');
    if ($buttonsValue === null) {
        $buttonsValue = !empty($template->buttons ?? null)
            ? json_encode($template->buttons, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
            : "[]";
    } elseif (is_array($buttonsValue)) {
        $buttonsValue = json_encode($buttonsValue, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    $sampleVariablesValue = old('sample_variables');
    if ($sampleVariablesValue === null) {
        $sampleVariablesValue = !empty($template->sample_variables ?? null)
            ? json_encode($template->sample_variables, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
            : "{}";
    } elseif (is_array($sampleVariablesValue)) {
        $sampleVariablesValue = json_encode($sampleVariablesValue, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    $eventHints = [
        'invoice.created' => 'Fatura criada',
        'invoice.upcoming_due' => 'Lembrete de fatura a vencer',
        'invoice.overdue' => 'Fatura vencida',
        'tenant.suspended_due_to_overdue' => 'Tenant suspenso por inadimplencia',
        'security.2fa_code' => 'Codigo de verificacao (2FA)',
        'tenant.welcome' => 'Boas-vindas ao tenant',
        'subscription.created' => 'Assinatura criada',
        'subscription.recovery_started' => 'Recovery de assinatura iniciado',
        'credentials.resent' => 'Credenciais reenviadas',
    ];
@endphp

<input type="hidden" name="provider" value="whatsapp_business">

<div class="alert alert-info">
    Este modulo e exclusivo para templates oficiais da Platform (Meta Cloud API).
    Nao use keys operacionais de clinica (appointment.*, waitlist.*) aqui.
</div>
<div class="alert alert-warning">
    Em runtime, somente templates com status <strong>approved</strong> sao enviados.
    Draft/pending/rejected nao disparam mensagem oficial.
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Key interna <span class="text-danger">*</span></label>
        <input type="text" name="key" class="form-control" value="{{ old('key', $template->key) }}" required>
        <small class="text-muted">Exemplo: invoice.created</small>
        <div class="mt-2">
            <small class="text-muted d-block">Eventos SaaS sugeridos:</small>
            <ul class="mb-0 ps-3">
                @foreach($eventHints as $eventKey => $eventLabel)
                    <li><small><code>{{ $eventKey }}</code> - {{ $eventLabel }}</small></li>
                @endforeach
            </ul>
        </div>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Nome do template Meta <span class="text-danger">*</span></label>
        <input type="text" name="meta_template_name" class="form-control"
            value="{{ old('meta_template_name', $template->meta_template_name) }}" required>
        <small class="text-muted">Apenas minúsculas, números e underscore.</small>
    </div>
</div>

<div class="row">
    <div class="col-md-4 mb-3">
        <label class="form-label">Provider</label>
        <input type="text" class="form-control bg-light" value="whatsapp_business" readonly>
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Categoria <span class="text-danger">*</span></label>
        <select name="category" class="form-control" required>
            <option value="UTILITY" {{ old('category', $template->category) === 'UTILITY' ? 'selected' : '' }}>UTILITY</option>
            <option value="SECURITY" {{ old('category', $template->category) === 'SECURITY' ? 'selected' : '' }}>SECURITY</option>
        </select>
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Idioma <span class="text-danger">*</span></label>
        <input type="text" name="language" class="form-control" value="{{ old('language', $template->language ?? 'pt_BR') }}" required>
    </div>
</div>

<div class="mb-3">
    <label class="form-label">Header (opcional)</label>
    <textarea name="header_text" class="form-control" rows="2">{{ old('header_text', $template->header_text) }}</textarea>
</div>

<div class="mb-3">
    <label class="form-label">Body <span class="text-danger">*</span></label>
    <textarea id="body_text" name="body_text" class="form-control" rows="10" required>{{ old('body_text', $template->body_text) }}</textarea>
    <small class="text-muted">Use placeholders Meta no formato <code>@{{1}}</code>, <code>@{{2}}</code>, ...</small>
</div>

<div class="mb-3">
    <label class="form-label">Footer (opcional)</label>
    <textarea name="footer_text" class="form-control" rows="2">{{ old('footer_text', $template->footer_text) }}</textarea>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Variáveis (JSON)</label>
        <textarea id="variables_json" name="variables" class="form-control" rows="7">{{ $variablesValue }}</textarea>
        <small class="text-muted">Exemplo: { "1": "tenant.trade_name", "2": "invoice.due_date" }</small>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Buttons (JSON opcional)</label>
        <textarea name="buttons" class="form-control" rows="7">{{ $buttonsValue }}</textarea>
    </div>
</div>

<div class="mb-3">
    <label class="form-label">Exemplos de Variáveis (JSON)</label>
    <textarea name="sample_variables" class="form-control" rows="5">{{ $sampleVariablesValue }}</textarea>
    <small class="text-muted">Obrigatório para envio à Meta quando houver placeholders. Exemplo: { "1": "Rafael", "2": "14/03/2026 às 09:00" }</small>
</div>

@if($isEdit && $templateStatus === 'rejected')
    <div class="alert alert-warning">
        Template rejeitado pela Meta: ajuste o conteúdo e reenvie para nova análise.
    </div>
@endif

@if($isEdit && $templateStatus === 'approved')
    <div class="alert alert-info">
        Templates aprovados na Meta não devem ser editados diretamente. Gere uma nova versão.
    </div>
@endif

<div class="card border">
    <div class="card-body">
        <h5 class="card-title">Preview</h5>
        <pre id="template_preview" class="mb-0">{{ old('body_text', $template->body_text) }}</pre>
    </div>
</div>

<div class="text-end mt-4">
    <button type="submit" class="btn btn-success">
        <i class="fas fa-save me-1"></i> {{ $isEdit ? 'Salvar Alterações' : 'Criar Template' }}
    </button>
    <a href="{{ route('Platform.whatsapp-official-templates.index') }}" class="btn btn-secondary">Cancelar</a>
</div>

@push('scripts')
<script>
    (function() {
        const bodyInput = document.getElementById('body_text');
        const preview = document.getElementById('template_preview');

        function refreshPreview() {
            preview.textContent = bodyInput.value || '';
        }

        if (bodyInput && preview) {
            bodyInput.addEventListener('input', refreshPreview);
            refreshPreview();
        }
    })();
</script>
@endpush
