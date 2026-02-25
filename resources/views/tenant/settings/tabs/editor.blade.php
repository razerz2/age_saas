<!-- Aba Editor -->
<div class="space-y-6">
    <div>
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Editor de Notificações</h2>
        <p class="text-sm text-gray-600 dark:text-gray-400">
            Edite templates por canal e tipo. Salvar cria override; restaurar remove override e volta ao padrão.
        </p>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-12 gap-6">
        <div class="xl:col-span-8 space-y-6">
            <form method="GET" action="{{ workspace_route('tenant.settings.index') }}" id="notification-template-filter-form" class="grid grid-cols-1 gap-4">
                <input type="hidden" name="tab" value="editor">

                <div>
                    <label for="editor_channel" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Canal</label>
                    <select id="editor_channel" name="channel"
                            onchange="document.getElementById('notification-template-filter-form').submit();"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        @foreach(($editor['channels'] ?? ['email', 'whatsapp']) as $channel)
                            <option value="{{ $channel }}" {{ ($editor['current_channel'] ?? 'email') === $channel ? 'selected' : '' }}>
                                {{ strtoupper($channel) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="editor_key" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tipo</label>
                    <select id="editor_key" name="key"
                            onchange="document.getElementById('notification-template-filter-form').submit();"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        @php $selectedChannel = $editor['current_channel'] ?? 'email'; @endphp
                        @foreach(($editor['keys'] ?? []) as $item)
                            @if(in_array($selectedChannel, $item['channels'] ?? [], true))
                                <option value="{{ $item['key'] }}" {{ ($editor['current_key'] ?? '') === $item['key'] ? 'selected' : '' }}>
                                    {{ $item['label'] }} ({{ $item['key'] }})
                                </option>
                            @endif
                        @endforeach
                    </select>
                </div>
            </form>

            @if(empty($editor['current_key']))
                <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-700 dark:bg-amber-900/20 dark:text-amber-300">
                    Nenhum template disponível para o canal selecionado.
                </div>
            @else
                <div class="space-y-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Template selecionado</p>
                            <h3 class="text-base font-semibold text-gray-900 dark:text-white">{{ $editor['current_key'] }}</h3>
                        </div>
                        @if(($editor['is_custom'] ?? false) === true)
                            <span class="inline-flex items-center rounded-full bg-blue-100 px-3 py-1 text-xs font-medium text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">
                                Personalizado
                            </span>
                        @else
                            <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700 dark:bg-gray-700 dark:text-gray-200">
                                Padrão
                            </span>
                        @endif
                    </div>

                    @php
                        $savedUnknownPlaceholders = session('editor_unknown_placeholders', []);
                    @endphp
                    @if(is_array($savedUnknownPlaceholders) && $savedUnknownPlaceholders !== [])
                        <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-700 dark:bg-amber-900/20 dark:text-amber-300">
                            <p class="font-medium mb-2">Template salvo com placeholders desconhecidos:</p>
                            <div class="flex flex-wrap gap-2">
                                @foreach($savedUnknownPlaceholders as $placeholder)
                                    <code class="inline-block rounded border border-amber-300 dark:border-amber-700 bg-white/80 dark:bg-amber-950/30 px-2 py-0.5 text-xs">{{ '{' }}{{ '{' }}{{ $placeholder }}{{ '}' }}{{ '}' }}</code>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <form method="POST" action="{{ workspace_route('tenant.settings.editor.save') }}" class="space-y-4">
                        @csrf
                        <input type="hidden" name="tab" value="editor">
                        <input type="hidden" name="channel" value="{{ $editor['current_channel'] }}">
                        <input type="hidden" name="key" value="{{ $editor['current_key'] }}">

                        @if(($editor['current_channel'] ?? 'email') === 'email' && !empty($editor['default_template']['subject'] ?? null))
                            <div>
                                <label for="editor_subject" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Assunto
                                    @if(($editor['subject_required'] ?? false) === true)
                                        <span class="text-red-500">*</span>
                                    @endif
                                </label>
                                <input
                                    id="editor_subject"
                                    type="text"
                                    name="subject"
                                    value="{{ old('subject', $editor['effective_template']['subject'] ?? '') }}"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                >
                            </div>
                        @endif


                        @php
                            $editorEmojis = ['✅', '❌', '⏳', '🔔', '📅', '🕐', '📍', '🏥', '👨‍⚕️', '📝', '📲', '📌', '🔗', '💬', '👍', '👋', '🙂', '😃', '⭐', '⚠️'];
                        @endphp
                        <div>
                            <p class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Emojis</p>
                            <div class="flex flex-wrap gap-2">
                                @foreach($editorEmojis as $emoji)
                                    <button
                                        type="button"
                                        data-emoji="{{ $emoji }}"
                                        aria-label="Inserir emoji {{ $emoji }}"
                                        title="Inserir {{ $emoji }}"
                                        class="js-emoji-btn inline-flex items-center justify-center rounded-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-1 text-sm hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors"
                                    >{{ $emoji }}</button>
                                @endforeach
                            </div>
                            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Clique para inserir no campo em foco (Assunto ou Conteúdo).</p>
                        </div>

                        <div>
                            <label for="editor_content" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Conteúdo <span class="text-red-500">*</span>
                            </label>
                            <textarea
                                id="editor_content"
                                name="content"
                                rows="12"
                                required
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white font-mono text-sm"
                            >{{ old('content', $editor['effective_template']['content'] ?? '') }}</textarea>
                        </div>

                        <div class="flex flex-wrap items-center gap-3">
                            <button
                                type="submit"
                                formaction="{{ workspace_route('tenant.settings.editor.restore') }}"
                                formnovalidate
                                onclick="return confirm('Restaurar este template para o padrão do sistema?');"
                                class="inline-flex items-center justify-center gap-2 rounded-lg border border-red-300 dark:border-red-700 bg-white dark:bg-gray-700 px-4 py-2 text-sm font-medium text-red-700 dark:text-red-300 hover:bg-red-50 dark:hover:bg-red-900/30 transition-colors"
                            >
                                <x-icon name="restore" class="text-sm" />
                                Restaurar padrão
                            </button>
                            <button
                                type="submit"
                                formaction="{{ workspace_route('tenant.settings.editor.preview') }}"
                                class="inline-flex items-center justify-center gap-2 rounded-lg border border-blue-300 dark:border-blue-700 bg-white dark:bg-gray-700 px-4 py-2 text-sm font-medium text-blue-700 dark:text-blue-300 hover:bg-blue-50 dark:hover:bg-blue-900/30 transition-colors"
                            >
                                <x-icon name="eye" class="text-sm" />
                                Pré-visualizar
                            </button>
                            <button type="submit" class="btn-patient-primary inline-flex items-center justify-center gap-2">
                                <x-icon name="content-save" class="text-sm" />
                                Salvar
                            </button>
                        </div>
                    </form>

                    @if(!empty($editor['preview']))
                        <div class="rounded-lg border border-blue-200 dark:border-blue-800 bg-blue-50/40 dark:bg-blue-900/10 p-4 space-y-3">
                            <div class="flex items-center justify-between">
                                <h4 class="text-sm font-semibold text-blue-900 dark:text-blue-200">Preview renderizado</h4>
                                <span class="text-xs text-blue-700 dark:text-blue-300 uppercase">
                                    {{ ($editor['preview']['context_source'] ?? 'mock') === 'appointment' ? 'Contexto real' : 'Contexto mock' }}
                                </span>
                            </div>

                            @if(!empty($editor['preview']['context_warning']))
                                <div class="rounded border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-800 dark:border-amber-700 dark:bg-amber-900/20 dark:text-amber-300">
                                    {{ $editor['preview']['context_warning'] }}
                                </div>
                            @endif

                            @php
                                $previewUnknownPlaceholders = $editor['preview']['unknown_placeholders'] ?? [];
                            @endphp
                            @if(is_array($previewUnknownPlaceholders) && $previewUnknownPlaceholders !== [])
                                <div class="rounded border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-800 dark:border-amber-700 dark:bg-amber-900/20 dark:text-amber-300">
                                    <p class="font-medium mb-2">Placeholders desconhecidos no preview:</p>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($previewUnknownPlaceholders as $placeholder)
                                            <code class="inline-block rounded border border-amber-300 dark:border-amber-700 bg-white/80 dark:bg-amber-950/30 px-2 py-0.5 text-xs">{{ '{' }}{{ '{' }}{{ $placeholder }}{{ '}' }}{{ '}' }}</code>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if(($editor['current_channel'] ?? 'email') === 'email')
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Assunto renderizado</label>
                                    <div class="rounded border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 px-3 py-2 text-sm text-gray-800 dark:text-gray-200 font-mono whitespace-pre-wrap">{{ $editor['preview']['subject_rendered'] ?? '' }}</div>
                                </div>
                            @endif

                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    {{ ($editor['current_channel'] ?? 'email') === 'email' ? 'Body renderizado' : 'Mensagem renderizada' }}
                                </label>
                                <div class="rounded border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 px-3 py-2 text-sm text-gray-800 dark:text-gray-200 font-mono whitespace-pre-wrap">{{ $editor['preview']['content_rendered'] ?? '' }}</div>
                            </div>
                        </div>
                    @endif

                </div>
            @endif
        </div>

        <aside class="xl:col-span-4">
            <div class="xl:sticky xl:top-6 space-y-4">
                <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 p-4">
                    <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Variáveis disponíveis</h4>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">Copie e cole no template usando o formato <code>{{ '{' }}{{ '{' }}chave{{ '}' }}{{ '}' }}</code>.</p>

                    <div class="space-y-4 max-h-[520px] overflow-y-auto pr-1">
                        @foreach(($editor['variables'] ?? []) as $group => $items)
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-2">{{ $group }}</p>
                                <div class="space-y-1">
                                    @foreach($items as $variable)
                                        <code class="block rounded border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 px-2 py-1 text-xs text-gray-700 dark:text-gray-300">{{ $variable }}</code>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </aside>
    </div>
</div>
@once
    <script>
        (function () {
            var subjectInput = document.getElementById('editor_subject');
            var contentInput = document.getElementById('editor_content');

            if (!contentInput) {
                return;
            }

            var focusableFields = [subjectInput, contentInput].filter(function (field) {
                return !!field;
            });
            var lastFocusedInput = null;

            focusableFields.forEach(function (field) {
                ['focus', 'click', 'keyup'].forEach(function (eventName) {
                    field.addEventListener(eventName, function () {
                        lastFocusedInput = field;
                    });
                });
            });

            function insertAtCursor(field, text) {
                if (!field || typeof field.value !== 'string') {
                    return;
                }

                var hasSelection = typeof field.selectionStart === 'number' && typeof field.selectionEnd === 'number';
                var start = hasSelection ? field.selectionStart : field.value.length;
                var end = hasSelection ? field.selectionEnd : field.value.length;
                var before = field.value.slice(0, start);
                var after = field.value.slice(end);

                field.value = before + text + after;

                var cursorPos = start + text.length;
                if (typeof field.setSelectionRange === 'function') {
                    field.setSelectionRange(cursorPos, cursorPos);
                }

                field.dispatchEvent(new Event('input', { bubbles: true }));
                field.focus();
            }

            document.querySelectorAll('.js-emoji-btn').forEach(function (button) {
                button.addEventListener('click', function () {
                    var emoji = button.getAttribute('data-emoji') || '';
                    if (!emoji) {
                        return;
                    }

                    var target = lastFocusedInput && focusableFields.indexOf(lastFocusedInput) !== -1
                        ? lastFocusedInput
                        : contentInput;

                    insertAtCursor(target, emoji);
                });
            });
        })();
    </script>
@endonce
