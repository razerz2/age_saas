@extends('layouts.tailadmin.app')

@section('title', 'Solicitar Mudança de Plano')

@section('content')

    <div class="page-header">
        <h3 class="page-title">Solicitar Mudança de Plano</h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ workspace_route('tenant.dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ workspace_route('tenant.subscription.show') }}">Assinatura</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Solicitar Mudança</li>
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
                    <h4 class="card-title mb-4">Informações da Solicitação</h4>

                    {{-- Plano Atual --}}
                    <div class="mb-4 p-3 bg-light rounded">
                        <label class="fw-bold text-muted mb-2">Plano Atual</label>
                        <p class="fs-5 mb-0">{{ $subscription->plan->name ?? '—' }}</p>
                        <p class="text-muted mb-0">{{ $subscription->plan->formatted_price ?? '—' }}/mês</p>
                        <p class="text-muted mb-0 mt-2">
                            <small>Forma de pagamento: 
                                <strong>
                                    @if($subscription->payment_method === 'PIX')
                                        PIX
                                    @elseif($subscription->payment_method === 'BOLETO')
                                        Boleto Bancário
                                    @elseif($subscription->payment_method === 'CREDIT_CARD')
                                        Cartão de Crédito
                                    @elseif($subscription->payment_method === 'DEBIT_CARD')
                                        Cartão de Débito
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
                                @foreach($plans as $plan)
                                    <option value="{{ $plan->id }}" 
                                            data-price="{{ $plan->formatted_price }}"
                                            data-description="{{ $plan->description ?? '' }}">
                                        {{ $plan->name }} - {{ $plan->formatted_price }}/mês
                                    </option>
                                @endforeach
                            </select>
                            <div id="plan-description" class="mt-2 text-muted small"></div>
                        </div>

                        <div class="mb-4">
                            <label for="requested_payment_method" class="form-label fw-bold">
                                Forma de Pagamento <span class="text-danger">*</span>
                            </label>
                            <select name="requested_payment_method" id="requested_payment_method" class="form-select" required>
                                <option value="">Selecione uma forma de pagamento</option>
                                <option value="PIX" @selected(old('requested_payment_method', $subscription->payment_method) === 'PIX')>
                                    PIX
                                </option>
                                <option value="BOLETO" @selected(old('requested_payment_method', $subscription->payment_method) === 'BOLETO')>
                                    Boleto Bancário
                                </option>
                                <option value="CREDIT_CARD" @selected(old('requested_payment_method', $subscription->payment_method) === 'CREDIT_CARD')>
                                    Cartão de Crédito
                                </option>
                                <option value="DEBIT_CARD" @selected(old('requested_payment_method', $subscription->payment_method) === 'DEBIT_CARD')>
                                    Cartão de Débito
                                </option>
                            </select>
                            <div class="form-text">A forma de pagamento atual está pré-selecionada</div>
                        </div>

                        <div class="mb-4">
                            <label for="reason" class="form-label fw-bold">
                                Motivo da Solicitação <span class="text-muted">(opcional)</span>
                            </label>
                            <textarea name="reason" id="reason" class="form-control" rows="4" 
                                      placeholder="Descreva o motivo da mudança de plano..." 
                                      maxlength="1000">{{ old('reason') }}</textarea>
                            <div class="form-text">Máximo de 1000 caracteres</div>
                        </div>

                        <div class="alert alert-warning">
                            <i class="mdi mdi-alert-outline me-2"></i>
                            <strong>Atenção:</strong> A mudança de plano está sujeita à aprovação do administrador. 
                            Você será notificado sobre a decisão.
                        </div>

                        <div class="flex flex-wrap items-center gap-3">
                            <x-tailadmin-button type="submit" variant="primary">
                                <i class="mdi mdi-send"></i> Enviar Solicitação
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

    @push('scripts')
    <script>
        document.getElementById('requested_plan_id').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const description = selectedOption.getAttribute('data-description');
            const descriptionDiv = document.getElementById('plan-description');
            
            if (description) {
                descriptionDiv.textContent = description;
            } else {
                descriptionDiv.textContent = '';
            }
        });
    </script>
    @endpush

@endsection

