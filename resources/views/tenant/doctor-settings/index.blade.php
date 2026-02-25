@extends('layouts.tailadmin.app')

@section('title', 'ConfiguraÃƒÂ§ÃƒÂµes')
@section('page', 'doctor-settings')

@section('content')

    <!-- Page Header -->
    <div class="page-header mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white mb-2">ConfiguraÃƒÂ§ÃƒÂµes</h1>
                <nav class="flex" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 md:space-x-3">
                        <li class="inline-flex items-center">
                            <a href="{{ workspace_route('tenant.dashboard') }}" class="text-gray-700 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
                                <x-icon name="home-outline" size="text-base" class="mr-2" />
                                Dashboard
                            </a>
                        </li>
                        <li aria-current="page">
                            <div class="flex items-center">
                                <x-icon name="chevron-right" size="text-sm" class="text-gray-400" />
                                <span class="ml-1 text-gray-500 dark:text-gray-400 md:ml-2">ConfiguraÃƒÂ§ÃƒÂµes</span>
                            </div>
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <div class="mb-6 flex items-center justify-between">
        <a href="{{ workspace_route('tenant.doctors.show', $doctor->id) }}" class="btn btn-outline inline-flex items-center">
            <x-icon name="arrow-left" size="text-sm" class="mr-2" />
            Voltar
        </a>
        <div></div>
    </div>

    @if (session('success'))
        <div class="bg-green-50 border border-green-200 rounded-md p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <x-icon name="check-circle-outline" size="text-lg" class="text-green-400" />
                </div>
                <div class="ml-3">
                    <p class="text-sm text-green-700">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="bg-red-50 border border-red-200 rounded-md p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <x-icon name="alert-circle-outline" size="text-lg" class="text-red-400" />
                </div>
                <div class="ml-3">
                    <p class="text-sm text-red-700">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Card Principal -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white flex items-center">
                <x-icon name="account-outline" size="text-xl" class="mr-2 text-blue-600" />
                {{ $doctor->user->display_name ?? $doctor->user->name }}
            </h2>
        </div>

        <div class="p-6">
            {{-- Abas --}}
            <ul class="nav nav-tabs flex flex-wrap border-b border-gray-200 dark:border-gray-700 text-sm font-medium" id="settingsTabs" role="tablist">
                <li class="nav-item mr-2" role="presentation">
                    <button class="nav-link active inline-flex items-center px-4 py-2 rounded-t-lg border border-transparent text-gray-600 dark:text-gray-300 hover:text-blue-600 hover:border-blue-600" id="calendar-tab" data-bs-toggle="tab" data-bs-target="#calendar" type="button" role="tab">
                        <x-icon name="calendar-month-outline" size="text-sm" class="mr-2" />
                        CalendÃƒÂ¡rio
                    </button>
                </li>
                <li class="nav-item mr-2" role="presentation">
                    <button class="nav-link inline-flex items-center px-4 py-2 rounded-t-lg border border-transparent text-gray-600 dark:text-gray-300 hover:text-blue-600 hover:border-blue-600" id="hours-tab" data-bs-toggle="tab" data-bs-target="#hours" type="button" role="tab">
                        <x-icon name="clock-outline" size="text-sm" class="mr-2" />
                        HorÃƒÂ¡rios
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link inline-flex items-center px-4 py-2 rounded-t-lg border border-transparent text-gray-600 dark:text-gray-300 hover:text-blue-600 hover:border-blue-600" id="types-tab" data-bs-toggle="tab" data-bs-target="#types" type="button" role="tab">
                        <x-icon name="file-document-outline" size="text-sm" class="mr-2" />
                        Tipos de Atendimento
                    </button>
                </li>
            </ul>

            <div class="tab-content mt-6" id="settingsTabsContent">
                {{-- Aba CalendÃƒÂ¡rio --}}
                <div class="tab-pane fade show active" id="calendar" role="tabpanel">
                    <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/20 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                            <x-icon name="calendar-month-outline" size="text-lg" class="mr-2 text-blue-600" />
                            CalendÃƒÂ¡rio
                        </h3>

                        @if($calendar)
                            <form action="{{ workspace_route('tenant.doctor-settings.update-calendar') }}" method="POST">
                                @csrf
                                @method('PUT')

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Nome <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('name') border-red-500 @enderror"
                                               name="name" value="{{ old('name', $calendar->name) }}"
                                               placeholder="Ex: CalendÃƒÂ¡rio Principal" required>
                                        @error('name')
                                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            ID Externo
                                        </label>
                                        <input type="text" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('external_id') border-red-500 @enderror"
                                               name="external_id" value="{{ old('external_id', $calendar->external_id) }}"
                                               placeholder="ID do calendÃƒÂ¡rio em sistema externo (opcional)">
                                        <small class="text-gray-500 dark:text-gray-400">ID usado para sincronizaÃƒÂ§ÃƒÂ£o com calendÃƒÂ¡rios externos</small>
                                        @error('external_id')
                                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                <div class="flex justify-end mt-6">
                                    <button type="submit" class="btn btn-primary inline-flex items-center">
                                        <x-icon name="content-save-outline" size="text-sm" class="mr-2" />
                                        Salvar CalendÃƒÂ¡rio
                                    </button>
                                </div>
                            </form>
                        @else
                            <form action="{{ workspace_route('tenant.doctor-settings.update-calendar') }}" method="POST">
                                @csrf
                                @method('PUT')

                                <div class="bg-blue-50 border border-blue-200 text-blue-800 rounded-md p-4 mb-6">
                                    <div class="flex items-start">
                                        <x-icon name="information-outline" size="text-lg" class="mr-2 mt-0.5" />
                                        <span>Nenhum calendÃƒÂ¡rio cadastrado. Preencha os dados abaixo para criar um.</span>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Nome <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('name') border-red-500 @enderror"
                                               name="name" value="{{ old('name') }}"
                                               placeholder="Ex: CalendÃƒÂ¡rio Principal" required>
                                        @error('name')
                                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            ID Externo
                                        </label>
                                        <input type="text" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('external_id') border-red-500 @enderror"
                                               name="external_id" value="{{ old('external_id') }}"
                                               placeholder="ID do calendÃƒÂ¡rio em sistema externo (opcional)">
                                        <small class="text-gray-500 dark:text-gray-400">ID usado para sincronizaÃƒÂ§ÃƒÂ£o com calendÃƒÂ¡rios externos</small>
                                        @error('external_id')
                                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                <div class="flex justify-end mt-6">
                                    <button type="submit" class="btn btn-primary inline-flex items-center">
                                        <x-icon name="plus" size="text-sm" class="mr-2" />
                                        Criar CalendÃƒÂ¡rio
                                    </button>
                                </div>
                            </form>
                        @endif
                    </div>
                </div>

                {{-- Aba HorÃƒÂ¡rios --}}
                <div class="tab-pane fade" id="hours" role="tabpanel">
                    <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/20 p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                                <x-icon name="clock-outline" size="text-lg" class="mr-2 text-blue-600" />
                                HorÃƒÂ¡rios de Atendimento
                            </h3>
                            <button type="button" class="btn btn-primary inline-flex items-center" data-bs-toggle="modal" data-bs-target="#addHourModal">
                                <x-icon name="plus" size="text-sm" class="mr-2" />
                                Novo HorÃƒÂ¡rio
                            </button>
                        </div>

                        @if($businessHours->count() > 0)
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-100 dark:bg-gray-800">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Dia da Semana</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">HorÃƒÂ¡rio InÃƒÂ­cio</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">HorÃƒÂ¡rio Fim</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Intervalo</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider" style="width: 140px;">AÃƒÂ§ÃƒÂµes</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                                        @php
                                            $days = ['Domingo', 'Segunda', 'TerÃƒÂ§a', 'Quarta', 'Quinta', 'Sexta', 'SÃƒÂ¡bado'];
                                        @endphp
                                        @foreach($businessHours as $hour)
                                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $days[$hour->weekday] ?? $hour->weekday }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">{{ $hour->start_time }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">{{ $hour->end_time }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                    @if($hour->break_start_time && $hour->break_end_time)
                                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-200">
                                                            Intervalo: {{ $hour->break_start_time }} - {{ $hour->break_end_time }}
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                                    <button type="button" class="inline-flex items-center px-3 py-1.5 bg-amber-100 hover:bg-amber-200 text-amber-700 dark:bg-amber-900 dark:text-amber-300 dark:hover:bg-amber-800 text-xs font-medium rounded-md transition-colors tenant-action-edit"
                                                            data-action="edit-hour"
                                                            data-hour-id="{{ $hour->id }}"
                                                            data-weekday="{{ $hour->weekday }}"
                                                            data-start-time="{{ $hour->start_time }}"
                                                            data-end-time="{{ $hour->end_time }}"
                                                            data-break-start="{{ $hour->break_start_time }}"
                                                            data-break-end="{{ $hour->break_end_time }}">
                                                        Editar
                                                    </button>
                                                    <form action="{{ workspace_route('tenant.doctor-settings.destroy-business-hour', $hour->id) }}"
                                                          method="POST" class="inline" id="doctor-settings-hours-delete-form-{{ $hour->id }}">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="button"
                                                            class="btn btn-outline tenant-action-delete"
                                                            data-delete-trigger="1"
                                                            data-delete-form="#doctor-settings-hours-delete-form-{{ $hour->id }}"
                                                            data-delete-title="Remover horário"
                                                            data-delete-message="Tem certeza que deseja remover este horário?">
                                                            Excluir
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="bg-blue-50 border border-blue-200 text-blue-800 rounded-md p-4">
                                <div class="flex items-start">
                                    <x-icon name="information-outline" size="text-lg" class="mr-2 mt-0.5" />
                                    <span>Nenhum horÃƒÂ¡rio cadastrado. Clique em "Novo HorÃƒÂ¡rio" para adicionar.</span>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Aba Tipos --}}
                <div class="tab-pane fade" id="types" role="tabpanel">
                    <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/20 p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                                <x-icon name="file-document-outline" size="text-lg" class="mr-2 text-blue-600" />
                                Tipos de Atendimento
                            </h3>
                            <button type="button" class="btn btn-primary inline-flex items-center" data-bs-toggle="modal" data-bs-target="#addTypeModal">
                                <x-icon name="plus" size="text-sm" class="mr-2" />
                                Novo Tipo
                            </button>
                        </div>

                        @if($appointmentTypes->count() > 0)
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-100 dark:bg-gray-800">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Nome</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">DuraÃƒÂ§ÃƒÂ£o (min)</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider" style="width: 140px;">AÃƒÂ§ÃƒÂµes</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                                        @foreach($appointmentTypes as $type)
                                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $type->name }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">{{ $type->duration_min ?? 'N/A' }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                    @if ($type->is_active)
                                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-200">Ativo</span>
                                                    @else
                                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-200">Inativo</span>
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                                    <button type="button" class="inline-flex items-center px-3 py-1.5 bg-amber-100 hover:bg-amber-200 text-amber-700 dark:bg-amber-900 dark:text-amber-300 dark:hover:bg-amber-800 text-xs font-medium rounded-md transition-colors tenant-action-edit"
                                                            data-action="edit-type"
                                                            data-type-id="{{ $type->id }}"
                                                            data-type-name="{{ $type->name }}"
                                                            data-type-duration="{{ $type->duration_min }}"
                                                            data-type-active="{{ $type->is_active }}">
                                                        Editar
                                                    </button>
                                                    <form action="{{ workspace_route('tenant.doctor-settings.destroy-appointment-type', $type->id) }}"
                                                          method="POST" class="inline" id="doctor-settings-types-delete-form-{{ $type->id }}">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="button"
                                                            class="btn btn-outline tenant-action-delete"
                                                            data-delete-trigger="1"
                                                            data-delete-form="#doctor-settings-types-delete-form-{{ $type->id }}"
                                                            data-delete-title="Remover tipo"
                                                            data-delete-message="Tem certeza que deseja remover este tipo de atendimento?">
                                                            Excluir
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="bg-blue-50 border border-blue-200 text-blue-800 rounded-md p-4">
                                <div class="flex items-start">
                                    <x-icon name="information-outline" size="text-lg" class="mr-2 mt-0.5" />
                                    <span>Nenhum tipo de atendimento cadastrado. Clique em "Novo Tipo" para adicionar.</span>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Adicionar HorÃƒÂ¡rio --}}
    <div class="modal fade" id="addHourModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ workspace_route('tenant.doctor-settings.store-business-hour') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Novo HorÃƒÂ¡rio de Atendimento</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Dias da Semana <span class="text-red-500">*</span>
                            </label>
                            <select id="weekday-select" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('weekdays') border-red-500 @enderror">
                                <option value="">Selecione um dia da semana</option>
                                <option value="0" data-name="Domingo">Domingo</option>
                                <option value="1" data-name="Segunda-feira">Segunda-feira</option>
                                <option value="2" data-name="TerÃƒÂ§a-feira">TerÃƒÂ§a-feira</option>
                                <option value="3" data-name="Quarta-feira">Quarta-feira</option>
                                <option value="4" data-name="Quinta-feira">Quinta-feira</option>
                                <option value="5" data-name="Sexta-feira">Sexta-feira</option>
                                <option value="6" data-name="SÃƒÂ¡bado">SÃƒÂ¡bado</option>
                            </select>
                            <div class="mt-3 flex gap-2">
                                <button type="button" id="add-weekday-btn" class="btn btn-primary">
                                    Adicionar Dia
                                </button>
                                <button type="button" id="clear-weekdays-btn" class="btn btn-outline">
                                    Limpar
                                </button>
                            </div>
                            <div id="selected-weekdays" class="border border-gray-200 dark:border-gray-600 rounded-md p-3 bg-gray-50 dark:bg-gray-700 mt-3" style="min-height: 60px;" data-badge-style="bootstrap">
                                <p class="text-gray-500 dark:text-gray-400 mb-0">Nenhum dia selecionado</p>
                            </div>
                            <div id="weekdays-inputs"></div>
                            @error('weekdays')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 business-hours-form-layout">
                            <div>
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        HorÃƒÂ¡rio InÃƒÂ­cio <span class="text-red-500">*</span>
                                    </label>
                                    <input type="time" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('start_time') border-red-500 @enderror"
                                           name="start_time" value="{{ old('start_time') }}" required>
                                    <small class="text-xs text-gray-400" style="visibility: hidden;">Opcional</small>
                                    @error('start_time')
                                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        HorÃƒÂ¡rio Fim <span class="text-red-500">*</span>
                                    </label>
                                    <input type="time" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('end_time') border-red-500 @enderror"
                                           name="end_time" value="{{ old('end_time') }}" required>
                                    <small class="text-xs text-gray-400" style="visibility: hidden;">Opcional</small>
                                    @error('end_time')
                                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                            <div>
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        InÃƒÂ­cio do Intervalo
                                    </label>
                                    <input type="time" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('break_start_time') border-red-500 @enderror"
                                           name="break_start_time" value="{{ old('break_start_time') }}">
                                    <small class="text-xs text-gray-400">Opcional</small>
                                    @error('break_start_time')
                                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Fim do Intervalo
                                    </label>
                                    <input type="time" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('break_end_time') border-red-500 @enderror"
                                           name="break_end_time" value="{{ old('break_end_time') }}">
                                    <small class="text-xs text-gray-400">Opcional</small>
                                    @error('break_end_time')
                                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Editar HorÃƒÂ¡rio --}}
    <div class="modal fade" id="editHourModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="editHourForm" method="POST" data-action-template="{{ workspace_route('tenant.doctor-settings.update-business-hour', ':id') }}">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title">Editar HorÃƒÂ¡rio de Atendimento</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Dia da Semana <span class="text-red-500">*</span>
                            </label>
                            <select name="weekday" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white" required>
                                <option value="0">Domingo</option>
                                <option value="1">Segunda-feira</option>
                                <option value="2">TerÃƒÂ§a-feira</option>
                                <option value="3">Quarta-feira</option>
                                <option value="4">Quinta-feira</option>
                                <option value="5">Sexta-feira</option>
                                <option value="6">SÃƒÂ¡bado</option>
                            </select>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 business-hours-form-layout">
                            <div>
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        HorÃƒÂ¡rio InÃƒÂ­cio <span class="text-red-500">*</span>
                                    </label>
                                    <input type="time" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white" name="start_time" required>
                                    <small class="text-xs text-gray-400" style="visibility: hidden;">Opcional</small>
                                </div>
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        HorÃƒÂ¡rio Fim <span class="text-red-500">*</span>
                                    </label>
                                    <input type="time" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white" name="end_time" required>
                                    <small class="text-xs text-gray-400" style="visibility: hidden;">Opcional</small>
                                </div>
                            </div>
                            <div>
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        InÃƒÂ­cio do Intervalo
                                    </label>
                                    <input type="time" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white" name="break_start_time">
                                    <small class="text-xs text-gray-400">Opcional</small>
                                </div>
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Fim do Intervalo
                                    </label>
                                    <input type="time" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white" name="break_end_time">
                                    <small class="text-xs text-gray-400">Opcional</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Adicionar Tipo --}}
    <div class="modal fade" id="addTypeModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ workspace_route('tenant.doctor-settings.store-appointment-type') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Novo Tipo de Atendimento</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Nome <span class="text-red-500">*</span>
                            </label>
                            <input type="text" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('name') border-red-500 @enderror"
                                   name="name" value="{{ old('name') }}"
                                   placeholder="Ex: Consulta MÃƒÂ©dica, Retorno, etc." required>
                            @error('name')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    DuraÃƒÂ§ÃƒÂ£o (minutos) <span class="text-red-500">*</span>
                                </label>
                                <input type="number" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('duration_min') border-red-500 @enderror"
                                       name="duration_min" value="{{ old('duration_min', 30) }}"
                                       min="1" placeholder="30" required>
                                @error('duration_min')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Status
                                </label>
                                <select name="is_active" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('is_active') border-red-500 @enderror">
                                    <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>Ativo</option>
                                    <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>Inativo</option>
                                </select>
                                @error('is_active')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Editar Tipo --}}
    <div class="modal fade" id="editTypeModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="editTypeForm" method="POST" data-action-template="{{ workspace_route('tenant.doctor-settings.update-appointment-type', ':id') }}">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title">Editar Tipo de Atendimento</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Nome <span class="text-red-500">*</span>
                            </label>
                            <input type="text" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white" name="name" required>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    DuraÃƒÂ§ÃƒÂ£o (minutos) <span class="text-red-500">*</span>
                                </label>
                                <input type="number" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white" name="duration_min" min="1" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Status
                                </label>
                                <select name="is_active" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                                    <option value="1">Ativo</option>
                                    <option value="0">Inativo</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

