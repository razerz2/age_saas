<div class="flex gap-2 flex-wrap">
    <x-table-action-button 
        href="{{ workspace_route('tenant.patients.show', $patient->id) }}"
        title="Ver detalhes"
        color="blue"
        data-stop-propagation="true">
        <x-icon name="eye-outline" size="text-xs" />
    </x-table-action-button>

    <x-table-action-button 
        href="{{ workspace_route('tenant.patients.edit', $patient->id) }}"
        title="Editar"
        color="amber"
        data-stop-propagation="true">
        <x-icon name="pencil-outline" size="text-xs" />
    </x-table-action-button>

    <x-table-action-button 
        href="{{ workspace_route('tenant.patients.login.form', $patient->id) }}"
        title="{{ isset($patient->login) ? 'Editar login' : 'Criar login' }}"
        color="purple"
        data-stop-propagation="true">
        <x-icon name="key-outline" size="text-xs" />
    </x-table-action-button>
</div>

