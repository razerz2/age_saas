@extends('layouts.tailadmin.app')

@section('title', 'Visualizar Formulário')
@section('page', 'forms')

@section('content')

    <div class="page-header">
        <h3 class="page-title"> Visualizar Formulário </h3>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ workspace_route('tenant.dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ workspace_route('tenant.forms.index') }}">Formulários</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ workspace_route('tenant.forms.show', ['form' => $form->id]) }}">{{ $form->name }}</a>
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
                        <div class="flex items-center gap-2">
                            <span class="badge bg-{{ $form->is_active ? 'success' : 'danger' }} me-2">
                                {{ $form->is_active ? 'Ativo' : 'Inativo' }}
                            </span>
                            <x-tailadmin-button type="button" variant="secondary" size="sm" class="bg-transparent border-gray-300 text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-white/5" data-form-preview-action="print">
                                <i class="mdi mdi-printer"></i>
                                Imprimir
                            </x-tailadmin-button>
                            <x-tailadmin-button type="button" variant="secondary" size="sm" class="bg-transparent border-gray-300 text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-white/5" data-form-preview-action="close">
                                <i class="mdi mdi-close"></i>
                                Fechar
                            </x-tailadmin-button>
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
                        <x-tailadmin-button variant="secondary" size="md" href="{{ workspace_route('tenant.forms.show', ['form' => $form->id]) }}"
                            class="bg-transparent border-gray-300 text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-white/5">
                            <i class="mdi mdi-arrow-left"></i>
                            Voltar
                        </x-tailadmin-button>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
