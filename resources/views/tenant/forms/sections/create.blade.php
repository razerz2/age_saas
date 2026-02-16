@extends('layouts.tailadmin.app')

@section('title', 'Criar Seção')

@section('content')

    <div class="page-header">
        <h3 class="page-title"> Criar Seção </h3>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ workspace_route('tenant.dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ workspace_route('tenant.forms.index') }}">Formulários</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Criar Seção</li>
            </ol>
        </nav>
    </div>

    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Nova Seção</h4>
                    <p class="card-description"> Preencha os dados abaixo </p>

                    <form class="forms-sample" action="{{ workspace_route('tenant.forms.sections.store', $form->id) }}" method="POST">
                        @csrf

                        <div class="form-group">
                            <label>Título</label>
                            <input type="text" class="form-control" name="title" required>
                        </div>

                        <div class="form-group">
                            <label>Posição</label>
                            <input type="number" class="form-control" name="position" min="0" value="0">
                        </div>

                        <div class="flex flex-wrap items-center gap-3">
                            <x-tailadmin-button type="submit" variant="primary">
                                Salvar
                            </x-tailadmin-button>
                            <x-tailadmin-button variant="secondary" size="md" href="{{ workspace_route('tenant.forms.show', ['form' => $form->id]) }}"
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

