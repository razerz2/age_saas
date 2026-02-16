@extends('layouts.tailadmin.app')

@section('title', 'Configurações')

@section('content')
<!-- Page Header -->
<div class="page-header mb-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 bg-gradient-to-r from-blue-600 to-blue-700 rounded-xl flex items-center justify-center shadow-lg">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Configurações</h1>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Gerencie as configurações do sistema</p>
            </div>
        </div>
        <x-help-button module="settings" />
    </div>
</div>

<!-- Main Settings Card -->
<div class="max-w-7xl mx-auto" x-data="{ tab: '{{ request()->get('tab', 'clinica') }}' }">
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

@push('styles')
    <link href="{{ asset('css/tenant-settings.css') }}" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        /* TailAdmin Tab Styles */
        .tab-button {
            @apply px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-all duration-200 flex items-center gap-2;
        }
        
        .tab-button.active {
            @apply bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 hover:bg-blue-100 dark:hover:bg-blue-900/30;
        }
        
        /* Responsive tabs */
        @media (max-width: 768px) {
            #settingsTabs {
                @apply flex-wrap gap-2;
            }
            
            .tab-button {
                @apply px-3 py-1.5 text-xs;
            }
        }
        
        /* Botões padrão com suporte a modo claro e escuro */
        .btn-patient-primary {
            background-color: #2563eb;
            color: white;
            border: 1px solid #d1d5db;
            padding: 0.625rem 1.25rem;
            font-size: 0.875rem;
            font-weight: 500;
            border-radius: 0.375rem;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            text-decoration: none;
        }
        
        .btn-patient-primary:hover {
            background-color: #1d4ed8;
        }
        
        .btn-patient-secondary {
            background-color: transparent;
            color: #374151;
            border: 1px solid #d1d5db;
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            font-weight: 500;
            border-radius: 0.375rem;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            text-decoration: none;
        }
        
        .btn-patient-secondary:hover {
            background-color: #f9fafb;
        }
        
        /* Modo escuro via preferência do sistema */
        @media (prefers-color-scheme: dark) {
            .btn-patient-primary {
                background-color: transparent;
                color: white;
                border-color: #d1d5db;
            }
            
            .btn-patient-primary:hover {
                background-color: #1f2937;
            }
            
            .btn-patient-secondary {
                background-color: transparent;
                color: white;
                border-color: #d1d5db;
            }
            
            .btn-patient-secondary:hover {
                background-color: #1f2937;
            }
        }
        
        /* Modo escuro via classe */
        .dark .btn-patient-primary {
            background-color: transparent;
            color: white;
            border-color: #d1d5db;
        }
        
        .dark .btn-patient-primary:hover {
            background-color: #1f2937;
        }
        
        .dark .btn-patient-secondary {
            background-color: transparent;
            color: white;
            border-color: #d1d5db;
        }
        
        .dark .btn-patient-secondary:hover {
            background-color: #1f2937;
        }
    </style>
@endpush

@push('scripts')
<script>
    // Função para trocar abas sem Bootstrap
    function switchTab(tabId) {
        // Remover active de todas as tabs e panes
        document.querySelectorAll('.tab-button').forEach(btn => {
            btn.classList.remove('active');
            btn.setAttribute('aria-selected', 'false');
        });
        
        document.querySelectorAll('.tab-content').forEach(pane => {
            pane.classList.remove('active');
        });
        
        // Adicionar active na tab e pane correspondentes
        const activeTab = document.getElementById(tabId + '-tab');
        const activePane = document.getElementById(tabId);
        
        if (activeTab && activePane) {
            activeTab.classList.add('active');
            activeTab.setAttribute('aria-selected', 'true');
            activePane.classList.add('active');
        }
        
        // Atualizar hash na URL
        if (history.pushState) {
            history.pushState(null, null, '#' + tabId);
        } else {
            window.location.hash = '#' + tabId;
        }
        
        // Scroll suave até o conteúdo
        window.scrollTo({
            top: document.getElementById('settingsTabs').offsetTop - 20,
            behavior: 'smooth'
        });
    }
    
    // Inicialização quando o DOM estiver pronto
    document.addEventListener('DOMContentLoaded', function() {
        // Verificar se há mensagem de sucesso com redirecionamento para aba específica
        @if(session('redirect_to_tab'))
            switchTab('{{ session('redirect_to_tab') }}');
        @endif
        
        // Verificar se há hash na URL para abrir aba específica
        if (window.location.hash) {
            const hash = window.location.hash.substring(1); // Remove o #
            const targetPane = document.getElementById(hash);
            if (targetPane) {
                switchTab(hash);
            }
        }
        
        // Mostrar/ocultar campo de horas de cancelamento
        const cancellationCheckbox = document.getElementById('appointments_allow_cancellation');
        if (cancellationCheckbox) {
            cancellationCheckbox.addEventListener('change', function() {
                const cancellationGroup = document.getElementById('cancellation_hours_group');
                if (cancellationGroup) {
                    cancellationGroup.style.display = this.checked ? 'block' : 'none';
                }
            });
        }

        // Mostrar/ocultar opção de sincronização automática do Google Calendar
        const googleCalendarCheckbox = document.getElementById('integrations_google_calendar_enabled');
        if (googleCalendarCheckbox) {
            googleCalendarCheckbox.addEventListener('change', function() {
                const autoSyncGroup = document.getElementById('google_calendar_auto_sync_group');
                if (autoSyncGroup) {
                    autoSyncGroup.style.display = (this.checked && !this.disabled) ? 'block' : 'none';
                }
            });
        }

        // Converter array de checkboxes em string separada por vírgula para dias da semana
        const calendarForm = document.querySelector('form[action*="calendar"]');
        if (calendarForm) {
            calendarForm.addEventListener('submit', function(e) {
                const checkboxes = document.querySelectorAll('input[name="calendar_default_weekdays[]"]:checked');
                const values = Array.from(checkboxes).map(cb => cb.value);
                
                // Criar campo hidden com os valores
                let hiddenField = this.querySelector('input[name="calendar_default_weekdays"]');
                if (!hiddenField) {
                    hiddenField = document.createElement('input');
                    hiddenField.type = 'hidden';
                    hiddenField.name = 'calendar_default_weekdays';
                    this.appendChild(hiddenField);
                }
                hiddenField.value = values.join(',');
            });
        }

        // Mostrar/ocultar configurações de email baseado no driver
        const emailDriver = document.getElementById('email_driver');
        if (emailDriver) {
            emailDriver.addEventListener('change', function() {
                const emailConfig = document.getElementById('email_tenancy_config');
                if (emailConfig) {
                    emailConfig.style.display = this.value === 'tenancy' ? 'block' : 'none';
                }
            });
        }

        // Mostrar/ocultar configurações de WhatsApp baseado no driver
        const whatsappDriver = document.getElementById('whatsapp_driver');
        if (whatsappDriver) {
            whatsappDriver.addEventListener('change', function() {
                const whatsappConfig = document.getElementById('whatsapp_tenancy_config');
                if (whatsappConfig) {
                    whatsappConfig.style.display = this.value === 'tenancy' ? 'block' : 'none';
                }
            });
        }

        // Mostrar/ocultar campos de personalização de profissionais
        const professionalCustomization = document.getElementById('professional_customization_enabled');
        if (professionalCustomization) {
            professionalCustomization.addEventListener('change', function() {
                const customizationFields = document.getElementById('professional_customization_fields');
                if (customizationFields) {
                    customizationFields.style.display = this.checked ? 'block' : 'none';
                }
            });
        }
    });

    // Função para copiar o link de agendamento público
    function copyPublicBookingLink() {
        const linkInput = document.getElementById('publicBookingLink');
        if (!linkInput) {
            showAlert({ type: 'error', title: 'Erro', message: 'Link não encontrado.' });
            return;
        }

        const link = linkInput.value;

        // Tentar usar a API Clipboard moderna
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(link).then(function() {
                showCopySuccess();
            }).catch(function(err) {
                console.error('Erro ao copiar:', err);
                fallbackCopy(link);
            });
        } else {
            // Fallback para navegadores mais antigos
            fallbackCopy(link);
        }
    }

    function fallbackCopy(text) {
        const textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.style.position = 'fixed';
        textarea.style.opacity = '0';
        document.body.appendChild(textarea);
        textarea.select();
        
        try {
            document.execCommand('copy');
            showCopySuccess();
        } catch (err) {
            console.error('Erro ao copiar:', err);
            showAlert({ type: 'error', title: 'Erro', message: 'Erro ao copiar. Por favor, copie manualmente.' });
        }
        
        document.body.removeChild(textarea);
    }

    function showCopySuccess() {
        const alert = document.getElementById('copySuccessAlert');
        if (alert) {
            alert.style.display = 'flex';
            setTimeout(function() {
                alert.style.display = 'none';
            }, 3000);
        }
    }

    // Função para preview de imagem
    function previewImage(input, previewId) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById(previewId);
                if (preview) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Função para remover imagem
    function removeImage(type, event) {
        const removeBtnContainer = (event && event.target) ? event.target.closest('.mb-3') : null;
        confirmAction({
            title: 'Remover imagem',
            message: 'Tem certeza que deseja remover a imagem personalizada? Será usada a imagem padrão do sistema.',
            confirmText: 'Remover',
            cancelText: 'Cancelar',
            type: 'warning',
            onConfirm: () => {
            const inputId = 'remove-' + type;
            const input = document.getElementById(inputId);
            if (input) {
                input.value = '1';
            }
            
            // Resetar preview para imagem padrão
            const previewId = type + '-preview';
            const preview = document.getElementById(previewId);
            if (preview) {
                if (type === 'logo') {
                    preview.src = '{{ asset("tailadmin/assets/images/logo/logo.svg") }}';
                } else if (type === 'logo_mini') {
                    // Se houver logo normal, usar ele, senão usar padrão
                    const logoPreview = document.getElementById('logo-preview');
                    if (logoPreview && logoPreview.src) {
                        preview.src = logoPreview.src;
                    } else {
                        preview.src = '{{ asset("tailadmin/assets/images/logo/logo.svg") }}';
                    }
                } else if (type === 'favicon') {
                    preview.src = '{{ asset("tailadmin/assets/images/logo/logo-icon.svg") }}';
                }
            }
            
            // Limpar input de arquivo
            const fileInputId = type + '-input';
            const fileInput = document.getElementById(fileInputId);
            if (fileInput) {
                fileInput.value = '';
            }
            
            // Ocultar botão de remover
            if (removeBtnContainer) {
                removeBtnContainer.style.display = 'none';
            }
            }
        });
    }

    // Configuração de Localização (Estados/Cidades)
    document.addEventListener('DOMContentLoaded', function() {
        const stateSelect = document.getElementById('estado_id');
        const citySelect = document.getElementById('cidade_id');
        const zipcodeField = document.getElementById('cep');
        const addressField = document.getElementById('endereco');
        const neighborhoodField = document.getElementById('bairro');

        const currentEstadoId = '{{ $localizacao->estado_id ?? "" }}';
        const currentCidadeId = '{{ $localizacao->cidade_id ?? "" }}';
        const brazilId = '{{ $brazilId }}';

        async function loadStates() {
            if (!stateSelect) return;
            stateSelect.innerHTML = '<option value="">Carregando estados...</option>';
            try {
                const response = await fetch('{{ route('api.public.estados', ['pais' => ':paisId']) }}'.replace(':paisId', brazilId));
                const data = await response.json();
                stateSelect.innerHTML = '<option value="">Selecione o estado</option>';
                data.forEach(state => {
                    const option = document.createElement('option');
                    option.value = state.id_estado;
                    option.dataset.abbr = state.uf;
                    option.textContent = state.nome_estado;
                    if (currentEstadoId == state.id_estado) {
                        option.selected = true;
                    }
                    stateSelect.appendChild(option);
                });

                if (stateSelect.value) {
                    loadCities(stateSelect.value);
                }
            } catch (error) {
                console.error('Erro ao carregar estados:', error);
                stateSelect.innerHTML = '<option value="">Erro ao carregar</option>';
            }
        }

        async function loadCities(stateId) {
            if (!citySelect) return;
            if (!stateId) {
                citySelect.innerHTML = '<option value="">Selecione o estado primeiro</option>';
                return;
            }
            citySelect.innerHTML = '<option value="">Carregando cidades...</option>';
            try {
                const response = await fetch('{{ route('api.public.cidades', ['estado' => ':id']) }}'.replace(':id', stateId));
                const data = await response.json();
                citySelect.innerHTML = '<option value="">Selecione a cidade</option>';
                data.forEach(city => {
                    const option = document.createElement('option');
                    option.value = city.id_cidade;
                    option.dataset.name = city.nome_cidade;
                    option.textContent = city.nome_cidade;
                    if (currentCidadeId == city.id_cidade) {
                        option.selected = true;
                    }
                    citySelect.appendChild(option);
                });
            } catch (error) {
                console.error('Erro ao carregar cidades:', error);
                citySelect.innerHTML = '<option value="">Erro ao carregar</option>';
            }
        }

        if (stateSelect) {
            stateSelect.addEventListener('change', function() {
                loadCities(this.value);
            });
        }

        if (zipcodeField) {
            zipcodeField.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length > 8) value = value.substring(0, 8);
                if (value.length > 5) {
                    value = value.substring(0, 5) + '-' + value.substring(5);
                }
                e.target.value = value;

                if (value.replace(/\D/g, '').length === 8) {
                    fetch(`https://viacep.com.br/ws/${value.replace(/\D/g, '')}/json/`)
                        .then(response => response.json())
                        .then(data => {
                            if (!data.erro) {
                                if (addressField) addressField.value = data.logradouro;
                                if (neighborhoodField) neighborhoodField.value = data.bairro;
                                
                                if (data.uf) {
                                    for (let i = 0; i < stateSelect.options.length; i++) {
                                        if (stateSelect.options[i].dataset.abbr === data.uf) {
                                            stateSelect.selectedIndex = i;
                                            loadCities(stateSelect.value).then(() => {
                                                if (data.localidade) {
                                                    for (let j = 0; j < citySelect.options.length; j++) {
                                                        if (citySelect.options[j].dataset.name.toLowerCase() === data.localidade.toLowerCase()) {
                                                            citySelect.selectedIndex = j;
                                                            break;
                                                        }
                                                    }
                                                }
                                            });
                                            break;
                                        }
                                    }
                                }
                            }
                        });
                }
            });
        }

        loadStates();
    });
</script>
@endpush
@endsection

