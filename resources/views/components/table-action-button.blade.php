@props([
    'href' => null,
    'title',
    'type' => 'button',
    'color' => 'gray',
])

@php
    $variants = [
        'blue' => 'bg-blue-100 text-blue-700 hover:bg-blue-200 focus-visible:ring-blue-500 dark:bg-blue-900 dark:text-blue-200 dark:hover:bg-blue-800',
        'amber' => 'bg-amber-100 text-amber-700 hover:bg-amber-200 focus-visible:ring-amber-500 dark:bg-amber-900 dark:text-amber-300 dark:hover:bg-amber-800',
        'purple' => 'bg-purple-100 text-purple-700 hover:bg-purple-200 focus-visible:ring-purple-500 dark:bg-purple-900 dark:text-purple-200 dark:hover:bg-purple-800',
        'indigo' => 'bg-indigo-100 text-indigo-700 hover:bg-indigo-200 focus-visible:ring-indigo-500 dark:bg-indigo-900 dark:text-indigo-200 dark:hover:bg-indigo-800',
        'yellow' => 'bg-yellow-100 text-yellow-800 hover:bg-yellow-200 focus-visible:ring-yellow-500 dark:bg-yellow-900 dark:text-yellow-300 dark:hover:bg-yellow-800',
        'green' => 'bg-green-100 text-green-700 hover:bg-green-200 focus-visible:ring-green-500 dark:bg-green-900 dark:text-green-200 dark:hover:bg-green-800',
        'red' => 'bg-red-100 text-red-700 hover:bg-red-200 focus-visible:ring-red-500 dark:bg-red-900 dark:text-red-300 dark:hover:bg-red-800',
        'gray' => 'bg-gray-100 text-gray-700 hover:bg-gray-200 focus-visible:ring-gray-500 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800',
    ];

    $variantClasses = $variants[$color] ?? $variants['gray'];
    $isDisabled = $attributes->has('disabled');
    $stateClasses = $isDisabled ? 'cursor-not-allowed opacity-50 pointer-events-none' : 'cursor-pointer';
    $normalizedTitle = mb_strtolower(trim((string) $title));
    $semanticClass = '';

    if (str_starts_with($normalizedTitle, 'ver') || str_starts_with($normalizedTitle, 'visualizar') || str_starts_with($normalizedTitle, 'view')) {
        $semanticClass = 'tenant-action-view';
    } elseif (str_starts_with($normalizedTitle, 'editar') || str_starts_with($normalizedTitle, 'edit')) {
        $semanticClass = 'tenant-action-edit';
    } elseif (
        str_starts_with($normalizedTitle, 'excluir') ||
        str_starts_with($normalizedTitle, 'remover') ||
        str_starts_with($normalizedTitle, 'desconectar') ||
        str_starts_with($normalizedTitle, 'delete')
    ) {
        $semanticClass = 'tenant-action-delete';
    }

    $baseClasses = trim("table-action-btn inline-flex items-center justify-center text-xs font-medium rounded-lg transition-colors duration-200 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-offset-white dark:focus-visible:ring-offset-gray-900 {$stateClasses} {$variantClasses} {$semanticClass}");

    $mergedAttributes = $attributes->merge([
        'class' => $baseClasses,
        'title' => $title,
        'aria-label' => $title,
    ]);
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $mergedAttributes }}>
        <span class="sr-only">{{ $title }}</span>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $mergedAttributes }}>
        <span class="sr-only">{{ $title }}</span>
        {{ $slot }}
    </button>
@endif
