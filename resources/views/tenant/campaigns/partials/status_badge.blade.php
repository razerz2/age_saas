@php
    $status = strtolower((string) ($campaign->status ?? ''));
    $map = [
        'draft' => [
            'label' => 'Rascunho',
            'icon' => 'mdi-file-document-edit-outline',
            'classes' => 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-300',
        ],
        'active' => [
            'label' => 'Ativa',
            'icon' => 'mdi-play-circle-outline',
            'classes' => 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400',
        ],
        'paused' => [
            'label' => 'Pausada',
            'icon' => 'mdi-pause-circle-outline',
            'classes' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/20 dark:text-amber-400',
        ],
        'archived' => [
            'label' => 'Arquivada',
            'icon' => 'mdi-archive-outline',
            'classes' => 'bg-slate-100 text-slate-800 dark:bg-slate-900/20 dark:text-slate-300',
        ],
        'blocked' => [
            'label' => 'Bloqueada',
            'icon' => 'mdi-block-helper',
            'classes' => 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400',
        ],
    ];

    $resolved = $map[$status] ?? [
        'label' => ucfirst($status ?: 'Indefinido'),
        'icon' => 'mdi-help-circle-outline',
        'classes' => 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-300',
    ];
@endphp

<span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium {{ $resolved['classes'] }}">
    <i class="mdi {{ $resolved['icon'] }} text-xs"></i>
    {{ $resolved['label'] }}
</span>

