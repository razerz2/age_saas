@php
    $isCompleted = in_array($appointment->status, ['attended', 'completed'], true);
    $isCanceled = in_array($appointment->status, ['canceled', 'cancelled'], true);
@endphp

@if ($isCompleted)
    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400">
        Concluída
    </span>
@elseif ($isCanceled)
    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400">
        Cancelada
    </span>
@else
    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/20 dark:text-amber-400">
        Agendada
    </span>
@endif
