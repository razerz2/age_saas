@php
    $isOnline = ($mode ?? 'presencial') === 'online';
@endphp
<span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $isOnline ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300' : 'bg-slate-100 text-slate-800 dark:bg-slate-900/30 dark:text-slate-300' }}">
    {{ $isOnline ? 'Online' : 'Presencial' }}
</span>
