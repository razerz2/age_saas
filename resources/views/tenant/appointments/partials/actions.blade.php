<div class="flex gap-2 flex-wrap">
    <x-table-action-button 
        href="{{ workspace_route('tenant.appointments.show', $appointment->id) }}"
        title="Ver"
        color="blue"
        class="js-stop-propagation">
        <x-icon name="eye-outline" class="w-3 h-3" />
    </x-table-action-button>

    <x-table-action-button 
        href="{{ workspace_route('tenant.appointments.edit', $appointment->id) }}"
        title="Editar"
        color="amber"
        class="js-stop-propagation">
        <x-icon name="pencil-outline" class="w-3 h-3" />
    </x-table-action-button>

    @if (auth('tenant')->user() && auth('tenant')->user()->role === 'admin')
        <form action="{{ workspace_route('tenant.appointments.destroy', $appointment->id) }}" method="POST" class="inline js-stop-propagation">
            @csrf
            @method('DELETE')
            <x-table-action-button 
                as="button"
                type="submit"
                title="Excluir"
                color="red">
                <x-icon name="trash-can-outline" class="w-3 h-3" />
            </x-table-action-button>
        </form>
    @endif
</div>
