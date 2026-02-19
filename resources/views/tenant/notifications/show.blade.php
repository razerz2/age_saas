@extends('layouts.tailadmin.app')

@section('title', 'Detalhes da Notificação')
@section('page', 'notifications')

@section('content')
    <div class="page-header mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white mb-2">Detalhes da Notificação</h1>
                <nav class="flex" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 md:space-x-3">
                        <li class="inline-flex items-center">
                            <a href="{{ workspace_route('tenant.dashboard') }}" class="text-gray-700 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white inline-flex items-center">
                                <x-icon name="home-outline" class="w-4 h-4 mr-2" />
                                Dashboard
                            </a>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <x-icon name="chevron-right" class="w-4 h-4 text-gray-400" />
                                <a href="{{ workspace_route('tenant.notifications.index') }}" class="ml-1 text-gray-700 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white md:ml-2">Notificações</a>
                            </div>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <x-icon name="chevron-right" class="w-4 h-4 text-gray-400" />
                                <span class="ml-1 text-gray-500 dark:text-gray-400 md:ml-2">Detalhes</span>
                            </div>
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 max-w-3xl">
        <div class="p-6">
            <div class="flex items-start gap-4 mb-6">
                <div class="flex-shrink-0">
                    @php
                        $levelColors = [
                            'error' => 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-200',
                            'warning' => 'bg-amber-100 text-amber-700 dark:bg-amber-900 dark:text-amber-200',
                            'info' => 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-200',
                            'success' => 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-200'
                        ];
                        $color = $levelColors[$notification->level] ?? 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-200';
                    @endphp
                    <div class="h-16 w-16 rounded-full {{ $color }} flex items-center justify-center">
                        <x-icon :name="$notification->type === 'appointment' ? 'bell-outline' : 'file-document-edit-outline'" class="text-2xl" />
                    </div>
                </div>
                <div class="flex-1">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">{{ $notification->title }}</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">{{ $notification->message }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-3">
                        {{ $notification->created_at->format('d/m/Y H:i:s') }} ({{ $notification->created_at->diffForHumans() }})
                    </p>
                </div>
            </div>

            @if($notification->metadata)
                <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Informações Adicionais</h3>
                    <dl class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        @foreach($notification->metadata as $key => $value)
                            <div class="md:col-span-1">
                                <dt class="text-xs text-gray-500 dark:text-gray-400">{{ ucfirst(str_replace('_', ' ', $key)) }}</dt>
                            </div>
                            <div class="md:col-span-2">
                                <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $value }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </div>
            @endif

            @if($notification->related)
                <div class="border-t border-gray-200 dark:border-gray-700 pt-4 mt-4">
                    <div class="flex items-center justify-end gap-3 flex-nowrap">
                        <a href="@if($notification->type === 'appointment') {{ workspace_route('tenant.appointments.show', ['id' => $notification->related_id]) }} @elseif($notification->type === 'form_response') {{ workspace_route('tenant.responses.show', ['id' => $notification->related_id]) }} @endif"
                           class="btn btn-primary">
                            <x-icon name="eye-outline" class="w-4 h-4 mr-2" />
                            Ver {{ $notification->type === 'appointment' ? 'Agendamento' : 'Resposta' }}
                        </a>
                    </div>
                </div>
            @endif

            <div class="mt-6">
                <div class="flex items-center justify-end gap-3 flex-nowrap">
                    <a href="{{ workspace_route('tenant.notifications.index') }}" class="btn btn-outline">
                        <x-icon name="arrow-left" class="w-4 h-4 mr-2" />
                        Voltar
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
