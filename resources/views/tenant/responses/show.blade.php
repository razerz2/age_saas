@extends('layouts.connect_plus.app')

@section('title', 'Detalhes da Resposta')

@section('content')

    <div class="page-header">
        <h3 class="page-title"> Detalhes da Resposta </h3>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('tenant.dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('tenant.responses.index') }}">Respostas</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Detalhes</li>
            </ol>
        </nav>
    </div>

    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Informações Gerais</h4>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>ID:</strong> {{ $response->id }}</p>
                            <p><strong>Formulário:</strong> {{ $response->form->name ?? 'N/A' }}</p>
                            <p><strong>Paciente:</strong> {{ $response->patient->full_name ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Agendamento:</strong> {{ $response->appointment_id ?? 'N/A' }}</p>
                            <p><strong>Data de Envio:</strong> {{ $response->submitted_at ? $response->submitted_at->format('d/m/Y H:i') : 'N/A' }}</p>
                            <p><strong>Status:</strong> 
                                @if($response->status == 'submitted')
                                    <span class="badge bg-success">Enviado</span>
                                @else
                                    <span class="badge bg-warning">Pendente</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <hr class="my-4">

                    <h5 class="mb-3">Respostas</h5>
                    
                    @if($response->answers && $response->answers->count() > 0)
                        @if($response->form->sections && $response->form->sections->count() > 0)
                            @foreach($response->form->sections->sortBy('position') as $section)
                                <h6 class="mt-4 mb-3">{{ $section->title ?? 'Seção sem título' }}</h6>
                                
                                @foreach($section->questions->sortBy('position') as $question)
                                    @php
                                        $answer = $response->answers->firstWhere('question_id', $question->id);
                                    @endphp
                                    <div class="mb-3">
                                        <strong>{{ $question->label }}:</strong>
                                        @if($answer)
                                            <span>{{ $answer->value ?? 'N/A' }}</span>
                                        @else
                                            <span class="text-muted">Não respondido</span>
                                        @endif
                                    </div>
                                @endforeach
                            @endforeach
                        @else
                            @foreach($response->form->questions->sortBy('position') as $question)
                                @php
                                    $answer = $response->answers->firstWhere('question_id', $question->id);
                                @endphp
                                <div class="mb-3">
                                    <strong>{{ $question->label }}:</strong>
                                    @if($answer)
                                        <span>{{ $answer->value ?? 'N/A' }}</span>
                                    @else
                                        <span class="text-muted">Não respondido</span>
                                    @endif
                                </div>
                            @endforeach
                        @endif
                    @else
                        <p class="text-muted">Nenhuma resposta encontrada.</p>
                    @endif

                    <div class="mt-4">
                        <a href="{{ route('tenant.responses.edit', $response->id) }}" class="btn btn-warning">Editar</a>
                        <a href="{{ route('tenant.responses.index') }}" class="btn btn-light">Voltar</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
