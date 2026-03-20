@extends('layouts.tailadmin.app')

@section('title', 'Solicitar Mudanca de Plano')
@section('page', 'plan-change-request')

@section('content')

    <div class="page-header">
        <h3 class="page-title">Solicitar Mudanca de Plano</h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ workspace_route('tenant.dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ workspace_route('tenant.subscription.show') }}">Assinatura</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Solicitar Mudanca</li>
            </ol>
        </nav>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="mdi mdi-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Erro!</strong> Verifique os campos abaixo.
            <ul class="mt-2 mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-4">Informacoes da Solicitacao</h4>

                    @if (!empty($isTrialConversionContext))
                        <div class="alert alert-warning mb-4">
                            <i class="mdi mdi-alert-outline me-2"></i>
                            <strong>Conversao de periodo de teste:</strong>
                            escolha um plano pago para continuar com acesso completo.
                        </div>
                    @endif

                    <div class="mb-4 p-3 bg-light rounded">
                        <label class="fw-bold text-muted mb-2">Plano Atual</label>
                        <p class="fs-5 mb-0">{{ $subscription->plan->name ?? '—' }}</p>
                        <p class="text-muted mb-0">{{ $subscription->plan->formatted_price ?? '—' }}/mes</p>
                        <p class="text-muted mb-0 mt-2">
                            <small>Forma de pagamento:
                                <strong>
                                    @if ($subscription->payment_method === 'PIX')
                                        PIX
                                    @elseif($subscription->payment_method === 'BOLETO')
                                        Boleto Bancario
                                    @elseif($subscription->payment_method === 'CREDIT_CARD')
                                        Cartao de Credito
                                    @elseif($subscription->payment_method === 'DEBIT_CARD')
                                        Cartao de Debito
                                    @else
                                        {{ $subscription->payment_method ?? '—' }}
                                    @endif
                                </strong>
                            </small>
                        </p>
                    </div>

                    <form method="POST" action="{{ workspace_route('tenant.plan-change-request.store') }}">
                        @csrf

                        <div class="mb-4">
                            <label for="requested_plan_id" class="form-label fw-bold">
                                Novo Plano <span class="text-danger">*</span>
                            </label>
                            <select name="requested_plan_id" id="requested_plan_id" class="form-select" required>
                                <option value="">Selecione um plano</option>
                                @foreach ($plans as $plan)
                                    <option
                                        value="{{ $plan->id }}"
                                        data-price="{{ $plan->formatted_price }}"
                                        data-description="{{ $plan->description ?? '' }}"
                                        data-is-test="{{ $plan->isTest() ? '1' : '0' }}"
                                        @selected(old('requested_plan_id') == $plan->id)
                                    >
                                        {{ $plan->name }} - {{ $plan->formatted_price }}/mes
                                    </option>
                                @endforeach
                            </select>
                            <div id="plan-description" class="mt-2 text-muted small"></div>
                        </div>

                        <div id="test-plan-warning" class="alert alert-info d-none mb-4">
                            <i class="mdi mdi-information-outline me-2"></i>
                            Este e um plano de teste. Nenhuma cobranca sera gerada.
                        </div>

                        <div class="mb-4" id="payment-method-section">
                            <label for="requested_payment_method" class="form-label fw-bold">
                                Forma de Pagamento <span class="text-danger">*</span>
                            </label>
                            <select name="requested_payment_method" id="requested_payment_method" class="form-select" required>
                                <option value="">Selecione uma forma de pagamento</option>
                                <option value="PIX" @selected(old('requested_payment_method', $subscription->payment_method) === 'PIX')>PIX</option>
                                <option value="BOLETO" @selected(old('requested_payment_method', $subscription->payment_method) === 'BOLETO')>Boleto Bancario</option>
                                <option value="CREDIT_CARD" @selected(old('requested_payment_method', $subscription->payment_method) === 'CREDIT_CARD')>Cartao de Credito</option>
                                <option value="DEBIT_CARD" @selected(old('requested_payment_method', $subscription->payment_method) === 'DEBIT_CARD')>Cartao de Debito</option>
                            </select>
                            <div class="form-text">A forma de pagamento atual esta pre-selecionada</div>
                        </div>

                        <div class="mb-4">
                            <label for="reason" class="form-label fw-bold">
                                Motivo da Solicitacao <span class="text-muted">(opcional)</span>
                            </label>
                            <textarea name="reason" id="reason" class="form-control" rows="4"
                                placeholder="Descreva o motivo da mudanca de plano..."
                                maxlength="1000">{{ old('reason') }}</textarea>
                            <div class="form-text">Maximo de 1000 caracteres</div>
                        </div>

                        <div class="alert alert-warning">
                            <i class="mdi mdi-alert-outline me-2"></i>
                            <strong>Atencao:</strong> A mudanca de plano esta sujeita a aprovacao do administrador.
                            Voce sera notificado sobre a decisao.
                        </div>

                        <div class="flex flex-wrap items-center gap-3">
                            <x-tailadmin-button type="submit" variant="primary">
                                <i class="mdi mdi-send"></i> Enviar Solicitacao
                            </x-tailadmin-button>
                            <x-tailadmin-button variant="secondary" size="md" href="{{ workspace_route('tenant.subscription.show') }}"
                                class="bg-transparent border-gray-300 text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-white/5">
                                <i class="mdi mdi-close"></i> Cancelar
                            </x-tailadmin-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const planSelect = document.getElementById('requested_plan_id');
            const planDescription = document.getElementById('plan-description');
            const paymentSection = document.getElementById('payment-method-section');
            const paymentSelect = document.getElementById('requested_payment_method');
            const testPlanWarning = document.getElementById('test-plan-warning');

            function applyPlanSelectionState() {
                const selectedOption = planSelect.options[planSelect.selectedIndex];
                if (!selectedOption || !selectedOption.value) {
                    planDescription.textContent = '';
                    paymentSection.classList.remove('d-none');
                    paymentSelect.required = true;
                    paymentSelect.disabled = false;
                    testPlanWarning.classList.add('d-none');
                    return;
                }

                const description = selectedOption.dataset.description || '';
                planDescription.textContent = description;

                const isTestPlan = selectedOption.dataset.isTest === '1';

                paymentSection.classList.toggle('d-none', isTestPlan);
                paymentSelect.required = !isTestPlan;
                paymentSelect.disabled = isTestPlan;
                testPlanWarning.classList.toggle('d-none', !isTestPlan);
            }

            planSelect.addEventListener('change', applyPlanSelectionState);
            applyPlanSelectionState();
        });
    </script>
@endpush

