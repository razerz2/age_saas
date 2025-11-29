@extends('layouts.connect_plus.app')

@section('title', 'Detalhes do Formulário')

@section('content')

    <div class="page-header">
        <h3 class="page-title"> Detalhes do Formulário </h3>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('tenant.dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('tenant.forms.index') }}">Formulários</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Detalhes</li>
            </ol>
        </nav>
    </div>

    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Detalhes</h4>

                    <p><strong>ID:</strong> {{ $form->id }}</p>
                    <p><strong>Nome:</strong> {{ $form->name }}</p>
                    <p><strong>Descrição:</strong> {{ $form->description ?? 'N/A' }}</p>
                    <p><strong>Especialidade:</strong> {{ $form->specialty->name ?? 'N/A' }}</p>
                    <p><strong>Médico:</strong> {{ $form->doctor->user->name ?? 'N/A' }}</p>
                    <p><strong>Status:</strong> 
                        @if ($form->is_active)
                            <span class="badge bg-success">Ativo</span>
                        @else
                            <span class="badge bg-danger">Inativo</span>
                        @endif
                    </p>
                    <p><strong>Criado em:</strong> {{ $form->created_at }}</p>
                    
                    @if($sectionsCount > 0 || $questionsCount > 0)
                        <p class="text-success">
                            <i class="mdi mdi-check-circle me-1"></i>
                            Formulário possui {{ $sectionsCount }} seção(ões) e {{ $questionsCount }} pergunta(s)
                        </p>
                    @else
                        <p class="text-muted">
                            <i class="mdi mdi-information-outline me-1"></i>
                            Este formulário ainda não possui conteúdo. Use o botão "Construir" na lista para adicionar seções e perguntas.
                        </p>
                    @endif

                    <div class="mt-3">
                        @if($sectionsCount > 0 || $questionsCount > 0)
                            <a href="{{ route('tenant.forms.preview', $form->id) }}" class="btn btn-primary" target="_blank">
                                <i class="mdi mdi-eye me-1"></i>
                                Visualizar Formulário
                            </a>
                        @endif
                        <a href="{{ route('tenant.forms.edit', $form->id) }}" class="btn btn-warning">
                            <i class="mdi mdi-pencil-outline me-1"></i>
                            Editar
                        </a>
                        <a href="{{ route('tenant.forms.index') }}" class="btn btn-light">
                            <i class="mdi mdi-arrow-left me-1"></i>
                            Voltar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

