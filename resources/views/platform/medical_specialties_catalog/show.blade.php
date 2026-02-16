@extends('layouts.freedash.app')
@section('title', 'Visualizar Especialidades Médicas')

@section('content')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-7 align-self-center">
                <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">Visualizar Especialidade</h4>
                <div class="d-flex align-items-center">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb m-0 p-0">
                            <li class="breadcrumb-item"><a href="{{ route('Platform.dashboard') }}" class="text-muted">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item"><a href="{{ route('Platform.medical_specialties_catalog.index') }}"
                                    class="text-muted">Especialidades</a></li>
                            <li class="breadcrumb-item text-muted active" aria-current="page">Visualizar</li>
                        </ol>
                    </nav>
                </div>
            </div>
            <div class="col-5 align-self-center">
                <div class="customize-input float-end">
                    <a href="{{ route('Platform.medical_specialties_catalog.index') }}" class="btn btn-secondary shadow-sm">
                        <i class="fa fa-arrow-left me-1"></i> Voltar
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h4 class="card-title mb-4">Detalhes da Especialidade</h4>

                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <tbody>
                                    <tr>
                                        <th style="width: 25%;">Nome</th>
                                        <td>{{ $medical_specialties_catalog->name }}</td>
                                    </tr>
                                    <tr>
                                        <th>Código CBO</th>
                                        <td>{{ $medical_specialties_catalog->code ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Tipo</th>
                                        <td>
                                            @if ($medical_specialties_catalog->type === 'medical_specialty')
                                                <span class="badge bg-primary">Especialidade Médica</span>
                                            @elseif ($medical_specialties_catalog->type === 'health_profession')
                                                <span class="badge bg-success">Profissão da Saúde</span>
                                            @else
                                                <span class="badge bg-secondary">Outro</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Criada em</th>
                                        <td>{{ $medical_specialties_catalog->created_at->format('d/m/Y H:i') }}</td>
                                    </tr>
                                    <tr>
                                        <th>Última Atualização</th>
                                        <td>{{ $medical_specialties_catalog->updated_at->format('d/m/Y H:i') }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="text-end mt-3">
                            <a href="{{ route('Platform.medical_specialties_catalog.edit', $medical_specialties_catalog) }}"
                                class="btn btn-warning text-white shadow-sm">
                                <i class="fa fa-edit me-1"></i> Editar
                            </a>
                            <a href="{{ route('Platform.medical_specialties_catalog.index') }}"
                                class="btn btn-secondary">Voltar</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('layouts.freedash.footer')
@endsection
