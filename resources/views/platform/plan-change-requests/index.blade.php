@extends('layouts.freedash.app')
@section('title', 'Listar Plan Change Requests')

@section('content')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-7 align-self-center">
                <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">Solicitações de Mudança de Plano</h4>
                <div class="d-flex align-items-center">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb m-0 p-0">
                            <li class="breadcrumb-item">
                                <a href="{{ route('Platform.dashboard') }}" class="text-muted">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item text-muted active" aria-current="page">Solicitações de Mudança</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h4 class="card-title mb-3">Lista de Solicitações</h4>

                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <div class="table-responsive">
                            <table id="requests_table" class="table table-striped table-bordered text-nowrap align-middle">
                                <thead class="bg-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Tenant</th>
                                        <th>Plano Atual</th>
                                        <th>Plano Solicitado</th>
                                        <th>Status</th>
                                        <th>Data da Solicitação</th>
                                        <th>Revisado Por</th>
                                        <th class="text-center">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($requests as $request)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $request->tenant->trade_name ?? '—' }}</td>
                                            <td>
                                                <strong>{{ $request->currentPlan->name ?? '—' }}</strong><br>
                                                <small class="text-muted">{{ $request->currentPlan->formatted_price ?? '—' }}/mês</small>
                                            </td>
                                            <td>
                                                <strong>{{ $request->requestedPlan->name ?? '—' }}</strong><br>
                                                <small class="text-muted">{{ $request->requestedPlan->formatted_price ?? '—' }}/mês</small>
                                            </td>
                                            <td>
                                                <span class="badge {{ $request->statusBadgeClass() }}">
                                                    {{ $request->statusLabel() }}
                                                </span>
                                            </td>
                                            <td>{{ $request->created_at->format('d/m/Y H:i') }}</td>
                                            <td>
                                                @if($request->reviewer)
                                                    {{ $request->reviewer->name ?? '—' }}<br>
                                                    <small class="text-muted">{{ $request->reviewed_at ? $request->reviewed_at->format('d/m/Y H:i') : '—' }}</small>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <a href="{{ route('Platform.plan-change-requests.show', $request->id) }}" 
                                                   class="btn btn-sm btn-info text-white" title="Ver Detalhes">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @if($request->isPending())
                                                    <button type="button" class="btn btn-sm btn-success" 
                                                            onclick="approveRequest('{{ $request->id }}')" title="Aprovar">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-danger" 
                                                            onclick="rejectRequest('{{ $request->id }}')" title="Rejeitar">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center py-4">
                                                <p class="text-muted mb-0">Nenhuma solicitação encontrada.</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
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
                <form id="approveForm" method="POST">
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
                <form id="rejectForm" method="POST">
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
    function approveRequest(requestId) {
        const form = document.getElementById('approveForm');
        form.action = '{{ route("Platform.plan-change-requests.approve", ":id") }}'.replace(':id', requestId);
        new bootstrap.Modal(document.getElementById('approveModal')).show();
    }

    function rejectRequest(requestId) {
        const form = document.getElementById('rejectForm');
        form.action = '{{ route("Platform.plan-change-requests.reject", ":id") }}'.replace(':id', requestId);
        new bootstrap.Modal(document.getElementById('rejectModal')).show();
    }

    $(function() {
        $('#requests_table').DataTable({
            responsive: true,
            pageLength: 10,
            order: [[5, 'desc']], // Ordenar por data de solicitação
            language: {
                url: "{{ asset('freedash/assets/js/datatables-lang/pt-BR.json') }}"
            }
        });
    });
</script>
@endpush

