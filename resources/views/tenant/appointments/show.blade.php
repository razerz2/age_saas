@extends('layouts.connect_plus.app')

@section('title', 'Detalhes do Agendamento')

@section('content')

    <div class="page-header">
        <h3 class="page-title"> Detalhes do Agendamento </h3>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('tenant.dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('tenant.appointments.index') }}">Agendamentos</a>
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

                    <p><strong>ID:</strong> {{ $appointment->id }}</p>
                    <p><strong>Calendário:</strong> {{ $appointment->calendar->name ?? 'N/A' }}</p>
                    <p><strong>Tipo de Consulta:</strong> {{ $appointment->type->name ?? 'N/A' }}</p>
                    <p><strong>Paciente:</strong> {{ $appointment->patient->full_name ?? 'N/A' }}</p>
                    <p><strong>Especialidade:</strong> {{ $appointment->specialty->name ?? 'N/A' }}</p>
                    <p><strong>Data e Hora de Início:</strong> {{ $appointment->starts_at ? $appointment->starts_at->format('d/m/Y H:i') : 'N/A' }}</p>
                    <p><strong>Data e Hora de Fim:</strong> {{ $appointment->ends_at ? $appointment->ends_at->format('d/m/Y H:i') : 'N/A' }}</p>
                    <p><strong>Status:</strong> {{ $appointment->status_translated }}</p>
                    <p><strong>Observações:</strong> {{ $appointment->notes ?? 'N/A' }}</p>
                    <p><strong>Criado em:</strong> {{ $appointment->created_at }}</p>

                    <a href="{{ route('tenant.appointments.edit', $appointment->id) }}" class="btn btn-warning">Editar</a>
                    <a href="{{ route('tenant.appointments.index') }}" class="btn btn-light">Voltar</a>
                </div>
            </div>
        </div>
    </div>

@endsection

