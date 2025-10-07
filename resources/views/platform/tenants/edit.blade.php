@extends('layouts.freedash.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-10 col-md-12">

            @if ($errors->has('general'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ $errors->first('general') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">
                        <i class="fas fa-edit text-primary me-2"></i> Editar Tenant
                    </h4>
                    <a href="{{ route('Platform.tenants.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </a>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('Platform.tenants.update', $tenant) }}">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Razão Social *</label>
                                <input type="text" name="legal_name" value="{{ old('legal_name', $tenant->legal_name) }}" class="form-control" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Nome Fantasia</label>
                                <input type="text" name="trade_name" value="{{ old('trade_name', $tenant->trade_name) }}" class="form-control">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Documento</label>
                                <input type="text" name="document" value="{{ old('document', $tenant->document) }}" class="form-control">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Subdomínio *</label>
                                <input type="text" name="subdomain" value="{{ old('subdomain', $tenant->subdomain) }}" class="form-control" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" value="{{ old('email', $tenant->email) }}" class="form-control">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Telefone</label>
                                <input type="text" name="phone" value="{{ old('phone', $tenant->phone) }}" class="form-control">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Status *</label>
                                <select name="status" class="form-select">
                                    @foreach(['trial' => 'Trial', 'active' => 'Ativo', 'suspended' => 'Suspenso', 'cancelled' => 'Cancelado'] as $val => $label)
                                        <option value="{{ $val }}" @selected(old('status', $tenant->status) === $val)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Trial até</label>
                                <input type="date" name="trial_ends_at"
                                       value="{{ old('trial_ends_at', optional($tenant->trial_ends_at)->format('Y-m-d')) }}"
                                       class="form-control">
                            </div>

                            <div class="col-12 mt-4">
                                <h5 class="text-primary fw-bold mb-2">
                                    <i class="fas fa-database me-2"></i> Configuração do Banco de Dados
                                </h5>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">DB Host *</label>
                                <input type="text" name="db_host" value="{{ old('db_host', $tenant->db_host) }}" class="form-control" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">DB Name *</label>
                                <input type="text" name="db_name" value="{{ old('db_name', $tenant->db_name) }}" class="form-control" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">DB User *</label>
                                <input type="text" name="db_username" value="{{ old('db_username', $tenant->db_username) }}" class="form-control" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">DB Password *</label>
                                <input type="password" name="db_password" value="{{ old('db_password', $tenant->db_password) }}" class="form-control" required>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save me-2"></i> Atualizar Tenant
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>
@include("layouts.freedash.footer")
@endsection