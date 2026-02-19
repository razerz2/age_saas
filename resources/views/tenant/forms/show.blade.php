@extends('layouts.tailadmin.app')

@section('title', 'Detalhes do Formulário')
@section('page', 'forms')

@section('content')

    <div class="page-header">
        <h3 class="page-title">
            <x-icon name="file-document-edit" class=" text-primary me-2" />
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
                            <x-icon name="file-document" class=" text-primary me-2" />
                            Informações do Formulário
                        </h4>
                        <div class="flex items-center gap-2">
                            <x-tailadmin-button variant="warning" size="sm" href="{{ workspace_route('tenant.forms.edit', ['form' => $form->id]) }}">
                                <x-icon name="pencil" class="" /> Editar
                            </x-tailadmin-button>
                            <x-tailadmin-button variant="secondary" size="sm" href="{{ workspace_route('tenant.forms.index') }}"
                                class="bg-transparent border-gray-300 text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-white/5">
                                <x-icon name="arrow-left" class="" /> Voltar
                            </x-tailadmin-button>
                        </div>
                    </div>

                    {{-- Status Badge --}}
                    <div class="mb-4">
                        @if ($form->is_active)
                            <span class="badge bg-success px-3 py-2">
                                <x-icon name="check-circle" class=" me-1" /> Ativo
                            </span>
                        @else
                            <span class="badge bg-danger px-3 py-2">
                                <x-icon name="close-circle" class=" me-1" /> Inativo
                            </span>
                        @endif
                    </div>

                    {{-- Informações Principais --}}
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <div class="border rounded p-3 h-100">
                                <label class="text-muted small mb-1 d-block">
                                    <x-icon name="identifier" class=" me-1" /> ID
                                </label>
                                <p class="mb-0 fw-semibold">{{ $form->id }}</p>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="border rounded p-3 h-100">
                                <label class="text-muted small mb-1 d-block">
                                    <x-icon name="format-title" class=" me-1" /> Nome
                                </label>
                                <p class="mb-0 fw-semibold">{{ $form->name }}</p>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="border rounded p-3 h-100">
                                <label class="text-muted small mb-1 d-block">
                                    <x-icon name="medical-bag" class=" me-1" /> Especialidade
                                </label>
                                <p class="mb-0 fw-semibold">{{ $form->specialty->name ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="border rounded p-3 h-100">
                                <label class="text-muted small mb-1 d-block">
                                    <x-icon name="doctor" class=" me-1" /> Médico
                                </label>
                                <p class="mb-0 fw-semibold">{{ $form->doctor->user->name ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Descrição --}}
                    @if($form->description)
                        <div class="mb-4">
                            <label class="text-muted small mb-2 d-block">
                                <x-icon name="text" class=" me-1" /> Descrição
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
                                    <x-icon name="check-circle" class=" me-2 fs-4" />
                                    <div>
                                        <strong>Formulário configurado</strong>
                                        <p class="mb-0">Possui {{ $sectionsCount }} seção(ões) e {{ $questionsCount }} pergunta(s)</p>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="alert alert-warning">
                                <x-icon name="information-outline" class=" me-2" />
                                Este formulário ainda não possui conteúdo. Use o botão "Construir" na lista para adicionar seções e perguntas.
                            </div>
                        @endif
                    </div>

                    {{-- Botões de Ação --}}
                    <div class="border-top pt-3">
                        <div class="flex items-center justify-end gap-3 flex-nowrap">
                            @if($sectionsCount > 0 || $questionsCount > 0)
                                <x-tailadmin-button variant="primary" size="md" href="{{ workspace_route('tenant.forms.preview', $form->id) }}" target="_blank" class="inline-flex items-center gap-2">
                                    <x-icon name="eye" class="" />
                                    Visualizar Formulário
                                </x-tailadmin-button>
                            @endif
                            <x-tailadmin-button variant="secondary" size="md" href="{{ workspace_route('tenant.forms.builder', $form->id) }}"
                                class="inline-flex items-center gap-2 border-info text-info bg-info/10 hover:bg-info/20 dark:border-info/40 dark:text-info dark:hover:bg-info/30">
                                <x-icon name="tools" class="" />
                                Construir Formulário
                            </x-tailadmin-button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
