@props([
    'variant' => 'primary',
    'size' => 'md',
    'href' => null,
    'type' => 'button',
])

@php
    $baseClasses = 'inline-flex items-center justify-center gap-2 rounded-lg font-semibold transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2';

    $sizeClasses = match ($size) {
        'xs' => 'px-2.5 py-1 text-xs',
        'sm' => 'px-3 py-2 text-sm',
        'lg' => 'px-5 py-2.5 text-base',
        default => 'px-4 py-2 text-sm',
    };

    $variantClasses = match ($variant) {
        'secondary' => 'border border-gray-200 bg-white text-gray-700 hover:border-gray-300 hover:bg-gray-50 focus-visible:ring-primary/60 focus-visible:ring-offset-white dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:border-gray-600 dark:hover:bg-white/5 dark:focus-visible:ring-offset-gray-900',
        'outline' => 'border border-gray-200 bg-white text-gray-700 hover:border-gray-300 hover:bg-gray-50 focus-visible:ring-primary/60 focus-visible:ring-offset-white dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:border-gray-600 dark:hover:bg-white/5 dark:focus-visible:ring-offset-gray-900',
        'ghost' => 'border border-transparent bg-transparent text-gray-700 hover:text-gray-900 dark:text-gray-200 dark:hover:text-white focus-visible:ring-primary/60 focus-visible:ring-offset-white dark:focus-visible:ring-offset-gray-900',
        'danger' => 'bg-error text-white hover:bg-error/90 focus-visible:ring-error/50 focus-visible:ring-offset-white dark:bg-error/80 dark:hover:bg-error/70 dark:focus-visible:ring-offset-gray-900',
        'success' => 'bg-success text-white hover:bg-success/90 focus-visible:ring-success/50 focus-visible:ring-offset-white dark:bg-success/80 dark:hover:bg-success/70 dark:focus-visible:ring-offset-gray-900',
        'warning' => 'bg-warning text-gray-900 hover:bg-warning/90 focus-visible:ring-warning/50 focus-visible:ring-offset-white dark:bg-warning/80 dark:hover:bg-warning/70 dark:focus-visible:ring-offset-gray-900',
        'muted' => 'border border-gray-200 bg-gray-100 text-gray-700 hover:bg-gray-200 focus-visible:ring-gray-500/40 focus-visible:ring-offset-white dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 dark:focus-visible:ring-offset-gray-900',
        default => 'bg-primary text-white hover:bg-primary/90 focus-visible:ring-primary/60 focus-visible:ring-offset-white dark:bg-primary/80 dark:hover:bg-primary/70 dark:focus-visible:ring-offset-gray-900',
    };

    $classes = trim("{$baseClasses} {$sizeClasses} {$variantClasses} {$attributes->get('class')}");
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif
