@extends('layouts.freedash.app')
@section('title', 'Import Results Clinic Networks')

@section('content')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-7 align-self-center">
                <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">Resultado da Importação</h4>
                <div class="d-flex align-items-center">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb m-0 p-0">
                            <li class="breadcrumb-item">
                                <a href="{{ route('Platform.dashboard') }}" class="text-muted">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item text-muted">
                                <a href="{{ route('Platform.clinic-networks.index') }}" class="text-muted">Redes de Clínicas</a>
                            </li>
                            <li class="breadcrumb-item text-muted">
                                <a href="{{ route('Platform.clinic-networks.edit', $network->id) }}" class="text-muted">{{ $network->name }}</a>
                            </li>
                            <li class="breadcrumb-item text-muted active" aria-current="page">Resultados</li>
                        </ol>
                    </nav>
                </div>
            </div>
            <div class="col-5 align-self-center text-end">
                <a href="{{ route('Platform.clinic-networks.edit', $network->id) }}" class="btn btn-primary shadow-sm">
                    <i class="fa fa-arrow-left me-1"></i> Concluir e Voltar
                </a>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            {{-- Sucessos --}}
            <div class="col-md-6">
                <div class="card shadow-sm border-success">
                    <div class="card-header bg-success text-white">
                        <h5 class="card-title mb-0 text-white"><i class="fas fa-check-circle me-2"></i>Sucesso ({{ count($results['success']) }})</h5>
                    </div>
                    <div class="card-body">
                        @if(empty($results['success']))
                            <p class="text-muted">Nenhuma clínica foi importada com sucesso.</p>
                        @else
                            <ul class="list-group list-group-flush">
                                @foreach($results['success'] as $success)
                                    <li class="list-group-item text-success">{{ $success }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Erros --}}
            <div class="col-md-6">
                <div class="card shadow-sm border-danger">
                    <div class="card-header bg-danger text-white">
                        <h5 class="card-title mb-0 text-white"><i class="fas fa-times-circle me-2"></i>Falhas ({{ count($results['errors']) }})</h5>
                    </div>
                    <div class="card-body">
                        @if(empty($results['errors']))
                            <p class="text-muted">Nenhuma falha registrada.</p>
                        @else
                            <ul class="list-group list-group-flush">
                                @foreach($results['errors'] as $error)
                                    <li class="list-group-item text-danger">{{ $error }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

@include("layouts.freedash.footer")
@endsection

