@extends('layouts.tailadmin.app')

@section('title', 'Detalhes da Resposta')
@section('page', 'responses')

@section('content')

    <div class="page-header">
        <h3 class="page-title">
            <x-icon name="file-document-check" class=" text-primary me-2" />
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
                            <x-icon name="file-document" class=" text-primary me-2" />
                            Informações da Resposta
                        </h4>
                        <div class="flex items-center justify-end gap-3 flex-nowrap">
                            <x-tailadmin-button variant="secondary" size="sm" href="{{ workspace_route('tenant.responses.edit', $response->id) }}"
                                class="inline-flex items-center gap-2 border-warning text-warning bg-warning/10 hover:bg-warning/20 dark:bg-warning/20 dark:hover:bg-warning/30 dark:text-warning">
                                <x-icon name="pencil" class="" /> Editar
                            </x-tailadmin-button>
                            <x-tailadmin-button variant="secondary" size="sm" href="{{ workspace_route('tenant.responses.index') }}"
                                class="inline-flex items-center gap-2 bg-transparent border-gray-300 text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-white/5">
                                <x-icon name="arrow-left" class="" /> Voltar
                            </x-tailadmin-button>
                        </div>
                    </div>

                    {{-- Status Badge --}}
                    <div class="mb-4">
                        @if($response->status == 'submitted')
                            <span class="badge bg-success px-3 py-2">
                                <x-icon name="check-circle" class=" me-1" /> Enviado
                            </span>
                        @else
                            <span class="badge bg-warning px-3 py-2">
                                <x-icon name="clock-outline" class=" me-1" /> Pendente
                            </span>
                        @endif
                    </div>

                    {{-- Informações Gerais --}}
                    <h5 class="text-primary mb-3">
                        <x-icon name="information-outline" class=" me-2" />
                        Informações Gerais
                    </h5>
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <div class="border rounded p-3 h-100">
                                <label class="text-muted small mb-1 d-block">
                                    <x-icon name="identifier" class=" me-1" /> ID
                                </label>
                                <p class="mb-0 fw-semibold">{{ $response->id }}</p>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="border rounded p-3 h-100">
                                <label class="text-muted small mb-1 d-block">
                                    <x-icon name="file-document-edit" class=" me-1" /> Formulário
                                </label>
                                <p class="mb-0 fw-semibold">{{ $response->form->name ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="border rounded p-3 h-100">
                                <label class="text-muted small mb-1 d-block">
                                    <x-icon name="account-heart" class=" me-1" /> Paciente
                                </label>
                                <p class="mb-0 fw-semibold">{{ $response->patient->full_name ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="border rounded p-3 h-100">
                                <label class="text-muted small mb-1 d-block">
                                    <x-icon name="calendar-clock" class=" me-1" /> Agendamento
                                </label>
                                <p class="mb-0 fw-semibold">{{ $response->appointment_id ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="border rounded p-3 h-100">
                                <label class="text-muted small mb-1 d-block">
                                    <x-icon name="calendar-check" class=" me-1" /> Data de Envio
                                </label>
                                <p class="mb-0 fw-semibold">
                                    {{ $response->submitted_at ? $response->submitted_at->format('d/m/Y H:i') : 'N/A' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Respostas --}}
                    <h5 class="text-primary mb-3">
                        <x-icon name="text-box" class=" me-2" />
                        Respostas
                    </h5>
                    
                    @if($response->answers && $response->answers->count() > 0)
                        @if($response->form->sections && $response->form->sections->count() > 0)
                            @foreach($response->form->sections->sortBy('position') as $section)
                                <div class="border rounded p-3 mb-3">
                                    <h6 class="text-primary mb-3">
                                        <x-icon name="folder-outline" class=" me-1" />
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
                                                    <x-icon name="minus-circle" class=" me-1" />
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
                                                <x-icon name="minus-circle" class=" me-1" />
                                                Não respondido
                                            </p>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    @else
                        <div class="alert alert-info">
                            <x-icon name="information-outline" class=" me-2" />
                            Nenhuma resposta encontrada.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

@endsection
