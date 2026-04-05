@extends('layouts.freedash.app')
@section('title', 'Editar Plans')

@section('content')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-7 align-self-center">
                <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">Editar Plano</h4>
                <div class="d-flex align-items-center">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb m-0 p-0">
                            <li class="breadcrumb-item">
                                <a href="{{ route('Platform.dashboard') }}" class="text-muted">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item text-muted">
                                <a href="{{ route('Platform.plans.index') }}" class="text-muted">Planos</a>
                            </li>
                            <li class="breadcrumb-item text-muted active" aria-current="page">Editar</li>
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
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                        <strong>Ops!</strong> Verifique os erros abaixo:
                        <ul class="mt-2 mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
                    </div>
                @endif

                <h4 class="card-title mb-4">Atualizar Plano</h4>

                <form method="POST" action="{{ route('Platform.plans.update', $plan->id) }}">
                    @csrf
                    @method('PUT')

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Nome</label>
                            <input type="text" name="name" value="{{ old('name', $plan->name) }}" class="form-control" required>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Periodicidade</label>
                            <select name="periodicity" class="form-select" required>
                                <option value="monthly" @selected(old('periodicity', $plan->periodicity) === 'monthly')>Mensal</option>
                                <option value="yearly" @selected(old('periodicity', $plan->periodicity) === 'yearly')>Anual</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Duração (em meses)</label>
                            <select name="period_months" class="form-select" required>
                                @for ($i = 1; $i <= 12; $i++)
                                    <option value="{{ $i }}" {{ old('period_months', $plan->period_months) == $i ? 'selected' : '' }}>
                                        {{ $i }} {{ $i == 1 ? 'mês' : 'meses' }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-12">
                            <label class="form-label">Resumo do Plano (para exibição na landing page)</label>
                            <textarea name="description" class="form-control" rows="2"
                                placeholder="Breve descrição do plano para exibir na landing">{{ old('description', $plan->description) }}</textarea>
                            <small class="text-muted">Este texto aparece antes da lista de recursos no card da landing.</small>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Categoria *</label>
                            <select name="category" class="form-select" required>
                                <option value="commercial" @selected(old('category', $plan->category) == 'commercial')>Comercial (B2C/B2B Leve)</option>
                                <option value="contractual" @selected(old('category', $plan->category) == 'contractual')>Contratual (Exclusivo para Redes)</option>
                                <option value="sandbox" @selected(old('category', $plan->category) == 'sandbox')>Sandbox (Testes Internos)</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Preço (R$)</label>
                            <input type="number" step="0.01" name="price_cents"
                                value="{{ old('price_cents', $plan->price_cents / 100) }}" class="form-control" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Plano Ativo</label><br>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" @checked(old('is_active', $plan->is_active))>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Tipo *</label>
                            <select name="plan_type" id="plan_type" class="form-select" required>
                                <option value="real" @selected(old('plan_type', $plan->plan_type ?? 'real') == 'real')>Produção</option>
                                <option value="test" @selected(old('plan_type', $plan->plan_type ?? 'real') == 'test')>Teste</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Visível na Landing Page</label><br>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="show_on_landing_page" value="1"
                                    @checked(old('show_on_landing_page', $plan->show_on_landing_page))>
                            </div>
                        </div>
                    </div>

                    <div id="trial-disabled-info" class="alert alert-warning d-none">
                        Planos de teste não podem oferecer trial comercial na landing.
                    </div>

                    <div class="row mb-3" id="trial-settings-wrapper">
                        <div class="col-md-6">
                            <label class="form-label">Habilitar Trial Comercial</label><br>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" id="trial_enabled" name="trial_enabled" value="1"
                                    @checked(old('trial_enabled', $plan->trial_enabled))>
                            </div>
                            <small class="text-muted">Disponível apenas para planos de produção.</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Dias de Trial</label>
                            <input type="number" min="1" max="365" id="trial_days" name="trial_days"
                                value="{{ old('trial_days', $plan->trial_days) }}" class="form-control">
                            <small class="text-muted">Exemplo: 7, 14 ou 30 dias.</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Recursos (um por linha)</label>
                        <textarea name="features_json" class="form-control" rows="4"
                            placeholder="Agendamentos ilimitados&#10;Relatórios personalizados">{{ old('features_json', implode("\n", $plan->features ?? [])) }}</textarea>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-success px-4">
                            <i class="fa fa-save me-1"></i> Atualizar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @include('layouts.freedash.footer')
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const planType = document.getElementById('plan_type');
            const trialEnabled = document.getElementById('trial_enabled');
            const trialDays = document.getElementById('trial_days');
            const trialInfo = document.getElementById('trial-disabled-info');
            const trialWrapper = document.getElementById('trial-settings-wrapper');

            function syncTrialFields() {
                const isTestPlan = planType.value === 'test';
                const isTrialEnabled = trialEnabled.checked;

                trialInfo.classList.toggle('d-none', !isTestPlan);
                trialEnabled.disabled = isTestPlan;
                trialDays.disabled = isTestPlan || !isTrialEnabled;
                trialDays.required = !isTestPlan && isTrialEnabled;
                trialWrapper.classList.toggle('opacity-50', isTestPlan);

                if (isTestPlan) {
                    trialEnabled.checked = false;
                    trialDays.value = '';
                }
            }

            planType.addEventListener('change', syncTrialFields);
            trialEnabled.addEventListener('change', syncTrialFields);
            syncTrialFields();
        });
    </script>
@endpush
