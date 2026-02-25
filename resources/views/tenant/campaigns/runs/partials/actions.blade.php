<div class="inline-flex items-center gap-1">
    <a
        href="{{ workspace_route('tenant.campaigns.recipients.index', ['campaign' => $campaign->id, 'run_id' => $run->id]) }}"
        title="Ver destinatários"
        onclick="event.stopPropagation()"
        class="inline-flex items-center justify-center rounded-xl border border-indigo-100 bg-indigo-50 px-2.5 py-1.5 text-xs font-medium text-indigo-700 hover:bg-indigo-100 dark:border-indigo-900/40 dark:bg-indigo-900/20 dark:text-indigo-300"
    >
        <i class="mdi mdi-account-multiple-outline text-sm"></i>
    </a>

    <a
        href="{{ workspace_route('tenant.campaigns.show', ['campaign' => $campaign->id]) }}"
        title="Ver campanha"
        onclick="event.stopPropagation()"
        class="inline-flex items-center justify-center rounded-xl border border-blue-100 bg-blue-50 px-2.5 py-1.5 text-xs font-medium text-blue-700 hover:bg-blue-100 dark:border-blue-900/40 dark:bg-blue-900/20 dark:text-blue-300"
    >
        <i class="mdi mdi-eye-outline text-sm"></i>
    </a>
</div>
