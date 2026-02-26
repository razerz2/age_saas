@php
    $modalId = trim((string) ($modalId ?? 'variables-modal'));
    $title = trim((string) ($title ?? 'Variáveis disponíveis'));
    $hint = trim((string) ($hint ?? 'Copie e cole no template usando o formato'));
    $variables = is_array($variables ?? null) ? $variables : [];
@endphp

<div
    id="{{ $modalId }}"
    class="js-variables-modal fixed inset-0 z-999999 hidden items-center justify-center overflow-y-auto p-5"
    aria-hidden="true"
>
    <div class="fixed inset-0 h-full w-full bg-gray-400/50 backdrop-blur-[2px]" data-variables-modal-close></div>

    <div class="relative w-full max-w-3xl rounded-3xl bg-white p-6 dark:bg-gray-900">
        <button
            type="button"
            class="absolute right-3 top-3 inline-flex h-10 w-10 items-center justify-center rounded-full border border-gray-200 text-gray-500 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800"
            data-variables-modal-close
            title="Fechar"
            aria-label="Fechar"
        >
            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                <path d="M15 5L5 15M5 5L15 15" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></path>
            </svg>
        </button>

        <h4 class="mb-1 text-xl font-semibold text-gray-800 dark:text-white">{{ $title }}</h4>
        <p class="mb-4 text-xs text-gray-500 dark:text-gray-400">
            {{ $hint }}
            <code class="rounded border border-gray-200 bg-gray-50 px-1 py-0.5 text-[11px] text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200">@{{chave}}</code>.
        </p>

        @include('tenant.components.variables-list', [
            'variables' => $variables,
            'listContainerClass' => 'max-h-[520px] space-y-4 overflow-y-auto pr-1',
        ])
    </div>
</div>
