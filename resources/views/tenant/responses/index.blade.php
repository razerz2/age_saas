@extends('layouts.tailadmin.app')

@section('title', 'Respostas de Formulários')
@section('page', 'responses')

@section('content')
<div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-6">
    <!-- Page Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Respostas de Formulários</h1>
                <nav class="flex mt-2" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 md:space-x-3">
                        <li class="inline-flex items-center">
                            <a href="{{ workspace_route('tenant.dashboard') }}" class="text-gray-700 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white inline-flex items-center">
                                <x-icon name="home-outline" class="w-4 h-4 mr-2" />
                                Dashboard
                            </a>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <x-icon name="chevron-right" class="w-4 h-4 text-gray-400" />
                                <span class="ml-1 text-gray-500 dark:text-gray-400">Respostas de Formulários</span>
                            </div>
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <div class="max-w-6xl mx-auto">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h4 class="text-xl font-semibold text-gray-900 dark:text-white flex items-center">
                    <x-icon name="information-outline" class="w-5 h-5 mr-2 text-blue-600" />
                    Lista de Respostas
                </h4>
            </div>
            <div class="p-6">
                <div>
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700" id="datatable-list">
                        <thead class="bg-gray-50 dark:bg-gray-700/40">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">ID</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Formulário</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Paciente</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Agendamento</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Data de Envio</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Ações</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                            @foreach ($responses as $response)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40">
                                    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">{{ truncate_uuid($response->id) }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">{{ $response->form->name ?? 'N/A' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">{{ $response->patient->full_name ?? 'N/A' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">{{ $response->appointment_id ?? 'N/A' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">{{ $response->submitted_at ? $response->submitted_at->format('d/m/Y H:i') : 'N/A' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">{{ $response->status ?? 'N/A' }}</td>
                                    <td class="px-4 py-3 text-sm text-right">
                                        <div class="flex flex-wrap justify-end gap-2">
                                            <a href="{{ workspace_route('tenant.responses.show', $response->id) }}" class="btn btn-outline">Ver</a>
                                            <a href="{{ workspace_route('tenant.responses.edit', $response->id) }}" class="btn btn-outline">Editar</a>
                                            <form action="{{ workspace_route('tenant.responses.destroy', $response->id) }}"
                                                  method="POST"
                                                  class="delete-response-form"
                                                  data-confirm-delete="true" data-form-name="{{ $response->form->name ?? 'N/A' }}" data-patient-name="{{ $response->patient->full_name ?? 'N/A' }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger">
                                                    <x-icon name="trash-can-outline" class="w-4 h-4 mr-1" />
                                                    Excluir
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

