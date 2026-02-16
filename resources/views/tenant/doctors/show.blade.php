@extends('layouts.tailadmin.app')

@section('title', 'Detalhes do Médico')

@section('content')
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    Detalhes do Médico
                </h1>
                <nav class="flex mt-2" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 md:space-x-3">
                        <li class="inline-flex items-center">
                            <a href="{{ workspace_route('tenant.dashboard') }}" class="text-gray-700 hover:text-gray-900">
                                Dashboard
                            </a>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414L9 14.707a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <a href="{{ workspace_route('tenant.doctors.index') }}" class="ml-1 text-gray-700 hover:text-gray-900">Médicos</a>
                            </div>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414L9 14.707a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="ml-1 text-gray-500">Detalhes</span>
                            </div>
                        </li>
                    </ol>
                </nav>
            </div>
            <div class="flex items-center justify-end gap-3 flex-nowrap">
                <a href="{{ workspace_route('tenant.doctors.edit', $doctor->id) }}" class="btn-patient-primary inline-flex items-center gap-2">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Editar
                </a>
                <a href="{{ workspace_route('tenant.doctors.index') }}" class="btn-patient-secondary inline-flex items-center gap-2">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Voltar
                </a>
                @if(!$doctor->hasAppointments())
                    <form action="{{ workspace_route('tenant.doctors.destroy', $doctor->id) }}" method="POST" class="inline"
                          onsubmit="event.preventDefault(); confirmAction({ title: 'Excluir médico', message: 'Tem certeza que deseja excluir este médico? Esta ação não pode ser desfeita.', confirmText: 'Excluir', cancelText: 'Cancelar', type: 'error', onConfirm: () => event.target.submit() }); return false;">
                        @csrf
                        @method('DELETE')
                    <button type="submit" class="btn-patient-secondary text-red-600 hover:text-red-800 inline-flex items-center gap-2">
                            <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            Excluir
                        </button>
                    </form>
                @else
                    <button type="button" class="btn-patient-secondary text-gray-500 cursor-not-allowed inline-flex items-center gap-2" title="Não é possível excluir médico com atendimentos cadastrados">
                        <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                        Excluir
                    </button>
                @endif
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 mt-6">
        <div class="px-6 py-5 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Informações do Médico</h2>
        </div>

        <div class="p-6 space-y-6">
            <div class="flex flex-wrap gap-3">
                @if ($doctor->status === 'active')
                    <span class="inline-flex items-center px-3 py-1 rounded-full bg-green-100 text-green-800 text-xs font-semibold">
                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.704 5.29a1 1 0 00-1.414-1.414L8 11.166 5.707 8.871a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l7-7z" clip-rule="evenodd"></path>
                        </svg>
                        Ativo
                    </span>
                @else
                    <span class="inline-flex items-center px-3 py-1 rounded-full bg-red-100 text-red-800 text-xs font-semibold">
                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1.414-9.414a1 1 0 10-1.414 1.414l1.414-1.414zm0 0L10 12l-1.414-1.414a1 1 0 011.414-1.414l1.414 1.414z" clip-rule="evenodd"></path>
                        </svg>
                        Bloqueado
                    </span>
                @endif
                <span class="inline-flex items-center px-3 py-1 rounded-full bg-blue-100 text-blue-800 text-xs font-semibold">
                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM11 12H9v4h2v-4z" clip-rule="evenodd"></path>
                    </svg>
                    Médico
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4">
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">ID</p>
                    <p class="text-gray-900 dark:text-gray-100 font-semibold">{{ $doctor->id }}</p>
                </div>
                <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4">
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Usuário vinculado</p>
                    <p class="text-gray-900 dark:text-gray-100 font-semibold">{{ $doctor->user->name_full ?? $doctor->user->name ?? 'N/A' }}</p>
                </div>
                <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4">
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">CRM/Registro</p>
                    <p class="text-gray-900 dark:text-gray-100 font-semibold">{{ $doctor->crm_number ?? 'N/A' }}</p>
                </div>
                <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4">
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Estado (UF)</p>
                    <p class="text-gray-900 dark:text-gray-100 font-semibold">{{ $doctor->crm_state ?? 'N/A' }}</p>
                </div>
            </div>

            @if($doctor->signature)
                <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4">
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Assinatura</p>
                    <p class="text-gray-900 dark:text-gray-100 font-medium">{{ $doctor->signature }}</p>
                </div>
            @endif

            <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4">
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">Especialidades</p>
                @if($doctor->specialties->count())
                    <div class="flex flex-wrap gap-2">
                        @foreach($doctor->specialties as $specialty)
                            <span class="inline-flex items-center px-3 py-1 rounded-full bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 text-xs font-semibold">
                                {{ $specialty->name }}
                            </span>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-500 dark:text-gray-400">Nenhuma especialidade cadastrada</p>
                @endif
            </div>

            @php
                $customizationEnabled = tenant_setting('professional.customization_enabled') === 'true';
            @endphp
            @if($customizationEnabled)
                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                    <p class="text-sm text-blue-800 dark:text-blue-200 font-medium mb-2">Personalização de rótulos</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @if($doctor->label_singular)
                            <div class="bg-white dark:bg-gray-900/60 rounded-lg p-3">
                                <p class="text-xs text-gray-500">Tipo (singular)</p>
                                <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $doctor->label_singular }}</p>
                            </div>
                        @endif
                        @if($doctor->label_plural)
                            <div class="bg-white dark:bg-gray-900/60 rounded-lg p-3">
                                <p class="text-xs text-gray-500">Tipo (plural)</p>
                                <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $doctor->label_plural }}</p>
                            </div>
                        @endif
                        @if($doctor->registration_label)
                            <div class="bg-white dark:bg-gray-900/60 rounded-lg p-3">
                                <p class="text-xs text-gray-500">Registro (rótulo)</p>
                                <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $doctor->registration_label }}</p>
                            </div>
                        @endif
                        @if($doctor->registration_value)
                            <div class="bg-white dark:bg-gray-900/60 rounded-lg p-3">
                                <p class="text-xs text-gray-500">Registro completo</p>
                                <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $doctor->registration_value }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Criado em</p>
                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $doctor->created_at->format('d/m/Y H:i') }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Atualizado em</p>
                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $doctor->updated_at->format('d/m/Y H:i') }}</p>
                </div>
            </div>
        </div>
    </div>
@endsection
