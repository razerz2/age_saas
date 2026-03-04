@php
    $active = (bool) ($isActive ?? false);
@endphp
<span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $active ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' : 'bg-slate-100 text-slate-800 dark:bg-slate-900/30 dark:text-slate-300' }}">
    {{ $active ? 'Ativo' : 'Inativo' }}
</span>
