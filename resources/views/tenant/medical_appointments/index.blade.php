@extends('layouts.tailadmin.app')

@section('title', 'Atendimento Médico')

@section('page', 'medical_appointments')

@section('content')
<div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-6">
    <!-- Page Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Atendimento Médico</h1>
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
                                <span class="ml-1 text-gray-500 dark:text-gray-400">Atendimento Médico</span>
                            </div>
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <div class="max-w-4xl mx-auto">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h4 class="text-xl font-semibold text-gray-900 dark:text-white flex items-center">
                    <x-icon name="stethoscope" class="w-5 h-5 mr-2 text-blue-600" />
                    Iniciar Atendimento
                </h4>
            </div>
            <div class="p-6">
                <form action="{{ workspace_route('tenant.medical-appointments.start') }}" method="POST">
                    @csrf

                    @php
                        $user = auth('tenant')->user();
                        $showDoctorSelect = ($user->role === 'admin' || $user->role === 'user') && $doctors->isNotEmpty();
                    @endphp

                    @if($showDoctorSelect)
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Médicos</label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                @foreach($doctors as $doctor)
                                    <label for="doctor_{{ $doctor->id }}" class="flex items-center gap-2 p-3 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/40">
                                        <input class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 @error('doctor_ids') border-red-500 @enderror"
                                               type="checkbox"
                                               name="doctor_ids[]"
                                               id="doctor_{{ $doctor->id }}"
                                               value="{{ $doctor->id }}"
                                               {{ (is_array(old('doctor_ids')) && in_array($doctor->id, old('doctor_ids'))) ? 'checked' : '' }}>
                                        <span class="text-sm text-gray-700 dark:text-gray-200">{{ $doctor->user->name ?? 'Sem nome' }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error('doctor_ids')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                            @error('doctor_ids.*')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Selecione um ou mais médicos</p>
                        </div>
                    @endif

                    <div class="mb-6">
                        <label for="date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Data do Atendimento</label>
                        <input type="date"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white @error('date') border-red-500 @enderror"
                               id="date"
                               name="date"
                               value="{{ old('date', date('Y-m-d')) }}"
                               required>
                        @error('date')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="btn btn-primary">
                            <x-icon name="play" class="w-5 h-5 mr-2" />
                            Iniciar Atendimento
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

