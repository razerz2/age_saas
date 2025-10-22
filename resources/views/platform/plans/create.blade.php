@extends('layouts.freedash.app')
@section('content')
<div class="page-breadcrumb">
    <div class="row">
        <div class="col-7 align-self-center">
            <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">Novo Plano</h4>
            <div class="d-flex align-items-center">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb m-0 p-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route('Platform.dashboard') }}" class="text-muted">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item text-muted">
                            <a href="{{ route('Platform.plans.index') }}" class="text-muted">Planos</a>
                        </li>
                        <li class="breadcrumb-item text-muted active" aria-current="page">Novo</li>
                    </ol>
                </nav>
            </div>
        </div>
        <div class="col-5 align-self-center text-end">
            <a href="{{ route('Platform.plans.index') }}" class="btn btn-secondary shadow-sm">
                <i class="fa fa-arrow-left me-1"></i> Voltar
            </a>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <h4 class="card-title mb-4">Cadastrar Plano</h4>

            {{-- Exibe erros de validação --}}
            @if ($errors->any())
                <div class="alert alert-danger">
                    <strong>Ops!</strong> Verifique os campos abaixo:
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('Platform.plans.store') }}">
                @csrf

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Nome</label>
                        <input type="text" name="name" value="{{ old('name') }}" class="form-control" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Periodicidade</label>
                        <select name="periodicity" class="form-select" required>
                            <option value="monthly" @selected(old('periodicity') == 'monthly')>Mensal</option>
                            <option value="yearly" @selected(old('periodicity') == 'yearly')>Anual</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Duração (em meses)</label>
                        <select name="period_months" class="form-select" required>
                            @for ($i = 1; $i <= 12; $i++)
                                <option value="{{ $i }}" {{ old('period_months') == $i ? 'selected' : '' }}>
                                    {{ $i }} {{ $i == 1 ? 'mês' : 'meses' }}
                                </option>
                            @endfor
                        </select>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Preço (R$)</label>
                        <input type="number" step="0.01" name="price_cents" value="{{ old('price_cents') }}" class="form-control" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Plano Ativo</label><br>
                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" checked>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Recursos (um por linha)</label>
                    <textarea name="features_json" class="form-control" rows="4"
                        placeholder="Agendamentos ilimitados&#10;Relatórios personalizados">{{ old('features_json') }}</textarea>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-success px-4">
                        <i class="fa fa-save me-1"></i> Salvar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@include('layouts.freedash.footer')
@endsection

