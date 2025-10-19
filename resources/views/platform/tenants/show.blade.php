@extends('layouts.freedash.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-10 col-md-12">

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">
                        <i class="fas fa-building text-primary me-2"></i> Detalhes do Tenant
                    </h4>
                    <a href="{{ route('Platform.tenants.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </a>
                </div>

                <div class="card-body">
                    {{-- Informações gerais --}}
                    <h5 class="text-primary fw-bold mb-3">
                        <i class="fas fa-info-circle me-2"></i> Informações Gerais
                    </h5>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="fw-semibold text-muted">Razão Social:</label>
                            <p class="mb-0">{{ $tenant->legal_name }}</p>
                        </div>

                        <div class="col-md-6">
                            <label class="fw-semibold text-muted">Nome Fantasia:</label>
                            <p class="mb-0">{{ $tenant->trade_name ?? '-' }}</p>
                        </div>

                        <div class="col-md-4">
                            <label class="fw-semibold text-muted">Documento:</label>
                            <p class="mb-0">{{ $tenant->document ?? '-' }}</p>
                        </div>

                        <div class="col-md-4">
                            <label class="fw-semibold text-muted">Email:</label>
                            <p class="mb-0">{{ $tenant->email ?? '-' }}</p>
                        </div>

                        <div class="col-md-4">
                            <label class="fw-semibold text-muted">Telefone:</label>
                            <p class="mb-0">{{ $tenant->phone ?? '-' }}</p>
                        </div>

                        <div class="col-md-4">
                            <label class="fw-semibold text-muted">Subdomínio:</label>
                            <p class="mb-0">{{ $tenant->subdomain }}</p>
                        </div>

                        <div class="col-md-4">
                            <label class="fw-semibold text-muted">Status:</label>
                            <span class="badge bg-{{ $tenant->status === 'active' ? 'success' : ($tenant->status === 'trial' ? 'info' : ($tenant->status === 'suspended' ? 'warning' : 'danger')) }}">
                                {{ ucfirst($tenant->status) }}
                            </span>
                        </div>

                        <div class="col-md-4">
                            <label class="fw-semibold text-muted">Trial até:</label>
                            <p class="mb-0">
                                {{ $tenant->trial_ends_at ? $tenant->trial_ends_at->format('d/m/Y') : '-' }}
                            </p>
                        </div>
                    </div>

                    {{-- Localização --}}
                    <div class="mt-5">
                        <h5 class="text-primary fw-bold mb-3">
                            <i class="fas fa-map-marker-alt me-2"></i> Localização da Empresa
                        </h5>

                        @if ($tenant->localizacao)
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="fw-semibold text-muted">Endereço:</label>
                                    <p class="mb-0">{{ $tenant->localizacao->endereco ?? '-' }}</p>
                                </div>

                                <div class="col-md-2">
                                    <label class="fw-semibold text-muted">Número:</label>
                                    <p class="mb-0">{{ $tenant->localizacao->n_endereco ?? '-' }}</p>
                                </div>

                                <div class="col-md-4">
                                    <label class="fw-semibold text-muted">Complemento:</label>
                                    <p class="mb-0">{{ $tenant->localizacao->complemento ?? '-' }}</p>
                                </div>

                                <div class="col-md-4">
                                    <label class="fw-semibold text-muted">Bairro:</label>
                                    <p class="mb-0">{{ $tenant->localizacao->bairro ?? '-' }}</p>
                                </div>

                                <div class="col-md-4">
                                    <label class="fw-semibold text-muted">CEP:</label>
                                    <p class="mb-0">{{ $tenant->localizacao->cep ?? '-' }}</p>
                                </div>

                                <div class="col-md-4">
                                    <label class="fw-semibold text-muted">Cidade:</label>
                                    <p class="mb-0">{{ $tenant->localizacao->cidade->nome_cidade ?? '-' }}</p>
                                </div>

                                <div class="col-md-4">
                                    <label class="fw-semibold text-muted">Estado:</label>
                                    <p class="mb-0">
                                        {{ $tenant->localizacao->estado->nome_estado ?? '-' }}
                                        @if($tenant->localizacao->estado && $tenant->localizacao->estado->uf)
                                            ({{ $tenant->localizacao->estado->uf }})
                                        @endif
                                    </p>
                                </div>

                                <div class="col-md-4">
                                    <label class="fw-semibold text-muted">País:</label>
                                    <p class="mb-0">{{ $tenant->localizacao->pais->nome ?? '-' }}</p>
                                </div>
                            </div>
                        @else
                            <div class="alert alert-light border mt-2">
                                <i class="fas fa-info-circle text-secondary me-2"></i>
                                Nenhuma localização cadastrada para este tenant.
                            </div>
                        @endif
                    </div>

                    {{-- Banco de Dados --}}
                    <div class="mt-5">
                        <h5 class="text-primary fw-bold mb-3">
                            <i class="fas fa-database me-2"></i> Configuração do Banco de Dados
                        </h5>

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="fw-semibold text-muted">Host:</label>
                                <p class="mb-0">{{ $tenant->db_host }}</p>
                            </div>

                            <div class="col-md-4">
                                <label class="fw-semibold text-muted">Database:</label>
                                <p class="mb-0">{{ $tenant->db_name }}</p>
                            </div>

                            <div class="col-md-4">
                                <label class="fw-semibold text-muted">Usuário:</label>
                                <p class="mb-0">{{ $tenant->db_username }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-4">
                        <a href="{{ route('platform.tenants.edit', $tenant->id) }}" class="btn btn-primary">
                            <i class="fas fa-edit me-2"></i> Editar Tenant
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

@include('layouts.freedash.footer')
@endsection
