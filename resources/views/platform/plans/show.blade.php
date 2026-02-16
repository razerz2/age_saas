@extends('layouts.freedash.app')
@section('title', 'Visualizar Plans')

@section('content')
<div class="page-breadcrumb">
    <div class="row">
        <div class="col-7 align-self-center">
            <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">Detalhes do Plano</h4>
            <div class="d-flex align-items-center">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb m-0 p-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route('Platform.dashboard') }}" class="text-muted">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item text-muted">
                            <a href="{{ route('Platform.plans.index') }}" class="text-muted">Planos</a>
                        </li>
                        <li class="breadcrumb-item text-muted active" aria-current="page">Detalhes</li>
                    </ol>
                </nav>
            </div>
        </div>
        <div class="col-5 align-self-center text-end">
            <a href="{{ route('Platform.plans.index') }}" class="btn btn-secondary shadow-sm me-2">
                <i class="fa fa-arrow-left me-1"></i> Voltar
            </a>
            <a href="{{ route('Platform.plans.edit', $plan->id) }}" class="btn btn-warning shadow-sm">
                <i class="fa fa-edit me-1"></i> Editar
            </a>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <h4 class="card-title mb-4">Informações do Plano</h4>

            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="fw-bold">Nome</label>
                    <p>{{ $plan->name }}</p>
                </div>

                <div class="col-md-4">
                    <label class="fw-bold">Periodicidade</label>
                    <p>
                        {{ $plan->periodicity === 'monthly' ? 'Mensal' : 'Anual' }}
                    </p>
                </div>

                <div class="col-md-4">
                    <label class="fw-bold">Duração</label>
                    <p>{{ $plan->period_months }} {{ $plan->period_months == 1 ? 'mês' : 'meses' }}</p>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="fw-bold">Preço</label>
                    <p>{{ $plan->formatted_price }}</p>
                </div>

                <div class="col-md-4">
                    <label class="fw-bold">Status</label>
                    <p>
                        @if($plan->is_active)
                            <span class="badge bg-success">Ativo</span>
                        @else
                            <span class="badge bg-danger">Inativo</span>
                        @endif
                    </p>
                </div>

                <div class="col-md-4">
                    <label class="fw-bold">Criado em</label>
                    <p>{{ $plan->created_at->format('d/m/Y H:i') }}</p>
                </div>
            </div>

            <div class="mb-3">
                <label class="fw-bold">Recursos</label>
                @if(!empty($plan->features))
                    <ul class="list-group">
                        @foreach ($plan->features as $feature)
                            <li class="list-group-item">
                                <i class="fa fa-check text-success me-2"></i>{{ $feature }}
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-muted fst-italic">Nenhum recurso cadastrado para este plano.</p>
                @endif
            </div>

        </div>
    </div>
</div>
@include('layouts.freedash.footer')
@endsection

