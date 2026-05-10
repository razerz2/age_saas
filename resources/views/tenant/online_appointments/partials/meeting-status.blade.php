@php
    $status = (string) optional($appointment->onlineInstructions)->meeting_status;

    $label = 'Não gerada';
    $class = 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300';

    if ($status === 'generated') {
        $label = 'Gerada';
        $class = 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400';
    } elseif ($status === 'pending') {
        $label = 'Pendente';
        $class = 'bg-amber-100 text-amber-800 dark:bg-amber-900/20 dark:text-amber-300';
    } elseif ($status === 'failed') {
        $label = 'Falhou';
        $class = 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-300';
    } elseif ($status === 'manual_required') {
        $label = 'Manual';
        $class = 'bg-orange-100 text-orange-800 dark:bg-orange-900/20 dark:text-orange-300';
    } elseif ($status === 'cancelled') {
        $label = 'Cancelada';
        $class = 'bg-slate-200 text-slate-700 dark:bg-slate-800 dark:text-slate-300';
    } elseif ($status === 'skipped') {
        $label = 'Ignorada';
        $class = 'bg-sky-100 text-sky-800 dark:bg-sky-900/20 dark:text-sky-300';
    }
@endphp

<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $class }}">
    {{ $label }}
</span>
