@php
    $mode = $appointment->appointment_mode ?? 'presencial';
@endphp

@if ($mode === 'online')
    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400">
        <x-icon name="video-outline" class="w-3 h-3 mr-1" />        Online
    </span>
@else
    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400">
        <x-icon name="map-marker-outline" class="w-3 h-3 mr-1" />        Presencial
    </span>
@endif
