@extends('layouts.connect_plus.app')

@section('title', 'Detalhes do Formulário')

@section('content')

    <div class="page-header">
        <h3 class="page-title">
            <i class="mdi mdi-file-document-edit text-primary me-2"></i>
            Detalhes do Formulário
        </h3>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ workspace_route('tenant.dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ workspace_route('tenant.forms.index') }}">Formulários</a>
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
                            Informações do Formulário
                        </h4>
                        <div>
                            <a href="{{ workspace_route('tenant.forms.edit', ['form' => $form->id]) }}" class="btn btn-warning btn-sm">
                                <i class="mdi mdi-pencil me-1"></i> Editar
                            </a>
                            <a href="{{ workspace_route('tenant.forms.index') }}" class="btn btn-secondary btn-sm">
                                <i class="mdi mdi-arrow-left me-1"></i> Voltar
                            </a>
                        </div>
                    </div>

                    {{-- Status Badge --}}
                    <div class="mb-4">
                        @if ($form->is_active)
                            <span class="badge bg-success px-3 py-2">
                                <i class="mdi mdi-check-circle me-1"></i> Ativo
                            </span>
                        @else
                            <span class="badge bg-danger px-3 py-2">
                                <i class="mdi mdi-close-circle me-1"></i> Inativo
                            </span>
                        @endif
                    </div>

                    {{-- Informações Principais --}}
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <div class="border rounded p-3 h-100">
                                <label class="text-muted small mb-1 d-block">
                                    <i class="mdi mdi-identifier me-1"></i> ID
                                </label>
                                <p class="mb-0 fw-semibold">{{ $form->id }}</p>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="border rounded p-3 h-100">
                                <label class="text-muted small mb-1 d-block">
                                    <i class="mdi mdi-format-title me-1"></i> Nome
                                </label>
                                <p class="mb-0 fw-semibold">{{ $form->name }}</p>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="border rounded p-3 h-100">
                                <label class="text-muted small mb-1 d-block">
                                    <i class="mdi mdi-medical-bag me-1"></i> Especialidade
                                </label>
                                <p class="mb-0 fw-semibold">{{ $form->specialty->name ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="border rounded p-3 h-100">
                                <label class="text-muted small mb-1 d-block">
                                    <i class="mdi mdi-doctor me-1"></i> Médico
                                </label>
                                <p class="mb-0 fw-semibold">{{ $form->doctor->user->name ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Descrição --}}
                    @if($form->description)
                        <div class="mb-4">
                            <label class="text-muted small mb-2 d-block">
                                <i class="mdi mdi-text me-1"></i> Descrição
                            </label>
                            <div class="border rounded p-3 bg-light">
                                <p class="mb-0">{{ $form->description }}</p>
                            </div>
                        </div>
                    @endif

                    {{-- Estatísticas do Formulário --}}
                    <div class="mb-4">
                        @if($sectionsCount > 0 || $questionsCount > 0)
                            <div class="alert alert-success">
                                <div class="d-flex align-items-center">
                                    <i class="mdi mdi-check-circle me-2 fs-4"></i>
                                    <div>
                                        <strong>Formulário configurado</strong>
                                        <p class="mb-0">Possui {{ $sectionsCount }} seção(ões) e {{ $questionsCount }} pergunta(s)</p>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="alert alert-warning">
                                <i class="mdi mdi-information-outline me-2"></i>
                                Este formulário ainda não possui conteúdo. Use o botão "Construir" na lista para adicionar seções e perguntas.
                            </div>
                        @endif
                    </div>

                    {{-- Botões de Ação --}}
                    <div class="border-top pt-3">
                        <div class="d-flex gap-2 flex-wrap">
                            @if($sectionsCount > 0 || $questionsCount > 0)
                                <a href="{{ workspace_route('tenant.forms.preview', $form->id) }}" class="btn btn-primary" target="_blank">
                                    <i class="mdi mdi-eye me-1"></i>
                                    Visualizar Formulário
                                </a>
                            @endif
                            <a href="{{ workspace_route('tenant.forms.builder', $form->id) }}" class="btn btn-info">
                                <i class="mdi mdi-tools me-1"></i>
                                Construir Formulário
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

