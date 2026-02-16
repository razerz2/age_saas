@extends('layouts.tailadmin.app')

@section('title', 'Detalhes da Resposta')

@section('content')

    <div class="page-header">
        <h3 class="page-title">
            <i class="mdi mdi-file-document-check text-primary me-2"></i>
            Detalhes da Resposta
        </h3>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ workspace_route('tenant.dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ workspace_route('tenant.responses.index') }}">Respostas</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Detalhes</li>
            </ol>
        </nav>
    </div>

    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    {{-- Header do Card --}}
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="card-title mb-0">
                            <i class="mdi mdi-file-document text-primary me-2"></i>
                            Informações da Resposta
                        </h4>
                        <div class="flex items-center justify-end gap-3 flex-nowrap">
                            <x-tailadmin-button variant="secondary" size="sm" href="{{ workspace_route('tenant.responses.edit', $response->id) }}"
                                class="inline-flex items-center gap-2 border-warning text-warning bg-warning/10 hover:bg-warning/20 dark:bg-warning/20 dark:hover:bg-warning/30 dark:text-warning">
                                <i class="mdi mdi-pencil"></i> Editar
                            </x-tailadmin-button>
                            <x-tailadmin-button variant="secondary" size="sm" href="{{ workspace_route('tenant.responses.index') }}"
                                class="inline-flex items-center gap-2 bg-transparent border-gray-300 text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-white/5">
                                <i class="mdi mdi-arrow-left"></i> Voltar
                            </x-tailadmin-button>
                        </div>
                    </div>

                    {{-- Status Badge --}}
                    <div class="mb-4">
                        @if($response->status == 'submitted')
                            <span class="badge bg-success px-3 py-2">
                                <i class="mdi mdi-check-circle me-1"></i> Enviado
                            </span>
                        @else
                            <span class="badge bg-warning px-3 py-2">
                                <i class="mdi mdi-clock-outline me-1"></i> Pendente
                            </span>
                        @endif
                    </div>

                    {{-- Informações Gerais --}}
                    <h5 class="text-primary mb-3">
                        <i class="mdi mdi-information-outline me-2"></i>
                        Informações Gerais
                    </h5>
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <div class="border rounded p-3 h-100">
                                <label class="text-muted small mb-1 d-block">
                                    <i class="mdi mdi-identifier me-1"></i> ID
                                </label>
                                <p class="mb-0 fw-semibold">{{ $response->id }}</p>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="border rounded p-3 h-100">
                                <label class="text-muted small mb-1 d-block">
                                    <i class="mdi mdi-file-document-edit me-1"></i> Formulário
                                </label>
                                <p class="mb-0 fw-semibold">{{ $response->form->name ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="border rounded p-3 h-100">
                                <label class="text-muted small mb-1 d-block">
                                    <i class="mdi mdi-account-heart me-1"></i> Paciente
                                </label>
                                <p class="mb-0 fw-semibold">{{ $response->patient->full_name ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="border rounded p-3 h-100">
                                <label class="text-muted small mb-1 d-block">
                                    <i class="mdi mdi-calendar-clock me-1"></i> Agendamento
                                </label>
                                <p class="mb-0 fw-semibold">{{ $response->appointment_id ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="border rounded p-3 h-100">
                                <label class="text-muted small mb-1 d-block">
                                    <i class="mdi mdi-calendar-check me-1"></i> Data de Envio
                                </label>
                                <p class="mb-0 fw-semibold">
                                    {{ $response->submitted_at ? $response->submitted_at->format('d/m/Y H:i') : 'N/A' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Respostas --}}
                    <h5 class="text-primary mb-3">
                        <i class="mdi mdi-text-box me-2"></i>
                        Respostas
                    </h5>
                    
                    @if($response->answers && $response->answers->count() > 0)
                        @if($response->form->sections && $response->form->sections->count() > 0)
                            @foreach($response->form->sections->sortBy('position') as $section)
                                <div class="border rounded p-3 mb-3">
                                    <h6 class="text-primary mb-3">
                                        <i class="mdi mdi-folder-outline me-1"></i>
                                        {{ $section->title ?? 'Seção sem título' }}
                                    </h6>
                                    
                                    @foreach($section->questions->sortBy('position') as $question)
                                        @php
                                            $answer = $response->answers->firstWhere('question_id', $question->id);
                                        @endphp
                                        <div class="mb-3 pb-3 border-bottom">
                                            <label class="text-muted small d-block mb-1">
                                                <strong>{{ $question->label }}</strong>
                                            </label>
                                            @if($answer)
                                                <p class="mb-0">{{ $answer->value ?? 'N/A' }}</p>
                                            @else
                                                <p class="mb-0 text-muted">
                                                    <i class="mdi mdi-minus-circle me-1"></i>
                                                    Não respondido
                                                </p>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                        @else
                            <div class="border rounded p-3">
                                @foreach($response->form->questions->sortBy('position') as $question)
                                    @php
                                        $answer = $response->answers->firstWhere('question_id', $question->id);
                                    @endphp
                                    <div class="mb-3 pb-3 border-bottom">
                                        <label class="text-muted small d-block mb-1">
                                            <strong>{{ $question->label }}</strong>
                                        </label>
                                        @if($answer)
                                            <p class="mb-0">{{ $answer->value ?? 'N/A' }}</p>
                                        @else
                                            <p class="mb-0 text-muted">
                                                <i class="mdi mdi-minus-circle me-1"></i>
                                                Não respondido
                                            </p>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    @else
                        <div class="alert alert-info">
                            <i class="mdi mdi-information-outline me-2"></i>
                            Nenhuma resposta encontrada.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

@endsection
