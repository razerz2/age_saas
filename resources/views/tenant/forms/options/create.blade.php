@extends('layouts.tailadmin.app')

@section('title', 'Criar Opção')

@section('content')

    <div class="page-header">
        <h3 class="page-title"> Criar Opção </h3>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ workspace_route('tenant.dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ workspace_route('tenant.forms.index') }}">Formulários</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Criar Opção</li>
            </ol>
        </nav>
    </div>

    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Nova Opção</h4>
                    <p class="card-description"> Preencha os dados abaixo </p>

                    <form class="forms-sample" action="{{ workspace_route('tenant.forms.options.store', $question->id) }}" method="POST">
                        @csrf

                        <div class="form-group">
                            <label>Pergunta</label>
                            <input type="text" class="form-control" value="{{ $question->label }}" disabled>
                            <input type="hidden" name="question_id" value="{{ $question->id }}">
                        </div>

                        <div class="form-group">
                            <label>Label</label>
                            <input type="text" class="form-control" name="label" required>
                        </div>

                        <div class="form-group">
                            <label>Valor</label>
                            <input type="text" class="form-control" name="value" required>
                        </div>

                        <div class="form-group">
                            <label>Posição</label>
                            <input type="number" class="form-control" name="position" min="0" value="0">
                        </div>

                        <div class="flex flex-wrap items-center gap-3">
                            <x-tailadmin-button type="submit" variant="primary">
                                Salvar
                            </x-tailadmin-button>
                            <x-tailadmin-button variant="secondary" size="md" href="{{ workspace_route('tenant.forms.show', ['form' => $question->form_id]) }}"
                                class="bg-transparent border-gray-300 text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-white/5">
                                Cancelar
                            </x-tailadmin-button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

@endsection

