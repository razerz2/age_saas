@php
    $moduleEnabled = $moduleEnabled ?? true;
@endphp

<div class="inline-flex items-center gap-1">
    <a
        href="{{ workspace_route('tenant.campaigns.show', ['campaign' => $campaign->id]) }}"
        title="Ver"
        onclick="event.stopPropagation()"
        class="inline-flex items-center justify-center rounded-xl border border-blue-100 bg-blue-50 px-2.5 py-1.5 text-xs font-medium text-blue-700 hover:bg-blue-100 dark:border-blue-900/40 dark:bg-blue-900/20 dark:text-blue-300 table-action-btn tenant-action-view"
    >
        <i class="mdi mdi-eye-outline text-sm"></i>
    </a>

    @if ($moduleEnabled)
        <a
            href="{{ workspace_route('tenant.campaigns.edit', ['campaign' => $campaign->id]) }}"
            title="Editar"
            onclick="event.stopPropagation()"
            class="inline-flex items-center justify-center rounded-xl border border-amber-100 bg-amber-50 px-2.5 py-1.5 text-xs font-medium text-amber-700 hover:bg-amber-100 dark:border-amber-900/40 dark:bg-amber-900/20 dark:text-amber-300 table-action-btn tenant-action-edit"
        >
            <i class="mdi mdi-pencil-outline text-sm"></i>
        </a>

        <form
            id="campaigns-delete-form-{{ $campaign->id }}"
            action="{{ workspace_route('tenant.campaigns.destroy', ['campaign' => $campaign->id]) }}"
            method="POST"
            class="inline-flex"
           
        >
            @csrf
            @method('DELETE')

            <button
                type="button"
                title="Excluir"
                data-delete-trigger="1"
                data-delete-form="#campaigns-delete-form-{{ $campaign->id }}"
                data-delete-title="Excluir campanha"
                data-delete-message="Tem certeza que deseja excluir esta campanha?"
                onclick="event.stopPropagation()"
                class="inline-flex items-center justify-center rounded-xl border border-red-100 bg-red-50 px-2.5 py-1.5 text-xs font-medium text-red-700 hover:bg-red-100 dark:border-red-900/40 dark:bg-red-900/20 dark:text-red-300 table-action-btn tenant-action-delete"
            >
                <i class="mdi mdi-delete-outline text-sm"></i>
            </button>
        </form>
    @else
        <span
            title="Edição indisponível sem canais configurados"
            class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-gray-100 px-2.5 py-1.5 text-xs font-medium text-gray-400 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-500 cursor-not-allowed"
        >
            <i class="mdi mdi-pencil-off-outline text-sm"></i>
        </span>
        <span
            title="Exclusão indisponível sem canais configurados"
            class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-gray-100 px-2.5 py-1.5 text-xs font-medium text-gray-400 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-500 cursor-not-allowed"
        >
            <i class="mdi mdi-delete-off-outline text-sm"></i>
        </span>
    @endif
</div>

