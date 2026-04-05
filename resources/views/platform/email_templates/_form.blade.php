@php
    $isEdit = isset($template) && $template->exists;
@endphp

<div class="mb-3">
    <label class="form-label">Key/Evento <span class="text-danger">*</span></label>
    <input
        type="text"
        name="name"
        class="form-control"
        value="{{ old('name', $template->name) }}"
        required
    >
    <small class="text-muted">Use letras minusculas, numeros, ponto e underscore.</small>
</div>

<div class="mb-3">
    <label class="form-label">Nome de Exibicao <span class="text-danger">*</span></label>
    <input
        type="text"
        name="display_name"
        class="form-control"
        value="{{ old('display_name', $template->display_name) }}"
        required
    >
</div>

<div class="mb-3">
    <label class="form-label">Assunto <span class="text-danger">*</span></label>
    <input
        type="text"
        name="subject"
        class="form-control"
        value="{{ old('subject', $template->subject) }}"
        required
    >
</div>

<div class="mb-3">
    <label class="form-label">Body/Conteúdo <span class="text-danger">*</span></label>
    <textarea
        name="body"
        class="form-control"
        rows="12"
        required
    >{{ old('body', $template->body) }}</textarea>
</div>

<div class="form-check form-switch mb-3">
    <input
        class="form-check-input"
        type="checkbox"
        role="switch"
        id="enabled"
        name="enabled"
        value="1"
        {{ old('enabled', $template->enabled ?? true) ? 'checked' : '' }}
    >
    <label class="form-check-label" for="enabled">Template ativo</label>
</div>

@if($isEdit)
    <div class="alert alert-light border">
        <small class="text-muted">
            Restaurar padrão volta para os valores iniciais de assunto e body deste registro.
        </small>
    </div>
@endif

<div class="text-end mt-4 d-flex justify-content-end gap-2">
    @if($isEdit)
        <form method="POST" action="{{ route($restoreRouteName, $template) }}" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-outline-warning">
                <i class="fas fa-undo me-1"></i> Restaurar Padrão
            </button>
        </form>
    @endif
    <button type="submit" class="btn btn-success">
        <i class="fas fa-save me-1"></i> {{ $isEdit ? 'Salvar Alterações' : 'Criar Template' }}
    </button>
    <a href="{{ route($indexRouteName) }}" class="btn btn-secondary">Cancelar</a>
</div>
