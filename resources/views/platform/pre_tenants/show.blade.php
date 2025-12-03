@extends('layouts.freedash.app')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-lg-10 col-md-12">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">
                            <i class="fas fa-user-plus text-primary me-2"></i> Detalhes do Pré-Cadastro
                        </h4>
                        <a href="{{ route('Platform.pre_tenants.index') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Voltar
                        </a>
                    </div>

                    <div class="card-body">
                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        @if (session('warning'))
                            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-triangle me-1"></i> {{ session('warning') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

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

                        {{-- Informações gerais --}}
                        <h5 class="text-primary fw-bold mb-3">
                            <i class="fas fa-info-circle me-2"></i> Informações Gerais
                        </h5>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="fw-semibold text-muted">Nome:</label>
                                <p class="mb-0">{{ $preTenant->name }}</p>
                            </div>

                            <div class="col-md-6">
                                <label class="fw-semibold text-muted">Nome Fantasia:</label>
                                <p class="mb-0">{{ $preTenant->fantasy_name ?? '-' }}</p>
                            </div>

                            <div class="col-md-4">
                                <label class="fw-semibold text-muted">Email:</label>
                                <p class="mb-0">{{ $preTenant->email }}</p>
                            </div>

                            <div class="col-md-4">
                                <label class="fw-semibold text-muted">Telefone:</label>
                                <p class="mb-0">{{ $preTenant->phone ?? '-' }}</p>
                            </div>

                            <div class="col-md-4">
                                <label class="fw-semibold text-muted">Documento:</label>
                                <p class="mb-0">{{ $preTenant->document ?? '-' }}</p>
                            </div>

                            <div class="col-md-4">
                                <label class="fw-semibold text-muted">Plano:</label>
                                <p class="mb-0">{{ $preTenant->plan->name ?? '-' }}</p>
                            </div>

                            <div class="col-md-4">
                                <label class="fw-semibold text-muted">Status:</label>
                                <p class="mb-0">
                                    @if ($preTenant->status === 'paid')
                                        <span class="badge bg-success">Pago</span>
                                    @elseif ($preTenant->status === 'canceled')
                                        <span class="badge bg-danger">Cancelado</span>
                                    @else
                                        <span class="badge bg-warning">Pendente</span>
                                    @endif
                                </p>
                            </div>

                            <div class="col-md-4">
                                <label class="fw-semibold text-muted">Subdomínio Sugerido:</label>
                                <p class="mb-0">{{ $preTenant->subdomain_suggested ?? '-' }}</p>
                            </div>

                            <div class="col-md-4">
                                <label class="fw-semibold text-muted">Criado em:</label>
                                <p class="mb-0">{{ $preTenant->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>

                        {{-- Localização --}}
                        @if ($preTenant->address || $preTenant->pais)
                            <div class="mt-5">
                                <h5 class="text-primary fw-bold mb-3">
                                    <i class="fas fa-map-marker-alt me-2"></i> Localização
                                </h5>

                                <div class="row g-3">
                                    @if ($preTenant->address)
                                        <div class="col-md-6">
                                            <label class="fw-semibold text-muted">Endereço:</label>
                                            <p class="mb-0">{{ $preTenant->address }}</p>
                                        </div>
                                    @endif

                                    @if ($preTenant->zipcode)
                                        <div class="col-md-3">
                                            <label class="fw-semibold text-muted">CEP:</label>
                                            <p class="mb-0">{{ $preTenant->zipcode }}</p>
                                        </div>
                                    @endif

                                    @if ($preTenant->cidade)
                                        <div class="col-md-3">
                                            <label class="fw-semibold text-muted">Cidade:</label>
                                            <p class="mb-0">{{ $preTenant->cidade->nome_cidade ?? '-' }}</p>
                                        </div>
                                    @endif

                                    @if ($preTenant->estado)
                                        <div class="col-md-3">
                                            <label class="fw-semibold text-muted">Estado:</label>
                                            <p class="mb-0">
                                                {{ $preTenant->estado->nome_estado ?? '-' }}
                                                @if ($preTenant->estado && $preTenant->estado->uf)
                                                    ({{ $preTenant->estado->uf }})
                                                @endif
                                            </p>
                                        </div>
                                    @endif

                                    @if ($preTenant->pais)
                                        <div class="col-md-3">
                                            <label class="fw-semibold text-muted">País:</label>
                                            <p class="mb-0">{{ $preTenant->pais->nome ?? '-' }}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif

                        {{-- Informações do Asaas --}}
                        <div class="mt-5">
                            <h5 class="text-primary fw-bold mb-3">
                                <i class="fas fa-credit-card me-2"></i> Informações de Pagamento
                            </h5>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="fw-semibold text-muted">ID Cliente Asaas:</label>
                                    <p class="mb-0">{{ $preTenant->asaas_customer_id ?? '-' }}</p>
                                </div>

                                <div class="col-md-6">
                                    <label class="fw-semibold text-muted">ID Pagamento Asaas:</label>
                                    <p class="mb-0">{{ $preTenant->asaas_payment_id ?? '-' }}</p>
                                </div>

                                <div class="col-md-6">
                                    <label class="fw-semibold text-muted">Status do Pagamento:</label>
                                    <p class="mb-0">{{ $preTenant->payment_status ?? '-' }}</p>
                                </div>
                            </div>
                        </div>

                        {{-- Logs --}}
                        @if ($preTenant->logs->count() > 0)
                            <div class="mt-5">
                                <h5 class="text-primary fw-bold mb-3">
                                    <i class="fas fa-history me-2"></i> Histórico de Eventos
                                </h5>

                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Evento</th>
                                                <th>Data</th>
                                                <th>Detalhes</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($preTenant->logs->sortByDesc('created_at') as $log)
                                                <tr>
                                                    <td>{{ $log->event }}</td>
                                                    <td>{{ $log->created_at->format('d/m/Y H:i') }}</td>
                                                    <td>
                                                        @if ($log->payload)
                                                            <pre class="mb-0 small">{{ json_encode($log->payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif

                        {{-- Ações --}}
                        <div class="mt-5">
                            <h5 class="text-primary fw-bold mb-3">
                                <i class="fas fa-cog me-2"></i> Ações
                            </h5>

                            <div class="d-flex gap-2">
                                @if (!$preTenant->isPaid() && $preTenant->status !== 'canceled')
                                    <form action="{{ route('Platform.pre_tenants.approve', $preTenant->id) }}" method="POST"
                                        class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-success"
                                            onclick="return confirm('Deseja realmente aprovar este pré-cadastro e criar o tenant?')">
                                            <i class="fas fa-check me-1"></i> Aprovar e Criar Tenant
                                        </button>
                                    </form>
                                @endif

                                @if ($preTenant->status !== 'canceled')
                                    <form action="{{ route('Platform.pre_tenants.cancel', $preTenant->id) }}" method="POST"
                                        class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-danger"
                                            onclick="return confirm('Deseja realmente cancelar este pré-cadastro?')">
                                            <i class="fas fa-times me-1"></i> Cancelar
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('layouts.freedash.footer')
@endsection

