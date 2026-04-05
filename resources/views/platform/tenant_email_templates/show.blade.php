@extends('layouts.freedash.app')
@section('title', 'Detalhes Template de Email Tenant')

@section('content')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-7 align-self-center">
                <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">Detalhes Template de Email Tenant</h4>
                <div class="d-flex align-items-center">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb m-0 p-0">
                            <li class="breadcrumb-item"><a href="{{ route('Platform.dashboard') }}" class="text-muted">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('Platform.tenant-email-templates.index') }}" class="text-muted">Email Tenant</a></li>
                            <li class="breadcrumb-item text-muted active" aria-current="page">Detalhes</li>
                        </ol>
                    </nav>
                </div>
            </div>
            <div class="col-5 align-self-center text-end">
                <div class="d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#tenantEmailTestSendModal">
                        <i class="fas fa-vial me-1"></i> Testar envio
                    </button>
                    <a href="{{ route('Platform.tenant-email-templates.edit', $template) }}" class="btn btn-warning text-white">
                        <i class="fas fa-edit me-1"></i> Editar
                    </a>
                    <a href="{{ route('Platform.tenant-email-templates.index') }}" class="btn btn-outline-secondary">Voltar</a>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if($errors->any() && !$errors->has('destination_email'))
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="alert alert-info">
            Escopo atual: Tenant. O teste envia somente assunto e body do template atual, sem uso de módulo WhatsApp e sem layout estrutural.
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong>Key:</strong>
                        <div><code>{{ $template->name }}</code></div>
                    </div>
                    <div class="col-md-4">
                        <strong>Nome:</strong>
                        <div>{{ $template->display_name }}</div>
                    </div>
                    <div class="col-md-4">
                        <strong>Status:</strong>
                        <div>
                            <span class="badge bg-{{ $template->enabled ? 'success' : 'secondary' }}">
                                {{ $template->enabled ? 'ATIVO' : 'INATIVO' }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Atualizado em:</strong>
                        <div>{{ $template->updated_at?->format('d/m/Y H:i') ?? '-' }}</div>
                    </div>
                    <div class="col-md-6">
                        <strong>Criado em:</strong>
                        <div>{{ $template->created_at?->format('d/m/Y H:i') ?? '-' }}</div>
                    </div>
                </div>

                <div class="mb-3">
                    <strong>Assunto:</strong>
                    <pre class="border rounded p-3 mt-2 bg-light">{{ $template->subject }}</pre>
                </div>

                <div class="mb-0">
                    <strong>Body/Conteúdo:</strong>
                    <pre class="border rounded p-3 mt-2 bg-light">{{ $template->body }}</pre>
                </div>
            </div>
        </div>
    </div>

    @include('platform.email_templates._test_send_modal', [
        'template' => $template,
        'modalId' => 'tenantEmailTestSendModal',
        'routeName' => 'Platform.tenant-email-templates.test-send',
    ])

    @include('layouts.freedash.footer')
@endsection
