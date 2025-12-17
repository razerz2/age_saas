@extends('layouts.connect_plus.app')

@section('title', 'Nova Conta Financeira')

@section('content')

    <div class="page-header">
        <h3 class="page-title">
            <i class="mdi mdi-wallet-plus text-primary me-2"></i>
            Nova Conta Financeira
        </h3>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ workspace_route('tenant.dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ workspace_route('tenant.finance.index') }}">Financeiro</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ workspace_route('tenant.finance.accounts.index') }}">Contas</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Nova</li>
            </ol>
        </nav>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <form action="{{ workspace_route('tenant.finance.accounts.store', ['slug' => tenant()->subdomain]) }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="name" class="form-label">Nome da Conta <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="type" class="form-label">Tipo <span class="text-danger">*</span></label>
                            <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required>
                                <option value="">Selecione...</option>
                                <option value="cash" {{ old('type') === 'cash' ? 'selected' : '' }}>Dinheiro</option>
                                <option value="bank" {{ old('type') === 'bank' ? 'selected' : '' }}>Banco</option>
                                <option value="pix" {{ old('type') === 'pix' ? 'selected' : '' }}>PIX</option>
                                <option value="credit" {{ old('type') === 'credit' ? 'selected' : '' }}>Crédito</option>
                            </select>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="initial_balance" class="form-label">Saldo Inicial <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0" 
                                   class="form-control @error('initial_balance') is-invalid @enderror" 
                                   id="initial_balance" name="initial_balance" value="{{ old('initial_balance', '0.00') }}" required>
                            @error('initial_balance')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="active" name="active" value="1" 
                                       {{ old('active', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="active">
                                    Conta ativa
                                </label>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="mdi mdi-content-save"></i> Salvar
                            </button>
                            <a href="{{ workspace_route('tenant.finance.accounts.index', ['slug' => tenant()->subdomain]) }}" 
                               class="btn btn-secondary">
                                <i class="mdi mdi-close"></i> Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection

