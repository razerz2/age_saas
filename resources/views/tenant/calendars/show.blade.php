@extends('layouts.connect_plus.app')

@section('title', 'Detalhes do Calendário')

@section('content')

    <div class="page-header">
        <h3 class="page-title">
            <i class="mdi mdi-calendar text-primary me-2"></i>
            Detalhes do Calendário
        </h3>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ workspace_route('tenant.dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('tenant.calendars.index') }}">Calendários</a>
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
                            <i class="mdi mdi-calendar-text text-primary me-2"></i>
                            Informações do Calendário
                        </h4>
                        <div>
                            <a href="{{ route('tenant.calendars.edit', $calendar->id) }}" class="btn btn-warning btn-sm">
                                <i class="mdi mdi-pencil me-1"></i> Editar
                            </a>
                            <a href="{{ route('tenant.calendars.index') }}" class="btn btn-secondary btn-sm">
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
                                <p class="mb-0 fw-semibold">{{ $calendar->id }}</p>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="border rounded p-3 h-100">
                                <label class="text-muted small mb-1 d-block">
                                    <i class="mdi mdi-format-title me-1"></i> Nome
                                </label>
                                <p class="mb-0 fw-semibold">{{ $calendar->name }}</p>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="border rounded p-3 h-100">
                                <label class="text-muted small mb-1 d-block">
                                    <i class="mdi mdi-doctor me-1"></i> Médico
                                </label>
                                <p class="mb-0 fw-semibold">{{ $calendar->doctor->user->name ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="border rounded p-3 h-100">
                                <label class="text-muted small mb-1 d-block">
                                    <i class="mdi mdi-link me-1"></i> ID Externo
                                </label>
                                <p class="mb-0 fw-semibold">{{ $calendar->external_id ?? 'N/A' }}</p>
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
                                <p class="mb-0">{{ $calendar->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted small mb-1 d-block">
                                    <i class="mdi mdi-calendar-edit me-1"></i> Atualizado em
                                </label>
                                <p class="mb-0">{{ $calendar->updated_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Botões de Ação --}}
                    <div class="border-top pt-3 mt-3">
                        <a href="{{ workspace_route('tenant.calendars.events', ['id' => $calendar->id]) }}" class="btn btn-primary">
                            <i class="mdi mdi-calendar-clock me-1"></i> Ver Eventos
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

