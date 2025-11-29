@extends('layouts.connect_plus.app')

@section('title', 'Detalhes da Notificação')

@section('content')
<div class="page-header">
    <h3 class="page-title">
        <span class="page-title-icon bg-gradient-primary text-white me-2">
            <i class="mdi mdi-bell-outline"></i>
        </span>
        Detalhes da Notificação
    </h3>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-start mb-4">
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
                             style="width: 64px; height: 64px;">
                            <i class="mdi mdi-{{ $notification->type === 'appointment' ? 'calendar-check' : 'file-document-edit' }}" style="font-size: 32px;"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <h4 class="mb-2">{{ $notification->title }}</h4>
                        <p class="text-muted mb-2">{{ $notification->message }}</p>
                        <small class="text-muted">
                            <i class="mdi mdi-clock-outline"></i>
                            {{ $notification->created_at->format('d/m/Y H:i:s') }}
                            ({{ $notification->created_at->diffForHumans() }})
                        </small>
                    </div>
                </div>

                @if($notification->metadata)
                    <div class="border-top pt-3">
                        <h6 class="mb-3">Informações Adicionais</h6>
                        <dl class="row">
                            @foreach($notification->metadata as $key => $value)
                                <dt class="col-sm-3">{{ ucfirst(str_replace('_', ' ', $key)) }}</dt>
                                <dd class="col-sm-9">{{ $value }}</dd>
                            @endforeach
                        </dl>
                    </div>
                @endif

                @if($notification->related)
                    <div class="border-top pt-3 mt-3">
                        <a href="@if($notification->type === 'appointment') {{ route('tenant.appointments.show', $notification->related_id) }} @elseif($notification->type === 'form_response') {{ route('tenant.responses.show', $notification->related_id) }} @endif" 
                           class="btn btn-primary">
                            <i class="mdi mdi-eye me-2"></i>
                            Ver {{ $notification->type === 'appointment' ? 'Agendamento' : 'Resposta' }}
                        </a>
                    </div>
                @endif

                <div class="mt-4">
                    <a href="{{ route('tenant.notifications.index') }}" class="btn btn-secondary">
                        <i class="mdi mdi-arrow-left me-1"></i> Voltar
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

