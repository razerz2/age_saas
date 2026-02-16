<div class="flex gap-2 flex-wrap">
    <x-table-action-button 
        href="{{ workspace_route('tenant.calendars.show', $calendar->id) }}"
        title="Ver"
        color="blue">
        <!-- ícone olho -->
    </x-table-action-button>

    <x-table-action-button 
        href="{{ workspace_route('tenant.calendars.edit', $calendar->id) }}"
        title="Editar"
        color="amber">
        <!-- ícone lápis -->
    </x-table-action-button>
</div>
