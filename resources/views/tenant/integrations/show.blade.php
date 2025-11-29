@extends('layouts.connect_plus.app')

@section('title', 'Detalhes da Integração')

@section('content')

    <div class="page-header">
        <h3 class="page-title"> Detalhes da Integração </h3>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('tenant.dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('tenant.integrations.index') }}">Integrações</a>
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

                    <p><strong>ID:</strong> {{ $integration->id }}</p>
                    <p><strong>Chave:</strong> {{ $integration->key }}</p>
                    <p><strong>Status:</strong> 
                        @if ($integration->is_enabled)
                            <span class="badge bg-success">Habilitado</span>
                        @else
                            <span class="badge bg-danger">Desabilitado</span>
                        @endif
                    </p>
                    <p><strong>Configuração:</strong></p>
                    <pre>{{ is_array($integration->config) ? json_encode($integration->config, JSON_PRETTY_PRINT) : $integration->config }}</pre>
                    <p><strong>Criado em:</strong> {{ $integration->created_at }}</p>

                    <a href="{{ route('tenant.integrations.edit', $integration->id) }}" class="btn btn-warning">Editar</a>
                    <a href="{{ route('tenant.integrations.index') }}" class="btn btn-light">Voltar</a>
                </div>
            </div>
        </div>
    </div>

@endsection

