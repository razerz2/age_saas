<div class="actions-wrap">
    <a
        href="{{ workspace_route('tenant.agenda-settings.show', $calendar->id) }}"
        title="Visualizar"
        onclick="event.stopPropagation()"
        class="inline-flex items-center justify-center rounded-xl border border-blue-100 bg-blue-50 px-2.5 py-1.5 text-xs font-medium text-blue-700 hover:bg-blue-100 dark:border-blue-900/40 dark:bg-blue-900/20 dark:text-blue-300 table-action-btn tenant-action-view"
    >
        <x-icon name="eye-outline" class="w-3 h-3" />
    </a>

    <a
        href="{{ workspace_route('tenant.agenda-settings.edit', $calendar->id) }}"
        title="Editar"
        onclick="event.stopPropagation()"
        class="inline-flex items-center justify-center rounded-xl border border-amber-100 bg-amber-50 px-2.5 py-1.5 text-xs font-medium text-amber-700 hover:bg-amber-100 dark:border-amber-900/40 dark:bg-amber-900/20 dark:text-amber-300 table-action-btn tenant-action-edit"
    >
        <x-icon name="pencil-outline" class="w-3 h-3" />
    </a>

    <a
        href="{{ workspace_route('tenant.agenda-settings.calendar-sync', $calendar->id) }}"
        title="Sincronizar calendário"
        onclick="event.stopPropagation()"
        class="inline-flex items-center justify-center rounded-xl border border-blue-100 bg-blue-50 px-2.5 py-1.5 text-xs font-medium text-blue-700 hover:bg-blue-100 dark:border-blue-900/40 dark:bg-blue-900/20 dark:text-blue-300 table-action-btn"
    >
        <x-icon name="calendar-outline" class="w-3 h-3" />
    </a>

    <form
        action="{{ workspace_route('tenant.agenda-settings.toggle-status', $calendar->id) }}"
        method="POST"
        class="inline-flex"
    >
        @csrf
        @method('PATCH')
        <button
            type="submit"
            title="{{ $calendar->is_active ? 'Desativar' : 'Ativar' }}"
            onclick="event.stopPropagation()"
            class="inline-flex items-center justify-center rounded-xl border px-2.5 py-1.5 text-xs font-medium table-action-btn {{ $calendar->is_active ? 'border-gray-200 bg-gray-50 text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-900/20 dark:text-gray-300' : 'border-green-100 bg-green-50 text-green-700 hover:bg-green-100 dark:border-green-900/40 dark:bg-green-900/20 dark:text-green-300' }}"
        >
            <x-icon name="toggle-switch-outline" class="w-3 h-3" />
        </button>
    </form>

    <form
        id="agenda-settings-delete-form-{{ $calendar->id }}"
        action="{{ workspace_route('tenant.agenda-settings.destroy', $calendar->id) }}"
        method="POST"
        class="inline-flex"
    >
        @csrf
        @method('DELETE')
        <button
            type="button"
            title="Excluir"
            data-delete-trigger="1"
            data-delete-form="#agenda-settings-delete-form-{{ $calendar->id }}"
            data-delete-title="Excluir agenda"
            data-delete-message="Tem certeza que deseja excluir esta agenda? Esta ação remove calendário, horários e tipos vinculados."
            onclick="event.stopPropagation()"
            class="inline-flex items-center justify-center rounded-xl border border-red-100 bg-red-50 px-2.5 py-1.5 text-xs font-medium text-red-700 hover:bg-red-100 dark:border-red-900/40 dark:bg-red-900/20 dark:text-red-300 table-action-btn tenant-action-delete"
        >
            <x-icon name="trash-can-outline" class="w-3 h-3" />
        </button>
    </form>
</div>
