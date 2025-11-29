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

    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Detalhes</h4>

                    <p><strong>ID:</strong> {{ $specialty->id }}</p>
                    <p><strong>Nome:</strong> {{ $specialty->name }}</p>
                    <p><strong>Código:</strong> {{ $specialty->code ?? 'N/A' }}</p>
                    <p><strong>Criado em:</strong> {{ $specialty->created_at }}</p>

                    <a href="{{ route('tenant.specialties.edit', $specialty->id) }}" class="btn btn-warning">Editar</a>
                    <a href="{{ route('tenant.specialties.index') }}" class="btn btn-light">Voltar</a>
                </div>
            </div>
        </div>
    </div>

@endsection
