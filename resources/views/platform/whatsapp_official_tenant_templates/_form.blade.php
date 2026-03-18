@php
    $isEdit = isset($template) && $template->exists;
    $variablesValue = old('variables');
    if ($variablesValue === null) {
        $variablesValue = !empty($template->variables ?? null)
            ? json_encode($template->variables, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
            : "{}";
    } elseif (is_array($variablesValue)) {
        $variablesValue = json_encode($variablesValue, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    $sampleVariablesValue = old('sample_variables');
    if ($sampleVariablesValue === null) {
        $sampleVariablesValue = !empty($template->sample_variables ?? null)
            ? json_encode($template->sample_variables, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
            : "{}";
    } elseif (is_array($sampleVariablesValue)) {
        $sampleVariablesValue = json_encode($sampleVariablesValue, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
@endphp

<input type="hidden" name="provider" value="{{ \App\Models\Platform\WhatsAppOfficialTemplate::PROVIDER }}">
<input type="hidden" name="category" value="UTILITY">
<input type="hidden" name="language" value="pt_BR">

<div class="alert alert-info">
    Este módulo representa o baseline padrão oficial tenant (Meta), separado do catálogo geral e separado do Não Oficial.
</div>

<div class="alert alert-warning">
    Somente templates com status <strong>approved</strong> estão aptos para uso operacional real.
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Evento (key) <span class="text-danger">*</span></label>
        <select name="key" class="form-control" required>
            <option value="">Selecione...</option>
            @foreach($allowedKeys as $key)
                <option value="{{ $key }}" {{ old('key', $template->key) === $key ? 'selected' : '' }}>
                    {{ $key }} - {{ $eventLabels[$key] ?? $key }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Nome Meta <span class="text-danger">*</span></label>
        <input type="text" name="meta_template_name" class="form-control"
            value="{{ old('meta_template_name', $template->meta_template_name) }}" required>
    </div>
</div>

<div class="row">
    <div class="col-md-4 mb-3">
        <label class="form-label">Provider</label>
        <input type="text" class="form-control bg-light" value="whatsapp_business" readonly>
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Categoria</label>
        <input type="text" class="form-control bg-light" value="UTILITY" readonly>
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Idioma</label>
        <input type="text" class="form-control bg-light" value="pt_BR" readonly>
    </div>
</div>

@if($isEdit)
    <div class="mb-3">
        <label class="form-label">Status atual</label>
        <input type="text" class="form-control bg-light" value="{{ strtoupper((string) $template->status) }}" readonly>
    </div>
@endif

<div class="mb-3">
    <label class="form-label">Body <span class="text-danger">*</span></label>
    <textarea id="body_text" name="body_text" class="form-control" rows="8" required>{{ old('body_text', $template->body_text) }}</textarea>
    <small class="text-muted">Use placeholders numéricos Meta no formato <code>@{{1}}</code>, <code>@{{2}}</code>, ...</small>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Variables (JSON)</label>
        <textarea name="variables" class="form-control" rows="7">{{ $variablesValue }}</textarea>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Sample Variables (JSON)</label>
        <textarea name="sample_variables" class="form-control" rows="7">{{ $sampleVariablesValue }}</textarea>
    </div>
</div>

<div class="text-end mt-4">
    <button type="submit" class="btn btn-success">
        <i class="fas fa-save me-1"></i> {{ $isEdit ? 'Salvar Alterações' : 'Criar Template' }}
    </button>
    <a href="{{ route('Platform.whatsapp-official-tenant-templates.index') }}" class="btn btn-secondary">Cancelar</a>
</div>

