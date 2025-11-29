@extends('layouts.connect_plus.app')

@section('title', 'Detalhes da Sincronização')

@section('content')

    <div class="page-header">
        <h3 class="page-title"> Detalhes da Sincronização </h3>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('tenant.dashboard') }}">Dashboard</a>
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
                    <h4 class="card-title">Detalhes</h4>

                    <p><strong>ID:</strong> {{ $syncState->id }}</p>
                    <p><strong>Agendamento ID:</strong> {{ $syncState->appointment_id ?? 'N/A' }}</p>
                    <p><strong>ID Evento Externo:</strong> {{ $syncState->external_event_id ?? 'N/A' }}</p>
                    <p><strong>Provedor:</strong> {{ $syncState->provider ?? 'N/A' }}</p>
                    <p><strong>Última Sincronização:</strong> {{ $syncState->last_sync_at ? $syncState->last_sync_at->format('d/m/Y H:i') : 'N/A' }}</p>
                    <p><strong>Criado em:</strong> {{ $syncState->created_at }}</p>

                    <a href="{{ route('tenant.calendar-sync.index') }}" class="btn btn-light">Voltar</a>
                </div>
            </div>
        </div>
    </div>

@endsection

