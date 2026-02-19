<div class="flex gap-2 flex-wrap">
    <x-table-action-button
        href="{{ workspace_route('tenant.business-hours.edit', $hour->id) }}"
        title="Editar"
        color="amber">
        <x-icon name="pencil-outline" class="w-3 h-3" />
    </x-table-action-button>

    <form action="{{ workspace_route('tenant.business-hours.destroy', $hour->id) }}"
          method="POST"
          class="inline">
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
</div>
