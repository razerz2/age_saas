@extends('layouts.network-admin')

@section('title', 'Detalhes da Clínica')

@section('content')
<div class="page-header">
    <h3 class="page-title">
        <span class="page-title-icon bg-gradient-primary text-white me-2">
            <i class="mdi mdi-hospital-building"></i>
        </span> Detalhes da Clínica
    </h3>
    <nav aria-label="breadcrumb">
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('network.dashboard', ['network' => app('currentNetwork')->slug]) }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('network.clinics.index', ['network' => app('currentNetwork')->slug]) }}">Clínicas</a></li>
            <li class="breadcrumb-item active" aria-current="page">Detalhes</li>
        </ul>
    </nav>
</div>

<div class="row">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">
                    <i class="mdi mdi-information text-primary me-2"></i> Informações Gerais
                </h4>
                <a href="{{ route('network.clinics.index', ['network' => app('currentNetwork')->slug]) }}" class="btn btn-sm btn-outline-secondary">
                    <i class="mdi mdi-arrow-left"></i> Voltar
                </a>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="fw-semibold text-muted">Razão Social:</label>
                        <p class="mb-0">{{ $clinic->legal_name }}</p>
                    </div>

                    <div class="col-md-6">
                        <label class="fw-semibold text-muted">Nome Fantasia:</label>
                        <p class="mb-0">{{ $clinic->trade_name ?? '-' }}</p>
                    </div>

                    <div class="col-md-4">
                        <label class="fw-semibold text-muted">Documento:</label>
                        <p class="mb-0">{{ $clinic->document ?? '-' }}</p>
                    </div>

                    <div class="col-md-4">
                        <label class="fw-semibold text-muted">Email:</label>
                        <p class="mb-0">{{ $clinic->email ?? '-' }}</p>
                    </div>

                    <div class="col-md-4">
                        <label class="fw-semibold text-muted">Telefone:</label>
                        <p class="mb-0">{{ $clinic->phone ?? '-' }}</p>
                    </div>

                    <div class="col-md-4">
                        <label class="fw-semibold text-muted">Subdomínio:</label>
                        <p class="mb-0">
                            <span class="badge badge-outline-primary">{{ $clinic->subdomain }}</span>
                        </p>
                    </div>

                    <div class="col-md-4">
                        <label class="fw-semibold text-muted">Status:</label>
                        <p class="mb-0">
                            <span class="badge bg-{{ $clinic->status === 'active' ? 'success' : ($clinic->status === 'trial' ? 'info' : ($clinic->status === 'suspended' ? 'warning' : 'danger')) }}">
                                {{ ucfirst($clinic->status) }}
                            </span>
                        </p>
                    </div>

                    <div class="col-md-4">
                        <label class="fw-semibold text-muted">Assinatura:</label>
                        <p class="mb-0">
                            @if($clinic->activeSubscription)
                                <span class="badge bg-gradient-success">{{ $clinic->activeSubscription->plan->name ?? 'Ativa' }}</span>
                            @else
                                <span class="badge bg-gradient-secondary">Sem assinatura</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if($clinic->localizacao)
<div class="row mt-4">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white">
                <h4 class="card-title mb-0">
                    <i class="mdi mdi-map-marker text-primary me-2"></i> Localização
                </h4>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="fw-semibold text-muted">Endereço:</label>
                        <p class="mb-0">{{ $clinic->localizacao->endereco ?? '-' }}</p>
                    </div>

                    <div class="col-md-2">
                        <label class="fw-semibold text-muted">Número:</label>
                        <p class="mb-0">{{ $clinic->localizacao->n_endereco ?? '-' }}</p>
                    </div>

                    <div class="col-md-4">
                        <label class="fw-semibold text-muted">Complemento:</label>
                        <p class="mb-0">{{ $clinic->localizacao->complemento ?? '-' }}</p>
                    </div>

                    <div class="col-md-4">
                        <label class="fw-semibold text-muted">Bairro:</label>
                        <p class="mb-0">{{ $clinic->localizacao->bairro ?? '-' }}</p>
                    </div>

                    <div class="col-md-4">
                        <label class="fw-semibold text-muted">CEP:</label>
                        <p class="mb-0">{{ $clinic->localizacao->cep ?? '-' }}</p>
                    </div>

                    <div class="col-md-4">
                        <label class="fw-semibold text-muted">Cidade:</label>
                        <p class="mb-0">{{ $clinic->localizacao->cidade->nome_cidade ?? '-' }}</p>
                    </div>

                    <div class="col-md-4">
                        <label class="fw-semibold text-muted">Estado:</label>
                        <p class="mb-0">
                            {{ $clinic->localizacao->estado->uf ?? ($clinic->localizacao->estado->nome_estado ?? '-') }}
                        </p>
                    </div>

                    <div class="col-md-4">
                        <label class="fw-semibold text-muted">País:</label>
                        <p class="mb-0">{{ $clinic->localizacao->pais->nome ?? '-' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

@if($clinic->activeSubscription)
<div class="row mt-4">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white">
                <h4 class="card-title mb-0">
                    <i class="mdi mdi-credit-card text-primary me-2"></i> Informações da Assinatura
                </h4>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="fw-semibold text-muted">Plano:</label>
                        <p class="mb-0">{{ $clinic->activeSubscription->plan->name ?? '-' }}</p>
                    </div>

                    <div class="col-md-4">
                        <label class="fw-semibold text-muted">Status:</label>
                        <p class="mb-0">
                            <span class="badge bg-gradient-success">{{ ucfirst($clinic->activeSubscription->status ?? 'Ativa') }}</span>
                        </p>
                    </div>

                    <div class="col-md-4">
                        <label class="fw-semibold text-muted">Início:</label>
                        <p class="mb-0">{{ $clinic->activeSubscription->starts_at ? $clinic->activeSubscription->starts_at->format('d/m/Y') : '-' }}</p>
                    </div>

                    @if($clinic->activeSubscription->ends_at)
                    <div class="col-md-4">
                        <label class="fw-semibold text-muted">Término:</label>
                        <p class="mb-0">{{ $clinic->activeSubscription->ends_at->format('d/m/Y') }}</p>
                    </div>
                    @endif

                    <div class="col-md-4">
                        <label class="fw-semibold text-muted">Renovação Automática:</label>
                        <p class="mb-0">
                            <span class="badge bg-{{ $clinic->activeSubscription->auto_renew ? 'success' : 'secondary' }}">
                                {{ $clinic->activeSubscription->auto_renew ? 'Sim' : 'Não' }}
                            </span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

@endsection

@push('styles')
<style>
    .badge-outline-primary {
        color: #b66dff;
        border: 1px solid #b66dff;
        background: transparent;
    }
    .fw-semibold {
        font-weight: 600;
    }
</style>
@endpush

