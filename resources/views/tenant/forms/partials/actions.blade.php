<div class="flex gap-2 flex-wrap">
    <x-table-action-button 
        href="{{ workspace_route('tenant.forms.show', $form->id) }}"
        title="Visualizar"
        color="blue">
        <!-- ícone olho -->
    </x-table-action-button>

    <x-table-action-button 
        href="{{ workspace_route('tenant.forms.edit', $form->id) }}"
        title="Editar"
        color="amber">
        <!-- ícone lápis -->
    </x-table-action-button>

    <x-table-action-button 
        href="{{ workspace_route('tenant.forms.builder', $form->id) }}"
        title="Builder"
        color="purple">
        <!-- ícone builder -->
    </x-table-action-button>
</div>
