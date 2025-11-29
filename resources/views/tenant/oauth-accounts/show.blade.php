@extends('layouts.connect_plus.app')

@section('title', 'Detalhes da Conta OAuth')

@section('content')

    <div class="page-header">
        <h3 class="page-title"> Detalhes da Conta OAuth </h3>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('tenant.dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('tenant.oauth-accounts.index') }}">Contas OAuth</a>
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

                    <p><strong>ID:</strong> {{ $oauthAccount->id }}</p>
                    <p><strong>Integração:</strong> {{ $oauthAccount->integration->key ?? 'N/A' }}</p>
                    <p><strong>Usuário ID:</strong> {{ $oauthAccount->user_id ?? 'N/A' }}</p>
                    <p><strong>Expira em:</strong> {{ $oauthAccount->expires_at ? $oauthAccount->expires_at->format('d/m/Y H:i') : 'N/A' }}</p>
                    <p><strong>Criado em:</strong> {{ $oauthAccount->created_at }}</p>

                    <a href="{{ route('tenant.oauth-accounts.index') }}" class="btn btn-light">Voltar</a>
                </div>
            </div>
        </div>
    </div>

@endsection

