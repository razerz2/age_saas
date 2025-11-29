@extends('layouts.connect_plus.app')

@section('title', 'Visualizar Formulário')

@section('content')

    <div class="page-header">
        <h3 class="page-title"> Visualizar Formulário </h3>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('tenant.dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('tenant.forms.index') }}">Formulários</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('tenant.forms.show', $form->id) }}">{{ $form->name }}</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Visualizar</li>
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
                                <i class="mdi mdi-file-document-text text-primary me-2"></i>
                                {{ $form->name }}
                            </h4>
                            @if($form->description)
                                <p class="card-description mb-0 text-muted">{{ $form->description }}</p>
                            @endif
                        </div>
                        <div>
                            <span class="badge bg-{{ $form->is_active ? 'success' : 'danger' }} me-2">
                                {{ $form->is_active ? 'Ativo' : 'Inativo' }}
                            </span>
                            <button onclick="window.print()" class="btn btn-sm btn-light">
                                <i class="mdi mdi-printer me-1"></i>
                                Imprimir
                            </button>
                            <button onclick="window.close()" class="btn btn-sm btn-light">
                                <i class="mdi mdi-close me-1"></i>
                                Fechar
                            </button>
                        </div>
                    </div>

                    <div class="mb-4 p-3 bg-light rounded">
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Médico:</strong> {{ $form->doctor->user->name ?? 'N/A' }}
                            </div>
                            @if($form->specialty)
                                <div class="col-md-6">
                                    <strong>Especialidade:</strong> {{ $form->specialty->name }}
                                </div>
                            @endif
                        </div>
                    </div>

                    @if($form->sections->isEmpty() && $form->questions->where('section_id', null)->isEmpty())
                        <div class="alert alert-warning">
                            <i class="mdi mdi-alert-outline me-2"></i>
                            Este formulário ainda não possui perguntas. Use o construtor para adicionar seções e perguntas.
                        </div>
                    @else
                        <form class="forms-sample">
                            {{-- Perguntas sem seção --}}
                            @if($form->questions->where('section_id', null)->isNotEmpty())
                                <div class="mb-4">
                                    <h5 class="mb-3 text-primary border-bottom pb-2">
                                        <i class="mdi mdi-file-document me-2"></i>
                                        Perguntas Gerais
                                    </h5>
                                    
                                    @foreach($form->questions->where('section_id', null)->sortBy('position') as $question)
                                        @include('tenant.forms.partials.preview-question', ['question' => $question])
                                    @endforeach
                                </div>
                            @endif

                            {{-- Seções com perguntas --}}
                            @foreach($form->sections->sortBy('position') as $section)
                                <div class="mb-4">
                                    <h5 class="mb-3 text-primary border-bottom pb-2">
                                        <i class="mdi mdi-folder me-2"></i>
                                        {{ $section->title ?: 'Seção sem título' }}
                                    </h5>
                                    
                                    @if($section->questions->isEmpty())
                                        <p class="text-muted">Nenhuma pergunta nesta seção.</p>
                                    @else
                                        @foreach($section->questions->sortBy('position') as $question)
                                            @include('tenant.forms.partials.preview-question', ['question' => $question])
                                        @endforeach
                                    @endif
                                </div>
                            @endforeach
                        </form>
                    @endif

                    <div class="mt-4 pt-3 border-top">
                        <a href="{{ route('tenant.forms.show', $form->id) }}" class="btn btn-light">
                            <i class="mdi mdi-arrow-left me-1"></i>
                            Voltar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('styles')
<style>
    @media print {
        .page-header,
        .breadcrumb,
        .btn,
        .card-header {
            display: none !important;
        }
        .card {
            border: none !important;
            box-shadow: none !important;
        }
    }
    .preview-question {
        margin-bottom: 1.5rem;
        padding: 1.5rem;
        background: #ffffff;
        border-radius: 8px;
        border-left: 4px solid #007bff;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    .preview-question > label {
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 1rem;
        display: block;
        font-size: 1rem;
        line-height: 1.5;
    }
    .preview-question .help-text {
        font-size: 0.875rem;
        color: #6c757d;
        margin-bottom: 1rem;
        display: block;
        line-height: 1.5;
    }
    .preview-question .form-control {
        pointer-events: none;
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        padding: 0.5rem 0.75rem;
    }
    .preview-question .form-check {
        pointer-events: none;
        margin-bottom: 0.75rem;
        padding-left: 1.75rem;
    }
    .preview-question .form-check:last-child {
        margin-bottom: 0;
    }
    .preview-question .form-check-input {
        pointer-events: none;
        cursor: not-allowed;
        margin-top: 0.25rem;
    }
    .preview-question .form-check-label {
        cursor: default;
        margin-left: 0.5rem;
        font-weight: 400;
        color: #495057;
    }
    .preview-question .boolean-options {
        margin-top: 0.5rem;
        margin-bottom: 0.5rem;
    }
    .preview-question .boolean-options .form-check {
        display: inline-block;
        margin-right: 2rem;
        margin-bottom: 0;
        padding-left: 1.75rem;
    }
    .preview-question .boolean-options .form-check:last-child {
        margin-right: 0;
    }
    .preview-question small {
        margin-top: 1rem;
        display: block;
        font-size: 0.8rem;
        color: #6c757d;
        padding-top: 0.75rem;
        padding-left: 0.25rem;
    }
    .preview-question .options-container {
        margin-top: 0.75rem;
        margin-bottom: 0.5rem;
    }
    .preview-question .options-container .form-check {
        padding-left: 1.75rem;
    }
</style>
@endpush

