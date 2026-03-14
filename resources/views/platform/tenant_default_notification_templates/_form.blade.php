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
    Este modulo define o baseline padrao de templates operacionais do Tenant.
    Ele e separado do modulo de Templates WhatsApp Oficial da Platform.
</div>

<div class="row">
    <div class="col-md-4 mb-3">
        <label class="form-label">Canal <span class="text-danger">*</span></label>
        <select name="channel" class="form-control" required>
            <option value="whatsapp" {{ old('channel', $template->channel) === 'whatsapp' ? 'selected' : '' }}>whatsapp</option>
            <option value="email" {{ old('channel', $template->channel) === 'email' ? 'selected' : '' }}>email</option>
        </select>
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Idioma <span class="text-danger">*</span></label>
        <input type="text" name="language" class="form-control" value="{{ old('language', $template->language ?? 'pt_BR') }}" required>
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Categoria <span class="text-danger">*</span></label>
        <input type="text" name="category" class="form-control" value="{{ old('category', $template->category) }}" required>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Key <span class="text-danger">*</span></label>
        <input type="text" name="key" class="form-control" value="{{ old('key', $template->key) }}" required>
        <small class="text-muted">Exemplo: appointment.pending_confirmation</small>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Titulo <span class="text-danger">*</span></label>
        <input type="text" name="title" class="form-control" value="{{ old('title', $template->title) }}" required>
    </div>
</div>

<div class="mb-3">
    <label class="form-label">Assunto (apenas email)</label>
    <input type="text" name="subject" class="form-control" value="{{ old('subject', $template->subject) }}">
</div>

<div class="mb-3">
    <label class="form-label">Conteudo padrao <span class="text-danger">*</span></label>
    <textarea id="content" name="content" class="form-control" rows="10" required>{{ old('content', $template->content) }}</textarea>
</div>

<div class="mb-3">
    <label class="form-label">Variaveis (JSON)</label>
    <textarea name="variables" class="form-control" rows="6">{{ $variablesValue }}</textarea>
    <small class="text-muted">Exemplo: ["patient.name", "appointment.date"]</small>
</div>

<div class="form-check mb-3">
    <input class="form-check-input" type="checkbox" value="1" id="is_active" name="is_active"
        {{ old('is_active', $template->is_active ?? true) ? 'checked' : '' }}>
    <label class="form-check-label" for="is_active">
        Ativo para provisionamento de novos tenants
    </label>
</div>

<div class="card border">
    <div class="card-body">
        <h5 class="card-title">Preview</h5>
        <pre id="content_preview" class="mb-0">{{ old('content', $template->content) }}</pre>
    </div>
</div>

<div class="text-end mt-4">
    <button type="submit" class="btn btn-success">
        <i class="fas fa-save me-1"></i> {{ $isEdit ? 'Salvar Alteracoes' : 'Criar Template' }}
    </button>
    <a href="{{ route('Platform.tenant-default-notification-templates.index') }}" class="btn btn-secondary">Cancelar</a>
</div>

@push('scripts')
<script>
    (function() {
        const bodyInput = document.getElementById('content');
        const preview = document.getElementById('content_preview');

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

