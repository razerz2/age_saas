@extends('layouts.freedash.app')

@section('content')

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-10 col-md-12">

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">
                        <i class="fas fa-building text-primary me-2"></i> Visualizar Tenant
                    </h4>
                    <div>
                        <a href="{{ route('Platform.tenants.edit', $tenant) }}" class="btn btn-sm btn-outline-primary me-2">
                            <i class="fas fa-edit"></i> Editar
                        </a>
                        <a href="{{ route('Platform.tenants.index') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Voltar
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <form>
                        <div class="row g-3">

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Razão Social</label>
                                <input type="text" value="{{ $tenant->legal_name }}" class="form-control" readonly>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Nome Fantasia</label>
                                <input type="text" value="{{ $tenant->trade_name }}" class="form-control" readonly>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Documento</label>
                                <input type="text" value="{{ $tenant->document }}" class="form-control" readonly>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Subdomínio</label>
                                <input type="text" value="{{ $tenant->subdomain }}" class="form-control" readonly>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Email</label>
                                <input type="email" value="{{ $tenant->email }}" class="form-control" readonly>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Telefone</label>
                                <input type="text" value="{{ $tenant->phone }}" class="form-control" readonly>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Status</label>
                                <input type="text" value="@switch($tenant->status)
                                    @case('trial') Trial @break
                                    @case('active') Ativo @break
                                    @case('suspended') Suspenso @break
                                    @case('cancelled') Cancelado @break
                                @endswitch" class="form-control" readonly>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Trial até</label>
                                <input type="text"
                                       value="{{ optional($tenant->trial_ends_at)->format('d/m/Y') ?? '-' }}"
                                       class="form-control"
                                       readonly>
                            </div>

                            <div class="col-12 mt-4">
                                <h5 class="text-primary fw-bold mb-2">
                                    <i class="fas fa-database me-2"></i> Configuração do Banco de Dados
                                </h5>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">DB Host</label>
                                <input type="text" value="{{ $tenant->db_host }}" class="form-control" readonly>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">DB Name</label>
                                <input type="text" value="{{ $tenant->db_name }}" class="form-control" readonly>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">DB User</label>
                                <input type="text" value="{{ $tenant->db_user }}" class="form-control" readonly>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">DB Password</label>
                                <input type="password" value="{{ $tenant->db_password_enc }}" class="form-control" readonly>
                            </div>

                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <a href="{{ route('Platform.tenants.edit', $tenant) }}" class="btn btn-primary me-2">
                                <i class="fas fa-pen"></i> Editar
                            </a>
                            <a href="{{ route('Platform.tenants.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Voltar
                            </a>
                        </div>

                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

@include("layouts.freedash.footer")
@endsection