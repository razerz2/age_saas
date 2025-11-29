@extends('layouts.connect_plus.app')

@section('title', 'Criar Pergunta')

@section('content')

    <div class="page-header">
        <h3 class="page-title"> Criar Pergunta </h3>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('tenant.dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('tenant.forms.index') }}">Formulários</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Criar Pergunta</li>
            </ol>
        </nav>
    </div>

    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Nova Pergunta</h4>
                    <p class="card-description"> Preencha os dados abaixo </p>

                    <form class="forms-sample" action="{{ route('tenant.forms.questions.store', $form->id) }}" method="POST">
                        @csrf

                        <div class="form-group">
                            <label>Formulário</label>
                            <input type="text" class="form-control" value="{{ $form->name }}" disabled>
                            <input type="hidden" name="form_id" value="{{ $form->id }}">
                        </div>

                        <div class="form-group">
                            <label>Seção</label>
                            <select name="section_id" class="form-control">
                                <option value="">Sem seção</option>
                                @foreach($sections as $section)
                                    <option value="{{ $section->id }}">{{ $section->title }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Label</label>
                            <input type="text" class="form-control" name="label" required>
                        </div>

                        <div class="form-group">
                            <label>Texto de Ajuda</label>
                            <textarea class="form-control" name="help_text" rows="2"></textarea>
                        </div>

                        <div class="form-group">
                            <label>Tipo</label>
                            <select name="type" class="form-control" required>
                                <option value="text">Texto</option>
                                <option value="textarea">Área de Texto</option>
                                <option value="number">Número</option>
                                <option value="email">E-mail</option>
                                <option value="date">Data</option>
                                <option value="select">Seleção</option>
                                <option value="radio">Radio</option>
                                <option value="checkbox">Checkbox</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Obrigatória</label>
                            <select name="required" class="form-control">
                                <option value="1">Sim</option>
                                <option value="0">Não</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Posição</label>
                            <input type="number" class="form-control" name="position" min="0" value="0">
                        </div>

                        <button type="submit" class="btn btn-primary me-2">Salvar</button>
                        <a href="{{ route('tenant.forms.show', $form->id) }}" class="btn btn-light">Cancelar</a>
                    </form>

                </div>
            </div>
        </div>
    </div>

@endsection

