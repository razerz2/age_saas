@php
    $isEdit = isset($template) && $template->exists;
    $variablesValue = old('variables');
    if ($variablesValue === null) {
        $variablesValue = !empty($template->variables ?? null)
            ? json_encode($template->variables, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
            : "[]";
    } elseif (is_array($variablesValue)) {
        $variablesValue = json_encode($variablesValue, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
@endphp

<div class="alert alert-info">
    Template interno da Platform para o dominio WhatsApp Nao Oficial.
    Nao sao enviados para Meta e nao exigem aprovacao externa.
</div>
<div class="alert alert-secondary">
    Padrao recomendado de variaveis Platform nao oficial: <code>customer_name</code>, <code>tenant_name</code>,
    <code>invoice_amount</code>, <code>due_date</code>, <code>payment_link</code>, <code>plan_name</code>,
    <code>plan_amount</code>, <code>login_url</code>, <code>code</code>, <code>expires_in_minutes</code>.
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Key <span class="text-danger">*</span></label>
        <input type="text" name="key" class="form-control" value="{{ old('key', $template->key) }}" required>
        <small class="text-muted">Exemplo: invoice.overdue</small>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Nome/Titulo <span class="text-danger">*</span></label>
        <input type="text" name="title" class="form-control" value="{{ old('title', $template->title) }}" required>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Categoria <span class="text-danger">*</span></label>
        <input type="text" name="category" class="form-control" value="{{ old('category', $template->category) }}" required>
    </div>
    <div class="col-md-6 d-flex align-items-center">
        <div class="form-check mt-4">
            <input class="form-check-input" type="checkbox" value="1" id="is_active" name="is_active"
                {{ old('is_active', $template->is_active ?? true) ? 'checked' : '' }}>
            <label class="form-check-label" for="is_active">
                Template ativo
            </label>
        </div>
    </div>
</div>

<div class="mb-3">
    <label class="form-label">Corpo da mensagem <span class="text-danger">*</span></label>
    <textarea id="body" name="body" class="form-control" rows="10" required>{{ old('body', $template->body) }}</textarea>
</div>

<div class="mb-3">
    <label class="form-label">Variaveis (JSON)</label>
    <textarea name="variables" class="form-control" rows="6">{{ $variablesValue }}</textarea>
    <small class="text-muted">Exemplo: ["patient.name", "appointment.date"]</small>
</div>

<div class="card border">
    <div class="card-body">
        <h5 class="card-title">Preview (texto final sem envio)</h5>
        <pre id="body_preview" class="mb-0">{{ old('body', $template->body) }}</pre>
    </div>
</div>

<div class="text-end mt-4">
    <button type="submit" class="btn btn-success">
        <i class="fas fa-save me-1"></i> {{ $isEdit ? 'Salvar Alteracoes' : 'Criar Template' }}
    </button>
    <a href="{{ route('Platform.whatsapp-unofficial-templates.index') }}" class="btn btn-secondary">Cancelar</a>
</div>

@push('scripts')
<script>
    (function() {
        const bodyInput = document.getElementById('body');
        const preview = document.getElementById('body_preview');

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
