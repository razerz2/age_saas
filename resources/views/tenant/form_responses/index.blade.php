@extends('layouts.tailadmin.app')

@section('title', 'Respostas de Formulários')

@section('content')

<div class="bg-white rounded-lg shadow-sm border border-gray-200">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-900">
            Lista de Respostas
        </h2>
    </div>

    <div class="p-6">
        <x-tenant.grid
            id="form-responses-grid"
            :columns="[
                ['name' => 'form', 'label' => 'Formulário'],
                ['name' => 'patient', 'label' => 'Paciente'],
                ['name' => 'appointment', 'label' => 'Consulta'],
                ['name' => 'created_at', 'label' => 'Respondido em'],
                ['name' => 'actions', 'label' => 'Ações'],
            ]"
            ajaxUrl="{{ workspace_route('tenant.form-responses.grid-data') }}"
            :pagination="true"
            :search="true"
            :sort="true"
        />
    </div>
</div>

@endsection
