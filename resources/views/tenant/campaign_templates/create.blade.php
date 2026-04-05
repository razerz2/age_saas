@extends('layouts.tailadmin.app')

@section('title', 'Novo Template de Campanha')
@section('page', 'campaign-templates')

@section('content')
    @php
        $formAction = workspace_route('tenant.campaign-templates.store');
        $httpMethod = 'POST';
        $submitLabel = 'Salvar template';
        $cancelUrl = workspace_route('tenant.campaign-templates.index');
    @endphp

    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Novo Template de Campanha</h1>
            <nav class="mt-2 flex" aria-label="Breadcrumb">
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
                                class="ml-1 text-gray-700 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">Campanhas</a>
                        </div>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <x-icon name="chevron-right" size="text-sm" class="text-gray-400" />
                            <a href="{{ workspace_route('tenant.campaign-templates.index') }}"
                                class="ml-1 text-gray-700 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">Templates</a>
                        </div>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <x-icon name="chevron-right" size="text-sm" class="text-gray-400" />
                            <span class="ml-1 text-gray-500 dark:text-gray-400">Novo</span>
                        </div>
                    </li>
                </ol>
            </nav>
        </div>

        @include('tenant.campaign_templates._form', [
            'campaignTemplate' => $campaignTemplate,
            'availableVariables' => $availableVariables,
            'formAction' => $formAction,
            'httpMethod' => $httpMethod,
            'submitLabel' => $submitLabel,
            'cancelUrl' => $cancelUrl,
        ])
    </div>
@endsection

