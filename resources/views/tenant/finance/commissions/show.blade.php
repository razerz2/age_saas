@extends('layouts.tailadmin.app')

@section('title', 'Detalhes da Comissão')

@section('content')

    <!-- Page Header -->
    <div class="page-header mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white mb-2">Detalhes da Comissão</h1>
                <nav class="flex" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 md:space-x-3">
                        <li class="inline-flex items-center">
                            <a href="{{ workspace_route('tenant.dashboard') }}" class="text-gray-700 hover:text-blue-600 dark:text-gray-300 dark:hover:text-white">
                                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001 1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
                                </svg>
                                Dashboard
                            </a>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <a href="{{ workspace_route('tenant.finance.index') }}" class="ml-1 text-gray-700 hover:text-blue-600 dark:text-gray-300 dark:hover:text-white md:ml-2">Financeiro</a>
                            </div>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <a href="{{ workspace_route('tenant.finance.commissions.index') }}" class="ml-1 text-gray-700 hover:text-blue-600 dark:text-gray-300 dark:hover:text-white md:ml-2">Comissões</a>
                            </div>
                        </li>
                        <li aria-current="page">
                            <div class="flex items-center">
                                <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="ml-1 text-gray-500 dark:text-gray-400 md:ml-2">Detalhes</span>
                            </div>
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="bg-green-50 border border-green-200 rounded-md p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-green-700">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="bg-red-50 border border-red-200 rounded-md p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-red-700">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 max-w-3xl">
        <div class="p-6">
            <div class="flex items-start justify-between mb-6">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Comissão #{{ substr($commission->id, 0, 8) }}</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Detalhes da comissão</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Médico</p>
                    <p class="text-sm text-gray-900 dark:text-gray-100">{{ $commission->doctor->user->name ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Valor da Comissão</p>
                    <p class="text-lg font-semibold text-blue-600 dark:text-blue-400">R$ {{ number_format($commission->amount, 2, ',', '.') }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Percentual</p>
                    <p class="text-sm text-gray-900 dark:text-gray-100">{{ number_format($commission->percentage, 2, ',', '.') }}%</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Status</p>
                    @if($commission->status === 'paid')
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-200">Pago</span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-700 dark:bg-amber-900 dark:text-amber-200">Pendente</span>
                    @endif
                </div>
            </div>

            @if($commission->transaction)
                <div class="mb-6">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Transação Relacionada</p>
                    <p class="text-sm text-gray-900 dark:text-gray-100">
                        R$ {{ number_format($commission->transaction->amount, 2, ',', '.') }}
                        @if($commission->transaction->appointment)
                            - Agendamento em {{ $commission->transaction->appointment->starts_at->format('d/m/Y H:i') }}
                        @endif
                    </p>
                </div>
            @endif

            @if($commission->paid_at)
                <div class="mb-6">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Data de Pagamento</p>
                    <p class="text-sm text-gray-900 dark:text-gray-100">{{ $commission->paid_at->format('d/m/Y H:i') }}</p>
                </div>
            @endif

            <div class="flex items-center justify-end gap-3 flex-nowrap">
                @if(auth()->guard('tenant')->user()->role === 'admin' && !$commission->isPaid())
                    <form action="{{ workspace_route('tenant.finance.commissions.markPaid', ['slug' => tenant()->subdomain, 'commission' => $commission->id]) }}"
                          method="POST" class="inline"
                          onsubmit="event.preventDefault(); confirmAction({ title: 'Marcar comissão como paga', message: 'Tem certeza que deseja marcar esta comissão como paga?', confirmText: 'Marcar como paga', cancelText: 'Cancelar', type: 'warning', onConfirm: () => event.target.submit() }); return false;">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-md transition-colors">
                            Marcar como Paga
                        </button>
                    </form>
                @endif

                <a href="{{ workspace_route('tenant.finance.commissions.index', ['slug' => tenant()->subdomain]) }}"
                   class="inline-flex items-center gap-2 px-4 py-2 border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 text-sm font-medium rounded-md transition-colors">
                    Voltar
                </a>
            </div>
        </div>
    </div>

@endsection

