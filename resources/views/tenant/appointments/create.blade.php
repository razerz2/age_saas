@extends('layouts.tailadmin.app')

@section('title', 'Criar Agendamento')
@section('page', 'appointments')

@section('content')

    <!-- Page Header -->
    <div class="page-header mb-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <nav class="min-w-0 flex-1" aria-label="breadcrumb">
                <ol class="flex flex-wrap items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                    <li>
                        <a href="{{ workspace_route('tenant.dashboard') }}" class="inline-flex items-center gap-2 text-gray-700 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">
                            <x-icon name="home-outline" class="w-5 h-5" />                            Dashboard
                        </a>
                    </li>
                    <li class="flex items-center gap-2">
                        <x-icon name="chevron-right" class="w-4 h-4 text-gray-400" />
                        <a href="{{ workspace_route('tenant.appointments.index') }}" class="text-gray-700 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">Agendamentos</a>
                    </li>
                    <li class="flex items-center gap-2">
                        <x-icon name="chevron-right" class="w-4 h-4 text-gray-400" />
                        <span class="text-gray-900 dark:text-white font-semibold">Criar</span>
                    </li>
                </ol>
            </nav>
            <div class="flex-shrink-0">
                <x-help-button module="appointments" />
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white flex items-center">
                        <x-icon name="calendar-outline" class="w-6 h-6 mr-2 text-blue-600" />                        Novo Agendamento
                    </h2>
                    <p class="text-gray-600 dark:text-gray-400 mt-1">Preencha os dados abaixo para criar um novo agendamento</p>
                </div>
                <div>
                    <a href="{{ workspace_route('tenant.public-booking-link.index') }}" 
                       class="btn btn-primary" 
                       title="Acessar link de agendamento público">
                        <x-icon name="link-variant" class="w-4 h-4 mr-2" />                        Link de Agendamento
                    </a>
                </div>
            </div>
        </div>

        <div class="p-6">
            <!-- Card informativo sobre agendamento público -->
            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 mb-6" data-dismissible="alert">
                <div class="flex items-start">
                    <x-icon name="information-outline" class="w-6 h-6 text-blue-600 dark:text-blue-400 mt-0.5 mr-3 flex-shrink-0" />
                    <div class="flex-1">
                        <h3 class="text-blue-900 dark:text-blue-100 font-semibold mb-2 flex items-center">
                            <x-icon name="link-variant" class="w-4 h-4 mr-2" />
                            Agendamento Público Disponível
                        </h3>
                        <p class="text-blue-800 dark:text-blue-200 text-sm mb-3">
                            Seus pacientes podem agendar consultas diretamente pela internet usando o link de agendamento público. 
                            Compartilhe o link nas redes sociais, WhatsApp ou site da clínica.
                        </p>
                        <a href="{{ workspace_route('tenant.public-booking-link.index') }}" 
                           class="btn btn-primary">
                            <x-icon name="content-copy" class="w-4 h-4 mr-1.5" />                            Ver e Copiar Link de Agendamento
                        </a>
                    </div>
                    <button type="button" class="ml-4 text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-200 js-dismiss">
                        <x-icon name="close" class="w-5 h-5" />                    </button>
                </div>
            </div>

            <!-- Exibição de erros de validação -->
            @if ($errors->any())
                <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4 mb-6" data-dismissible="alert">
                    <div class="flex items-start">
                        <x-icon name="alert-outline" class="w-6 h-6 text-red-600 dark:text-red-400 mt-0.5 mr-3 flex-shrink-0" />
                        <div class="flex-1">
                            <h3 class="text-red-900 dark:text-red-100 font-semibold mb-2">Erro de Validação!</h3>
                            <ul class="text-red-800 dark:text-red-200 text-sm space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                        <button type="button" class="ml-4 text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-200 js-dismiss">
                            <x-icon name="close" class="w-5 h-5" />                    </button>
                    </div>
                </div>
            @endif

                    <form class="space-y-8" action="{{ workspace_route('tenant.appointments.store') }}" method="POST">
                @csrf

                <!-- Seção: Informações Básicas -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                        <x-icon name="information-outline" class="w-5 h-5 mr-2 text-blue-600" />
                        Informações Básicas
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                <x-icon name="account-outline" class="w-4 h-4 inline mr-1" />                                Paciente <span class="text-red-500">*</span>
                            </label>
                            <select name="patient_id" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('patient_id') border-red-500 @enderror" required>
                                <option value="">Selecione um paciente</option>
                                @foreach($patients as $patient)
                                    <option value="{{ $patient->id }}" {{ old('patient_id') == $patient->id ? 'selected' : '' }}>{{ $patient->full_name }}</option>
                                @endforeach
                            </select>
                            @error('patient_id')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                <x-icon name="stethoscope" class="w-4 h-4 inline mr-1" />
                                Médico <span class="text-red-500">*</span>
                            </label>
                            <select name="doctor_id" id="doctor_id" data-initial-value="{{ old('doctor_id') }}" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('doctor_id') border-red-500 @enderror" required>
                                <option value="">Selecione um médico</option>
                                @foreach($doctors as $doctor)
                                    <option value="{{ $doctor->id }}" {{ old('doctor_id') == $doctor->id ? 'selected' : '' }}>
                                        {{ $doctor->user->name_full ?? $doctor->user->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('doctor_id')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">O calendário do médico será selecionado automaticamente</p>
                        </div>
                    </div>

                    <div data-appointment-type-wrapper class="hidden">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            <x-icon name="clipboard-text-outline" class="w-4 h-4 inline mr-1" />                            Tipo de Consulta
                        </label>
                        <select name="appointment_type" id="appointment_type" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('appointment_type') border-red-500 @enderror" disabled>
                            <option value="">Primeiro selecione um médico</option>
                        </select>
                        @error('appointment_type')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                <x-icon name="format-list-bulleted" class="w-4 h-4 inline mr-1" />                                Especialidade
                            </label>
                            <select name="specialty_id" id="specialty_id" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('specialty_id') border-red-500 @enderror" disabled>
                                <option value="">Primeiro selecione um médico</option>
                            </select>
                            @error('specialty_id')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        @php
                            $settings = \App\Models\Tenant\TenantSetting::getAll();
                            $defaultMode = $settings['appointments.default_appointment_mode'] ?? 'user_choice';
                        @endphp
                        @if($defaultMode === 'user_choice')
                            <div>
                                @include('tenant.appointments.partials.appointment_mode_select', ['appointment' => null])
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Seção: Data e Hora -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                        <x-icon name="clock-outline" class="w-5 h-5 mr-2 text-blue-600" />
                        Data e Horário
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                <x-icon name="calendar-outline" class="w-4 h-4 inline mr-1" />                                Data <span class="text-red-500">*</span>
                            </label>
                            <div class="flex gap-2">
                                <input type="date" id="appointment_date" class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('appointment_date') border-red-500 @enderror @error('starts_at') border-red-500 @enderror" 
                                       name="appointment_date" value="{{ old('appointment_date') }}" 
                                       min="{{ date('Y-m-d') }}" required>
                                <button type="button" class="btn btn-primary" id="btn-show-business-hours" 
                                        title="Ver dias trabalhados do médico" disabled>
                                    <x-icon name="close" class="w-5 h-5" />                    </button>
                            </div>
                            @error('appointment_date')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                            @error('starts_at')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                <x-icon name="clock-outline" class="w-4 h-4 inline mr-1" />
                                Horário Disponível <span class="text-red-500">*</span>
                            </label>
                            <select name="appointment_time" id="appointment_time" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('appointment_time') border-red-500 @enderror" required disabled>
                                <option value="">Primeiro selecione a data</option>
                            </select>
                            @error('appointment_time')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Horários disponíveis baseados nas configurações do médico</p>
                        </div>
                    </div>
                    <input type="hidden" name="starts_at" id="starts_at">
                    <input type="hidden" name="ends_at" id="ends_at">
                </div>

                <!-- Seção: Observações -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                        <x-icon name="note-text-outline" class="w-5 h-5 mr-2 text-blue-600" />
                        Observações
                    </h3>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            <x-icon name="note-text-outline" class="w-4 h-4 inline mr-1" />
                            Observações
                        </label>
                        <textarea class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('notes') border-red-500 @enderror" 
                                  name="notes" rows="4" 
                                  placeholder="Digite observações sobre o agendamento (opcional)">{{ old('notes') }}</textarea>
                        @error('notes')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Botões de Ação -->
                <div class="flex items-center justify-between pt-6 border-t border-gray-200 dark:border-gray-700">
                    <a href="{{ workspace_route('tenant.appointments.index') }}" class="btn btn-outline">
                        <x-icon name="arrow-left" class="w-4 h-4 mr-2" />                        Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <x-icon name="content-save-outline" class="w-5 h-5 mr-2" />                        Salvar Agendamento
                    </button>
                </div>
            </form>

                </div>
    </div>

    <!-- Modal de Dias Trabalhados -->
    <div id="businessHoursModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white dark:bg-gray-800">
            <div class="flex items-center justify-between pb-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                    <x-icon name="calendar-clock" class="w-5 h-5 mr-2 text-blue-600" />
                    Dias Trabalhados do Médico
                </h3>
                <button type="button" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 js-close-business-hours-modal">
                    <x-icon name="close" class="w-6 h-6" />
                </button>
            </div>
            <div class="mt-4">
                <div id="business-hours-loading" class="text-center py-8">
                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                    <p class="mt-3 text-gray-600 dark:text-gray-400">Carregando informações...</p>
                </div>
                <div id="business-hours-content" class="hidden">
                    <div class="mb-4">
                        <span class="font-medium text-gray-900 dark:text-white">Médico:</span> 
                        <span id="business-hours-doctor-name" class="text-gray-700 dark:text-gray-300">-</span>
                    </div>
                    <div id="business-hours-list">
                        <!-- Conteúdo será preenchido via JavaScript -->
                    </div>
                    <div id="business-hours-empty" class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 hidden">
                        <div class="flex items-center">
                            <x-icon name="information-outline" class="w-5 h-5 text-blue-600 dark:text-blue-400 mr-2" />
                            <span class="text-blue-800 dark:text-blue-200">Nenhum dia trabalhado configurado para este médico.</span>
                        </div>
                    </div>
                </div>
                <div id="business-hours-error" class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4 hidden">
                    <div class="flex items-center">
                        <x-icon name="alert-outline" class="w-5 h-5 text-red-600 dark:text-red-400 mr-2" />
                        <span id="business-hours-error-message" class="text-red-800 dark:text-red-200">Erro ao carregar informações.</span>
                    </div>
                </div>
            </div>
            <div class="flex justify-end pt-4 border-t border-gray-200 dark:border-gray-700 mt-6">
                <button type="button" class="js-close-business-hours-modal px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-md transition-colors">
                    Fechar
                </button>
            </div>
        </div>
    </div>
@endsection
