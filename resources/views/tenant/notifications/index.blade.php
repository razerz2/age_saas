@extends('layouts.connect_plus.app')

@section('title', 'Notificações')

@section('content')
<div class="page-header">
    <h3 class="page-title">
        <span class="page-title-icon bg-gradient-primary text-white me-2">
            <i class="mdi mdi-bell-outline"></i>
        </span>
        Notificações
    </h3>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                @forelse($notifications as $notification)
                    <div class="d-flex align-items-start border-bottom pb-3 mb-3 {{ $notification->status === 'new' ? 'bg-light p-3 rounded' : '' }}">
                        <div class="me-3">
                            @php
                                $levelColors = [
                                    'error' => 'bg-danger',
                                    'warning' => 'bg-warning',
                                    'info' => 'bg-info',
                                    'success' => 'bg-success'
                                ];
                                $color = $levelColors[$notification->level] ?? 'bg-info';
                            @endphp
                            <div class="rounded-circle {{ $color }} text-white d-flex align-items-center justify-content-center" 
                                 style="width: 48px; height: 48px;">
                                <i class="mdi mdi-{{ $notification->type === 'appointment' ? 'calendar-check' : 'file-document-edit' }}"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1">
                                <a href="{{ route('tenant.notifications.show', $notification->id) }}" 
                                   class="text-decoration-none">
                                    {{ $notification->title }}
                                </a>
                                @if($notification->status === 'new')
                                    <span class="badge bg-primary ms-2">Nova</span>
                                @endif
                            </h6>
                            <p class="text-muted mb-1">{{ $notification->message }}</p>
                            <small class="text-muted">
                                <i class="mdi mdi-clock-outline"></i>
                                {{ $notification->created_at->diffForHumans() }}
                            </small>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-5">
                        <i class="mdi mdi-bell-off-outline" style="font-size: 64px; opacity: 0.3;"></i>
                        <p class="text-muted mt-3">Nenhuma notificação encontrada</p>
                    </div>
                @endforelse

                {{ $notifications->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

