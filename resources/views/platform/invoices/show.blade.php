@extends('layouts.freedash.app')
@section('title', 'Visualizar Faturas')
@section('content')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-7 align-self-center">
                <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">Detalhes da Fatura</h4>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                {{-- ⚠️ Alertas de aviso --}}
                @if (session('warning'))
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-1"></i> {{ session('warning') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                {{-- 🔹 Exibição de erros de validação --}}
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        @if ($errors->has('general'))
                            {{ $errors->first('general') }}
                        @else
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        @endif
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
                    </div>
                @endif

                {{-- Cabeçalho --}}
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="mb-0">
                        <i class="fas fa-file-invoice-dollar text-primary me-2"></i>
                        {{ $invoice->tenant->trade_name ?? 'Tenant removido' }}
                    </h4>

                    <div>
                        <a href="{{ route('Platform.invoices.edit', $invoice->id) }}" class="btn btn-sm btn-primary me-2">
                            <i class="fas fa-edit me-1"></i> Editar
                        </a>
                        <a href="{{ route('Platform.invoices.index') }}" class="btn btn-sm btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Voltar
                        </a>
                    </div>
                </div>

                {{-- Informações principais --}}
                <h5 class="text-primary fw-bold mb-3">
                    <i class="fas fa-info-circle me-2"></i> Informações Gerais
                </h5>
                <dl class="row mb-4">
                    <dt class="col-sm-3">Valor</dt>
                    <dd class="col-sm-9">{{ $invoice->formatted_amount }}</dd>

                    <dt class="col-sm-3">Vencimento</dt>
                    <dd class="col-sm-9">{{ $invoice->due_date->format('d/m/Y') }}</dd>

                    <dt class="col-sm-3">Status</dt>
                    <dd class="col-sm-9">
                        @php
                            $colors = [
                                'pending' => 'info',
                                'paid' => 'success',
                                'overdue' => 'warning',
                                'canceled' => 'danger',
                            ];
                        @endphp
                        <span class="badge bg-{{ $colors[$invoice->status] ?? 'secondary' }}">
                            {{ ucfirst($invoice->status) }}
                        </span>
                    </dd>

                    <dt class="col-sm-3">Criado em</dt>
                    <dd class="col-sm-9">{{ $invoice->created_at->format('d/m/Y H:i') }}</dd>
                </dl>

                {{-- Informações do provedor --}}
                <h5 class="text-primary fw-bold mb-3">
                    <i class="fas fa-link me-2"></i> Informações do Pagamento
                </h5>
                <dl class="row mb-4">
                    <dt class="col-sm-3">Provedor</dt>
                    <dd class="col-sm-9">{{ strtoupper($invoice->provider ?? '-') }}</dd>

                    <dt class="col-sm-3">ID no Gateway</dt>
                    <dd class="col-sm-9">{{ $invoice->provider_id ?? '-' }}</dd>

                    <dt class="col-sm-3">Link de Pagamento</dt>
                    <dd class="col-sm-9">
                        @if ($invoice->payment_link)
                            <a href="{{ $invoice->payment_link }}" target="_blank" class="text-decoration-none">
                                <i class="fas fa-external-link-alt me-1 text-primary"></i>
                                {{ $invoice->payment_link }}
                            </a>
                        @else
                            -
                        @endif
                    </dd>
                </dl>
            </div>
        </div>

        <div class="card shadow-sm border-0 mt-4">
            <div class="card-body">
                <h4 class="card-title mb-4">Informações do Asaas</h4>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="fw-bold">ID Asaas</label>
                        <p>{{ $invoice->asaas_payment_id ?? '—' }}</p>
                    </div>

                    <div class="col-md-4">
                        <label class="fw-bold">Status Sincronização</label>
                        <p>
                            <span
                                class="badge 
                        @if ($invoice->asaas_synced) bg-success 
                        @else bg-danger @endif">
                                {{ $invoice->asaas_synced ? 'Sincronizado' : 'Não sincronizado' }}
                            </span>
                        </p>
                    </div>

                    <div class="col-md-4">
                        <label class="fw-bold">Status Asaas</label>
                        <p>{{ strtoupper($invoice->asaas_sync_status ?? '—') }}</p>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="fw-bold">Última Sincronização</label>
                        <p>{{ $invoice->asaas_last_sync_at ? $invoice->asaas_last_sync_at->format('d/m/Y H:i') : '—' }}
                        </p>
                    </div>

                    <div class="col-md-8">
                        <label class="fw-bold">Último Erro</label>
                        <p class="text-danger">{{ $invoice->asaas_last_error ?? '—' }}</p>
                    </div>
                </div>
                @php
                    use Illuminate\Support\Str;
                @endphp
                @if (
                    !Str::contains($invoice->payment_link, '/c/') &&
                        !in_array($invoice->status, ['paid', 'received', 'confirmed', 'canceled']))
                    <div class="d-flex justify-content-end mt-4">
                        <form action="{{ route('Platform.invoices.sync', $invoice) }}" method="POST" class="m-0 p-0">
                            @csrf
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-sync-alt me-1"></i> Sincronizar com Asaas
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>
    </div>

    @include('layouts.freedash.footer')
@endsection
