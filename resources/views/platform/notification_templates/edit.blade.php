@extends('layouts.freedash.app')
@section('content')
    {{-- Importar Trix Editor --}}
    <link rel="stylesheet" href="https://unpkg.com/trix@2.0.0/dist/trix.css">
    <script src="https://unpkg.com/trix@2.0.0/dist/trix.umd.min.js"></script>

    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-7 align-self-center">
                <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">
                    Editar Template: {{ $template->display_name }}
                </h4>
                <div class="d-flex align-items-center">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb m-0 p-0">
                            <li class="breadcrumb-item"><a href="{{ route('Platform.dashboard') }}"
                                    class="text-muted">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('Platform.notification-templates.index') }}"
                                    class="text-muted">Templates de Notificação</a></li>
                            <li class="breadcrumb-item text-muted active" aria-current="page">Editar</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            {{-- Formulário (Coluna 8) --}}
            <div class="col-md-8">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        {{-- ✅ Alertas de sucesso --}}
                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        {{-- 🔹 Exibição de erros de validação --}}
                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                                <strong>Ops!</strong> Verifique os erros abaixo:
                                <ul class="mt-2 mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('Platform.notification-templates.update', $template->id) }}">
                            @csrf
                            @method('PUT')

                            {{-- Nome (readonly) --}}
                            <div class="mb-3">
                                <label class="form-label">Nome</label>
                                <input type="text" class="form-control bg-light" value="{{ $template->name }}" readonly>
                            </div>

                            {{-- Canal (readonly) --}}
                            <div class="mb-3">
                                <label class="form-label">Canal</label>
                                <input type="text" class="form-control bg-light"
                                    value="{{ $template->channel === 'email' ? 'Email' : 'WhatsApp' }}" readonly>
                            </div>

                            {{-- Subject (apenas email) --}}
                            @if ($template->channel === 'email')
                                <div class="mb-3">
                                    <label class="form-label">Assunto <span class="text-danger">*</span></label>
                                    <input type="text" name="subject" class="form-control"
                                        value="{{ old('subject', $template->subject) }}" required>
                                    <small class="form-text text-muted">Use {{ '{{variavel}}' }} para variáveis</small>
                                </div>
                            @endif

                            {{-- Body --}}
                            <div class="mb-3">
                                <label class="form-label">Corpo da Mensagem <span class="text-danger">*</span></label>
                                @if ($template->channel === 'email')
                                    {{-- Trix Editor para Email --}}
                                    <input id="bodyField" type="hidden" name="body" value="{{ old('body', $template->body) }}">
                                    <trix-editor input="bodyField" class="trix-content"></trix-editor>
                                    <small class="form-text text-muted">Use {{ '{{variavel}}' }} para variáveis</small>
                                @else
                                    {{-- Textarea para WhatsApp --}}
                                    <textarea name="body" class="form-control h-56" rows="10" required>{{ old('body', $template->body) }}</textarea>
                                    <small class="form-text text-muted">Use {{ '{{variavel}}' }} para variáveis. WhatsApp suporta apenas texto simples.</small>
                                @endif
                            </div>

                            {{-- Botões --}}
                            <div class="text-end mt-4">
                                <button type="submit" class="btn btn-success px-4">
                                    <i class="fas fa-save me-2"></i>Salvar Alterações
                                </button>
                                <a href="{{ route('Platform.notification-templates.index') }}" class="btn btn-secondary">Cancelar</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Card de Variáveis (Coluna 4) --}}
            <div class="col-md-4">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="card-title mb-3">
                            <i class="fas fa-info-circle me-2"></i>Variáveis Disponíveis
                        </h5>
                        <div class="list-group">
                            @if (is_array($template->variables))
                                @foreach ($template->variables as $variable)
                                    <div class="list-group-item">
                                        <code>{{ '{{' . $variable . '}}' }}</code>
                                    </div>
                                @endforeach
                            @else
                                <div class="list-group-item text-muted">
                                    Nenhuma variável definida
                                </div>
                            @endif
                        </div>
                        <div class="mt-3">
                            <form action="{{ route('Platform.notification-templates.restore', $template->id) }}"
                                method="POST"
                                onsubmit="return confirmSubmit(event, 'Deseja restaurar este template para os valores padrão? Todas as alterações serão perdidas.', 'Restaurar Padrão')">
                                @csrf
                                <button type="submit" class="btn btn-warning w-100">
                                    <i class="fas fa-arrow-path me-2"></i>Restaurar Padrão
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('layouts.freedash.footer')
@endsection

@push('styles')
    <style>
        .trix-content {
            min-height: 300px;
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
            padding: 0.75rem;
        }
    </style>
@endpush

