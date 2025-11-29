@extends('layouts.connect_plus.app')

@section('title', 'Detalhes do Médico')

@section('content')

    <div class="page-header">
        <h3 class="page-title">
            <i class="mdi mdi-doctor text-primary me-2"></i>
            Detalhes do Médico
        </h3>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('tenant.dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('tenant.doctors.index') }}">Médicos</a>
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
                            <i class="mdi mdi-account-circle text-primary me-2"></i>
                            Informações do Médico
                        </h4>
                        <div>
                            <a href="{{ route('tenant.doctors.edit', $doctor->id) }}" class="btn btn-warning btn-sm">
                                <i class="mdi mdi-pencil me-1"></i> Editar
                            </a>
                            <a href="{{ route('tenant.doctors.index') }}" class="btn btn-secondary btn-sm">
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
                                <p class="mb-0 fw-semibold">{{ $doctor->id }}</p>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="border rounded p-3 h-100">
                                <label class="text-muted small mb-1 d-block">
                                    <i class="mdi mdi-account me-1"></i> Usuário
                                </label>
                                <p class="mb-0 fw-semibold">{{ $doctor->user->name ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Informações do CRM --}}
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <div class="border rounded p-3 h-100">
                                <label class="text-muted small mb-1 d-block">
                                    <i class="mdi mdi-card-account-details me-1"></i> Número CRM
                                </label>
                                <p class="mb-0 fw-semibold">{{ $doctor->crm_number ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="border rounded p-3 h-100">
                                <label class="text-muted small mb-1 d-block">
                                    <i class="mdi mdi-map-marker me-1"></i> Estado CRM
                                </label>
                                <p class="mb-0 fw-semibold">{{ $doctor->crm_state ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Especialidades --}}
                    <div class="mb-4">
                        <label class="text-muted small mb-2 d-block">
                            <i class="mdi mdi-medical-bag me-1"></i> Especialidades
                        </label>
                        @if($doctor->specialties->count() > 0)
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($doctor->specialties as $specialty)
                                    <span class="badge bg-primary-subtle text-primary px-3 py-2">
                                        <i class="mdi mdi-star me-1"></i>
                                        {{ $specialty->name }}
                                    </span>
                                @endforeach
                            </div>
                        @else
                            <div class="alert alert-info mb-0">
                                <i class="mdi mdi-information-outline me-2"></i>
                                Nenhuma especialidade cadastrada
                            </div>
                        @endif
                    </div>

                    {{-- Assinatura --}}
                    @if($doctor->signature)
                        <div class="mb-4">
                            <label class="text-muted small mb-2 d-block">
                                <i class="mdi mdi-pen me-1"></i> Assinatura
                            </label>
                            <div class="border rounded p-3 bg-light">
                                <p class="mb-0">{{ $doctor->signature }}</p>
                            </div>
                        </div>
                    @endif

                    {{-- Informações Adicionais --}}
                    <div class="border-top pt-3">
                        <div class="row">
                            <div class="col-md-6">
                                <label class="text-muted small mb-1 d-block">
                                    <i class="mdi mdi-calendar-plus me-1"></i> Criado em
                                </label>
                                <p class="mb-0">{{ $doctor->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted small mb-1 d-block">
                                    <i class="mdi mdi-calendar-edit me-1"></i> Atualizado em
                                </label>
                                <p class="mb-0">{{ $doctor->updated_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
