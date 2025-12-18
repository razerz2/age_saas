<div class="row mt-4">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-body">
                <h4 class="card-title mb-4 d-flex justify-content-between align-items-center">
                    <span>
                        <i class="fas fa-building text-primary me-2"></i>
                        Clínicas Vinculadas ({{ $network->tenants->count() }})
                    </span>
                    <a href="{{ route('Platform.clinic-networks.import', $network->id) }}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-upload me-1"></i> Importar Clínicas (CSV)
                    </a>
                </h4>

                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                {{-- Formulário para vincular nova clínica --}}
                @if($availableTenants->count() > 0)
                <div class="mb-4">
                    <form action="{{ route('Platform.clinic-networks.attach-tenant', $network->id) }}" method="POST" class="row g-3 align-items-end">
                        @csrf
                        <div class="col-md-8">
                            <label for="tenant_id" class="form-label">Vincular Nova Clínica</label>
                            <select class="form-select" id="tenant_id" name="tenant_id" required>
                                <option value="">Selecione uma clínica...</option>
                                @foreach($availableTenants as $tenant)
                                    <option value="{{ $tenant->id }}">
                                        {{ $tenant->trade_name ?? $tenant->legal_name }}
                                        @if($tenant->subdomain)
                                            ({{ $tenant->subdomain }})
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fa fa-plus me-1"></i> Vincular
                            </button>
                        </div>
                    </form>
                </div>
                @else
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Todas as clínicas já estão vinculadas a redes ou não há clínicas disponíveis.
                </div>
                @endif

                {{-- Lista de clínicas vinculadas --}}
                @if($network->tenants->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead class="bg-light">
                            <tr>
                                <th>#</th>
                                <th>Razão Social</th>
                                <th>Nome Fantasia</th>
                                <th>Subdomínio</th>
                                <th>Status</th>
                                <th class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($network->tenants as $tenant)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $tenant->legal_name }}</td>
                                    <td>{{ $tenant->trade_name ?? '-' }}</td>
                                    <td>
                                        <code class="text-primary">{{ $tenant->subdomain }}</code>
                                    </td>
                                    <td>
                                        <span class="badge {{ $tenant->status === 'active' ? 'bg-success' : 'bg-secondary' }}">
                                            {{ ucfirst($tenant->status) }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('Platform.tenants.show', $tenant->id) }}" 
                                           class="btn btn-sm btn-info text-white" 
                                           title="Ver detalhes">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <form action="{{ route('Platform.clinic-networks.detach-tenant', [$network->id, $tenant->id]) }}" 
                                              method="POST" 
                                              class="d-inline"
                                              onsubmit="return confirm('Deseja realmente remover esta clínica da rede?')">
                                            @csrf
                                            <button type="submit" 
                                                    class="btn btn-sm btn-danger" 
                                                    title="Remover da rede">
                                                <i class="fa fa-unlink"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Nenhuma clínica vinculada a esta rede. Use o formulário acima para vincular clínicas.
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

