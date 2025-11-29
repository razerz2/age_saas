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

    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Detalhes</h4>

                    <p><strong>ID:</strong> {{ $doctor->id }}</p>
                    <p><strong>Usuário:</strong> {{ $doctor->user->name ?? 'N/A' }}</p>
                    <p><strong>Número CRM:</strong> {{ $doctor->crm_number ?? 'N/A' }}</p>
                    <p><strong>Estado CRM:</strong> {{ $doctor->crm_state ?? 'N/A' }}</p>
                    <p><strong>Assinatura:</strong> {{ $doctor->signature ?? 'N/A' }}</p>
                    <p><strong>Criado em:</strong> {{ $doctor->created_at }}</p>

                    <a href="{{ route('tenant.doctors.edit', $doctor->id) }}" class="btn btn-warning">Editar</a>
                    <a href="{{ route('tenant.doctors.index') }}" class="btn btn-light">Voltar</a>
                </div>
            </div>
        </div>
    </div>

@endsection
