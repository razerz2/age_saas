@section('title', 'Usuários da Rede de Clínicas')

<div class="row mt-4">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                <div>
                    <h5 class="mb-1 fw-bold">
                        <i class="fas fa-users-cog text-primary me-2"></i> Usuários Gestores da Rede
                    </h5>
                    @php
                        $currentHost = request()->getHost();
                        $baseDomain = config('app.domain', 'agepro.com');
                        
                        // Se o host atual for localhost ou ip, usamos ele como base para facilitar o teste local
                        if (in_array($currentHost, ['localhost', '127.0.0.1'])) {
                            $baseDomain = $currentHost;
                        }

                        $networkDomain = $network->slug . '.' . $baseDomain;
                        $loginUrl = (request()->secure() ? 'https://' : 'http://') . $networkDomain . '/admin/login';
                    @endphp
                    <small class="text-muted">
                        <i class="fas fa-link me-1"></i> Link de Acesso: 
                        <a href="{{ $loginUrl }}" target="_blank" class="text-primary text-decoration-none fw-bold">
                            {{ $loginUrl }}
                        </a>
                    </small>
                </div>
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addUserModal">
                    <i class="fas fa-plus me-1"></i> Novo Usuário
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Nome</th>
                                <th>Email</th>
                                <th>Função</th>
                                <th>Status</th>
                                <th class="text-end pe-4">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($network->users as $user)
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm bg-light-primary text-primary rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                                                {{ strtoupper(substr($user->name, 0, 1)) }}
                                            </div>
                                            <span class="fw-medium">{{ $user->name }}</span>
                                        </div>
                                    </td>
                                    <td>{{ $user->email }}</td>
                                    <td>
                                        @if($user->role === 'admin')
                                            <span class="badge bg-danger-soft text-danger">Administrador</span>
                                        @elseif($user->role === 'finance')
                                            <span class="badge bg-success-soft text-success">Financeiro</span>
                                        @else
                                            <span class="badge bg-info-soft text-info">Gestor</span>
                                        @endif
                                    </td>
                                    <td>
                                        <form action="{{ route('Platform.clinic-networks.users.toggle', [$network->id, $user->id]) }}" method="POST">
                                            @csrf
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" onchange="this.form.submit()" {{ $user->is_active ? 'checked' : '' }}>
                                                <label class="form-check-label {{ $user->is_active ? 'text-success' : 'text-danger' }}">
                                                    {{ $user->is_active ? 'Ativo' : 'Inativo' }}
                                                </label>
                                            </div>
                                        </form>
                                    </td>
                                    <td class="text-end pe-4">
                                        <form action="{{ route('Platform.clinic-networks.users.destroy', [$network->id, $user->id]) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja remover este usuário?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        <i class="fas fa-info-circle me-1"></i> Nenhum usuário gestor cadastrado para esta rede.
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

<!-- Modal Novo Usuário -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="addUserModalLabel">Adicionar Gestor de Rede</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('Platform.clinic-networks.users.store', $network->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nome Completo</label>
                        <input type="text" name="name" class="form-control" required placeholder="Ex: João Silva">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">E-mail de Acesso</label>
                        <input type="email" name="email" class="form-control" required placeholder="joao@clinica.com.br">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Senha</label>
                            <input type="password" name="password" class="form-control" required minlength="8">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Confirmar Senha</label>
                            <input type="password" name="password_confirmation" class="form-control" required minlength="8">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Perfil de Acesso</label>
                        <select name="role" class="form-select" required>
                            <option value="admin">Administrador (Acesso Total)</option>
                            <option value="manager" selected>Gestor de Rede</option>
                            <option value="finance">Financeiro</option>
                        </select>
                    </div>
                    <div class="form-check form-switch mt-2">
                        <input class="form-check-input" type="checkbox" name="is_active" id="user_active" value="1" checked>
                        <label class="form-check-label" for="user_active">Usuário Ativo</label>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Criar Usuário</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .bg-danger-soft { background-color: rgba(220, 53, 69, 0.1); }
    .bg-success-soft { background-color: rgba(40, 167, 69, 0.1); }
    .bg-info-soft { background-color: rgba(23, 162, 184, 0.1); }
    .bg-light-primary { background-color: rgba(85, 110, 230, 0.1); }
</style>

