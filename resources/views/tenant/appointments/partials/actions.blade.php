<div class="actions-wrap">
    <a
        href="{{ workspace_route('tenant.appointments.show', $appointment->id) }}"
        title="Ver"
        onclick="event.stopPropagation()"
        class="inline-flex items-center justify-center rounded-xl border border-blue-100 bg-blue-50 px-2.5 py-1.5 text-xs font-medium text-blue-700 hover:bg-blue-100 dark:border-blue-900/40 dark:bg-blue-900/20 dark:text-blue-300 table-action-btn tenant-action-view"
    >
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
        </svg>
    </a>

    <a
        href="{{ workspace_route('tenant.appointments.edit', $appointment->id) }}"
        title="Editar"
        onclick="event.stopPropagation()"
        class="inline-flex items-center justify-center rounded-xl border border-amber-100 bg-amber-50 px-2.5 py-1.5 text-xs font-medium text-amber-700 hover:bg-amber-100 dark:border-amber-900/40 dark:bg-amber-900/20 dark:text-amber-300 table-action-btn tenant-action-edit"
    >
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
        </svg>
    </a>

    @if (auth('tenant')->user() && auth('tenant')->user()->role === 'admin')
        <form
            id="appointments-delete-form-{{ $appointment->id }}"
            action="{{ workspace_route('tenant.appointments.destroy', $appointment->id) }}"
            method="POST"
            class="inline-flex js-stop-propagation"
           
        >
            @csrf
            @method('DELETE')

            <button
                type="button"
                title="Excluir"
                data-delete-trigger="1"
                data-delete-form="#appointments-delete-form-{{ $appointment->id }}"
                data-delete-title="Excluir agendamento"
                data-delete-message="Tem certeza que deseja excluir este agendamento?"
                onclick="event.stopPropagation()"
                class="inline-flex items-center justify-center rounded-xl border border-red-100 bg-red-50 px-2.5 py-1.5 text-xs font-medium text-red-700 hover:bg-red-100 dark:border-red-900/40 dark:bg-red-900/20 dark:text-red-300 table-action-btn tenant-action-delete"
            >
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
            </button>
        </form>
    @endif
</div>

