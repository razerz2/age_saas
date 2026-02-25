@php
    $status = strtolower((string) ($recipient->status ?? ''));
    $map = [
        'pending' => [
            'label' => 'Pendente',
            'icon' => 'mdi-timer-sand',
            'classes' => 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-300',
        ],
        'sent' => [
            'label' => 'Enviado',
            'icon' => 'mdi-check-circle-outline',
            'classes' => 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400',
        ],
        'error' => [
            'label' => 'Erro',
            'icon' => 'mdi-alert-circle-outline',
            'classes' => 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400',
        ],
        'skipped' => [
            'label' => 'Ignorado',
            'icon' => 'mdi-skip-next-circle-outline',
            'classes' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/20 dark:text-amber-400',
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
