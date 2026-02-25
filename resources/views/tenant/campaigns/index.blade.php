@extends('layouts.tailadmin.app')

@section('title', 'Campanhas')
@section('page', 'campaigns')

@section('content')
    @php
        $tenantSlug = request()->route('slug') ?? tenant()?->subdomain ?? '';
        $integrationsUrl = \Illuminate\Support\Facades\Route::has('tenant.integrations.index')
            ? workspace_route('tenant.integrations.index')
            : url('/workspace/' . $tenantSlug . '/integrations');
    @endphp

    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-6">
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Campanhas</h1>
                    <nav class="flex mt-2" aria-label="Breadcrumb">
                        <ol class="inline-flex items-center space-x-1 md:space-x-3">
                            <li class="inline-flex items-center">
                                <a href="{{ workspace_route('tenant.dashboard') }}"
                                    class="text-gray-700 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white inline-flex items-center">
                                    <x-icon name="home-outline" size="text-base" class="mr-2" />
                                    Dashboard
                                </a>
                            </li>
                            <li>
                                <div class="flex items-center">
                                    <x-icon name="chevron-right" size="text-sm" class="text-gray-400" />
                                    <span class="ml-1 text-gray-500 dark:text-gray-400">Campanhas</span>
                                </div>
                            </li>
                        </ol>
                    </nav>
                </div>

                @if ($moduleEnabled)
                    <a href="{{ workspace_route('tenant.campaigns.create') }}" class="btn btn-primary inline-flex items-center">
                        <x-icon name="plus" size="text-sm" class="mr-2" />
                        Nova Campanha
                    </a>
                @else
                    <button type="button"
                        class="btn btn-primary inline-flex items-center opacity-60 cursor-not-allowed"
                        disabled
                        aria-disabled="true">
                        <x-icon name="plus" size="text-sm" class="mr-2" />
                        Nova Campanha
                    </button>
                @endif
            </div>
        </div>

        @if (session('success'))
            <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg dark:bg-green-900/20 dark:border-green-800">
                <div class="flex">
                    <x-icon name="check-circle-outline" size="text-lg" class="text-green-600 dark:text-green-400" />
                    <div class="ml-3">
                        <p class="text-sm text-green-800 dark:text-green-200">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if (session('warning'))
            <div class="mb-6 p-4 bg-amber-50 border border-amber-200 rounded-lg dark:bg-amber-900/20 dark:border-amber-800">
                <div class="flex">
                    <x-icon name="alert-circle-outline" size="text-lg" class="text-amber-600 dark:text-amber-400" />
                    <div class="ml-3">
                        <p class="text-sm text-amber-800 dark:text-amber-200">{{ session('warning') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if (!$moduleEnabled)
            <div class="mb-6 p-4 bg-amber-50 border border-amber-200 rounded-lg dark:bg-amber-900/20 dark:border-amber-800">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex">
                        <x-icon name="alert-circle-outline" size="text-lg" class="text-amber-600 dark:text-amber-400" />
                        <div class="ml-3">
                            <p class="text-sm text-amber-800 dark:text-amber-200">
                                Campanhas indisponíveis: configure sua API de Email e/ou WhatsApp em Integrações.
                            </p>
                        </div>
                    </div>
                    <a href="{{ $integrationsUrl }}" class="btn btn-outline inline-flex items-center whitespace-nowrap">
                        Configurar Integrações
                    </a>
                </div>
            </div>
        @endif

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Lista de Campanhas</h2>
            </div>

            <div class="p-6">
                <div
                    id="campaigns-grid-wrapper"
                    data-grid-url="{{ workspace_route('tenant.campaigns.grid') }}"
                    data-show-url-template="{{ workspace_route('tenant.campaigns.show', ['campaign' => '__CAMPAIGN_ID__']) }}"
                    data-row-click-link-selector='a[title="Ver"]'
                >
                    <div id="campaigns-grid"></div>
                </div>
            </div>
        </div>
    </div>
@endsection
