@extends('layouts.tailadmin.app')

@section('title', 'Configurações')
@section('page', 'settings')

@section('content')

<div id="settings-config"
     data-brazil-id="{{ $brazilId }}"
     data-states-url-template="{{ route('api.public.estados', ['pais' => ':paisId']) }}"
     data-cities-url-template="{{ route('api.public.cidades', ['estado' => ':id']) }}"
     data-current-state-id="{{ $localizacao->estado_id ?? '' }}"
     data-current-city-id="{{ $localizacao->cidade_id ?? '' }}"
     data-redirect-tab="{{ session('redirect_to_tab') }}"></div>

<!-- Page Header -->
<div class="page-header mb-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <x-icon name="cog-outline" size="text-xl" class="text-gray-700 dark:text-gray-200" />
                    Configurações
                </h1>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Gerencie as configurações do sistema</p>
            </div>
        </div>
        <x-help-button module="settings" />
    </div>
</div>

<!-- Main Settings Card -->
<div class="mx-auto"
     :class="tab === 'editor' ? 'max-w-[96rem]' : 'max-w-7xl'"
     x-data="{ tab: '{{ request()->get('tab', 'clinica') }}' }"
     data-alpine="true">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
        
        <!-- HEADER DE ABAS (UMA VEZ) -->
        <div class="border-b border-gray-200 dark:border-gray-700">
            <nav class="flex space-x-1 overflow-x-auto">
                <!-- Clínica -->
                <button @click="tab = 'clinica'" 
                        :class="tab === 'clinica' ? 'border-blue-500 text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'"
                        class="whitespace-nowrap py-4 px-4 border-b-2 font-medium text-sm flex items-center gap-2 transition-colors duration-200 min-w-fit">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                    <span>Clínica</span>
                </button>
                
                <!-- Geral -->
                <button @click="tab = 'geral'" 
                        :class="tab === 'geral' ? 'border-blue-500 text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'"
                        class="whitespace-nowrap py-4 px-4 border-b-2 font-medium text-sm flex items-center gap-2 transition-colors duration-200 min-w-fit">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                    </svg>
                    <span>Geral</span>
                </button>
                
                <!-- Agendamentos -->
                <button @click="tab = 'agendamentos'" 
                        :class="tab === 'agendamentos' ? 'border-blue-500 text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'"
                        class="whitespace-nowrap py-4 px-4 border-b-2 font-medium text-sm flex items-center gap-2 transition-colors duration-200 min-w-fit">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <span>Agendamentos</span>
                </button>
                
                <!-- Calendário -->
                <button @click="tab = 'calendario'" 
                        :class="tab === 'calendario' ? 'border-blue-500 text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'"
                        class="whitespace-nowrap py-4 px-4 border-b-2 font-medium text-sm flex items-center gap-2 transition-colors duration-200 min-w-fit">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <span>Calendário</span>
                </button>
                
                <!-- Profissionais -->
                <button @click="tab = 'profissionais'" 
                        :class="tab === 'profissionais' ? 'border-blue-500 text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'"
                        class="whitespace-nowrap py-4 px-4 border-b-2 font-medium text-sm flex items-center gap-2 transition-colors duration-200 min-w-fit">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <span>Profissionais</span>
                </button>
                
                <!-- Usuários (apenas admin) -->
                @if(auth()->user()->can('manage-users'))
                <button @click="tab = 'usuarios'" 
                        :class="tab === 'usuarios' ? 'border-blue-500 text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'"
                        class="whitespace-nowrap py-4 px-4 border-b-2 font-medium text-sm flex items-center gap-2 transition-colors duration-200 min-w-fit">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                    <span>Usuários</span>
                </button>
                @endif
                
                <!-- Notificações -->
                <button @click="tab = 'notificacoes'" 
                        :class="tab === 'notificacoes' ? 'border-blue-500 text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'"
                        class="whitespace-nowrap py-4 px-4 border-b-2 font-medium text-sm flex items-center gap-2 transition-colors duration-200 min-w-fit">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                    </svg>
                    <span>Notificações</span>
                </button>
                
                <!-- Editor -->
                <button @click="tab = 'editor'"
                        :class="tab === 'editor' ? 'border-blue-500 text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'"
                        class="whitespace-nowrap py-4 px-4 border-b-2 font-medium text-sm flex items-center gap-2 transition-colors duration-200 min-w-fit">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5h8m-8 7h8m-8 7h8M5 5h.01M5 12h.01M5 19h.01"></path>
                    </svg>
                    <span>Editor</span>
                </button>

                <!-- Financeiro (apenas admin) -->
                @if(auth()->user()->can('manage-finance'))
                <button @click="tab = 'financeiro'" 
                        :class="tab === 'financeiro' ? 'border-blue-500 text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'"
                        class="whitespace-nowrap py-4 px-4 border-b-2 font-medium text-sm flex items-center gap-2 transition-colors duration-200 min-w-fit">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span>Financeiro</span>
                </button>
                @endif
                
                <!-- Integrações -->
                <button @click="tab = 'integracoes'" 
                        :class="tab === 'integracoes' ? 'border-blue-500 text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'"
                        class="whitespace-nowrap py-4 px-4 border-b-2 font-medium text-sm flex items-center gap-2 transition-colors duration-200 min-w-fit">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                    </svg>
                    <span>Integrações</span>
                </button>
                
                <!-- Link Público -->
                <button @click="tab = 'link-publico'" 
                        :class="tab === 'link-publico' ? 'border-blue-500 text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'"
                        class="whitespace-nowrap py-4 px-4 border-b-2 font-medium text-sm flex items-center gap-2 transition-colors duration-200 min-w-fit">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                    </svg>
                    <span>Link Público</span>
                </button>
                
                <!-- Aparência -->
                <button @click="tab = 'aparencia'" 
                        :class="tab === 'aparencia' ? 'border-blue-500 text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'"
                        class="whitespace-nowrap py-4 px-4 border-b-2 font-medium text-sm flex items-center gap-2 transition-colors duration-200 min-w-fit">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"></path>
                    </svg>
                    <span>Aparência</span>
                </button>
            </nav>
        </div>

        <!-- CONTEÚDO -->
        <div class="p-6">
            @if(session('success'))
                <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 dark:border-green-800 dark:bg-green-900/20 dark:text-green-300">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-800 dark:bg-red-900/20 dark:text-red-300">
                    {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-800 dark:bg-red-900/20 dark:text-red-300">
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <!-- Clínica -->
            <div x-show="tab === 'clinica'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                @include('tenant.settings.tabs.clinica')
            </div>

            <!-- Geral -->
            <div x-show="tab === 'geral'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                @include('tenant.settings.tabs.geral')
            </div>

            <!-- Agendamentos -->
            <div x-show="tab === 'agendamentos'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                @include('tenant.settings.tabs.agendamentos')
            </div>

            <!-- Calendário -->
            <div x-show="tab === 'calendario'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                @include('tenant.settings.tabs.calendario')
            </div>

            <!-- Profissionais -->
            <div x-show="tab === 'profissionais'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                @include('tenant.settings.tabs.profissionais')
            </div>

            <!-- Usuários (apenas admin) -->
            @if(auth()->user()->can('manage-users'))
            <div x-show="tab === 'usuarios'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                @include('tenant.settings.tabs.usuarios')
            </div>
            @endif

            <!-- Notificações -->
            <div x-show="tab === 'notificacoes'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                @include('tenant.settings.tabs.notificacoes')
            </div>

            <!-- Editor -->
            <div x-show="tab === 'editor'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                @include('tenant.settings.tabs.editor')
            </div>

            <!-- Financeiro (apenas admin) -->
            @if(auth()->user()->can('manage-finance'))
            <div x-show="tab === 'financeiro'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                @include('tenant.settings.tabs.financeiro')
            </div>
            @endif

            <!-- Integrações -->
            <div x-show="tab === 'integracoes'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                @include('tenant.settings.tabs.integracoes')
            </div>

            <!-- Link Público -->
            <div x-show="tab === 'link-publico'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                @include('tenant.settings.tabs.link-publico')
            </div>

            <!-- Aparência -->
            <div x-show="tab === 'aparencia'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                @include('tenant.settings.tabs.aparencia')
            </div>
        </div>
    </div>
</div>


@endsection
