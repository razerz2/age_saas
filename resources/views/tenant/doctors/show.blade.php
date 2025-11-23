@extends('layouts.connect_plus.app')

@section('title', 'Detalhes do Médico')

@section('content')

    <div class="page-header">
        <h3 class="page-title"> Detalhes do Médico </h3>

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

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Informações do Médico</h5>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><i class="mdi mdi-identifier"></i> <strong>ID:</strong> {{ $doctor->id }}</p>
                            <p><i class="mdi mdi-account-circle"></i> <strong>Usuário:</strong>
                                {{ $doctor->user->name_full ?? '-' }}</p>
                            <p><i class="mdi mdi-email-outline"></i> <strong>E-mail:</strong>
                                {{ $doctor->user->email ?? '-' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><i class="mdi mdi-card-account-details"></i> <strong>CRM:</strong>
                                {{ $doctor->crm_number ?? '-' }}</p>
                            <p><i class="mdi mdi-map-marker"></i> <strong>Estado CRM:</strong>
                                {{ $doctor->crm_state ?? '-' }}</p>
                            <p><i class="mdi mdi-pen"></i> <strong>Assinatura:</strong>
                                {{ $doctor->signature ?? '-' }}</p>
                        </div>
                    </div>

                    <h5 class="card-title mt-4">Especialidades</h5>
                    <div class="mb-3">
                        @if ($doctor->specialties->count() > 0)
                            @foreach ($doctor->specialties as $specialty)
                                <span class="badge bg-info me-2">{{ $specialty->name }}</span>
                            @endforeach
                        @else
                            <p class="text-muted">Nenhuma especialidade cadastrada</p>
                        @endif
                    </div>

                    <!-- Botão de Edição dentro do card e alinhado à direita -->
                    <div class="text-end mt-4">
                        <a href="{{ route('tenant.doctors.edit', $doctor->id) }}" class="btn btn-warning btn-small">
                            <i class="mdi mdi-pencil"></i> Editar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

