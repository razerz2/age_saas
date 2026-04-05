@extends('layouts.freedash.app')
@section('title', $pageTitle)

@php
    $statusBadge = [
        'approved' => 'success',
        'pending' => 'warning',
        'draft' => 'secondary',
        'rejected' => 'danger',
        'archived' => 'dark',
    ];
@endphp

@section('content')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-7 align-self-center">
                <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">{{ $pageTitle }}</h4>
                <div class="d-flex align-items-center">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb m-0 p-0">
                            <li class="breadcrumb-item"><a href="{{ route('Platform.dashboard') }}" class="text-muted">Dashboard</a></li>
                            <li class="breadcrumb-item text-muted active" aria-current="page">{{ $breadcrumbLabel }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
            <div class="col-5 align-self-center text-end">
                <a href="{{ route($backRouteName) }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Voltar para Templates
                </a>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="alert alert-info">
            {{ $introMessage }}
        </div>
        @if(!$bindingsStorageReady)
            <div class="alert alert-danger">
                Estrutura de vínculos oficiais ainda não foi criada neste ambiente. Execute as migrations pendentes.
            </div>
        @endif
        <div class="alert alert-warning">
            Cada key/evento aceita apenas <strong>1 template oficial ativo</strong> neste escopo.
            A troca de vínculo não apaga templates antigos.
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered align-middle">
                        <thead class="bg-light">
                            <tr>
                                <th>Key</th>
                                <th>Evento</th>
                                <th>Template Ativo</th>
                                <th>Nome Meta</th>
                                <th>Idioma</th>
                                <th>Status</th>
                                <th>Provider</th>
                                <th style="min-width: 360px;">Alterar Vínculo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($events as $event)
                                @php
                                    $eventKey = (string) ($event['key'] ?? '');
                                    $binding = $bindingsByEvent[$eventKey] ?? null;
                                    $currentTemplate = $binding?->officialTemplate;
                                    $options = $templatesByEvent[$eventKey] ?? collect();
                                @endphp
                                <tr>
                                    <td><code>{{ $eventKey }}</code></td>
                                    <td>{{ (string) ($event['label'] ?? $eventKey) }}</td>
                                    <td>
                                        @if($currentTemplate)
                                            v{{ (int) $currentTemplate->version }} (ID {{ $currentTemplate->id }})
                                        @else
                                            <span class="text-muted">Nenhum vínculo ativo</span>
                                        @endif
                                    </td>
                                    <td>{{ $currentTemplate?->meta_template_name ?? '-' }}</td>
                                    <td>{{ $currentTemplate?->language ?? '-' }}</td>
                                    <td>
                                        @if($currentTemplate)
                                            <span class="badge bg-{{ $statusBadge[(string) $currentTemplate->status] ?? 'secondary' }}">
                                                {{ strtoupper((string) $currentTemplate->status) }}
                                            </span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ $currentTemplate?->provider ?? '-' }}</td>
                                    <td>
                                        @if($options->isEmpty())
                                            <span class="text-muted">Sem templates APPROVED elegíveis para esta key.</span>
                                        @else
                                            <form action="{{ route($upsertRouteName) }}" method="POST" class="d-flex gap-2">
                                                @csrf
                                                <input type="hidden" name="event_key" value="{{ $eventKey }}">
                                                <select name="whatsapp_official_template_id" class="form-control" required>
                                                    @foreach($options as $option)
                                                        <option value="{{ $option->id }}"
                                                            {{ (string) ($currentTemplate?->id) === (string) $option->id ? 'selected' : '' }}>
                                                            v{{ (int) $option->version }} - {{ $option->meta_template_name }} ({{ strtoupper((string) $option->status) }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <button type="submit" class="btn btn-primary">
                                                    Salvar
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @include('layouts.freedash.footer')
@endsection
