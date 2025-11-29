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

    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Detalhes</h4>

                    <p><strong>ID:</strong> {{ $patient->id }}</p>
                    <p><strong>Nome Completo:</strong> {{ $patient->full_name }}</p>
                    <p><strong>CPF:</strong> {{ $patient->cpf ?? 'N/A' }}</p>
                    <p><strong>Data de Nascimento:</strong> {{ $patient->birth_date ? $patient->birth_date->format('d/m/Y') : 'N/A' }}</p>
                    <p><strong>E-mail:</strong> {{ $patient->email ?? 'N/A' }}</p>
                    <p><strong>Telefone:</strong> {{ $patient->phone ?? 'N/A' }}</p>
                    <p><strong>Status:</strong> 
                        @if ($patient->is_active)
                            <span class="badge bg-success">Ativo</span>
                        @else
                            <span class="badge bg-danger">Inativo</span>
                        @endif
                    </p>
                    <p><strong>Criado em:</strong> {{ $patient->created_at }}</p>

                    <a href="{{ route('tenant.patients.edit', $patient->id) }}" class="btn btn-warning">Editar</a>
                    <a href="{{ route('tenant.patients.index') }}" class="btn btn-light">Voltar</a>
                </div>
            </div>
        </div>
    </div>

@endsection
