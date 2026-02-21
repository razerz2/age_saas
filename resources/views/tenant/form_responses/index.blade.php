@extends('layouts.tailadmin.app')

@section('title', 'Respostas de Formulários')
@section('page', 'form_responses')

@section('content')

<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Respostas</h1>
            <nav class="flex mt-2" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <a href="{{ workspace_route('tenant.dashboard') }}"
                            class="text-gray-700 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white inline-flex items-center">
                            <x-icon name="home-outline" size="text-base" class="mr-2" />
                            Dashboard
                        </a>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <x-icon name="chevron-right" size="text-sm" class="text-gray-400" />
                            <span class="ml-1 text-gray-500 dark:text-gray-400">Respostas</span>
                        </div>
                    </li>
                </ol>
            </nav>
        </div>
    </div>
</div>

<div class="bg-white rounded-lg shadow-sm border border-gray-200">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-900">
            Lista de Respostas
        </h2>
    </div>

    <div class="p-6">
        <div
            id="responses-grid"
            data-row-click-link-selector='a[title="Ver"]'
        >
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
</div>

@endsection
