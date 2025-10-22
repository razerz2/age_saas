@extends('layouts.freedash.app')
@section('content')

<div class="page-breadcrumb">
    <div class="row">
        <div class="col-7 align-self-center">
            <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">
                Detalhe da Notificação
            </h4>
            <div class="d-flex align-items-center">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb m-0 p-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route('Platform.dashboard') }}" class="text-muted">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('Platform.system_notifications.index') }}" class="text-muted">
                                Notificações do Sistema
                            </a>
                        </li>
                        <li class="breadcrumb-item text-muted active" aria-current="page">Visualizar</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h4 class="mb-3 text-dark">
                        {{ $notification->title }}
                        <span class="badge 
                            @if ($notification->level == 'error') bg-danger
                            @elseif ($notification->level == 'warning') bg-warning
                            @else bg-info text-dark @endif ms-2">
                            {{ ucfirst($notification->level) }}
                        </span>
                    </h4>

                    <p class="text-muted mb-1">
                        <i class="fa fa-clock me-1"></i>
                        {{ \Carbon\Carbon::parse($notification->created_at)->format('d/m/Y H:i') }}
                    </p>

                    <p class="text-muted mb-3">
                        <i class="fa fa-tag me-1"></i>
                        Contexto: {{ $notification->context ?? 'Não informado' }}
                    </p>

                    <hr>

                    <div class="mb-4">
                        <p class="mb-0">{{ $notification->message }}</p>
                    </div>

                    <a href="{{ route('Platform.system_notifications.index') }}" class="btn btn-outline-secondary">
                        <i class="fa fa-arrow-left me-1"></i> Voltar
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@include('layouts.freedash.footer')
@endsection