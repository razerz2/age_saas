@php /** @var \App\Models\Tenant\RecurringAppointment $recurring */ @endphp
<div class="flex gap-2 flex-wrap">
    <x-table-action-button href="{{ workspace_route('tenant.recurring-appointments.show', ['id' => $recurring->id]) }}"
        title="Ver"
        color="blue">
        <x-icon name="eye-outline" class="w-3 h-3" />
    </x-table-action-button>
    <x-table-action-button href="{{ workspace_route('tenant.recurring-appointments.edit', ['id' => $recurring->id]) }}"
        title="Editar"
        color="amber">
        <x-icon name="pencil-outline" class="w-3 h-3" />
    </x-table-action-button>
</div>
