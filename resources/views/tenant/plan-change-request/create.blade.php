@extends('layouts.tailadmin.app')

@section('title', 'Solicitar mudança de plano')
@section('page', 'plan-change-request')

@section('content')
@php
    $currentPlanName = $subscription->plan->name ?? '—';
    $currentPlanPrice = $subscription->plan->formatted_price ?? '—';
    $currentPaymentMethodLabel = $subscription->payment_method_label ?? '—';
    $currentStatusLabel = method_exists($subscription, 'statusLabel') ? $subscription->statusLabel() : ucfirst((string) ($subscription->status ?? '—'));
    $hasPaymentOptions = !empty($paymentMethodOptions);
    $selectedMethod = old('requested_payment_method', $subscription->payment_method);
@endphp

<div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-6">
    <div class="page-header mb-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="min-w-0 flex-1">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Solicitar mudança de plano</h1>
                <nav class="flex mt-2" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 md:space-x-3">
                        <li class="inline-flex items-center">
                            <a href="{{ workspace_route('tenant.dashboard') }}"
                                class="text-gray-700 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white inline-flex items-center">
                                <x-icon name="home-outline" size="text-base" class="mr-2" />
                                Dashboard
                            </a>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <x-icon name="chevron-right" size="text-sm" class="text-gray-400" />
                                <a href="{{ workspace_route('tenant.subscription.show') }}"
                                    class="ml-1 text-gray-700 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
                                    Minha assinatura
                                </a>
                            </div>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <x-icon name="chevron-right" size="text-sm" class="text-gray-400" />
                                <span class="ml-1 text-gray-500 dark:text-gray-400">Solicitar mudança de plano</span>
                            </div>
                        </li>
                    </ol>
                </nav>
            </div>
            <div class="flex-shrink-0">
                <x-help-button module="subscription" />
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg dark:bg-green-900/20 dark:border-green-800">
            <div class="flex">
                <x-icon name="check-circle-outline" size="text-lg" class="text-green-600 dark:text-green-400" />
                <div class="ml-3">
                    <p class="text-sm text-green-800 dark:text-green-200">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg dark:bg-red-900/20 dark:border-red-800">
            <div class="flex">
                <x-icon name="alert-circle-outline" size="text-lg" class="text-red-600 dark:text-red-400" />
                <div class="ml-3">
                    <p class="text-sm text-red-800 dark:text-red-200">
                        @foreach ($errors->all() as $error)
                            {{ $error }}{{ !$loop->last ? ' | ' : '' }}
                        @endforeach
                    </p>
                </div>
            </div>
        </div>
    @endif

    @if (!empty($isTrialConversionContext))
        <div class="mb-6 p-4 bg-amber-50 border border-amber-200 rounded-lg dark:bg-amber-900/20 dark:border-amber-800">
            <div class="flex">
                <x-icon name="alert-triangle-outline" size="text-lg" class="text-amber-600 dark:text-amber-400" />
                <div class="ml-3">
                    <p class="text-sm text-amber-800 dark:text-amber-200">
                        <strong>Conversão de período de teste:</strong> escolha um plano pago para continuar com acesso completo.
                    </p>
                </div>
            </div>
        </div>
    @endif

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 mb-6">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Plano atual</h2>
        </div>
        <div class="p-6 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Plano</p>
                <p class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">{{ $currentPlanName }}</p>
            </div>
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Valor</p>
                <p class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">{{ $currentPlanPrice }}</p>
            </div>
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Forma de pagamento</p>
                <p class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">{{ $currentPaymentMethodLabel }}</p>
            </div>
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Status da assinatura</p>
                <p class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">{{ $currentStatusLabel }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Informações da solicitação</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Escolha o novo plano e a forma de pagamento desejada.</p>
        </div>

        <div class="p-6">
            <form method="POST" action="{{ workspace_route('tenant.plan-change-request.store') }}">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="requested_plan_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Novo plano <span class="text-red-500">*</span>
                        </label>
                        <select name="requested_plan_id" id="requested_plan_id"
                            class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:text-white"
                            required>
                            <option value="">Selecione um plano</option>
                            @foreach ($plans as $plan)
                                <option
                                    value="{{ $plan->id }}"
                                    data-price="{{ $plan->formatted_price }}"
                                    data-description="{{ $plan->description ?? '' }}"
                                    data-is-test="{{ $plan->isTest() ? '1' : '0' }}"
                                    @selected(old('requested_plan_id') == $plan->id)
                                >
                                    {{ $plan->name }} - {{ $plan->formatted_price }}/mês
                                </option>
                            @endforeach
                        </select>
                        <p id="plan-description" class="mt-2 text-sm text-gray-500 dark:text-gray-400"></p>
                    </div>

                    <div id="payment-method-section">
                        <label for="requested_payment_method" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Forma de pagamento <span class="text-red-500">*</span>
                        </label>
                        <select name="requested_payment_method" id="requested_payment_method"
                            class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:text-white"
                            {{ $hasPaymentOptions ? '' : 'disabled' }}
                            required>
                            <option value="">Selecione uma forma de pagamento</option>
                            @foreach ($paymentMethodOptions as $option)
                                <option value="{{ $option['value'] }}" @selected($selectedMethod === $option['value'])>
                                    {{ $option['label'] }}
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">A forma de pagamento atual está pré-selecionada.</p>
                    </div>

                    <div class="md:col-span-2">
                        <label for="reason" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Motivo da solicitação <span class="text-gray-400">(opcional)</span>
                        </label>
                        <textarea name="reason" id="reason" rows="4" maxlength="1000"
                            class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:text-white"
                            placeholder="Descreva o motivo da mudança de plano...">{{ old('reason') }}</textarea>
                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Máximo de 1000 caracteres.</p>
                    </div>
                </div>

                <div id="test-plan-warning" class="hidden mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg dark:bg-blue-900/20 dark:border-blue-800">
                    <p class="text-sm text-blue-800 dark:text-blue-200">
                        Este é um plano de teste. Nenhuma cobrança será gerada.
                    </p>
                </div>

                <div id="payment-unavailable-warning" class="{{ $hasPaymentOptions ? 'hidden' : '' }} mt-4 p-4 bg-amber-50 border border-amber-200 rounded-lg dark:bg-amber-900/20 dark:border-amber-800">
                    <p class="text-sm text-amber-800 dark:text-amber-200">
                        Nenhuma forma de pagamento disponível no momento. Entre em contato com o suporte.
                    </p>
                </div>

                <div class="mt-6 p-4 bg-amber-50 border border-amber-200 rounded-lg dark:bg-amber-900/20 dark:border-amber-800">
                    <p class="text-sm text-amber-800 dark:text-amber-200">
                        <strong>Atenção:</strong> A mudança de plano está sujeita à aprovação do administrador. Você será notificado sobre a decisão.
                    </p>
                </div>

                <div class="mt-6 flex flex-wrap items-center gap-3">
                    <x-tailadmin-button type="submit" variant="primary">
                        Enviar solicitação
                    </x-tailadmin-button>
                    <x-tailadmin-button variant="secondary" size="md" href="{{ workspace_route('tenant.subscription.show') }}"
                        class="bg-transparent border-gray-300 text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-white/5">
                        Cancelar
                    </x-tailadmin-button>
                </div>
            </form>
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
            const paymentUnavailableWarning = document.getElementById('payment-unavailable-warning');
            const hasPaymentOptions = {{ $hasPaymentOptions ? 'true' : 'false' }};

            function applyPlanSelectionState() {
                const selectedOption = planSelect.options[planSelect.selectedIndex];
                if (!selectedOption || !selectedOption.value) {
                    planDescription.textContent = '';
                    paymentSection.classList.remove('hidden');
                    paymentSelect.required = true;
                    paymentSelect.disabled = !hasPaymentOptions;
                    testPlanWarning.classList.add('hidden');
                    paymentUnavailableWarning.classList.toggle('hidden', hasPaymentOptions);
                    return;
                }

                const description = selectedOption.dataset.description || '';
                planDescription.textContent = description;

                const isTestPlan = selectedOption.dataset.isTest === '1';

                paymentSection.classList.toggle('hidden', isTestPlan);
                paymentSelect.required = !isTestPlan;
                paymentSelect.disabled = isTestPlan || !hasPaymentOptions;
                testPlanWarning.classList.toggle('hidden', !isTestPlan);

                if (!isTestPlan) {
                    paymentUnavailableWarning.classList.toggle('hidden', hasPaymentOptions);
                } else {
                    paymentUnavailableWarning.classList.add('hidden');
                }
            }

            planSelect.addEventListener('change', applyPlanSelectionState);
            applyPlanSelectionState();
        });
    </script>
@endpush