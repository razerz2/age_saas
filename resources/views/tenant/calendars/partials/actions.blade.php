<div class="flex gap-2 flex-wrap">
    <x-table-action-button
        href="{{ workspace_route('tenant.calendars.show', $calendar->id) }}"
        title="Ver"
        color="blue">
        <x-icon name="eye-outline" class="w-3 h-3" />
    </x-table-action-button>

    <x-table-action-button
        href="{{ workspace_route('tenant.calendars.edit', $calendar->id) }}"
        title="Editar"
        color="amber">
        <x-icon name="pencil-outline" class="w-3 h-3" />
    </x-table-action-button>
</div>
