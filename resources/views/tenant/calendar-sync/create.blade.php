@extends('layouts.connect_plus.app')

@section('title', 'Criar Estado de Sincronização')

@section('content')

    <div class="page-header">
        <h3 class="page-title"> Criar Estado de Sincronização </h3>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('tenant.dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('tenant.calendar-sync.index') }}">Sincronização de Calendário</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Criar</li>
            </ol>
        </nav>
    </div>

    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <div>
                            <h4 class="card-title mb-1">
                                <i class="mdi mdi-sync-plus text-primary me-2"></i>
                                Novo Estado de Sincronização
                            </h4>
                            <p class="card-description mb-0 text-muted">Preencha os dados abaixo para criar um novo estado de sincronização</p>
                        </div>
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <strong>Ops!</strong> Verifique os erros abaixo:
                            <ul class="mt-2 mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
                        </div>
                    @endif

                    <form class="forms-sample" action="{{ route('tenant.calendar-sync.store') }}" method="POST">
                        @csrf

                        {{-- Seção: Informações Básicas --}}
                        <div class="mb-4">
                            <h5 class="mb-3 text-primary">
                                <i class="mdi mdi-information-outline me-2"></i>
                                Informações Básicas
                            </h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="fw-semibold">
                                            <i class="mdi mdi-calendar-check me-1"></i>
                                            Agendamento <span class="text-danger">*</span>
                                        </label>
                                        <select name="appointment_id" class="form-control @error('appointment_id') is-invalid @enderror" required>
                                            <option value="">Selecione um agendamento</option>
                                            @foreach ($appointments as $appointment)
                                                <option value="{{ $appointment->id }}" {{ old('appointment_id') == $appointment->id ? 'selected' : '' }}>
                                                    {{ $appointment->patient->full_name ?? 'N/A' }} - 
                                                    {{ $appointment->starts_at ? $appointment->starts_at->format('d/m/Y H:i') : 'N/A' }}
                                                    @if($appointment->calendar && $appointment->calendar->doctor)
                                                        - Dr(a). {{ $appointment->calendar->doctor->user->name ?? 'N/A' }}
                                                    @endif
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('appointment_id')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="fw-semibold">
                                            <i class="mdi mdi-cloud me-1"></i>
                                            Provedor <span class="text-danger">*</span>
                                        </label>
                                        <select name="provider" class="form-control @error('provider') is-invalid @enderror" required>
                                            <option value="">Selecione um provedor</option>
                                            <option value="google" {{ old('provider') == 'google' ? 'selected' : '' }}>Google Calendar</option>
                                            <option value="apple" {{ old('provider') == 'apple' ? 'selected' : '' }}>Apple Calendar</option>
                                        </select>
                                        @error('provider')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Seção: Sincronização --}}
                        <div class="mb-4">
                            <h5 class="mb-3 text-primary">
                                <i class="mdi mdi-sync me-2"></i>
                                Dados de Sincronização
                            </h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="fw-semibold">
                                            <i class="mdi mdi-identifier me-1"></i>
                                            ID Evento Externo
                                        </label>
                                        <input type="text" 
                                               class="form-control @error('external_event_id') is-invalid @enderror" 
                                               name="external_event_id" 
                                               value="{{ old('external_event_id') }}"
                                               placeholder="ID do evento no calendário externo">
                                        <small class="form-text text-muted">ID do evento no calendário do provedor</small>
                                        @error('external_event_id')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="fw-semibold">
                                            <i class="mdi mdi-clock-outline me-1"></i>
                                            Última Sincronização
                                        </label>
                                        <input type="datetime-local" 
                                               class="form-control @error('last_sync_at') is-invalid @enderror" 
                                               name="last_sync_at" 
                                               value="{{ old('last_sync_at') }}">
                                        <small class="form-text text-muted">Data e hora da última sincronização</small>
                                        @error('last_sync_at')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Botões de Ação --}}
                        <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                            <a href="{{ route('tenant.calendar-sync.index') }}" class="btn btn-light">
                                <i class="mdi mdi-arrow-left me-1"></i>
                                Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="mdi mdi-content-save me-1"></i>
                                Salvar Estado de Sincronização
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

@push('styles')
    <link href="{{ asset('css/tenant-common.css') }}" rel="stylesheet">
@endpush

@endsection

