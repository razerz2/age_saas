@extends('layouts.freedash.app')
@section('title', 'Editar Template de Email Tenant')

@section('content')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-7 align-self-center">
                <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">Editar Template de Email Tenant</h4>
                <div class="d-flex align-items-center">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb m-0 p-0">
                            <li class="breadcrumb-item"><a href="{{ route('Platform.dashboard') }}" class="text-muted">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('Platform.tenant-email-templates.index') }}" class="text-muted">Email Tenant</a></li>
                            <li class="breadcrumb-item text-muted active" aria-current="page">Editar</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="card shadow-sm border-0">
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="alert alert-info">
                    Escopo atual: Tenant (baseline). Este cadastro não incorpora estrutura de layout.
                </div>

                <form method="POST" action="{{ route('Platform.tenant-email-templates.update', $template) }}">
                    @csrf
                    @method('PUT')
                    @include('platform.email_templates._form', [
                        'template' => $template,
                        'indexRouteName' => 'Platform.tenant-email-templates.index',
                        'restoreRouteName' => 'Platform.tenant-email-templates.restore',
                    ])
                </form>
            </div>
        </div>
    </div>

    @include('layouts.freedash.footer')
@endsection
