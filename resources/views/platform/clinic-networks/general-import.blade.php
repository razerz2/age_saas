@extends('layouts.freedash.app')
@section('title', 'General Import Clinic Networks')

@section('content')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-7 align-self-center">
                <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">Importar Clínicas</h4>
                <div class="d-flex align-items-center">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb m-0 p-0">
                            <li class="breadcrumb-item">
                                <a href="{{ route('Platform.dashboard') }}" class="text-muted">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item text-muted">
                                <a href="{{ route('Platform.clinic-networks.index') }}" class="text-muted">Redes de Clínicas</a>
                            </li>
                            <li class="breadcrumb-item text-muted active" aria-current="page">Importar</li>
                        </ol>
                    </nav>
                </div>
            </div>
            <div class="col-5 align-self-center text-end">
                <a href="{{ route('Platform.clinic-networks.index') }}" class="btn btn-secondary shadow-sm">
                    <i class="fa fa-arrow-left me-1"></i> Voltar
                </a>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h4 class="card-title mb-4">Importação em Lote</h4>

                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <strong>Erro!</strong> Por favor, corrija os seguintes problemas:
                                <ul class="mb-0 mt-2">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <div class="alert alert-info">
                            <h5 class="alert-heading"><i class="fas fa-info-circle me-2"></i>Instruções para Importação</h5>
                            <p class="mb-1">Selecione a rede de clínicas e o arquivo CSV separado por vírgulas (<code>,</code>).</p>
                            <code class="d-block bg-light p-2 mb-3">
                                legal_name, trade_name, document, email, phone, subdomain, endereco, n_endereco, complemento, bairro, cep, estado, cidade
                            </code>
                            <ul class="mb-0">
                                <li><strong>Localização:</strong> Informe o nome ou sigla do <code>estado</code> e o nome da <code>cidade</code>. O país é fixo em Brasil.</li>
                                <li><strong>IDs Proibidos:</strong> Não envie <code>pais_id</code>, <code>estado_id</code> ou <code>cidade_id</code>. O sistema identifica automaticamente pelos nomes.</li>
                                <li><strong>Segurança:</strong> Arquivos duplicados são bloqueados automaticamente.</li>
                            </ul>
                        </div>

                        <form action="{{ route('Platform.clinic-networks.general-import.process') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            
                            <div class="mb-3">
                                <label for="network_id" class="form-label">Rede de Clínicas <span class="text-danger">*</span></label>
                                <select class="form-select" id="network_id" name="network_id" required>
                                    <option value="">Selecione uma rede...</option>
                                    @foreach($networks as $network)
                                        <option value="{{ $network->id }}">{{ $network->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="plan_id" class="form-label">Plano Contratual <span class="text-danger">*</span></label>
                                <select class="form-select" id="plan_id" name="plan_id" required>
                                    <option value="">Selecione o plano para todas as clínicas...</option>
                                    @foreach($plans as $plan)
                                        <option value="{{ $plan->id }}">{{ $plan->name }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Apenas planos da categoria "contractual" são permitidos para redes.</small>
                            </div>

                            <div class="mb-4">
                                <label for="file" class="form-label">Selecione o Arquivo CSV <span class="text-danger">*</span></label>
                                <input type="file" class="form-control" id="file" name="file" accept=".csv" required>
                                <small class="text-muted">Tamanho máximo: 10MB</small>
                            </div>

                            <div class="mb-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="allow_duplicate_document" id="allow_duplicate_document" value="1">
                                    <label class="form-check-label fw-bold" for="allow_duplicate_document">
                                        Permitir CNPJ/CPF duplicado para esta importação
                                    </label>
                                </div>
                                <small class="text-muted">Se desmarcado, o sistema bloqueará qualquer cadastro com CNPJ/CPF já existente no banco de dados.</small>
                            </div>

                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-upload me-1"></i> Iniciar Importação
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

@include("layouts.freedash.footer")
@endsection

