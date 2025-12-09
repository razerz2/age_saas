@extends('layouts.connect_plus.app')

@section('title', 'Detalhes da Sincronização')

@section('content')

    <div class="page-header">
        <h3 class="page-title">
            <i class="mdi mdi-sync text-primary me-2"></i>
            Detalhes da Sincronização
        </h3>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ workspace_route('tenant.dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('tenant.calendar-sync.index') }}">Sincronização de Calendário</a>
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
                            <i class="mdi mdi-sync text-primary me-2"></i>
                            Informações da Sincronização
                        </h4>
                        <div>
                            <a href="{{ route('tenant.calendar-sync.index') }}" class="btn btn-secondary btn-sm">
                                <i class="mdi mdi-arrow-left me-1"></i> Voltar
                            </a>
                        </div>
                    </div>

                    {{-- Informações Principais --}}
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <div class="border rounded p-3 h-100">
                                <label class="text-muted small mb-1 d-block">
                                    <i class="mdi mdi-identifier me-1"></i> ID
                                </label>
                                <p class="mb-0 fw-semibold">{{ $syncState->id }}</p>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="border rounded p-3 h-100">
                                <label class="text-muted small mb-1 d-block">
                                    <i class="mdi mdi-calendar-clock me-1"></i> Agendamento ID
                                </label>
                                <p class="mb-0 fw-semibold">{{ $syncState->appointment_id ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="border rounded p-3 h-100">
                                <label class="text-muted small mb-1 d-block">
                                    <i class="mdi mdi-link me-1"></i> ID Evento Externo
                                </label>
                                <p class="mb-0 fw-semibold">{{ $syncState->external_event_id ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="border rounded p-3 h-100">
                                <label class="text-muted small mb-1 d-block">
                                    <i class="mdi mdi-cloud me-1"></i> Provedor
                                </label>
                                <p class="mb-0 fw-semibold">{{ $syncState->provider ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="border rounded p-3 h-100 bg-light">
                                <label class="text-muted small mb-1 d-block">
                                    <i class="mdi mdi-clock-check me-1"></i> Última Sincronização
                                </label>
                                <p class="mb-0 fw-semibold">
                                    {{ $syncState->last_sync_at ? $syncState->last_sync_at->format('d/m/Y H:i') : 'N/A' }}
                                    @if($syncState->last_sync_at)
                                        <span class="text-muted small ms-2">
                                            ({{ $syncState->last_sync_at->diffForHumans() }})
                                        </span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Informações Adicionais --}}
                    <div class="border-top pt-3">
                        <div class="row">
                            <div class="col-md-6">
                                <label class="text-muted small mb-1 d-block">
                                    <i class="mdi mdi-calendar-plus me-1"></i> Criado em
                                </label>
                                <p class="mb-0">{{ $syncState->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted small mb-1 d-block">
                                    <i class="mdi mdi-calendar-edit me-1"></i> Atualizado em
                                </label>
                                <p class="mb-0">{{ $syncState->updated_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

