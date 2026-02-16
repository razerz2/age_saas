@extends('layouts.tailadmin.app')

@section('title', 'Relatórios')

@section('content')

    <!-- Page Header -->
    <div class="page-header mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white mb-2">Relatórios</h1>
                <nav class="flex" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 md:space-x-3">
                        <li class="inline-flex items-center">
                            <a href="{{ workspace_route('tenant.dashboard') }}" class="text-gray-700 hover:text-blue-600 dark:text-gray-300 dark:hover:text-white">
                                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001 1h2a1 1 0 001 1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
                                </svg>
                                Dashboard
                            </a>
                        </li>
                        <li aria-current="page">
                            <div class="flex items-center">
                                <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="ml-1 text-gray-500 dark:text-gray-400 md:ml-2">Relatórios</span>
                            </div>
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <!-- Agendamentos -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center mb-4">
                <div class="flex-shrink-0 w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <div class="flex-grow-1 ml-3">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">Agendamentos</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Relatórios de agendamentos</p>
                </div>
            </div>
            <a href="{{ workspace_route('tenant.reports.appointments') }}" class="inline-flex items-center justify-center w-full px-4 py-2 bg-primary text-white hover:bg-primary/90 font-medium rounded-md transition-colors">
                Acessar
                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5-5m5 5V7m0 0l-5-5m5 5v10a2 2 0 002 2H6a2 2 0 00-2-2V12a2 2 0 002-2h2"></path>
                </svg>
            </a>
        </div>

        <!-- Pacientes -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center mb-4">
                <div class="flex-shrink-0 w-12 h-12 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600 dark:text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
                <div class="flex-grow-1 ml-3">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">Pacientes</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Relatórios de pacientes</p>
                </div>
            </div>
            <a href="{{ workspace_route('tenant.reports.patients') }}" class="inline-flex items-center justify-center w-full px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-md transition-colors">
                Acessar
                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5-5m5 5V7m0 0l-5-5m5 5v10a2 2 0 002 2H6a2 2 0 00-2-2V12a2 2 0 002-2h2"></path>
                </svg>
            </a>
        </div>

        <!-- Médicos -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center mb-4">
                <div class="flex-shrink-0 w-12 h-12 bg-cyan-100 dark:bg-cyan-900 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-cyan-600 dark:text-cyan-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
                <div class="flex-grow-1 ml-3">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">Médicos</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Relatórios de médicos</p>
                </div>
            </div>
            <a href="{{ workspace_route('tenant.reports.doctors') }}" class="inline-flex items-center justify-center w-full px-4 py-2 bg-cyan-600 hover:bg-cyan-700 text-white font-medium rounded-md transition-colors">
                Acessar
                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5-5m5 5V7m0 0l-5-5m5 5v10a2 2 0 002 2H6a2 2 0 00-2-2V12a2 2 0 002-2h2"></path>
                </svg>
            </a>
        </div>

        <!-- Recorrências -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center mb-4">
                <div class="flex-shrink-0 w-12 h-12 bg-amber-100 dark:bg-amber-900 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-amber-600 dark:text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m16 9v-5m0 0h-5m5 0v5m-5 0h-5"></path>
                    </svg>
                </div>
                <div class="flex-grow-1 ml-3">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">Recorrências</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Relatórios de recorrências</p>
                </div>
            </div>
            <a href="{{ workspace_route('tenant.reports.recurring') }}" class="inline-flex items-center justify-center w-full px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white font-medium rounded-md transition-colors">
                Acessar
                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5-5m5 5V7m0 0l-5-5m5 5v10a2 2 0 002 2H6a2 2 0 00-2-2V12a2 2 0 002-2h2"></path>
                </svg>
            </a>
        </div>

        <!-- Formulários -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center mb-4">
                <div class="flex-shrink-0 w-12 h-12 bg-red-100 dark:bg-red-900 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-red-600 dark:text-red-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2 2v10a2 2 0 002 2h14a2 2 0 002-2V7a2 2 0 00-2-2h-2"></path>
                    </svg>
                </div>
                <div class="flex-grow-1 ml-3">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">Formulários</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Relatórios de formulários</p>
                </div>
            </div>
            <a href="{{ workspace_route('tenant.reports.forms') }}" class="inline-flex items-center justify-center w-full px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-md transition-colors">
                Acessar
                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5-5m5 5V7m0 0l-5-5m5 5v10a2 2 0 002 2H6a2 2 0 00-2-2V12a2 2 0 002-2h2"></path>
                </svg>
            </a>
        </div>

        <!-- Portal do Paciente -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center mb-4">
                <div class="flex-shrink-0 w-12 h-12 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
                <div class="flex-grow-1 ml-3">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">Portal do Paciente</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Relatórios do portal</p>
                </div>
            </div>
            <a href="{{ workspace_route('tenant.reports.portal') }}" class="inline-flex items-center justify-center w-full px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-md transition-colors">
                Acessar
                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5-5m5 5V7m0 0l-5-5m5 5v10a2 2 0 002 2H6a2 2 0 00-2-2V12a2 2 0 002-2h2"></path>
                </svg>
            </a>
        </div>

        <!-- Notificações -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center mb-4">
                <div class="flex-shrink-0 w-12 h-12 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-600 dark:text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5m0 0l-5-5m5 5v10a2 2 0 002 2H6a2 2 0 00-2-2V7a2 2 0 00-2-2h2"></path>
                    </svg>
                </div>
                <div class="flex-grow-1 ml-3">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">Notificações</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Relatórios de notificações</p>
                </div>
            </div>
            <a href="{{ workspace_route('tenant.reports.notifications') }}" class="inline-flex items-center justify-center w-full px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white font-medium rounded-md transition-colors">
                Acessar
                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5-5m5 5V7m0 0l-5-5m5 5v10a2 2 0 002 2H6a2 2 0 00-2-2V12a2 2 0 002-2h2"></path>
                </svg>
            </a>
        </div>
    </div>

@endsection

