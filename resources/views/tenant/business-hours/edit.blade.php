@extends('layouts.connect_plus.app')

@section('title', 'Editar Horário Comercial')

@section('content')

    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <h3 class="page-title mb-0"> Editar Horário Comercial </h3>
            <x-help-button module="business-hours" />
        </div>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ workspace_route('tenant.dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('tenant.business-hours.index') }}">Horários Comerciais</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Editar</li>
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
                                <i class="mdi mdi-clock-edit text-primary me-2"></i>
                                Editar Horário Comercial
                            </h4>
                            <p class="card-description mb-0 text-muted">Atualize as informações do horário comercial abaixo</p>
                        </div>
                    </div>

                    <form class="forms-sample" action="{{ route('tenant.business-hours.update', $businessHour->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        {{-- Seção: Informações do Horário --}}
                        <div class="mb-4">
                            <h5 class="mb-3 text-primary">
                                <i class="mdi mdi-information-outline me-2"></i>
                                Informações do Horário
                            </h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="fw-semibold">
                                            <i class="mdi mdi-doctor me-1"></i>
                                            Médico <span class="text-danger">*</span>
                                        </label>
                                        <select name="doctor_id" class="form-control @error('doctor_id') is-invalid @enderror" required>
                                            <option value="">Selecione um médico</option>
                                            @foreach($doctors as $doctor)
                                                <option value="{{ $doctor->id }}" {{ old('doctor_id', $businessHour->doctor_id) == $doctor->id ? 'selected' : '' }}>{{ $doctor->user->name ?? 'N/A' }}</option>
                                            @endforeach
                                        </select>
                                        @error('doctor_id')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="fw-semibold">
                                            <i class="mdi mdi-calendar-week me-1"></i>
                                            Dia da Semana <span class="text-danger">*</span>
                                        </label>
                                        <select name="weekday" class="form-control @error('weekday') is-invalid @enderror" required>
                                            <option value="0" {{ old('weekday', $businessHour->weekday) == 0 ? 'selected' : '' }}>Domingo</option>
                                            <option value="1" {{ old('weekday', $businessHour->weekday) == 1 ? 'selected' : '' }}>Segunda-feira</option>
                                            <option value="2" {{ old('weekday', $businessHour->weekday) == 2 ? 'selected' : '' }}>Terça-feira</option>
                                            <option value="3" {{ old('weekday', $businessHour->weekday) == 3 ? 'selected' : '' }}>Quarta-feira</option>
                                            <option value="4" {{ old('weekday', $businessHour->weekday) == 4 ? 'selected' : '' }}>Quinta-feira</option>
                                            <option value="5" {{ old('weekday', $businessHour->weekday) == 5 ? 'selected' : '' }}>Sexta-feira</option>
                                            <option value="6" {{ old('weekday', $businessHour->weekday) == 6 ? 'selected' : '' }}>Sábado</option>
                                        </select>
                                        @error('weekday')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row business-hours-form-layout">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="fw-semibold">
                                            <i class="mdi mdi-clock-start me-1"></i>
                                            Horário Início <span class="text-danger">*</span>
                                        </label>
                                        <input type="time" class="form-control @error('start_time') is-invalid @enderror" 
                                               name="start_time" value="{{ old('start_time', $businessHour->start_time) }}" required>
                                        <small class="form-text text-muted" style="visibility: hidden;">Opcional</small>
                                        @error('start_time')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="form-group mb-3">
                                        <label class="fw-semibold">
                                            <i class="mdi mdi-clock-end me-1"></i>
                                            Horário Fim <span class="text-danger">*</span>
                                        </label>
                                        <input type="time" class="form-control @error('end_time') is-invalid @enderror" 
                                               name="end_time" value="{{ old('end_time', $businessHour->end_time) }}" required>
                                        <small class="form-text text-muted" style="visibility: hidden;">Opcional</small>
                                        @error('end_time')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="fw-semibold">
                                            <i class="mdi mdi-pause-circle-outline me-1"></i>
                                            Início do Intervalo
                                        </label>
                                        <input type="time" class="form-control @error('break_start_time') is-invalid @enderror" 
                                               name="break_start_time" value="{{ old('break_start_time', $businessHour->break_start_time) }}" 
                                               id="break_start_time">
                                        <small class="form-text text-muted">Opcional</small>
                                        @error('break_start_time')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="form-group mb-3">
                                        <label class="fw-semibold">
                                            <i class="mdi mdi-pause-circle me-1"></i>
                                            Fim do Intervalo
                                        </label>
                                        <input type="time" class="form-control @error('break_end_time') is-invalid @enderror" 
                                               name="break_end_time" value="{{ old('break_end_time', $businessHour->break_end_time) }}" 
                                               id="break_end_time">
                                        <small class="form-text text-muted">Opcional</small>
                                        @error('break_end_time')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Botões de Ação --}}
                        <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                            <a href="{{ route('tenant.business-hours.index') }}" class="btn btn-light">
                                <i class="mdi mdi-arrow-left me-1"></i>
                                Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="mdi mdi-content-save me-1"></i>
                                Atualizar Horário Comercial
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

@push('styles')
    <link href="{{ asset('css/tenant-common.css') }}" rel="stylesheet">
    <link href="{{ asset('css/tenant-business-hours.css') }}" rel="stylesheet">
@endpush

@endsection
