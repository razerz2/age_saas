@extends('layouts.tailadmin.app')

@section('title', 'Calendário')
@section('page', 'calendars-view')

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">Calendar</h2>
            <nav>
                <ol class="flex items-center gap-1.5">
                    <li>
                        <a class="inline-flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400"
                            href="{{ workspace_route('tenant.dashboard') }}">
                            Home
                            <svg class="stroke-current" width="17" height="16" viewBox="0 0 17 16" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path d="M6.5 12L10.5 8L6.5 4" stroke="" stroke-width="1.2" stroke-linecap="round"
                                    stroke-linejoin="round"></path>
                            </svg>
                        </a>
                    </li>
                    <li class="text-sm text-gray-800 dark:text-white/90">Calendário: {{ $calendar->name }}</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div
            id="calendar"
            class="min-h-screen"
            data-events-url="{{ $eventsUrl }}"
            data-timezone="{{ $tenantTimezone }}"
            data-calendar-name="{{ $calendar->name }}"
        ></div>
    </div>

    <div
        id="eventModal"
        class="fixed inset-0 z-999999 hidden items-center justify-center overflow-y-auto p-5"
        aria-hidden="true"
    >
        <div class="fixed inset-0 h-full w-full bg-gray-400/50 backdrop-blur-[32px]" data-modal-close></div>
        <div class="relative w-full max-w-[700px] rounded-3xl bg-white p-6 dark:bg-gray-900">
            <button
                type="button"
                class="absolute right-3 top-3 flex h-10 w-10 items-center justify-center rounded-full border border-gray-200 text-gray-500 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-white/[0.05]"
                data-modal-close
                aria-label="Fechar"
            >
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M15 5L5 15M5 5L15 15" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></path>
                </svg>
            </button>

            <h4 class="mb-1 text-xl font-semibold text-gray-800 dark:text-white/90" id="eventModalTitle">Evento</h4>
            <p class="mb-6 text-sm text-gray-500 dark:text-gray-400" id="eventModalSubtitle">Detalhes do evento</p>

            <div class="space-y-3 rounded-2xl border border-gray-200 p-4 dark:border-gray-800">
                <div>
                    <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Início</p>
                    <p class="text-sm font-medium text-gray-800 dark:text-white/90" id="eventModalStart">-</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Fim</p>
                    <p class="text-sm font-medium text-gray-800 dark:text-white/90" id="eventModalEnd">-</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Informações</p>
                    <p class="text-sm font-medium text-gray-800 dark:text-white/90" id="eventModalMeta">-</p>
                </div>
            </div>
        </div>
    </div>
@endsection
