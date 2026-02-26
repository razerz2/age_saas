@php
    $variables = is_array($variables ?? null) ? $variables : [];
    $listContainerClass = trim((string) ($listContainerClass ?? 'space-y-4'));
@endphp

<div class="{{ $listContainerClass }}">
    @foreach($variables as $group => $items)
        @php
            $items = is_array($items) ? $items : [];
        @endphp
        <div>
            <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ $group }}</p>
            <div class="space-y-1">
                @foreach($items as $item)
                    @php
                        $variable = is_array($item) ? (string) ($item['key'] ?? '') : (string) $item;
                        $description = is_array($item) ? (string) ($item['description'] ?? '') : '';
                    @endphp
                    @if($variable !== '')
                        <div class="flex items-center gap-2 rounded border border-gray-200 bg-white px-2 py-1 dark:border-gray-700 dark:bg-gray-900">
                            <code class="shrink-0 text-xs text-gray-700 dark:text-gray-200">{{ $variable }}</code>
                            <span class="min-w-0 flex-1 truncate text-xs text-gray-500 dark:text-gray-400">{{ $description }}</span>
                            <button
                                type="button"
                                class="js-copy-template-variable inline-flex h-6 w-6 items-center justify-center rounded border border-gray-200 text-gray-500 transition-colors hover:bg-gray-100 hover:text-gray-700 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800 dark:hover:text-white"
                                data-copy-variable="{{ $variable }}"
                                title="Copiar variável"
                                aria-label="Copiar variável"
                            >
                                <x-icon name="content-copy" class="h-3.5 w-3.5" />
                            </button>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    @endforeach
</div>
