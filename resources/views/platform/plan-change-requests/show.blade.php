@extends('layouts.freedash.app')
@section('title', 'Visualizar Solicitações de Mudança de Plano')
@section('content')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-7 align-self-center">
                <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">Detalhes da Solicitação</h4>
                <div class="d-flex align-items-center">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb m-0 p-0">
                            <li class="breadcrumb-item">
                                <a href="{{ route('Platform.dashboard') }}" class="text-muted">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('Platform.plan-change-requests.index') }}" class="text-muted">Solicitações</a>
                            </li>
                            <li class="breadcrumb-item text-muted active" aria-current="page">Detalhes</li>
                        </ol>
                    </nav>
                </div>
            </div>
            <div class="col-5 align-self-center text-end">
                <a href="{{ route('Platform.plan-change-requests.index') }}" class="btn btn-secondary shadow-sm">
                    <i class="fa fa-arrow-left me-1"></i> Voltar
                </a>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h4 class="card-title mb-4">Informações da Solicitação</h4>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="fw-bold">Tenant</label>
                                <p>{{ $planChangeRequest->tenant->trade_name ?? '—' }}</p>
                            </div>
                            <div class="col-md-6">
                                <label class="fw-bold">Status</label>
                                <p>
                                    <span class="badge {{ $planChangeRequest->statusBadgeClass() }}">
                                        {{ $planChangeRequest->statusLabel() }}
                                    </span>
                                </p>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="fw-bold">Plano Atual</label>
                                <p class="fs-5 mb-0">{{ $planChangeRequest->currentPlan->name ?? '—' }}</p>
                                <p class="text-muted">{{ $planChangeRequest->currentPlan->formatted_price ?? '—' }}/mês</p>
                            </div>
                            <div class="col-md-6">
                                <label class="fw-bold">Plano Solicitado</label>
                                <p class="fs-5 mb-0 text-primary">{{ $planChangeRequest->requestedPlan->name ?? '—' }}</p>
                                <p class="text-muted">{{ $planChangeRequest->requestedPlan->formatted_price ?? '—' }}/mês</p>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="fw-bold">Data da Solicitação</label>
                                <p>{{ $planChangeRequest->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                            @if($planChangeRequest->reviewed_at)
                            <div class="col-md-6">
                                <label class="fw-bold">Data da Revisão</label>
                                <p>{{ $planChangeRequest->reviewed_at->format('d/m/Y H:i') }}</p>
                            </div>
                            @endif
                        </div>

                        @if($planChangeRequest->reason)
                        <div class="mb-3">
                            <label class="fw-bold">Motivo da Solicitação</label>
                            <p class="p-3 bg-light rounded">{{ $planChangeRequest->reason }}</p>
                        </div>
                        @endif

                        @if($planChangeRequest->admin_notes)
                        <div class="mb-3">
                            <label class="fw-bold">Notas do Administrador</label>
                            <p class="p-3 bg-light rounded">{{ $planChangeRequest->admin_notes }}</p>
                        </div>
                        @endif

                        @if($planChangeRequest->reviewer)
                        <div class="mb-3">
                            <label class="fw-bold">Revisado Por</label>
                            <p>{{ $planChangeRequest->reviewer->name ?? '—' }}</p>
                        </div>
                        @endif

                        @if($planChangeRequest->canBeReviewed())
                        <div class="mt-4 pt-4 border-top">
                            <h5 class="mb-3">Ações</h5>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-success" onclick="approveRequest()">
                                    <i class="fas fa-check me-2"></i>Aprovar
                                </button>
                                <button type="button" class="btn btn-danger" onclick="rejectRequest()">
                                    <i class="fas fa-times me-2"></i>Rejeitar
                                </button>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Aprovação -->
    <div class="modal fade" id="approveModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Aprovar Solicitação</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="{{ route('Platform.plan-change-requests.approve', $planChangeRequest->id) }}">
                    @csrf
                    <div class="modal-body">
                        <p>Tem certeza que deseja aprovar esta solicitação de mudança de plano?</p>
                        <div class="mb-3">
                            <label for="approve_notes" class="form-label">Notas (opcional)</label>
                            <textarea name="admin_notes" id="approve_notes" class="form-control" rows="3" 
                                      placeholder="Adicione notas sobre a aprovação..." maxlength="1000"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">Aprovar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal de Rejeição -->
    <div class="modal fade" id="rejectModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Rejeitar Solicitação</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="{{ route('Platform.plan-change-requests.reject', $planChangeRequest->id) }}">
                    @csrf
                    <div class="modal-body">
                        <p>Tem certeza que deseja rejeitar esta solicitação de mudança de plano?</p>
                        <div class="mb-3">
                            <label for="reject_notes" class="form-label">Motivo da Rejeição <span class="text-danger">*</span></label>
                            <textarea name="admin_notes" id="reject_notes" class="form-control" rows="3" 
                                      placeholder="Informe o motivo da rejeição..." required maxlength="1000"></textarea>
                            <div class="form-text">Este motivo será visível para o tenant.</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger">Rejeitar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @include('layouts.freedash.footer')
@endsection

@push('scripts')
<script>
    function approveRequest() {
        new bootstrap.Modal(document.getElementById('approveModal')).show();
    }

    function rejectRequest() {
        new bootstrap.Modal(document.getElementById('rejectModal')).show();
    }
</script>
@endpush

