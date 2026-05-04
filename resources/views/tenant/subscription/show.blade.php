@extends('layouts.tailadmin.app')

@section('title', 'Minha assinatura')
@section('page', 'subscription')

@section('content')
    @php
        $invoiceStatusMap = [
            'paid' => 'Paga',
            'pending' => 'Pendente',
            'overdue' => 'Vencida',
            'canceled' => 'Cancelada',
        ];

        $formatDate = function ($date) {
            if (! $date) {
                return '—';
            }

            if ($date instanceof \Carbon\CarbonInterface) {
                return $date->format('d/m/Y');
            }

            try {
                return \Illuminate\Support\Carbon::parse($date)->format('d/m/Y');
            } catch (\Throwable $e) {
                return '—';
            }
        };

        $hasExpiredTrial = $currentTenant instanceof \App\Models\Platform\Tenant
            ? (! $currentTenant->isEligibleForAccess() && (bool) $currentTenant->expiredTrialSubscription())
            : false;

        $statusVariantClasses = [
            'success' => 'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-400',
            'warning' => 'bg-warning-50 text-warning-700 dark:bg-warning-500/15 dark:text-warning-400',
            'danger' => 'bg-error-50 text-error-700 dark:bg-error-500/15 dark:text-error-400',
            'info' => 'bg-blue-50 text-blue-700 dark:bg-blue-500/15 dark:text-blue-300',
            'secondary' => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
        ];

        $commercialVariant = $commercialStatus['variant'] ?? 'secondary';
        $commercialBadgeClass = $statusVariantClasses[$commercialVariant] ?? $statusVariantClasses['secondary'];

        $nextDueLabel = ($nextDueInvoice && $nextDueInvoice->status === 'overdue')
            ? 'Vencimento em atraso'
            : 'Próximo vencimento';

        if (! $nextDueInvoice && $subscription?->ends_at) {
            $nextDueLabel = 'Data de término';
        }

        $cancellationRequested = (bool) ($subscription?->cancel_at_period_end === true
            || $subscription?->cancellation_status === 'pending_period_end');
        $cycleEndDate = $subscription?->ends_at;
        $cycleEndDateFormatted = $cycleEndDate ? $formatDate($cycleEndDate) : null;
        $cycleEndLabel = $cycleEndDateFormatted ?: 'o fim do ciclo atual';
    @endphp

    <div class="page-header mb-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="min-w-0 flex-1">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Minha assinatura</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Acompanhe seu plano, faturas e pagamentos.</p>
                <nav class="mt-2 flex" aria-label="Breadcrumb">
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
                                <span class="ml-1 text-gray-500 dark:text-gray-400">Minha assinatura</span>
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
        <div class="mb-4 rounded-xl border border-success-200 bg-success-50 px-4 py-3 text-sm text-success-700 dark:border-success-700/40 dark:bg-success-500/10 dark:text-success-400">
            {{ session('success') }}
        </div>
    @endif

    @if (session('info'))
        <div class="mb-4 rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-700 dark:border-blue-700/40 dark:bg-blue-500/10 dark:text-blue-300">
            {{ session('info') }}
        </div>
    @endif

    @if (session('warning'))
        <div class="mb-4 rounded-xl border border-warning-200 bg-warning-50 px-4 py-3 text-sm text-warning-700 dark:border-warning-700/40 dark:bg-warning-500/10 dark:text-warning-300">
            {{ session('warning') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-4 rounded-xl border border-error-200 bg-error-50 px-4 py-3 text-sm text-error-700 dark:border-error-700/40 dark:bg-error-500/10 dark:text-error-400">
            {{ session('error') }}
        </div>
    @endif

    @if ($hasExpiredTrial)
        <div class="mb-6 rounded-2xl border border-error-300 bg-error-50 p-4 dark:border-error-700/50 dark:bg-error-500/10">
            <p class="text-sm font-semibold text-error-700 dark:text-error-400">Seu período de teste expirou.</p>
            <p class="mt-1 text-sm text-error-600 dark:text-error-300">Seu acesso foi pausado. Escolha um plano para continuar com acesso completo.</p>
            <div class="mt-3 flex flex-wrap gap-2">
                <x-tailadmin-button variant="primary" size="sm" href="{{ workspace_route('tenant.plan-change-request.create') }}">
                    Escolher plano
                </x-tailadmin-button>
                <x-tailadmin-button variant="secondary" size="sm" href="{{ workspace_route('tenant.plan-change-request.create') }}">
                    Regularizar agora
                </x-tailadmin-button>
            </div>
        </div>
    @endif

    @if (! $subscription)
        <div class="rounded-2xl border border-gray-200 bg-white p-8 text-center dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                <i class="mdi mdi-information-outline text-2xl"></i>
            </div>
            <h4 class="text-lg font-semibold text-gray-900 dark:text-white">Nenhuma assinatura encontrada</h4>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Entre em contato com o suporte para mais informações sobre sua assinatura.</p>
        </div>
    @else
        <div class="space-y-6">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
                <div class="mb-5 flex items-start justify-between gap-4">
                    <div>
                        <h4 class="text-lg font-semibold text-gray-900 dark:text-white">Resumo do plano</h4>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Dados atuais da assinatura ativa.</p>
                    </div>
                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-medium {{ $commercialBadgeClass }}">
                        {{ $commercialStatus['label'] ?? '—' }}
                    </span>
                </div>

                @if(! empty($commercialStatus['message']))
                    <p class="mb-4 text-sm text-gray-600 dark:text-gray-300">{{ $commercialStatus['message'] }}</p>
                @endif

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    <div class="rounded-xl border border-gray-200 p-4 dark:border-gray-800">
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Plano</p>
                        <p class="mt-2 text-base font-semibold text-gray-900 dark:text-white">{{ $subscription->plan->name ?? '—' }}</p>
                    </div>
                    <div class="rounded-xl border border-gray-200 p-4 dark:border-gray-800">
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Valor do plano</p>
                        <p class="mt-2 text-base font-semibold text-primary">{{ $subscription->plan->formatted_price ?? '—' }}</p>
                    </div>
                    <div class="rounded-xl border border-gray-200 p-4 dark:border-gray-800">
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Método de pagamento</p>
                        <p class="mt-2 text-base font-semibold text-gray-900 dark:text-white">{{ $subscription->payment_method_label ?? '—' }}</p>
                    </div>
                    <div class="rounded-xl border border-gray-200 p-4 dark:border-gray-800">
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ $nextDueLabel }}</p>
                        <p class="mt-2 text-base font-semibold text-gray-900 dark:text-white">{{ $formatDate($nextDueDate) }}</p>
                    </div>
                    <div class="rounded-xl border border-gray-200 p-4 dark:border-gray-800">
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Renovação automática</p>
                        <p class="mt-2 text-base font-semibold {{ $subscription->auto_renew ? 'text-success-700 dark:text-success-400' : 'text-gray-700 dark:text-gray-300' }}">
                            {{ $subscription->auto_renew ? 'Ativada' : 'Desativada' }}
                        </p>
                    </div>
                    <div class="rounded-xl border border-gray-200 p-4 dark:border-gray-800">
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">ÚLTIMA fatura paga</p>
                        <p class="mt-2 text-base font-semibold text-gray-900 dark:text-white">
                            @if($latestPaidInvoice)
                                {{ $formatDate($latestPaidInvoice->paid_at ?? $latestPaidInvoice->due_date) }}
                            @else
                                —
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            @if($primaryOpenInvoice)
                <div class="rounded-2xl border p-5 md:p-6
                    {{ $primaryOpenInvoice->status === 'overdue'
                        ? 'border-error-300 bg-error-50 dark:border-error-700/50 dark:bg-error-500/10'
                        : 'border-warning-300 bg-warning-50 dark:border-warning-700/50 dark:bg-warning-500/10' }}">
                    <h4 class="text-base font-semibold
                        {{ $primaryOpenInvoice->status === 'overdue'
                            ? 'text-error-700 dark:text-error-400'
                            : 'text-warning-700 dark:text-warning-400' }}">
                        Alerta financeiro
                    </h4>
                    <p class="mt-2 text-sm
                        {{ $primaryOpenInvoice->status === 'overdue'
                            ? 'text-error-700 dark:text-error-300'
                            : 'text-warning-700 dark:text-warning-300' }}">
                        @if($primaryOpenInvoice->status === 'overdue')
                            Existe uma fatura vencida. Regularize o pagamento para evitar bloqueio do acesso.
                        @else
                            Existe uma fatura pendente aguardando pagamento.
                        @endif
                    </p>
                    <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-3">
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Valor</p>
                            <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ $primaryOpenInvoice->formatted_amount ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Vencimento</p>
                            <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ $formatDate($primaryOpenInvoice->due_date) }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Método</p>
                            <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ $primaryOpenInvoice->payment_method ?? '—' }}</p>
                        </div>
                    </div>

                    <div class="mt-4">
                        @php
                            $hasPrimaryValidLink = is_string($primaryOpenInvoice->payment_link ?? null)
                                && filter_var($primaryOpenInvoice->payment_link, FILTER_VALIDATE_URL);
                        @endphp
                        @if($canPayNow || $hasPrimaryValidLink)
                            <div class="flex flex-wrap items-center gap-2">
                                @if($canPayNow)
                                    <x-tailadmin-button
                                        variant="primary"
                                        size="md"
                                        href="{{ $primaryOpenInvoice->payment_link }}"
                                        target="_blank"
                                        rel="noopener noreferrer">
                                        {{ $paymentActionLabel }}
                                    </x-tailadmin-button>
                                @endif
                                <x-tailadmin-button
                                    type="button"
                                    variant="secondary"
                                    size="md"
                                    class="js-copy-payment-link"
                                    data-payment-link="{{ $primaryOpenInvoice->payment_link }}">
                                    Copiar link
                                </x-tailadmin-button>
                                <form method="POST" action="{{ workspace_route('tenant.subscription.invoices.refresh-status', ['invoice' => $primaryOpenInvoice->id]) }}">
                                    @csrf
                                    <x-tailadmin-button type="submit" variant="secondary" size="md">
                                        Atualizar status
                                    </x-tailadmin-button>
                                </form>
                                <span class="js-copy-feedback hidden text-xs text-success-700 dark:text-success-400">Link copiado</span>
                            </div>
                        @elseif($paymentUnavailableMessage)
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-300">{{ $paymentUnavailableMessage }}</p>
                        @endif
                    </div>
                </div>
            @endif

            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
                <h4 class="text-lg font-semibold text-gray-900 dark:text-white">Opções do plano</h4>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Gerencie alterações importantes da sua assinatura.</p>

                @if($pendingPlanChangeRequest)
                    <div class="mt-4 rounded-xl border border-blue-200 bg-blue-50 p-4 dark:border-blue-700/40 dark:bg-blue-500/10">
                        <p class="text-sm font-medium text-blue-700 dark:text-blue-300">
                            Solicitação em andamento para o plano <strong>{{ $pendingPlanChangeRequest->requestedPlan->name ?? '—' }}</strong>.
                        </p>
                        <p class="mt-1 text-sm text-blue-700 dark:text-blue-300">Status: Aguardando aprovação.</p>
                    </div>
                @endif

                @if($cancellationRequested)
                    <div class="mt-4 rounded-xl border border-warning-200 bg-warning-50 p-4 dark:border-warning-700/40 dark:bg-warning-500/10">
                        <p class="text-sm font-medium text-warning-700 dark:text-warning-300">
                            Cancelamento solicitado. Sua assinatura permanece ativa até {{ $cycleEndLabel }}. Após essa data, ela não será renovada.
                        </p>
                    </div>
                @endif

                <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="rounded-xl border border-gray-200 p-4 dark:border-gray-800">
                        <h5 class="text-base font-semibold text-gray-900 dark:text-white">Mudança de plano</h5>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Ajuste recursos, limites ou altere para outro plano disponível.</p>
                        @if($cancellationRequested)
                            <p class="mt-3 text-xs font-medium text-warning-700 dark:text-warning-300">Há um cancelamento programado para esta assinatura.</p>
                            <div class="mt-4">
                                <x-tailadmin-button variant="secondary" size="md" disabled>
                                    Solicitar mudança de plano
                                </x-tailadmin-button>
                            </div>
                        @else
                            <div class="mt-4">
                                <x-tailadmin-button variant="primary" size="md" href="{{ workspace_route('tenant.plan-change-request.create') }}">
                                    Solicitar mudança de plano
                                </x-tailadmin-button>
                            </div>
                        @endif
                    </div>

                    <div class="rounded-xl border border-gray-200 p-4 dark:border-gray-800">
                        <h5 class="text-base font-semibold text-gray-900 dark:text-white">Cancelamento da assinatura</h5>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Solicite o encerramento da sua assinatura para o fim do ciclo atual.</p>
                        <div class="mt-4">
                            @if($cancellationRequested)
                                <x-tailadmin-button variant="secondary" size="md" disabled>
                                    Cancelamento solicitado
                                </x-tailadmin-button>
                            @else
                                <x-tailadmin-button
                                    type="button"
                                    variant="secondary"
                                    size="md"
                                    class="js-open-cancel-modal border-error-300 text-error-700 hover:bg-error-50 dark:border-error-700/50 dark:text-error-300 dark:hover:bg-error-500/10">
                                    Solicitar cancelamento
                                </x-tailadmin-button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            @if(! empty($planFeatures) && count($planFeatures) > 0)
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
                    <h4 class="text-lg font-semibold text-gray-900 dark:text-white">Funcionalidades do plano</h4>
                    <div class="mt-4 grid grid-cols-1 gap-2 sm:grid-cols-2">
                        @foreach($planFeatures as $feature)
                            <div class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                                <i class="mdi mdi-check-circle text-success-600 dark:text-success-400"></i>
                                <span>{{ $feature }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <h4 class="text-lg font-semibold text-gray-900 dark:text-white">Histórico de faturas</h4>
                </div>

                @if($invoices && $invoices->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                            <thead>
                                <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                    <th class="px-3 py-3">Vencimento</th>
                                    <th class="px-3 py-3">Valor</th>
                                    <th class="px-3 py-3">Método</th>
                                    <th class="px-3 py-3">Status</th>
                                    <th class="px-3 py-3">Pago em</th>
                                    <th class="px-3 py-3 text-right">Ações</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                @foreach($invoices as $invoice)
                                    @php
                                        $invoiceStatus = $invoiceStatusMap[$invoice->status ?? ''] ?? ucfirst((string) ($invoice->status ?? '—'));
                                        $paidAt = $invoice->paid_at ?? $invoice->payment_date ?? null;
                                        $canPayThisInvoice = in_array($invoice->status, ['pending', 'overdue'], true)
                                            && is_string($invoice->payment_link ?? null)
                                            && filter_var($invoice->payment_link, FILTER_VALIDATE_URL);
                                    @endphp
                                    <tr class="text-sm text-gray-700 dark:text-gray-300">
                                        <td class="px-3 py-3">{{ $formatDate($invoice->due_date) }}</td>
                                        <td class="px-3 py-3 font-semibold text-gray-900 dark:text-white">{{ $invoice->formatted_amount ?? '—' }}</td>
                                        <td class="px-3 py-3">{{ $invoice->payment_method ?? '—' }}</td>
                                        <td class="px-3 py-3">
                                            <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium
                                                @if($invoice->status === 'paid') bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-400
                                                @elseif($invoice->status === 'overdue') bg-error-50 text-error-700 dark:bg-error-500/15 dark:text-error-400
                                                @elseif($invoice->status === 'pending') bg-warning-50 text-warning-700 dark:bg-warning-500/15 dark:text-warning-400
                                                @elseif($invoice->status === 'canceled') bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300
                                                @else bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 @endif">
                                                {{ $invoiceStatus }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-3">{{ $formatDate($paidAt) }}</td>
                                        <td class="px-3 py-3 text-right">
                                            @if($canPayThisInvoice)
                                                <div class="flex items-center justify-end gap-2">
                                                    <x-tailadmin-button
                                                        variant="primary"
                                                        size="sm"
                                                        href="{{ $invoice->payment_link }}"
                                                        target="_blank"
                                                        rel="noopener noreferrer">
                                                        Pagar
                                                    </x-tailadmin-button>
                                                    <x-tailadmin-button
                                                        type="button"
                                                        variant="secondary"
                                                        size="sm"
                                                        class="js-copy-payment-link"
                                                        data-payment-link="{{ $invoice->payment_link }}">
                                                        Copiar link
                                                    </x-tailadmin-button>
                                                </div>
                                            @else
                                                <span class="text-xs text-gray-400 dark:text-gray-500">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="rounded-xl border border-dashed border-gray-300 p-8 text-center dark:border-gray-700">
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Nenhuma fatura encontrada.</p>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Quando houver cobranças, elas aparecerão aqui.</p>
                    </div>
                @endif
            </div>
        </div>
    @endif

    @if(! $cancellationRequested)
        <div id="cancel-subscription-modal" class="fixed inset-0 z-99999 hidden overflow-y-auto p-4">
            <div class="js-cancel-modal-close absolute inset-0 bg-gray-900/60"></div>
            <div class="relative mx-auto flex min-h-full w-full max-w-2xl items-center justify-center">
                <div class="w-full rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xl dark:border-gray-800 dark:bg-gray-900">
                    <div class="flex items-start justify-between gap-4">
                        <h4 class="text-lg font-semibold text-gray-900 dark:text-white">Solicitar cancelamento da assinatura</h4>
                        <button type="button" class="js-cancel-modal-close text-gray-400 hover:text-gray-600 dark:hover:text-gray-300" aria-label="Fechar modal">
                            <x-icon name="x-close" size="text-xl" />
                        </button>
                    </div>

                    <div class="mt-4 space-y-3 text-sm text-gray-600 dark:text-gray-300">
                        @if($cycleEndDateFormatted)
                            <p>Ao confirmar, sua assinatura continuará ativa até {{ $cycleEndDateFormatted }}. Após essa data, ela não será renovada e o acesso ao sistema será encerrado.</p>
                        @else
                            <p>Ao confirmar, sua assinatura continuará ativa até o fim do ciclo atual. Após esse período, ela não será renovada e o acesso ao sistema será encerrado.</p>
                        @endif
                        <p>Essa solicitação não cancela imediatamente o acesso e não gera reembolso automático.</p>
                        <p class="font-medium text-gray-800 dark:text-gray-100">Deseja continuar?</p>
                    </div>

                    <form method="POST" action="{{ workspace_route('tenant.subscription.cancel.request') }}" class="mt-5">
                        @csrf
                        <div>
                            <label for="cancel_reason" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Motivo do cancelamento</label>
                            <textarea
                                id="cancel_reason"
                                name="reason"
                                maxlength="1000"
                                rows="4"
                                placeholder="Conte-nos o motivo do cancelamento..."
                                class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-300 focus:outline-none focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">{{ old('reason') }}</textarea>
                        </div>

                        <div class="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                            <x-tailadmin-button type="button" variant="secondary" size="md" class="js-cancel-modal-close">
                                Voltar
                            </x-tailadmin-button>
                            <x-tailadmin-button type="submit" variant="primary" size="md">
                                Confirmar solicitação
                            </x-tailadmin-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const copyButtons = document.querySelectorAll('.js-copy-payment-link');
            const cancelModal = document.getElementById('cancel-subscription-modal');
            const openCancelModalButtons = document.querySelectorAll('.js-open-cancel-modal');
            const closeCancelModalButtons = document.querySelectorAll('.js-cancel-modal-close');

            function fallbackCopyText(text) {
                const textarea = document.createElement('textarea');
                textarea.value = text;
                textarea.setAttribute('readonly', '');
                textarea.style.position = 'absolute';
                textarea.style.left = '-9999px';
                document.body.appendChild(textarea);
                textarea.select();
                const ok = document.execCommand('copy');
                document.body.removeChild(textarea);
                return ok;
            }

            function showFeedback(button) {
                const inlineFeedback = button.parentElement && button.parentElement.querySelector('.js-copy-feedback');
                if (inlineFeedback) {
                    inlineFeedback.classList.remove('hidden');
                    setTimeout(() => inlineFeedback.classList.add('hidden'), 1800);
                    return;
                }

                const originalText = button.textContent;
                button.textContent = 'Link copiado';
                setTimeout(() => {
                    button.textContent = originalText;
                }, 1800);
            }

            copyButtons.forEach(function (button) {
                button.addEventListener('click', async function () {
                    const link = button.getAttribute('data-payment-link');
                    if (!link) {
                        return;
                    }

                    try {
                        if (navigator.clipboard && window.isSecureContext) {
                            await navigator.clipboard.writeText(link);
                        } else if (!fallbackCopyText(link)) {
                            return;
                        }

                        showFeedback(button);
                    } catch (e) {
                        // Sem feedback de erro para manter interface limpa.
                    }
                });
            });

            function openCancelModal() {
                if (!cancelModal) {
                    return;
                }

                cancelModal.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
            }

            function closeCancelModal() {
                if (!cancelModal) {
                    return;
                }

                cancelModal.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }

            openCancelModalButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    openCancelModal();
                });
            });

            closeCancelModalButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    closeCancelModal();
                });
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    closeCancelModal();
                }
            });
        });
    </script>
@endsection
