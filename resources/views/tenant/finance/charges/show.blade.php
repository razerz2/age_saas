@extends('layouts.tailadmin.app')

@section('title', 'Detalhes da Cobrança')

@section('content')

    <!-- Page Header -->
    <div class="page-header mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white mb-2">Detalhes da Cobrança</h1>
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
                                <a href="{{ workspace_route('tenant.finance.charges.index') }}" class="ml-1 text-gray-700 hover:text-blue-600 dark:text-gray-300 dark:hover:text-white md:ml-2">Cobranças</a>
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
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Cobrança #{{ substr($charge->id, 0, 8) }}</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Detalhes completos da cobrança</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Paciente</p>
                    <p class="text-sm text-gray-900 dark:text-gray-100">{{ $charge->patient->full_name ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Valor</p>
                    <p class="text-lg font-semibold text-blue-600 dark:text-blue-400">R$ {{ number_format($charge->amount, 2, ',', '.') }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Status</p>
                    @if($charge->status === 'paid')
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-200">Pago</span>
                    @elseif($charge->status === 'pending')
                        @if($charge->isOverdue())
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-200">Vencido</span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-700 dark:bg-amber-900 dark:text-amber-200">Pendente</span>
                        @endif
                    @elseif($charge->status === 'cancelled')
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">Cancelado</span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-800 text-white">{{ ucfirst($charge->status) }}</span>
                    @endif
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Vencimento</p>
                    <p class="text-sm text-gray-900 dark:text-gray-100">{{ $charge->due_date->format('d/m/Y') }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Origem</p>
                    @if($charge->origin === 'public')
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-200">Público</span>
                    @elseif($charge->origin === 'portal')
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-indigo-100 text-indigo-700 dark:bg-indigo-900 dark:text-indigo-200">Portal</span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">Interno</span>
                    @endif
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Tipo de Cobrança</p>
                    @if($charge->billing_type === 'reservation')
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-200">Reserva</span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-indigo-100 text-indigo-700 dark:bg-indigo-900 dark:text-indigo-200">Integral</span>
                    @endif
                </div>
            </div>

            @if($charge->appointment)
                <div class="mb-6">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Agendamento</p>
                    <p class="text-sm text-gray-900 dark:text-gray-100">
                        {{ $charge->appointment->starts_at->format('d/m/Y H:i') }}
                        @if($charge->appointment->doctor)
                            - Dr(a). {{ $charge->appointment->doctor->user->name ?? 'N/A' }}
                        @endif
                    </p>
                </div>
            @endif

            @if($charge->payment_link)
                <div class="mb-6">
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">Link de Pagamento</p>
                    <a href="{{ $charge->payment_link }}" target="_blank" class="inline-flex items-center px-4 py-2 bg-primary text-white hover:bg-primary/90 text-sm font-medium rounded-md transition-colors">
                        Abrir Link
                    </a>
                </div>
            @endif

            <div class="flex items-center justify-end gap-3 flex-nowrap">
                @if(auth()->guard('tenant')->user()->role !== 'doctor' && $charge->status === 'pending')
                    <form action="{{ workspace_route('tenant.finance.charges.resend', ['slug' => tenant()->subdomain, 'charge' => $charge->id]) }}"
                          method="POST" class="inline">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white text-sm font-medium rounded-md transition-colors">
                            Reenviar Link
                        </button>
                    </form>
                @endif

                @if(auth()->guard('tenant')->user()->role === 'admin' && $charge->status === 'pending')
                    <form action="{{ workspace_route('tenant.finance.charges.cancel', ['slug' => tenant()->subdomain, 'charge' => $charge->id]) }}"
                          method="POST" class="inline"
                          onsubmit="event.preventDefault(); confirmAction({ title: 'Cancelar cobrança', message: 'Tem certeza que deseja cancelar esta cobrança?', confirmText: 'Cancelar cobrança', cancelText: 'Voltar', type: 'warning', onConfirm: () => event.target.submit() }); return false;">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-md transition-colors">
                            Cancelar
                        </button>
                    </form>
                @endif

                <a href="{{ workspace_route('tenant.finance.charges.index', ['slug' => tenant()->subdomain]) }}"
                   class="inline-flex items-center gap-2 px-4 py-2 border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 text-sm font-medium rounded-md transition-colors">
                    Voltar
                </a>
            </div>
        </div>
    </div>

@endsection

