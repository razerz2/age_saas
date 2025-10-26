@extends('layouts.freedash.app')
@section('content')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-7 align-self-center">
                <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">Nova Assinatura</h4>
                <div class="d-flex align-items-center">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb m-0 p-0">
                            <li class="breadcrumb-item">
                                <a href="{{ route('Platform.dashboard') }}" class="text-muted">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item text-muted">
                                <a href="{{ route('Platform.subscriptions.index') }}" class="text-muted">Assinaturas</a>
                            </li>
                            <li class="breadcrumb-item text-muted active" aria-current="page">Nova</li>
                        </ol>
                    </nav>
                </div>
            </div>
            <div class="col-5 align-self-center">
                <div class="customize-input float-end">
                    <a href="{{ route('Platform.subscriptions.index') }}" class="btn btn-secondary shadow-sm">
                        <i class="fa fa-arrow-left me-1"></i> Voltar
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h4 class="card-title mb-4">Cadastrar Assinatura</h4>

                {{-- 🔹 Exibição de erros de validação --}}
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

                @if ($errors->has('general'))
                    <div class="alert alert-danger">
                        {{ $errors->first('general') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('Platform.subscriptions.store') }}">
                    @csrf

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Tenant</label>
                            <select name="tenant_id" class="form-select" required>
                                <option value="">Selecione um tenant...</option>
                                @foreach ($tenants as $tenant)
                                    <option value="{{ $tenant->id }}" @selected(old('tenant_id') == $tenant->id)>
                                        {{ $tenant->trade_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Plano</label>
                            <select name="plan_id" class="form-select" required>
                                <option value="">Selecione um plano...</option>
                                @foreach ($plans as $plan)
                                    <option value="{{ $plan->id }}" @selected(old('plan_id') == $plan->id)>
                                        {{ $plan->name }} ({{ $plan->formatted_price }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Início</label>
                            <input type="date" name="starts_at" id="starts_at"
                                value="{{ old('starts_at', now()->format('Y-m-d')) }}" class="form-control" required>
                        </div>>

                        <div class="col-md-4">
                            <label class="form-label">Fim (calculado automaticamente)</label>
                            <input type="date" name="ends_at" id="ends_at" class="form-control" readonly>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Dia de Vencimento</label>
                            <input type="number" name="due_day" value="{{ old('due_day', 1) }}" min="1"
                                max="28" class="form-control" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                <option value="active" @selected(old('status') == 'active')>Ativa</option>
                                <option value="trialing" @selected(old('status') == 'trialing')>Em teste</option>
                                <option value="past_due" @selected(old('status') == 'past_due')>Atrasada</option>
                                <option value="canceled" @selected(old('status') == 'canceled')>Cancelada</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- 🔹 Novo campo: Método de Pagamento --}}
                        <div class="col-md-4">
                            <label class="form-label">Método de Pagamento</label>
                            <select name="payment_method" class="form-select @error('payment_method') is-invalid @enderror"
                                required>
                                <option value="">Selecione...</option>
                                <option value="PIX" {{ old('payment_method') == 'PIX' ? 'selected' : '' }}>PIX</option>
                                <option value="BOLETO" {{ old('payment_method') == 'BOLETO' ? 'selected' : '' }}>Boleto
                                    Bancário</option>
                                <option value="CREDIT_CARD" {{ old('payment_method') == 'CREDIT_CARD' ? 'selected' : '' }}>
                                    Cartão de Crédito</option>
                                <option value="DEBIT_CARD" {{ old('payment_method') == 'DEBIT_CARD' ? 'selected' : '' }}>
                                    Cartão de Débito</option>
                            </select>
                            @error('payment_method')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Renovação Automática</label><br>
                            <div class="form-check form-switch mt-2">
                                <input type="checkbox" class="form-check-input" name="auto_renew" value="1"
                                    @checked(old('auto_renew', true))>
                            </div>
                        </div>
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
@push('scripts')
    @php
        $plansData = $plans->map(function ($p) {
            return [
                'id' => $p->id,
                'name' => $p->name,
                'months' => $p->period_months,
            ];
        });
    @endphp

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Lista dos planos e duração em meses (vinda do backend)
            const plans = @json($plansData);

            const planSelect = document.querySelector('[name="plan_id"]');
            const startInput = document.querySelector('#starts_at');
            const endInput = document.querySelector('#ends_at');

            function calcularFim() {
                const planId = planSelect.value;
                const startDate = startInput.value;

                if (!planId || !startDate) {
                    endInput.value = '';
                    return;
                }

                const plano = plans.find(p => p.id === planId);
                if (!plano) return;

                const dataInicio = new Date(startDate);
                dataInicio.setMonth(dataInicio.getMonth() + plano.months);

                const yyyy = dataInicio.getFullYear();
                const mm = String(dataInicio.getMonth() + 1).padStart(2, '0');
                const dd = String(dataInicio.getDate()).padStart(2, '0');

                endInput.value = `${yyyy}-${mm}-${dd}`;
            }

            planSelect.addEventListener('change', calcularFim);
            startInput.addEventListener('change', calcularFim);
        });
    </script>
@endpush
