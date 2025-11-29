@extends('layouts.connect_plus.app')

@section('title', 'Detalhes do Calendário')

@section('content')

    <div class="page-header">
        <h3 class="page-title"> Detalhes do Calendário </h3>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('tenant.dashboard') }}">Dashboard</a>
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
                    <h4 class="card-title">Detalhes</h4>

                    <p><strong>ID:</strong> {{ $calendar->id }}</p>
                    <p><strong>Nome:</strong> {{ $calendar->name }}</p>
                    <p><strong>Médico:</strong> {{ $calendar->doctor->user->name ?? 'N/A' }}</p>
                    <p><strong>ID Externo:</strong> {{ $calendar->external_id ?? 'N/A' }}</p>
                    <p><strong>Criado em:</strong> {{ $calendar->created_at }}</p>

                    <a href="{{ route('tenant.calendars.edit', $calendar->id) }}" class="btn btn-warning">Editar</a>
                    <a href="{{ route('tenant.calendars.events', $calendar->id) }}" class="btn btn-primary">Eventos</a>
                    <a href="{{ route('tenant.calendars.index') }}" class="btn btn-light">Voltar</a>
                </div>
            </div>
        </div>
    </div>

@endsection

