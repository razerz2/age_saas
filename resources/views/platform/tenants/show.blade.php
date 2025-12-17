@extends('layouts.freedash.app')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-lg-10 col-md-12">

                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">
                            <i class="fas fa-building text-primary me-2"></i> Detalhes do Tenant
                        </h4>
                        <a href="{{ route('Platform.tenants.index') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Voltar
                        </a>
                    </div>

                    <div class="card-body">
                        {{-- Informações gerais --}}
                        <h5 class="text-primary fw-bold mb-3">
                            <i class="fas fa-info-circle me-2"></i> Informações Gerais
                        </h5>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="fw-semibold text-muted">Razão Social:</label>
                                <p class="mb-0">{{ $tenant->legal_name }}</p>
                            </div>

                            <div class="col-md-6">
                                <label class="fw-semibold text-muted">Nome Fantasia:</label>
                                <p class="mb-0">{{ $tenant->trade_name ?? '-' }}</p>
                            </div>

                            <div class="col-md-4">
                                <label class="fw-semibold text-muted">Documento:</label>
                                <p class="mb-0">{{ $tenant->document ?? '-' }}</p>
                            </div>

                            <div class="col-md-4">
                                <label class="fw-semibold text-muted">Email:</label>
                                <p class="mb-0">{{ $tenant->email ?? '-' }}</p>
                            </div>

                            <div class="col-md-4">
                                <label class="fw-semibold text-muted">Telefone:</label>
                                <p class="mb-0">{{ $tenant->phone ?? '-' }}</p>
                            </div>

                            <div class="col-md-4">
                                <label class="fw-semibold text-muted">Subdomínio:</label>
                                <p class="mb-0">{{ $tenant->subdomain }}</p>
                            </div>

                            <div class="col-md-4">
                                <label class="fw-semibold text-muted">Status:</label>
                                <span
                                    class="badge bg-{{ $tenant->status === 'active' ? 'success' : ($tenant->status === 'trial' ? 'info' : ($tenant->status === 'suspended' ? 'warning' : 'danger')) }}">
                                    {{ ucfirst($tenant->status) }}
                                </span>
                            </div>

                            <div class="col-md-4">
                                <label class="fw-semibold text-muted">Trial até:</label>
                                <p class="mb-0">
                                    {{ $tenant->trial_ends_at ? $tenant->trial_ends_at->format('d/m/Y') : '-' }}
                                </p>
                            </div>
                        </div>

                        {{-- Localização --}}
                        <div class="mt-5">
                            <h5 class="text-primary fw-bold mb-3">
                                <i class="fas fa-map-marker-alt me-2"></i> Localização da Empresa
                            </h5>

                            @if ($tenant->localizacao)
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="fw-semibold text-muted">Endereço:</label>
                                        <p class="mb-0">{{ $tenant->localizacao->endereco ?? '-' }}</p>
                                    </div>

                                    <div class="col-md-2">
                                        <label class="fw-semibold text-muted">Número:</label>
                                        <p class="mb-0">{{ $tenant->localizacao->n_endereco ?? '-' }}</p>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="fw-semibold text-muted">Complemento:</label>
                                        <p class="mb-0">{{ $tenant->localizacao->complemento ?? '-' }}</p>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="fw-semibold text-muted">Bairro:</label>
                                        <p class="mb-0">{{ $tenant->localizacao->bairro ?? '-' }}</p>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="fw-semibold text-muted">CEP:</label>
                                        <p class="mb-0">{{ $tenant->localizacao->cep ?? '-' }}</p>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="fw-semibold text-muted">Cidade:</label>
                                        <p class="mb-0">{{ $tenant->localizacao->cidade->nome_cidade ?? '-' }}</p>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="fw-semibold text-muted">Estado:</label>
                                        <p class="mb-0">
                                            {{ $tenant->localizacao->estado->nome_estado ?? '-' }}
                                            @if ($tenant->localizacao->estado && $tenant->localizacao->estado->uf)
                                                ({{ $tenant->localizacao->estado->uf }})
                                            @endif
                                        </p>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="fw-semibold text-muted">País:</label>
                                        <p class="mb-0">{{ $tenant->localizacao->pais->nome ?? '-' }}</p>
                                    </div>
                                </div>
                            @else
                                <div class="alert alert-light border mt-2">
                                    <i class="fas fa-info-circle text-secondary me-2"></i>
                                    Nenhuma localização cadastrada para este tenant.
                                </div>
                            @endif
                        </div>

                        {{-- Informações de Acesso do Admin --}}
                        <div class="mt-5">
                            <h5 class="text-primary fw-bold mb-3">
                                <i class="fas fa-key me-2"></i> Informações de Acesso do Administrador
                            </h5>

                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label class="fw-semibold text-muted">Link de Acesso:</label>
                                    <p class="mb-0">
                                        <a href="{{ $loginUrl }}" target="_blank" class="text-primary text-decoration-none">
                                            <i class="fas fa-external-link-alt me-1"></i>{{ $loginUrl }}
                                        </a>
                                    </p>
                                </div>

                                <div class="col-md-6">
                                    <label class="fw-semibold text-muted">Usuário (Email):</label>
                                    <p class="mb-0 d-inline-flex align-items-center gap-2">
                                        @if($tenantAdmin && $tenantAdmin->email)
                                            <code class="bg-light px-2 py-1 rounded" id="adminEmail">{{ $tenantAdmin->email }}</code>
                                        @elseif($adminUser && $adminUser->email)
                                            <code class="bg-light px-2 py-1 rounded" id="adminEmail">{{ $adminUser->email }}</code>
                                        @else
                                            <span class="text-muted">Usuário admin não encontrado</span>
                                        @endif
                                        @if(($tenantAdmin && $tenantAdmin->email) || ($adminUser && $adminUser->email))
                                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="copyToClipboard('adminEmail', 'btnCopyEmail')" id="btnCopyEmail" title="Copiar email">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                        @endif
                                    </p>
                                </div>

                                <div class="col-md-6">
                                    <label class="fw-semibold text-muted">Senha:</label>
                                    <p class="mb-0 d-inline-flex align-items-center gap-2">
                                        @if($tenantAdmin && $tenantAdmin->password_visible && $tenantAdmin->password)
                                            <code class="bg-light px-2 py-1 rounded" id="adminPassword">{{ $tenantAdmin->password }}</code>
                                        @elseif($adminPassword)
                                            <code class="bg-light px-2 py-1 rounded" id="adminPassword">{{ $adminPassword }}</code>
                                        @else
                                            <span class="text-muted">Senha não disponível (já foi gerada anteriormente)</span>
                                        @endif
                                        @if(($tenantAdmin && $tenantAdmin->password_visible && $tenantAdmin->password) || $adminPassword)
                                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="copyToClipboard('adminPassword', 'btnCopyPassword')" id="btnCopyPassword" title="Copiar senha">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                        @endif
                                    </p>
                                </div>

                                @if($tenantAdmin)
                                    <div class="col-md-12">
                                        <label class="fw-semibold text-muted">Informações Adicionais:</label>
                                        <p class="mb-0">
                                            <small class="text-muted">
                                                <i class="fas fa-info-circle me-1"></i>
                                                Criado em: {{ $tenantAdmin->created_at->format('d/m/Y H:i') }}
                                                @if($tenantAdmin->name)
                                                    | Nome: {{ $tenantAdmin->name }}
                                                @endif
                                            </small>
                                        </p>
                                    </div>
                                @endif
                            </div>

                            @if(!$adminUser && !$tenantAdmin)
                                <div class="alert alert-warning mt-3">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <strong>Atenção:</strong> O usuário administrador não foi encontrado no banco de dados do tenant. 
                                    Isso pode indicar que o tenant ainda não foi totalmente provisionado.
                                </div>
                            @elseif($tenantAdmin && !$tenantAdmin->password_visible)
                                <div class="alert alert-info mt-3">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Informação:</strong> A senha do administrador não está mais visível por questões de segurança.
                                </div>
                            @endif
                        </div>

                        {{-- Banco de Dados --}}
                        <div class="mt-5">
                            <h5 class="text-primary fw-bold mb-3">
                                <i class="fas fa-database me-2"></i> Configuração do Banco de Dados
                            </h5>

                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="fw-semibold text-muted">Host:</label>
                                    <p class="mb-0">{{ $tenant->db_host }}</p>
                                </div>

                                <div class="col-md-4">
                                    <label class="fw-semibold text-muted">Database:</label>
                                    <p class="mb-0">{{ $tenant->db_name }}</p>
                                </div>

                                <div class="col-md-4">
                                    <label class="fw-semibold text-muted">Usuário:</label>
                                    <p class="mb-0">{{ $tenant->db_username }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <a href="{{ route('Platform.tenants.edit', $tenant->id) }}" class="btn btn-primary">
                                <i class="fas fa-edit me-2"></i> Editar Tenant
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card shadow-sm border-0 mt-4">
                    <div class="card-body">
                        <h4 class="card-title mb-4">Informações do Asaas</h4>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="fw-bold">ID Asaas</label>
                                <p>{{ $tenant->asaas_customer_id ?? '—' }}</p>
                            </div>

                            <div class="col-md-4">
                                <label class="fw-bold">Status Sincronização</label>
                                <p>
                                    <span
                                        class="badge 
                        @if ($tenant->asaas_synced) bg-success 
                        @else bg-danger @endif">
                                        {{ $tenant->asaas_synced ? 'Sincronizado' : 'Não sincronizado' }}
                                    </span>
                                </p>
                            </div>

                            <div class="col-md-4">
                                <label class="fw-bold">Status Asaas</label>
                                <p>{{ strtoupper($tenant->asaas_sync_status ?? '—') }}</p>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="fw-bold">Última Sincronização</label>
                                <p>{{ $tenant->asaas_last_sync_at ? $tenant->asaas_last_sync_at->format('d/m/Y H:i') : '—' }}
                                </p>
                            </div>

                            <div class="col-md-8">
                                <label class="fw-bold">Último Erro</label>
                                <p class="text-danger">{{ $tenant->asaas_last_error ?? '—' }}</p>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end mt-4 gap-2">
                            @if(in_array('api_tokens', auth()->user()->modules ?? []))
                            <a href="{{ route('Platform.tenants.api-tokens.index', $tenant) }}" class="btn btn-info">
                                <i class="fas fa-key me-1"></i> Gerenciar Tokens de API
                            </a>
                            @endif
                            <form action="{{ route('Platform.tenants.sync', $tenant) }}" method="POST"
                                class="m-0 p-0">
                                @csrf
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-sync-alt me-1"></i> Sincronizar com Asaas
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    @include('layouts.freedash.footer')

    <script>
        function copyToClipboard(elementId, buttonId) {
            const element = document.getElementById(elementId);
            if (!element) {
                showToast('Elemento não encontrado.', 'error');
                return;
            }

            const text = element.textContent.trim();

            // Tentar usar a API Clipboard moderna
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text).then(function() {
                    showCopySuccess(buttonId);
                }).catch(function(err) {
                    console.error('Erro ao copiar:', err);
                    fallbackCopy(text, buttonId);
                });
            } else {
                // Fallback para navegadores mais antigos
                fallbackCopy(text, buttonId);
            }
        }

        function fallbackCopy(text, buttonId) {
            const textarea = document.createElement('textarea');
            textarea.value = text;
            textarea.style.position = 'fixed';
            textarea.style.opacity = '0';
            document.body.appendChild(textarea);
            textarea.select();
            
            try {
                document.execCommand('copy');
                showCopySuccess(buttonId);
            } catch (err) {
                console.error('Erro ao copiar:', err);
                showToast('Erro ao copiar. Por favor, copie manualmente.', 'error');
            }
            
            document.body.removeChild(textarea);
        }

        function showCopySuccess(buttonId) {
            const btn = document.getElementById(buttonId);
            if (!btn) return;
            
            const originalHtml = btn.innerHTML;
            
            btn.innerHTML = '<i class="fas fa-check"></i>';
            btn.classList.remove('btn-outline-secondary');
            btn.classList.add('btn-success');
            btn.disabled = true;
            
            setTimeout(function() {
                btn.innerHTML = originalHtml;
                btn.classList.remove('btn-success');
                btn.classList.add('btn-outline-secondary');
                btn.disabled = false;
            }, 2000);
        }
    </script>
@endsection
