<div class="inline-flex items-center gap-1">
    <a
        href="{{ workspace_route('tenant.campaigns.show', ['campaign' => $campaign->id]) }}"
        title="Ver campanha"
        onclick="event.stopPropagation()"
        class="inline-flex items-center justify-center rounded-xl border border-blue-100 bg-blue-50 px-2.5 py-1.5 text-xs font-medium text-blue-700 hover:bg-blue-100 dark:border-blue-900/40 dark:bg-blue-900/20 dark:text-blue-300"
    >
        <i class="mdi mdi-eye-outline text-sm"></i>
    </a>

    @if (\Illuminate\Support\Facades\Route::has('tenant.notifications.index'))
        <a
            href="{{ workspace_route('tenant.notifications.index') }}"
            title="Abrir auditoria"
            onclick="event.stopPropagation()"
            class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-gray-50 px-2.5 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300"
        >
            <i class="mdi mdi-file-document-search-outline text-sm"></i>
        </a>
    @endif
</div>
