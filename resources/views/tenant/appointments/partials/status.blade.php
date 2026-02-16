@php
    $status = $appointment->status ?? null;
    $label  = $appointment->status_translated ?? ($status ?? 'N/A');

    switch ($status) {
        case 'scheduled':
        case 'confirmed':
            $bg  = 'bg-blue-100 dark:bg-blue-900/20';
            $txt = 'text-blue-800 dark:text-blue-400';
            break;

        case 'rescheduled':
            $bg  = 'bg-amber-100 dark:bg-amber-900/20';
            $txt = 'text-amber-800 dark:text-amber-400';
            break;

        case 'attended':
        case 'completed':
            $bg  = 'bg-green-100 dark:bg-green-900/20';
            $txt = 'text-green-800 dark:text-green-400';
            break;

        case 'canceled':
        case 'cancelled':
            $bg  = 'bg-red-100 dark:bg-red-900/20';
            $txt = 'text-red-800 dark:text-red-400';
            break;

        case 'no_show':
            $bg  = 'bg-gray-200 dark:bg-gray-700';
            $txt = 'text-gray-800 dark:text-gray-200';
            break;

        case 'arrived':
        case 'in_service':
            $bg  = 'bg-purple-100 dark:bg-purple-900/20';
            $txt = 'text-purple-800 dark:text-purple-400';
            break;

        default:
            $bg  = 'bg-gray-100 dark:bg-gray-900/20';
            $txt = 'text-gray-800 dark:text-gray-300';
            break;
    }
@endphp

<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $bg }} {{ $txt }}">
    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v4a1 1 0 00.293.707l2 2a1 1 0 001.414-1.414L11 10.586V7z" clip-rule="evenodd"></path>
    </svg>
    {{ $label }}
</span>
