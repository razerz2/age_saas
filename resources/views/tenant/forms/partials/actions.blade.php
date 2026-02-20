<div class="flex gap-2 flex-wrap items-center">
    <x-table-action-button
        href="{{ workspace_route('tenant.forms.show', $form->id) }}"
        title="Visualizar"
        color="blue">
        <x-icon name="eye-outline" size="text-base" />
    </x-table-action-button>

    <x-table-action-button
        href="{{ workspace_route('tenant.forms.edit', $form->id) }}"
        title="Editar"
        color="amber">
        <x-icon name="pencil-outline" size="text-base" />
    </x-table-action-button>

    <form action="{{ workspace_route('tenant.forms.destroy', $form->id) }}" method="POST" class="inline"
          data-confirm-form-delete="true" data-form-name="{{ $form->name }}">
        @csrf
        @method('DELETE')
        <x-table-action-button type="submit" title="Excluir" color="red">
            <x-icon name="trash-can-outline" size="text-base" />
        </x-table-action-button>
    </form>
</div>
