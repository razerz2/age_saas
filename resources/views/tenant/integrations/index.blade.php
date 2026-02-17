@extends('layouts.tailadmin.app')

@section('title', 'Integrações')

@section('content')

    <div class="page-header">
        <h3 class="page-title"> Integrações </h3>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ workspace_route('tenant.dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Integrações</li>
            </ol>
        </nav>
    </div>

    <div class="row">
        <div class="col-lg-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Integrações Disponíveis</h4>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card border-primary shadow-sm">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="me-3">
                                            <i class="mdi mdi-google text-primary" style="font-size: 3rem;"></i>
                                        </div>
                                        <div>
                                            <h5 class="card-title mb-0">Google Calendar</h5>
                                            <small class="text-muted">Sincronização Automática</small>
                                        </div>
                                    </div>
                                    <p class="card-text text-muted mb-3">
                                        Sincronize automaticamente os agendamentos com o Google Calendar. 
                                        Cada médico pode conectar sua própria conta do Google.
                                    </p>
                                    <ul class="list-unstyled mb-3">
                                        <li class="mb-2">
                                            <i class="mdi mdi-check-circle text-success me-2"></i>
                                            Sincronização automática de agendamentos
                                        </li>
                                        <li class="mb-2">
                                            <i class="mdi mdi-check-circle text-success me-2"></i>
                                            Suporte a agendamentos recorrentes
                                        </li>
                                        <li class="mb-2">
                                            <i class="mdi mdi-check-circle text-success me-2"></i>
                                            Conta individual por médico
                                        </li>
                                    </ul>
                                    <x-tailadmin-button variant="primary" size="md" href="{{ workspace_route('tenant.integrations.google.index') }}">
                                        <i class="mdi mdi-google"></i>
                                        Configurar Google Calendar
                                    </x-tailadmin-button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <h5 class="mb-3">Outras Integrações</h5>

                    <x-tailadmin-button variant="secondary" size="md" href="{{ workspace_route('tenant.integrations.create') }}"
                        class="mb-3 bg-transparent border-gray-300 text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-white/5">
                        + Nova Integração
                    </x-tailadmin-button>

                    <div>
                        <table class="table table-hover" id="datatable-list">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Chave</th>
                                    <th>Status</th>
                                    <th style="width: 140px;">Ações</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($integrations as $integration)
                                    <tr>
                                        <td>{{ truncate_uuid($integration->id) }}</td>
                                        <td>{{ $integration->key }}</td>
                                        <td>
                                            @if ($integration->is_enabled)
                                                <span class="badge bg-success">Habilitado</span>
                                            @else
                                                <span class="badge bg-danger">Desabilitado</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="flex items-center gap-2">
                                                <x-tailadmin-button variant="secondary" size="sm"
                                                    href="{{ workspace_route('tenant.integrations.show', $integration->id) }}"
                                                    class="border-info text-info bg-info/10 hover:bg-info/20 dark:border-info/40 dark:text-info dark:hover:bg-info/30">
                                                    Ver
                                                </x-tailadmin-button>
                                                <x-tailadmin-button variant="warning" size="sm"
                                                    href="{{ workspace_route('tenant.integrations.edit', $integration->id) }}">
                                                    Editar
                                                </x-tailadmin-button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>

@endsection


