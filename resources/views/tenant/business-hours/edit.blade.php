@extends('layouts.tailadmin.app')

@section('title', 'Editar Horário Comercial')

@section('content')
    <div class="page-header mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Editar Horário Comercial</h1>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Atualize as informações do horário comercial abaixo.</p>
            </div>
            <x-help-button module="business-hours" />
        </div>
        <nav class="flex mt-3" aria-label="breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3 text-sm text-gray-500 dark:text-gray-400">
                <li class="inline-flex items-center">
                    <a href="{{ workspace_route('tenant.dashboard') }}" class="inline-flex items-center gap-2 hover:text-gray-900 dark:hover:text-white">
                        <x-icon name="home-outline" class="w-4 h-4" />
                        Dashboard
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <x-icon name="chevron-right" class="w-4 h-4 text-gray-400" />
                        <a href="{{ workspace_route('tenant.business-hours.index') }}" class="ml-1 hover:text-gray-900 dark:hover:text-white">Horários Comerciais</a>
                    </div>
                </li>
                <li>
                    <div class="flex items-center">
                        <x-icon name="chevron-right" class="w-4 h-4 text-gray-400" />
                        <span class="ml-1 text-gray-500 dark:text-gray-400">Editar</span>
                    </div>
                </li>
            </ol>
        </nav>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="p-6">
            <form action="{{ workspace_route('tenant.business-hours.update', $businessHour->id) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <div class="space-y-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 block">
                                Médico <span class="text-red-500">*</span>
                            </label>
                            <select name="doctor_id" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('doctor_id') border-red-500 @enderror" required>
                                <option value="">Selecione um médico</option>
                                @foreach($doctors as $doctor)
                                    <option value="{{ $doctor->id }}" {{ old('doctor_id', $businessHour->doctor_id) == $doctor->id ? 'selected' : '' }}>{{ $doctor->user->name ?? 'N/A' }}</option>
                                @endforeach
                            </select>
                            @error('doctor_id')
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 block">
                                Dia da Semana <span class="text-red-500">*</span>
                            </label>
                            <select name="weekday" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('weekday') border-red-500 @enderror" required>
                                @foreach(['Domingo','Segunda-feira','Terça-feira','Quarta-feira','Quinta-feira','Sexta-feira','Sábado'] as $index => $label)
                                    <option value="{{ $index }}" {{ old('weekday', $businessHour->weekday) == $index ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('weekday')
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 block">
                                Horário Início <span class="text-red-500">*</span>
                            </label>
                            <input type="time" name="start_time" value="{{ old('start_time', $businessHour->start_time) }}" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('start_time') border-red-500 @enderror">
                            @error('start_time')
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 block">
                                Horário Fim <span class="text-red-500">*</span>
                            </label>
                            <input type="time" name="end_time" value="{{ old('end_time', $businessHour->end_time) }}" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('end_time') border-red-500 @enderror">
                            @error('end_time')
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 block">
                                Início do Intervalo
                            </label>
                            <input type="time" name="break_start_time" value="{{ old('break_start_time', $businessHour->break_start_time) }}" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('break_start_time') border-red-500 @enderror">
                            @error('break_start_time')
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 block">
                                Fim do Intervalo
                            </label>
                            <input type="time" name="break_end_time" value="{{ old('break_end_time', $businessHour->break_end_time) }}" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('break_end_time') border-red-500 @enderror">
                            @error('break_end_time')
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="flex flex-col gap-3 pt-3 border-t border-gray-200 dark:border-gray-700 sm:flex-row sm:items-center sm:justify-between flex-wrap">
                    <a href="{{ workspace_route('tenant.business-hours.index') }}" class="btn btn-outline">
                        <x-icon name="arrow-left" class="w-4 h-4 mr-2" />
                        Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <x-icon name="content-save-outline" class="w-4 h-4 mr-2" />
                        Atualizar Horário Comercial
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
