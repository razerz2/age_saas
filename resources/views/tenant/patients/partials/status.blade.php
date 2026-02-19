@php
    $isActive = (bool) ($patient->is_active ?? false);
@endphp

@if ($isActive)
    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400">
        <x-icon name="mdi-check-circle-outline" size="text-xs" class="mr-1" />
        Ativo
    </span>
@else
    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400">
        <x-icon name="mdi-close-circle-outline" size="text-xs" class="mr-1" />
        Inativo
    </span>
@endif
