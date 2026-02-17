@extends('layouts.tailadmin.app')

@section('title', 'Criar Horário Comercial')
@section('page', 'business-hours')

@section('content')

    <div class="page-header mb-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <nav class="min-w-0 flex-1" aria-label="breadcrumb">
                <ol class="flex flex-wrap items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                    <li>
                        <a href="{{ workspace_route('tenant.dashboard') }}" class="hover:text-blue-600 dark:hover:text-white">Dashboard</a>
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="h-4 w-4 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                        </svg>
                        <a href="{{ workspace_route('tenant.business-hours.index') }}" class="hover:text-blue-600 dark:hover:text-white">Horários Comerciais</a>
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="h-4 w-4 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                        </svg>
                        <span class="text-gray-900 dark:text-white font-semibold">Criar</span>
                    </li>
                </ol>
            </nav>
            <div class="flex-shrink-0">
                <x-help-button module="business-hours" />
            </div>
        </div>
    </div>

    <div class="max-w-6xl mx-auto">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h4 class="text-xl font-semibold text-gray-900 dark:text-white">Novo Horário Comercial</h4>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Preencha os dados abaixo para criar um novo horário comercial</p>
            </div>

            <form action="{{ workspace_route('tenant.business-hours.store') }}" method="POST" class="p-6 space-y-8">
                @csrf

                <div>
                    <h5 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Informações do Horário</h5>
                    <div class="grid grid-cols-1 gap-6">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Médico <span class="text-red-500">*</span>
                            </label>
                            <select name="doctor_id" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white @error('doctor_id') border-red-500 @enderror" required>
                                <option value="">Selecione um médico</option>
                                @foreach($doctors as $doctor)
                                    <option value="{{ $doctor->id }}" {{ old('doctor_id') == $doctor->id ? 'selected' : '' }}>{{ $doctor->user->name ?? 'N/A' }}</option>
                                @endforeach
                            </select>
                            @error('doctor_id')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div>
                    <h5 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Dias da Semana</h5>
                    <div class="space-y-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Selecione os dias da semana de atendimento</label>
                        <div class="grid grid-cols-1 gap-4 lg:grid-cols-[1fr_auto]">
                            <select id="weekday-select" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white @error('weekdays') border-red-500 @enderror">
                                <option value="">Selecione um dia da semana</option>
                                <option value="0" data-name="Domingo">Domingo</option>
                                <option value="1" data-name="Segunda-feira">Segunda-feira</option>
                                <option value="2" data-name="Terça-feira">Terça-feira</option>
                                <option value="3" data-name="Quarta-feira">Quarta-feira</option>
                                <option value="4" data-name="Quinta-feira">Quinta-feira</option>
                                <option value="5" data-name="Sexta-feira">Sexta-feira</option>
                                <option value="6" data-name="Sábado">Sábado</option>
                            </select>
                            <div class="flex flex-wrap gap-2">
                                <button type="button" id="add-weekday-btn" class="inline-flex items-center justify-center rounded-lg bg-primary px-4 py-2 text-sm font-medium text-white hover:bg-primary/90 transition-colors">
                                    Adicionar
                                </button>
                                <button type="button" id="clear-weekdays-btn" class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors">
                                    Limpar
                                </button>
                            </div>
                        </div>

                        <div id="selected-weekdays" class="min-h-[60px] rounded-lg border border-dashed border-gray-300 bg-gray-50 p-4 text-sm text-gray-600 dark:border-gray-600 dark:bg-gray-700/40 dark:text-gray-300" data-initial-selected='@json(old('weekdays', []))'>
                            @if(old('weekdays'))
                                @php
                                    $weekdayNames = [
                                        0 => 'Domingo',
                                        1 => 'Segunda-feira',
                                        2 => 'Terça-feira',
                                        3 => 'Quarta-feira',
                                        4 => 'Quinta-feira',
                                        5 => 'Sexta-feira',
                                        6 => 'Sábado'
                                    ];
                                @endphp
                                @foreach(old('weekdays') as $weekday)
                                    <span class="weekday-badge inline-flex items-center gap-2 rounded-full bg-primary px-3 py-1 text-sm font-medium text-white mr-2 mb-2" data-id="{{ $weekday }}">
                                        {{ $weekdayNames[$weekday] ?? 'Dia ' . $weekday }}
                                        <button type="button" class="weekday-remove inline-flex h-4 w-4 items-center justify-center rounded-full bg-white/20 text-white hover:bg-white/30" aria-label="Remover">×</button>
                                    </span>
                                @endforeach
                            @else
                                <p class="text-sm text-gray-500 dark:text-gray-400">Nenhum dia selecionado</p>
                            @endif
                        </div>

                        <div id="weekdays-inputs"></div>

                        @error('weekdays')
                            <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                        @error('weekdays.*')
                            <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <h5 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Horários</h5>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Horário Início <span class="text-red-500">*</span>
                            </label>
                            <input type="time" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white @error('start_time') border-red-500 @enderror"
                                   name="start_time" value="{{ old('start_time') }}" required>
                            @error('start_time')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Horário Fim <span class="text-red-500">*</span>
                            </label>
                            <input type="time" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white @error('end_time') border-red-500 @enderror"
                                   name="end_time" value="{{ old('end_time') }}" required>
                            @error('end_time')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Início do Intervalo
                            </label>
                            <input type="time" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white @error('break_start_time') border-red-500 @enderror"
                                   name="break_start_time" value="{{ old('break_start_time') }}"
                                   id="break_start_time">
                            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Opcional</p>
                            @error('break_start_time')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Fim do Intervalo
                            </label>
                            <input type="time" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white @error('break_end_time') border-red-500 @enderror"
                                   name="break_end_time" value="{{ old('break_end_time') }}"
                                   id="break_end_time">
                            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Opcional</p>
                            @error('break_end_time')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="flex flex-col gap-3 pt-6 border-t border-gray-200 dark:border-gray-700 sm:flex-row sm:items-center sm:justify-between">
                    <a href="{{ workspace_route('tenant.business-hours.index') }}" class="btn-patient-secondary">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Cancelar
                    </a>
                    <button type="submit" class="btn-patient-primary">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V2"></path>
                        </svg>
                        Salvar Horário Comercial
                    </button>
                </div>
            </form>
        </div>
    </div>

@endsection
