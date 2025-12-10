@extends('layouts.connect_plus.app')

@section('title', 'Detalhes do Horário Comercial')

@section('content')

    <div class="page-header">
        <h3 class="page-title">
            <i class="mdi mdi-clock-outline text-primary me-2"></i>
            Detalhes do Horário Comercial
        </h3>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ workspace_route('tenant.dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ workspace_route('tenant.business-hours.index') }}">Horários Comerciais</a>
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
                            <i class="mdi mdi-clock text-primary me-2"></i>
                            Informações do Horário Comercial
                        </h4>
                        <div>
                            <a href="{{ workspace_route('tenant.business-hours.edit', $businessHour->id) }}" class="btn btn-warning btn-sm">
                                <i class="mdi mdi-pencil me-1"></i> Editar
                            </a>
                            <a href="{{ workspace_route('tenant.business-hours.index') }}" class="btn btn-secondary btn-sm">
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
                                <p class="mb-0 fw-semibold">{{ $businessHour->id }}</p>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="border rounded p-3 h-100">
                                <label class="text-muted small mb-1 d-block">
                                    <i class="mdi mdi-doctor me-1"></i> Médico
                                </label>
                                <p class="mb-0 fw-semibold">{{ $businessHour->doctor->user->name ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="border rounded p-3 h-100 bg-light">
                                <label class="text-muted small mb-1 d-block">
                                    <i class="mdi mdi-calendar-week me-1"></i> Dia da Semana
                                </label>
                                <p class="mb-0 fw-semibold fs-6">
                                    @php
                                        $days = ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'];
                                    @endphp
                                    {{ $days[$businessHour->weekday] ?? $businessHour->weekday }}
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Horários --}}
                    <h5 class="text-primary mb-3">
                        <i class="mdi mdi-clock-time-four me-2"></i>
                        Horário de Funcionamento
                    </h5>
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <div class="border rounded p-3 bg-light">
                                <label class="text-muted small mb-1 d-block">
                                    <i class="mdi mdi-clock-start me-1"></i> Horário Início
                                </label>
                                <p class="mb-0 fw-semibold fs-5">{{ $businessHour->start_time }}</p>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="border rounded p-3 bg-light">
                                <label class="text-muted small mb-1 d-block">
                                    <i class="mdi mdi-clock-end me-1"></i> Horário Fim
                                </label>
                                <p class="mb-0 fw-semibold fs-5">{{ $businessHour->end_time }}</p>
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
                                <p class="mb-0">{{ $businessHour->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted small mb-1 d-block">
                                    <i class="mdi mdi-calendar-edit me-1"></i> Atualizado em
                                </label>
                                <p class="mb-0">{{ $businessHour->updated_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

