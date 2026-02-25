@extends('layouts.tailadmin.app')

@section('title', 'Destinatários da Campanha')
@section('page', 'campaigns')

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
        <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <nav class="flex" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 md:space-x-3">
                        <li class="inline-flex items-center">
                            <a href="{{ workspace_route('tenant.dashboard') }}"
                                class="inline-flex items-center text-gray-700 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
                                <x-icon name="home-outline" size="text-base" class="mr-2" />
                                Dashboard
                            </a>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <x-icon name="chevron-right" size="text-sm" class="text-gray-400" />
                                <a href="{{ workspace_route('tenant.campaigns.index') }}"
                                    class="ml-1 text-gray-700 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">
                                    Campanhas
                                </a>
                            </div>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <x-icon name="chevron-right" size="text-sm" class="text-gray-400" />
                                <a href="{{ workspace_route('tenant.campaigns.show', ['campaign' => $campaign->id]) }}"
                                    class="ml-1 text-gray-700 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">
                                    {{ $campaign->name }}
                                </a>
                            </div>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <x-icon name="chevron-right" size="text-sm" class="text-gray-400" />
                                <span class="ml-1 text-gray-500 dark:text-gray-400">Destinatários</span>
                            </div>
                        </li>
                    </ol>
                </nav>
                <h1 class="mt-3 text-2xl font-bold text-gray-900 dark:text-white">Destinatários da campanha</h1>
                @if (!empty($selectedRunId))
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">Filtro ativo por execução #{{ $selectedRunId }}</p>
                @endif
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ workspace_route('tenant.campaigns.show', ['campaign' => $campaign->id]) }}" class="btn btn-outline inline-flex items-center">
                    <i class="mdi mdi-arrow-left mr-1 text-sm"></i>
                    Voltar para campanha
                </a>
                <a href="{{ workspace_route('tenant.campaigns.runs.index', ['campaign' => $campaign->id]) }}" class="btn btn-outline inline-flex items-center">
                    <i class="mdi mdi-history mr-1 text-sm"></i>
                    Ver execuções
                </a>
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Lista de destinatários</h2>
            </div>
            <div class="p-6">
                <div
                    id="campaign-recipients-grid-wrapper"
                    data-grid-url="{{ workspace_route('tenant.campaigns.recipients.grid', ['campaign' => $campaign->id, 'run_id' => $selectedRunId]) }}"
                >
                    <div id="campaign-recipients-grid"></div>
                </div>
            </div>
        </div>
    </div>
@endsection
