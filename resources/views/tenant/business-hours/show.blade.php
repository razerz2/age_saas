@extends('layouts.connect_plus.app')

@section('title', 'Detalhes do Horário Comercial')

@section('content')

    <div class="page-header">
        <h3 class="page-title"> Detalhes do Horário Comercial </h3>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('tenant.dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('tenant.business-hours.index') }}">Horários Comerciais</a>
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

                    <p><strong>ID:</strong> {{ $businessHour->id }}</p>
                    <p><strong>Médico:</strong> {{ $businessHour->doctor->user->name ?? 'N/A' }}</p>
                    <p><strong>Dia da Semana:</strong> 
                        @php
                            $days = ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'];
                        @endphp
                        {{ $days[$businessHour->weekday] ?? $businessHour->weekday }}
                    </p>
                    <p><strong>Horário Início:</strong> {{ $businessHour->start_time }}</p>
                    <p><strong>Horário Fim:</strong> {{ $businessHour->end_time }}</p>
                    <p><strong>Criado em:</strong> {{ $businessHour->created_at }}</p>

                    <a href="{{ route('tenant.business-hours.edit', $businessHour->id) }}" class="btn btn-warning">Editar</a>
                    <a href="{{ route('tenant.business-hours.index') }}" class="btn btn-light">Voltar</a>
                </div>
            </div>
        </div>
    </div>

@endsection

