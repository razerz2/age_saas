@extends('layouts.tailadmin.app')

@section('title', 'Detalhes do Médico')
@section('page', 'doctors')

@section('content')
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <x-icon name="stethoscope" size="text-xl" class="text-blue-600" />
                    Detalhes do Médico
                </h1>
                <nav class="flex mt-2" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 md:space-x-3">
                        <li class="inline-flex items-center">
                            <a href="{{ workspace_route('tenant.dashboard') }}" class="text-gray-700 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
                                Dashboard
                            </a>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <x-icon name="chevron-right" size="text-sm" class="text-gray-400" />
                                <a href="{{ workspace_route('tenant.doctors.index') }}" class="ml-1 text-gray-700 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">Médicos</a>
                            </div>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <x-icon name="chevron-right" size="text-sm" class="text-gray-400" />
                                <span class="ml-1 text-gray-500 dark:text-gray-400">Detalhes</span>
                            </div>
                        </li>
                    </ol>
                </nav>
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
                        <x-icon name="check-circle-outline" size="text-xs" class="mr-1" />
                        Ativo
                    </span>
                @else
                    <span class="inline-flex items-center px-3 py-1 rounded-full bg-red-100 text-red-800 text-xs font-semibold">
                        <x-icon name="close-circle-outline" size="text-xs" class="mr-1" />
                        Bloqueado
                    </span>
                @endif
                <span class="inline-flex items-center px-3 py-1 rounded-full bg-blue-100 text-blue-800 text-xs font-semibold">
                    <x-icon name="stethoscope" size="text-xs" class="mr-1" />
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

            <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <a href="{{ workspace_route('tenant.doctors.index') }}" class="btn btn-outline inline-flex items-center">
                        <x-icon name="arrow-left" size="text-sm" class="mr-2" />
                        Voltar
                    </a>

                    <div class="flex flex-wrap items-center justify-end gap-3">
                        <a href="{{ workspace_route('tenant.doctors.edit', $doctor->id) }}" class="btn btn-outline inline-flex items-center">
                            <x-icon name="pencil-outline" size="text-sm" class="mr-2" />
                            Editar
                        </a>

                        @if(!$doctor->hasAppointments())
                            <form action="{{ workspace_route('tenant.doctors.destroy', $doctor->id) }}" method="POST" class="inline"
                                  data-confirm-submit="true"
                                  data-confirm-title="Excluir médico"
                                  data-confirm-message="Tem certeza que deseja excluir este médico? Esta ação não pode ser desfeita."
                                  data-confirm-confirm-text="Excluir"
                                  data-confirm-cancel-text="Cancelar"
                                  data-confirm-type="error">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger inline-flex items-center">
                                    <x-icon name="trash-can-outline" size="text-sm" class="mr-2" />
                                    Excluir
                                </button>
                            </form>
                        @else
                            <button type="button" class="btn btn-danger opacity-60 cursor-not-allowed inline-flex items-center" title="Não é possível excluir médico com atendimentos cadastrados" disabled>
                                <x-icon name="trash-can-outline" size="text-sm" class="mr-2" />
                                Excluir
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
