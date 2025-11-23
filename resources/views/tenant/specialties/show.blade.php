@extends('layouts.connect_plus.app')

@section('title', 'Detalhes da Especialidade')

@section('content')

    <div class="page-header">
        <h3 class="page-title"> Detalhes da Especialidade </h3>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('tenant.dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('tenant.specialties.index') }}">Especialidades</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Detalhes</li>
            </ol>
        </nav>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Informações da Especialidade</h5>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><i class="mdi mdi-identifier"></i> <strong>ID:</strong> {{ $specialty->id }}</p>
                            <p><i class="mdi mdi-tag"></i> <strong>Nome:</strong> {{ $specialty->name }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><i class="mdi mdi-code-tags"></i> <strong>Código:</strong> {{ $specialty->code ?? '-' }}</p>
                        </div>
                    </div>

                    <!-- Botão de Edição dentro do card e alinhado à direita -->
                    <div class="text-end mt-4">
                        <a href="{{ route('tenant.specialties.edit', $specialty->id) }}" class="btn btn-warning btn-small">
                            <i class="mdi mdi-pencil"></i> Editar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

