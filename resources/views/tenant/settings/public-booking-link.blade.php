@extends('layouts.tailadmin.app')

@section('title', 'Link de Agendamento Público')
@section('page', 'appointments')

@section('content')
    <div class="page-header mb-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <nav class="min-w-0 flex-1" aria-label="breadcrumb">
                <ol class="flex flex-wrap items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                    <li>
                        <a href="{{ workspace_route('tenant.dashboard') }}" class="inline-flex items-center gap-2 text-gray-700 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">
                            <x-icon name="home-outline" class="w-5 h-5" />
                            Dashboard
                        </a>
                    </li>
                    <li class="flex items-center gap-2">
                        <x-icon name="chevron-right" class="w-4 h-4 text-gray-400" />
                        <span class="text-gray-900 dark:text-white font-semibold">Agendamento Público</span>
                    </li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white flex items-center">
                <x-icon name="link-variant" class="w-6 h-6 mr-2 text-blue-600" />
                Link de Agendamento Público
            </h2>
            <p class="text-gray-600 dark:text-gray-400 mt-1">
                Compartilhe este link com seus pacientes para que eles iniciem o agendamento online.
            </p>
        </div>

        <div class="p-6 space-y-6">
            @if($publicBookingUrl)
                <div class="rounded-lg border border-blue-200 bg-blue-50 dark:border-blue-800 dark:bg-blue-900/20 p-4">
                    <label for="publicBookingLink" class="block text-sm font-medium text-blue-900 dark:text-blue-100 mb-2">
                        Link para compartilhar
                    </label>
                    <div class="flex flex-col sm:flex-row gap-2">
                        <input
                            id="publicBookingLink"
                            type="text"
                            class="w-full px-3 py-2 border border-blue-300 dark:border-blue-700 rounded-md shadow-sm bg-white dark:bg-gray-900 text-gray-900 dark:text-white"
                            value="{{ $publicBookingUrl }}"
                            readonly
                        >
                        <button
                            type="button"
                            class="btn btn-primary"
                            data-copy-booking-link
                            data-booking-link="{{ $publicBookingUrl }}"
                        >
                            <x-icon name="content-copy" class="w-4 h-4 mr-1.5" />
                            Copiar link
                        </button>
                    </div>
                    <p class="mt-2 text-sm text-blue-800 dark:text-blue-200 hidden" data-copy-feedback>
                        Link copiado para a área de transferência.
                    </p>
                </div>

                <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-2">Como usar</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Envie o link por WhatsApp, e-mail ou redes sociais. O paciente será direcionado ao fluxo público de identificação e agendamento.
                    </p>
                </div>
            @else
                <div class="rounded-lg border border-amber-200 bg-amber-50 dark:border-amber-800 dark:bg-amber-900/20 p-4 text-amber-800 dark:text-amber-200">
                    Não foi possível gerar o link de agendamento público para este tenant.
                </div>
            @endif
        </div>
    </div>
@endsection
