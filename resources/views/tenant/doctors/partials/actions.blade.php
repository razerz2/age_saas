<div class="flex gap-2 flex-wrap">
    <x-table-action-button 
        href="{{ workspace_route('tenant.doctors.show', $doctor->id) }}"
        title="Ver detalhes"
        color="blue"
        data-stop-propagation="true">
        <x-icon name="mdi-eye-outline" size="text-xs" />
    </x-table-action-button>

    <x-table-action-button 
        href="{{ workspace_route('tenant.doctors.edit', $doctor->id) }}"
        title="Editar"
        color="amber"
        data-stop-propagation="true">
        <x-icon name="mdi-pencil-outline" size="text-xs" />
    </x-table-action-button>
</div>
