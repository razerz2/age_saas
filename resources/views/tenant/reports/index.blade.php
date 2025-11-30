@extends('layouts.connect_plus.app')

@section('title', 'Relatórios')

@section('content')

<div class="page-header">
    <h3 class="page-title">Relatórios</h3>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Relatórios</li>
        </ol>
    </nav>
</div>

<div class="row">
    <div class="col-md-4 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="icon-bg bg-primary">
                            <i class="mdi mdi-calendar-clock"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-1">Agendamentos</h6>
                        <p class="text-muted mb-0">Relatórios de agendamentos</p>
                    </div>
                </div>
                <a href="{{ route('tenant.reports.appointments') }}" class="btn btn-primary btn-sm mt-3 w-100">
                    Acessar <i class="mdi mdi-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-4 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="icon-bg bg-success">
                            <i class="mdi mdi-account-heart"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-1">Pacientes</h6>
                        <p class="text-muted mb-0">Relatórios de pacientes</p>
                    </div>
                </div>
                <a href="{{ route('tenant.reports.patients') }}" class="btn btn-success btn-sm mt-3 w-100">
                    Acessar <i class="mdi mdi-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-4 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="icon-bg bg-info">
                            <i class="mdi mdi-stethoscope"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-1">Médicos</h6>
                        <p class="text-muted mb-0">Relatórios de médicos</p>
                    </div>
                </div>
                <a href="{{ route('tenant.reports.doctors') }}" class="btn btn-info btn-sm mt-3 w-100">
                    Acessar <i class="mdi mdi-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-4 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="icon-bg bg-warning">
                            <i class="mdi mdi-calendar-repeat"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-1">Recorrências</h6>
                        <p class="text-muted mb-0">Relatórios de recorrências</p>
                    </div>
                </div>
                <a href="{{ route('tenant.reports.recurring') }}" class="btn btn-warning btn-sm mt-3 w-100">
                    Acessar <i class="mdi mdi-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-4 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="icon-bg bg-danger">
                            <i class="mdi mdi-file-document-edit"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-1">Formulários</h6>
                        <p class="text-muted mb-0">Relatórios de formulários</p>
                    </div>
                </div>
                <a href="{{ route('tenant.reports.forms') }}" class="btn btn-danger btn-sm mt-3 w-100">
                    Acessar <i class="mdi mdi-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-4 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="icon-bg bg-secondary">
                            <i class="mdi mdi-account-circle"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-1">Portal do Paciente</h6>
                        <p class="text-muted mb-0">Relatórios do portal</p>
                    </div>
                </div>
                <a href="{{ route('tenant.reports.portal') }}" class="btn btn-secondary btn-sm mt-3 w-100">
                    Acessar <i class="mdi mdi-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-4 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="icon-bg bg-dark">
                            <i class="mdi mdi-bell"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-1">Notificações</h6>
                        <p class="text-muted mb-0">Relatórios de notificações</p>
                    </div>
                </div>
                <a href="{{ route('tenant.reports.notifications') }}" class="btn btn-dark btn-sm mt-3 w-100">
                    Acessar <i class="mdi mdi-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
</div>

@endsection

