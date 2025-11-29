@extends('layouts.connect_plus.app')

@section('title', 'Detalhes do Tipo de Consulta')

@section('content')

    <div class="page-header">
        <h3 class="page-title"> Detalhes do Tipo de Consulta </h3>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('tenant.dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('tenant.appointment-types.index') }}">Tipos de Consulta</a>
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

                    <p><strong>ID:</strong> {{ $appointmentType->id }}</p>
                    <p><strong>Nome:</strong> {{ $appointmentType->name }}</p>
                    <p><strong>Duração:</strong> {{ $appointmentType->duration_min ?? 'N/A' }} minutos</p>
                    <p><strong>Status:</strong> 
                        @if ($appointmentType->is_active)
                            <span class="badge bg-success">Ativo</span>
                        @else
                            <span class="badge bg-danger">Inativo</span>
                        @endif
                    </p>
                    <p><strong>Criado em:</strong> {{ $appointmentType->created_at }}</p>

                    <a href="{{ route('tenant.appointment-types.edit', $appointmentType->id) }}" class="btn btn-warning">Editar</a>
                    <a href="{{ route('tenant.appointment-types.index') }}" class="btn btn-light">Voltar</a>
                </div>
            </div>
        </div>
    </div>

@endsection

