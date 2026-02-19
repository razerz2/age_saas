@extends('layouts.tailadmin.app')

@section('title', 'Agenda')
@section('page', 'calendars')

@section('content')

    <!-- Page Header -->
    <div class="page-header mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white mb-2 flex items-center">
                    <x-icon name="calendar-month-outline" class="w-6 h-6 mr-2 text-blue-600" />
                    Agenda
                </h1>
                <nav class="flex" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 md:space-x-3">
                        <li class="inline-flex items-center">
                            <a href="{{ workspace_route('tenant.dashboard') }}" class="text-gray-700 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">
                                <x-icon name="home-outline" class="w-5 h-5 mr-2" />
                                Dashboard
                            </a>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <x-icon name="chevron-right" class="w-6 h-6 text-gray-400" />
                                <a href="{{ workspace_route('tenant.calendars.index') }}" class="ml-1 text-gray-700 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white md:ml-2">Calendários</a>
                            </div>
                        </li>
                        <li aria-current="page">
                            <div class="flex items-center">
                                <x-icon name="chevron-right" class="w-6 h-6 text-gray-400" />
                                <span class="ml-1 text-gray-500 dark:text-gray-400 md:ml-2">Agenda</span>
                            </div>
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white flex items-center">
                        <x-icon name="calendar-outline" class="w-6 h-6 mr-2 text-blue-600" />
                        Agenda - {{ $calendar->name ?? 'Calendário Principal' }}
                    </h2>
                    <p class="text-gray-600 dark:text-gray-400 mt-1">
                        <x-icon name="information-outline" class="w-4 h-4 inline mr-1" />
                        Visualização completa de compromissos neste calendário.
                    </p>
                </div>
            </div>
        </div>

        <div class="p-6">
            <div id="calendar" class="calendar-container" data-events-url="{{ workspace_route('tenant.calendars.events', ['id' => $calendar->id]) }}"></div>
        </div>
    </div>



@endsection
