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
                    <a href="{{ workspace_route('tenant.dashboard') }}" class="hover:text-gray-900 dark:hover:text-white">Dashboard</a>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <a href="{{ workspace_route('tenant.business-hours.index') }}" class="ml-1 hover:text-gray-900 dark:hover:text-white">Horários Comerciais</a>
                    </div>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
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
                        Atualizar Horário Comercial
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
