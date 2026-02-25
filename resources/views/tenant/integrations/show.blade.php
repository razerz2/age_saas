@extends('layouts.tailadmin.app')

@section('title', 'Detalhes da Integração')
@section('page', 'integrations')

@section('content')

    <div class="page-header">
        <h3 class="page-title">
            <x-icon name="link" class=" text-primary me-2" />
            Detalhes da Integração
        </h3>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ workspace_route('tenant.dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ workspace_route('tenant.integrations.index') }}">Integrações</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Detalhes</li>
            </ol>
        </nav>
    </div>

    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    {{-- Header do Card --}}
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="card-title mb-0">
                            <x-icon name="link" class=" text-primary me-2" />
                            Informações da Integração
                        </h4>
                        <div class="flex items-center justify-end gap-3 flex-nowrap">
                            <x-tailadmin-button variant="warning" size="sm" href="{{ workspace_route('tenant.integrations.edit', $integration->id) }}" class="inline-flex items-center gap-2 tenant-action-edit">
                                <x-icon name="pencil" class="" /> Editar
                            </x-tailadmin-button>
                            <x-tailadmin-button variant="secondary" size="sm" href="{{ workspace_route('tenant.integrations.index') }}"
                                class="inline-flex items-center gap-2 bg-transparent border-gray-300 text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-white/5">
                                <x-icon name="arrow-left" class="" /> Voltar
                            </x-tailadmin-button>
                        </div>
                    </div>

                    {{-- Status Badge --}}
                    <div class="mb-4">
                        @if ($integration->is_enabled)
                            <span class="badge bg-success px-3 py-2">
                                <x-icon name="check-circle" class=" me-1" /> Habilitado
                            </span>
                        @else
                            <span class="badge bg-danger px-3 py-2">
                                <x-icon name="close-circle" class=" me-1" /> Desabilitado
                            </span>
                        @endif
                    </div>

                    {{-- Informações Principais --}}
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <div class="border rounded p-3 h-100">
                                <label class="text-muted small mb-1 d-block">
                                    <x-icon name="identifier" class=" me-1" /> ID
                                </label>
                                <p class="mb-0 fw-semibold">{{ $integration->id }}</p>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="border rounded p-3 h-100">
                                <label class="text-muted small mb-1 d-block">
                                    <x-icon name="key" class=" me-1" /> Chave
                                </label>
                                <p class="mb-0 fw-semibold">{{ $integration->key }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Configuração --}}
                    <div class="mb-4">
                        <label class="text-muted small mb-2 d-block">
                            <x-icon name="cog" class=" me-1" /> Configuração
                        </label>
                        <div class="border rounded p-3 bg-light">
                            <pre class="mb-0" style="max-height: 300px; overflow-y: auto;">{{ is_array($integration->config) ? json_encode($integration->config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : $integration->config }}</pre>
                        </div>
                    </div>

                    {{-- Informações Adicionais --}}
                    <div class="border-top pt-3">
                        <div class="row">
                            <div class="col-md-6">
                                <label class="text-muted small mb-1 d-block">
                                    <x-icon name="calendar-plus" class=" me-1" /> Criado em
                                </label>
                                <p class="mb-0">{{ $integration->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted small mb-1 d-block">
                                    <x-icon name="calendar-edit" class=" me-1" /> Atualizado em
                                </label>
                                <p class="mb-0">{{ $integration->updated_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
