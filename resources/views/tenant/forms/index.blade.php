@extends('layouts.tailadmin.app')

@section('title', 'Formulários')
@section('page', 'forms')

@section('content')

<div class="bg-white rounded-lg shadow-sm border border-gray-200">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-900">
            Lista de Formulários
        </h2>
    </div>

    <div class="p-6">
        <x-tenant.grid
            id="forms-grid"
            :columns="[
                ['name' => 'name', 'label' => 'Nome'],
                ['name' => 'doctor', 'label' => 'Médico'],
                ['name' => 'specialty', 'label' => 'Especialidade'],
                ['name' => 'status_badge', 'label' => 'Status'],
                ['name' => 'created_at', 'label' => 'Criado em'],
                ['name' => 'actions', 'label' => 'Ações'],
            ]"
            ajaxUrl="{{ workspace_route('tenant.forms.grid-data') }}"
            :pagination="true"
            :search="true"
            :sort="true"
        />
    </div>
</div>

@endsection
