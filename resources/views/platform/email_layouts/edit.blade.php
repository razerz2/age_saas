@extends('layouts.freedash.app')
@section('content')
    {{-- Importar CodeMirror para edição de HTML --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/theme/monokai.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/xml/xml.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/htmlmixed/htmlmixed.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/css/css.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/javascript/javascript.min.js"></script>

    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-7 align-self-center">
                <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">
                    Editar Layout: {{ $layout->display_name }}
                </h4>
                <div class="d-flex align-items-center">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb m-0 p-0">
                            <li class="breadcrumb-item"><a href="{{ route('Platform.dashboard') }}"
                                    class="text-muted">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('Platform.email-layouts.index') }}"
                                    class="text-muted">Layouts de Email</a></li>
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
                <div class="card shadow-sm border-0 mb-3">
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

                        <form method="POST" action="{{ route('Platform.email-layouts.update', $layout->id) }}" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            {{-- Nome (readonly) --}}
                            <div class="mb-3">
                                <label class="form-label">Nome</label>
                                <input type="text" class="form-control bg-light" value="{{ $layout->name }}" readonly>
                            </div>

                            {{-- Display Name --}}
                            <div class="mb-3">
                                <label class="form-label">Nome de Exibição <span class="text-danger">*</span></label>
                                <input type="text" name="display_name" class="form-control"
                                    value="{{ old('display_name', $layout->display_name) }}" required>
                            </div>

                            {{-- Logo --}}
                            <div class="mb-3">
                                <label class="form-label">Logo</label>
                                <small class="form-text text-muted d-block mb-2">
                                    Faça upload de uma imagem ou informe uma URL. Se preenchido, o logo será exibido no lugar do nome do app no cabeçalho.
                                </small>
                                
                                {{-- Upload de arquivo --}}
                                <div class="mb-2">
                                    <label class="form-label small">Upload de Imagem</label>
                                    <input type="file" name="logo_file" class="form-control" accept="image/*">
                                    <small class="form-text text-muted">Formatos: JPG, PNG, GIF, SVG, WEBP (máx. 2MB)</small>
                                </div>

                                {{-- Ou URL --}}
                                <div class="mb-2">
                                    <label class="form-label small">Ou URL Externa</label>
                                    <input type="url" name="logo_url" class="form-control"
                                        value="{{ old('logo_url', $layout->logo_url) }}"
                                        placeholder="https://exemplo.com/logo.png">
                                    <small class="form-text text-muted">Deixe vazio para remover o logo atual</small>
                                </div>

                                {{-- Preview do logo atual --}}
                                @if($layout->logo_url)
                                    @php
                                        $logoUrl = $layout->logo_url;
                                        // Garantir URL absoluta
                                        if (!preg_match('/^https?:\/\//', $logoUrl)) {
                                            if (strpos($logoUrl, '//') === 0) {
                                                $logoUrl = 'http:' . $logoUrl;
                                            } elseif (strpos($logoUrl, 'storage/') === 0) {
                                                $logoUrl = asset($logoUrl);
                                            } else {
                                                $logoUrl = asset('storage/' . $logoUrl);
                                            }
                                        }
                                        $logoWidth = $layout->logo_width ?? 200;
                                        $logoHeight = $layout->logo_height ? $layout->logo_height . 'px' : 'auto';
                                    @endphp
                                    <div class="mt-3">
                                        <label class="form-label small">Preview do Logo Atual:</label>
                                        <div class="border rounded p-2" style="background-color: #f8f9fa; min-height: 100px; display: flex; align-items: center; justify-content: center;">
                                            <img src="{{ $logoUrl }}" alt="Preview Logo" 
                                                 style="max-width: {{ $logoWidth }}px; height: {{ $logoHeight }}; display: block; margin: 0 auto;"
                                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                            <div style="display: none; text-align: center; color: #999; padding: 20px;">
                                                <i class="fas fa-image fa-2x mb-2"></i><br>
                                                <small>Imagem não pôde ser carregada</small><br>
                                                <small class="text-muted" style="font-size: 10px;">{{ $logoUrl }}</small>
                                            </div>
                                        </div>
                                        <div class="mt-2">
                                            <small class="text-muted d-block mb-1" style="font-size: 11px;">URL: <code style="font-size: 10px;">{{ $logoUrl }}</code></small>
                                            <button type="button" class="btn btn-sm btn-danger" onclick="removeLogo()">
                                                <i class="fas fa-trash me-1"></i>Remover Logo
                                            </button>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            {{-- Dimensões do Logo --}}
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Largura do Logo (px)</label>
                                    <small class="form-text text-muted d-block mb-2">
                                        Largura máxima do logo. Padrão: 200px
                                    </small>
                                    <input type="number" name="logo_width" class="form-control"
                                        value="{{ old('logo_width', $layout->logo_width ?? 200) }}"
                                        min="50" max="500" step="10">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Altura do Logo (px) <small class="text-muted">(opcional)</small></label>
                                    <small class="form-text text-muted d-block mb-2">
                                        Deixe vazio para manter proporção automática
                                    </small>
                                    <input type="number" name="logo_height" class="form-control"
                                        value="{{ old('logo_height', $layout->logo_height) }}"
                                        min="20" max="500" step="10">
                                </div>
                            </div>

                            {{-- Cores --}}
                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <label class="form-label">Cor Primária <span class="text-danger">*</span></label>
                                    <input type="color" name="primary_color" class="form-control form-control-color"
                                        value="{{ old('primary_color', $layout->primary_color) }}" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Cor Secundária <span class="text-danger">*</span></label>
                                    <input type="color" name="secondary_color" class="form-control form-control-color"
                                        value="{{ old('secondary_color', $layout->secondary_color) }}" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Cor de Fundo <span class="text-danger">*</span></label>
                                    <input type="color" name="background_color" class="form-control form-control-color"
                                        value="{{ old('background_color', $layout->background_color) }}" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Cor do Texto <span class="text-danger">*</span></label>
                                    <input type="color" name="text_color" class="form-control form-control-color"
                                        value="{{ old('text_color', $layout->text_color) }}" required>
                                </div>
                            </div>

                            {{-- Header --}}
                            <div class="mb-3">
                                <label class="form-label">Cabeçalho (HTML)</label>
                                <small class="form-text text-muted d-block mb-2">
                                    @verbatim
                                    Use variáveis como <code>{{app_name}}</code> que serão substituídas automaticamente.
                                    @endverbatim
                                </small>
                                <textarea id="headerEditor" name="header" class="form-control" rows="8">{{ old('header', $layout->header) }}</textarea>
                            </div>

                            {{-- Footer --}}
                            <div class="mb-3">
                                <label class="form-label">Rodapé (HTML)</label>
                                <small class="form-text text-muted d-block mb-2">
                                    @verbatim
                                    Use variáveis como <code>{{app_name}}</code> que serão substituídas automaticamente.
                                    @endverbatim
                                </small>
                                <textarea id="footerEditor" name="footer" class="form-control" rows="8">{{ old('footer', $layout->footer) }}</textarea>
                            </div>

                            {{-- Ativo --}}
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_active" id="is_active"
                                        value="1" {{ old('is_active', $layout->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        Layout Ativo (apenas um layout pode estar ativo por vez)
                                    </label>
                                </div>
                            </div>

                            {{-- Botões --}}
                            <div class="text-end mt-4">
                                <button type="submit" class="btn btn-success px-4">
                                    <i class="fas fa-save me-2"></i>Salvar Alterações
                                </button>
                                <a href="{{ route('Platform.email-layouts.index') }}" class="btn btn-secondary">Cancelar</a>
                                <a href="{{ route('Platform.email-layouts.preview', $layout->id) }}" 
                                   class="btn btn-info" target="_blank">
                                    <i class="fas fa-eye me-2"></i>Preview
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Card de Informações (Coluna 4) --}}
            <div class="col-md-4">
                <div class="card shadow-sm border-0 mb-3">
                    <div class="card-body">
                        <h5 class="card-title mb-3">
                            <i class="fas fa-info-circle me-2"></i>Informações
                        </h5>
                        <div class="alert alert-info">
                            <strong>Dicas:</strong>
                            <ul class="mb-0 mt-2">
                                <li>Use HTML inline com estilos CSS</li>
                                <li>Variáveis disponíveis: @verbatim<code>{{app_name}}</code>@endverbatim</li>
                                <li>O layout será aplicado a todos os emails de notificação</li>
                                <li>Teste sempre no preview antes de salvar</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="card-title mb-3">
                            <i class="fas fa-palette me-2"></i>Preview de Cores
                        </h5>
                        <div class="mb-2">
                            <label class="form-label small">Primária</label>
                            <div class="p-3 rounded" style="background-color: {{ $layout->primary_color }}; color: white;">
                                Cor Primária
                            </div>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">Secundária</label>
                            <div class="p-3 rounded" style="background-color: {{ $layout->secondary_color }}; color: white;">
                                Cor Secundária
                            </div>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">Fundo</label>
                            <div class="p-3 rounded" style="background-color: {{ $layout->background_color }}; color: {{ $layout->text_color }};">
                                Cor de Fundo
                            </div>
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
        .CodeMirror {
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
            height: auto;
            min-height: 200px;
        }
        .CodeMirror-scroll {
            min-height: 200px;
        }
    </style>
@endpush

@push('scripts')
    <script>
        $(function() {
            // Inicializar CodeMirror para Header
            const headerEditor = CodeMirror.fromTextArea(document.getElementById('headerEditor'), {
                mode: 'htmlmixed',
                theme: 'monokai',
                lineNumbers: true,
                lineWrapping: true,
                indentUnit: 2,
            });

            // Inicializar CodeMirror para Footer
            const footerEditor = CodeMirror.fromTextArea(document.getElementById('footerEditor'), {
                mode: 'htmlmixed',
                theme: 'monokai',
                lineNumbers: true,
                lineWrapping: true,
                indentUnit: 2,
            });

            // Atualizar preview de cores quando mudar
            $('input[type="color"]').on('change', function() {
                const color = $(this).val();
                const name = $(this).attr('name');
                
                if (name === 'primary_color' || name === 'secondary_color') {
                    // Atualizar preview
                    $('.card-body').find('div[style*="background-color"]').each(function() {
                        if ($(this).text().includes('Primária') && name === 'primary_color') {
                            $(this).css('background-color', color);
                        }
                        if ($(this).text().includes('Secundária') && name === 'secondary_color') {
                            $(this).css('background-color', color);
                        }
                    });
                }
            });
        });

        function removeLogo() {
            if (confirm('Deseja realmente remover o logo atual?')) {
                // Limpar campos de logo
                $('input[name="logo_file"]').val('');
                $('input[name="logo_url"]').val('');
                
                // Esconder preview
                $('.mt-3').first().hide();
                
                // Adicionar campo hidden para indicar remoção
                if ($('input[name="remove_logo"]').length === 0) {
                    $('form').append('<input type="hidden" name="remove_logo" value="1">');
                }
            }
        }
    </script>
@endpush

