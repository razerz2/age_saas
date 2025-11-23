@extends('layouts.connect_plus.app')

@section('title', 'Detalhes do Paciente')

@section('content')

    <div class="page-header">
        <h3 class="page-title"> Detalhes do Paciente </h3>

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

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Informações Pessoais</h5>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><i class="mdi mdi-identifier"></i> <strong>ID:</strong> {{ $patient->id }}</p>
                            <p><i class="mdi mdi-account-circle"></i> <strong>Nome Completo:</strong>
                                {{ $patient->full_name }}</p>
                            <p><i class="mdi mdi-card-account-details"></i> <strong>CPF:</strong>
                                {{ $patient->cpf ?? '-' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><i class="mdi mdi-calendar"></i> <strong>Data de Nascimento:</strong>
                                {{ $patient->birth_date ? $patient->birth_date->format('d/m/Y') : '-' }}</p>
                            <p><i class="mdi mdi-email-outline"></i> <strong>E-mail:</strong>
                                {{ $patient->email ?? '-' }}</p>
                            <p><i class="mdi mdi-phone"></i> <strong>Telefone:</strong>
                                {{ $patient->phone ?? '-' }}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><i class="mdi mdi-check-circle-outline"></i> <strong>Status:</strong>
                                @if ($patient->is_active)
                                    <span class="badge bg-success">Ativo</span>
                                @else
                                    <span class="badge bg-danger">Inativo</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <!-- Botão de Edição dentro do card e alinhado à direita -->
                    <div class="text-end mt-4">
                        <a href="{{ route('tenant.patients.edit', $patient->id) }}" class="btn btn-warning btn-small">
                            <i class="mdi mdi-pencil"></i> Editar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

