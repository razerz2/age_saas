<div class="flex gap-2 flex-wrap">
    <x-table-action-button 
        href="{{ workspace_route('tenant.appointments.show', $appointment->id) }}"
        title="Ver"
        color="blue"
        class="js-stop-propagation">
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
        </svg>
    </x-table-action-button>

    <x-table-action-button 
        href="{{ workspace_route('tenant.appointments.edit', $appointment->id) }}"
        title="Editar"
        color="amber"
        class="js-stop-propagation">
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
        </svg>
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
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
            </x-table-action-button>
        </form>
    @endif
</div>
