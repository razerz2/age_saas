@extends('layouts.connect_plus.app')

@section('title', 'Detalhes do Paciente')

@section('content')

    <div class="page-header">
        <h3 class="page-title">
            <i class="mdi mdi-account-heart text-primary me-2"></i>
            Detalhes do Paciente
        </h3>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('tenant.dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('tenant.patients.index') }}">Pacientes</a>
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
                            Informações do Paciente
                        </h4>
                        <div>
                            <a href="{{ route('tenant.patients.edit', $patient->id) }}" class="btn btn-warning btn-sm">
                                <i class="mdi mdi-pencil me-1"></i> Editar
                            </a>
                            <a href="{{ route('tenant.patients.index') }}" class="btn btn-secondary btn-sm">
                                <i class="mdi mdi-arrow-left me-1"></i> Voltar
                            </a>
                        </div>
                    </div>

                    {{-- Status Badge --}}
                    <div class="mb-4">
                        @if ($patient->is_active)
                            <span class="badge bg-success px-3 py-2">
                                <i class="mdi mdi-check-circle me-1"></i> Ativo
                            </span>
                        @else
                            <span class="badge bg-danger px-3 py-2">
                                <i class="mdi mdi-close-circle me-1"></i> Inativo
                            </span>
                        @endif
                    </div>

                    {{-- Informações Pessoais --}}
                    <h5 class="text-primary mb-3">
                        <i class="mdi mdi-information-outline me-2"></i>
                        Informações Pessoais
                    </h5>
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <div class="border rounded p-3 h-100">
                                <label class="text-muted small mb-1 d-block">
                                    <i class="mdi mdi-identifier me-1"></i> ID
                                </label>
                                <p class="mb-0 fw-semibold">{{ $patient->id }}</p>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="border rounded p-3 h-100">
                                <label class="text-muted small mb-1 d-block">
                                    <i class="mdi mdi-account me-1"></i> Nome Completo
                                </label>
                                <p class="mb-0 fw-semibold">{{ $patient->full_name }}</p>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="border rounded p-3 h-100">
                                <label class="text-muted small mb-1 d-block">
                                    <i class="mdi mdi-card-account-details me-1"></i> CPF
                                </label>
                                <p class="mb-0 fw-semibold">{{ $patient->cpf ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="border rounded p-3 h-100">
                                <label class="text-muted small mb-1 d-block">
                                    <i class="mdi mdi-calendar me-1"></i> Data de Nascimento
                                </label>
                                <p class="mb-0 fw-semibold">
                                    {{ $patient->birth_date ? $patient->birth_date->format('d/m/Y') : 'N/A' }}
                                    @if($patient->birth_date)
                                        <span class="text-muted small ms-2">
                                            ({{ $patient->birth_date->age }} anos)
                                        </span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Informações de Contato --}}
                    <h5 class="text-primary mb-3">
                        <i class="mdi mdi-phone me-2"></i>
                        Informações de Contato
                    </h5>
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <div class="border rounded p-3 h-100">
                                <label class="text-muted small mb-1 d-block">
                                    <i class="mdi mdi-email me-1"></i> E-mail
                                </label>
                                @if($patient->email)
                                    <p class="mb-0 fw-semibold">
                                        <a href="mailto:{{ $patient->email }}" class="text-decoration-none">
                                            {{ $patient->email }}
                                        </a>
                                    </p>
                                @else
                                    <p class="mb-0 text-muted">N/A</p>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="border rounded p-3 h-100">
                                <label class="text-muted small mb-1 d-block">
                                    <i class="mdi mdi-phone me-1"></i> Telefone
                                </label>
                                @if($patient->phone)
                                    <p class="mb-0 fw-semibold">
                                        <a href="tel:{{ $patient->phone }}" class="text-decoration-none">
                                            {{ $patient->phone }}
                                        </a>
                                    </p>
                                @else
                                    <p class="mb-0 text-muted">N/A</p>
                                @endif
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
                                <p class="mb-0">{{ $patient->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted small mb-1 d-block">
                                    <i class="mdi mdi-calendar-edit me-1"></i> Atualizado em
                                </label>
                                <p class="mb-0">{{ $patient->updated_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
