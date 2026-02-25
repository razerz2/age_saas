@php
    $status = $appointment->status ?? null;
    $label  = $appointment->status_translated ?? ($status ?? 'N/A');
    if ($status === 'pending_confirmation') {
        $label = 'Pendente';
    } elseif ($status === 'expired') {
        $label = 'Expirado';
    } elseif (in_array($status, ['canceled', 'cancelled'], true)) {
        $label = 'Cancelado';
    }

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

        case 'pending_confirmation':
            $bg  = 'bg-yellow-100 dark:bg-yellow-900/20';
            $txt = 'text-yellow-800 dark:text-yellow-400';
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

        case 'expired':
            $bg  = 'bg-orange-100 dark:bg-orange-900/20';
            $txt = 'text-orange-800 dark:text-orange-400';
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
    <x-icon name="calendar-check-outline" class="w-3 h-3 mr-1" />
    {{ $label }}
</span>
