<div class="flex gap-2 flex-wrap">
    <x-table-action-button 
        href="{{ workspace_route('tenant.specialties.show', $specialty->id) }}"
        title="Ver"
        color="blue">
        <x-icon name="eye-outline" size="text-xs" />
    </x-table-action-button>

    <x-table-action-button 
        href="{{ workspace_route('tenant.specialties.edit', $specialty->id) }}"
        title="Editar"
        color="amber">
        <x-icon name="pencil-outline" size="text-xs" />
    </x-table-action-button>
</div>
