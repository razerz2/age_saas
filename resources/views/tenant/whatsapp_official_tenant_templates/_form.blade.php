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

<div class="mb-4 rounded-lg border border-blue-200 bg-blue-50 p-4 text-sm text-blue-800 dark:border-blue-900/50 dark:bg-blue-900/20 dark:text-blue-200">
    Templates oficiais do tenant usam categoria `UTILITY`, idioma `pt_BR` e nomenclatura canônica `tenant_{evento}`.
</div>

<div class="grid grid-cols-1 gap-4 md:grid-cols-2">
    <div>
        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
            Evento (key) <span class="text-red-500">*</span>
        </label>
        <select id="tenant-template-event-key" name="key" class="w-full rounded-lg border border-gray-300 px-3 py-2 dark:border-gray-600 dark:bg-gray-700 dark:text-white" required>
            <option value="">Selecione...</option>
            @foreach($allowedKeys as $key)
                <option value="{{ $key }}" {{ old('key', $template->key) === $key ? 'selected' : '' }}>
                    {{ $key }} - {{ $eventLabels[$key] ?? $key }}
                </option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
            Nome Meta
        </label>
        <input id="tenant-template-meta-name" type="text" name="meta_template_name" class="w-full rounded-lg border border-gray-300 bg-gray-100 px-3 py-2 dark:border-gray-600 dark:bg-gray-700 dark:text-white" value="{{ old('meta_template_name', $template->meta_template_name) }}" readonly>
    </div>
</div>

@if($isEdit)
    <div class="mt-4">
        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Status atual</label>
        <input type="text" class="w-full rounded-lg border border-gray-300 bg-gray-100 px-3 py-2 dark:border-gray-600 dark:bg-gray-700 dark:text-white" value="{{ strtoupper((string) $template->status) }}" readonly>
    </div>
@endif

<div class="mt-4">
    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
        Body <span class="text-red-500">*</span>
    </label>
    <textarea name="body_text" rows="8" class="w-full rounded-lg border border-gray-300 px-3 py-2 dark:border-gray-600 dark:bg-gray-700 dark:text-white" required>{{ old('body_text', $template->body_text) }}</textarea>
    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
        Use placeholders numericos no formato <code>@{{1}}</code>, <code>@{{2}}</code>, ...
    </p>
</div>

<div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
    <div>
        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Variables (JSON)</label>
        <textarea name="variables" rows="8" class="w-full rounded-lg border border-gray-300 px-3 py-2 font-mono text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">{{ $variablesValue }}</textarea>
    </div>
    <div>
        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Sample Variables (JSON)</label>
        <textarea name="sample_variables" rows="8" class="w-full rounded-lg border border-gray-300 px-3 py-2 font-mono text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">{{ $sampleVariablesValue }}</textarea>
    </div>
</div>

<div class="mt-6 flex flex-col gap-3 sm:flex-row sm:justify-end">
    <a href="{{ workspace_route('tenant.settings.whatsapp-official-tenant-templates.index') }}"
       class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700">
        Cancelar
    </a>
    <button type="submit"
            class="inline-flex items-center justify-center rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700">
        {{ $isEdit ? 'Salvar alteracoes' : 'Criar template' }}
    </button>
</div>

@push('scripts')
<script>
    (function () {
        const keyInput = document.getElementById('tenant-template-event-key');
        const metaNameInput = document.getElementById('tenant-template-meta-name');
        if (!keyInput || !metaNameInput) {
            return;
        }

        const buildCanonicalName = (key) => {
            return 'tenant_' + String(key || '')
                .trim()
                .toLowerCase()
                .replace(/[.\-\s]+/g, '_');
        };

        const syncMetaName = () => {
            const selectedKey = keyInput.value || '';
            metaNameInput.value = selectedKey ? buildCanonicalName(selectedKey) : '';
        };

        keyInput.addEventListener('change', syncMetaName);
        syncMetaName();
    })();
</script>
@endpush

