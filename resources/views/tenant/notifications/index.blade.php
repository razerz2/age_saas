@extends('layouts.tailadmin.app')

@section('title', 'Notificações')
@section('page', 'notifications')

@section('content')
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Notificações</h1>
                <nav class="flex mt-2" aria-label="Breadcrumb">
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
                                <span class="ml-1 text-gray-500 dark:text-gray-400">Notificações</span>
                            </div>
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="p-6 space-y-4">
            @forelse($notifications as $notification)
                @php
                    $levelColors = [
                        'error' => 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-200',
                        'warning' => 'bg-amber-100 text-amber-700 dark:bg-amber-900 dark:text-amber-200',
                        'info' => 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-200',
                        'success' => 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-200'
                    ];
                    $color = $levelColors[$notification->level] ?? 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-200';
                @endphp
                <div class="flex items-start gap-4 border-b border-gray-200 dark:border-gray-700 pb-4 {{ $notification->status === 'new' ? 'bg-gray-50 dark:bg-gray-900/20 p-4 rounded-md' : '' }}">
                    <div class="flex-shrink-0">
                        <div class="h-12 w-12 rounded-full {{ $color }} flex items-center justify-center">
                            <x-icon :name="$notification->type === 'appointment' ? 'bell-outline' : 'file-document-edit-outline'" />
                        </div>
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center gap-2">
                            <a href="{{ workspace_route('tenant.notifications.show', ['notification' => $notification->id]) }}"
                               class="text-sm font-semibold text-gray-900 dark:text-white hover:text-gray-900">
                                {{ $notification->title }}
                            </a>
                            @if($notification->status === 'new')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-200">Nova</span>
                            @endif
                        </div>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ $notification->message }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                            {{ $notification->created_at->diffForHumans() }}
                        </p>
                    </div>
                </div>
            @empty
                <div class="text-center py-10">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Nenhuma notificação encontrada</p>
                </div>
            @endforelse

            <div>
                {{ $notifications->links() }}
            </div>
        </div>
    </div>
@endsection
