@extends('layouts.connect_plus.app')

@section('title', 'Integração Apple Calendar')

@section('content')

    <div class="page-header">
        <h3 class="page-title">
            <i class="mdi mdi-apple text-primary me-2"></i>
            Integração Apple Calendar
        </h3>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ workspace_route('tenant.dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('tenant.integrations.index') }}">Integrações</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Apple Calendar</li>
            </ol>
        </nav>
    </div>

    {{-- Alertas --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="mdi mdi-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="mdi mdi-alert-circle me-1"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('info'))
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <i class="mdi mdi-information me-1"></i> {{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (!isset($hasAppleCalendarTable) || !$hasAppleCalendarTable)
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <h5 class="alert-heading">
                <i class="mdi mdi-alert-circle me-2"></i>
                Migrations Pendentes
            </h5>
            <p class="mb-2">
                A tabela <code>apple_calendar_tokens</code> ainda não foi criada. 
                Execute as migrations para ativar a integração com Apple Calendar.
            </p>
            <hr>
            <p class="mb-0">
                <strong>Opção 1 (Recomendado):</strong> Execute o script SQL em 
                <code>database/migrations/tenant/apple_calendar_migration.sql</code> diretamente no banco do tenant.
            </p>
            <p class="mb-0 mt-2">
                <strong>Opção 2:</strong> Execute via Artisan quando o tenant estiver ativo:
            </p>
            <pre class="bg-light p-2 mt-2 mb-0"><code>php artisan migrate --database=tenant --path=database/migrations/tenant/2025_12_03_084550_add_apple_calendar_fields_to_appointments_table.php
php artisan migrate --database=tenant --path=database/migrations/tenant/2025_12_03_084556_create_apple_calendar_tokens_table.php</code></pre>
        </div>
    @endif

    {{-- Card Informativo --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-info">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="mdi mdi-information-outline text-info me-2"></i>
                        Como Funciona a Integração
                    </h5>
                    <div class="row">
                        <div class="col-md-6">
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2">
                                    <i class="mdi mdi-check-circle text-success me-2"></i>
                                    <strong>Sincronização Automática:</strong> Todos os agendamentos são sincronizados automaticamente com o Apple Calendar (iCloud) do médico
                                </li>
                                <li class="mb-2">
                                    <i class="mdi mdi-check-circle text-success me-2"></i>
                                    <strong>Criação:</strong> Ao criar um agendamento, o evento é criado no Apple Calendar
                                </li>
                                <li class="mb-2">
                                    <i class="mdi mdi-check-circle text-success me-2"></i>
                                    <strong>Edição:</strong> Ao editar um agendamento, o evento é atualizado no Apple Calendar
                                </li>
                                <li class="mb-2">
                                    <i class="mdi mdi-check-circle text-success me-2"></i>
                                    <strong>Cancelamento:</strong> Ao cancelar um agendamento, o evento é removido do Apple Calendar
                                </li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2">
                                    <i class="mdi mdi-check-circle text-success me-2"></i>
                                    <strong>Protocolo CalDAV:</strong> Usa o protocolo CalDAV para sincronização com iCloud
                                </li>
                                <li class="mb-2">
                                    <i class="mdi mdi-check-circle text-success me-2"></i>
                                    <strong>Conta Individual:</strong> Cada médico conecta sua própria conta do iCloud
                                </li>
                                <li class="mb-2">
                                    <i class="mdi mdi-alert-circle text-warning me-2"></i>
                                    <strong>Senha de App:</strong> É necessário usar uma senha de app específica do iCloud (não a senha da conta)
                                </li>
                                <li class="mb-2">
                                    <i class="mdi mdi-check-circle text-success me-2"></i>
                                    <strong>Áreas Sincronizadas:</strong> Funciona para agendamentos criados em qualquer área do sistema (administrativa, pública, portal do paciente)
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="alert alert-warning mt-3 mb-0">
                        <i class="mdi mdi-alert me-2"></i>
                        <strong>Importante:</strong> Para usar o iCloud, você precisa gerar uma senha de app específica em 
                        <a href="https://appleid.apple.com/account/manage" target="_blank" rel="noopener noreferrer">appleid.apple.com</a>. 
                        A senha da sua conta Apple não funcionará para CalDAV.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-4">
                        <i class="mdi mdi-apple text-primary me-2"></i>
                        Integração Apple Calendar por Médico
                    </h4>

                    <p class="text-muted mb-4">
                        Cada médico pode conectar sua própria conta do Apple Calendar (iCloud). 
                        Os agendamentos serão sincronizados automaticamente com o calendário do médico conectado.
                    </p>

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Médico</th>
                                    <th>Status</th>
                                    <th>Última Atualização</th>
                                    <th class="text-end">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($doctors as $doctor)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="me-3">
                                                    <i class="mdi mdi-account-circle text-primary" style="font-size: 2rem;"></i>
                                                </div>
                                                <div>
                                                    <strong>{{ $doctor->user->name_full ?? $doctor->user->name }}</strong>
                                                    @if ($doctor->crm_number)
                                                        <br>
                                                        <small class="text-muted">CRM: {{ $doctor->crm_number }}/{{ $doctor->crm_state }}</small>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @if (isset($hasAppleCalendarTable) && $hasAppleCalendarTable && $doctor->appleCalendarToken)
                                                <div>
                                                    <span class="badge bg-success">
                                                        <i class="mdi mdi-check-circle me-1"></i>
                                                        Conectado
                                                    </span>
                                                    <br>
                                                    <small class="text-success mt-1 d-inline-block">
                                                        <i class="mdi mdi-sync me-1"></i>
                                                        Sincronização ativa
                                                    </small>
                                                </div>
                                            @else
                                                <span class="badge bg-secondary">
                                                    <i class="mdi mdi-close-circle me-1"></i>
                                                    Desconectado
                                                </span>
                                                <br>
                                                <small class="text-muted mt-1 d-inline-block">
                                                    Clique em "Conectar Apple" para ativar
                                                </small>
                                            @endif
                                        </td>
                                        <td>
                                            @if (isset($hasAppleCalendarTable) && $hasAppleCalendarTable && $doctor->appleCalendarToken && $doctor->appleCalendarToken->updated_at)
                                                {{ $doctor->appleCalendarToken->updated_at->format('d/m/Y H:i') }}
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            @php
                                                $canConnect = false;
                                                if ($user->role === 'admin') {
                                                    $canConnect = true;
                                                } elseif ($user->role === 'doctor') {
                                                    // Carregar relacionamento se não estiver carregado
                                                    if (!$user->relationLoaded('doctor')) {
                                                        $user->load('doctor');
                                                    }
                                                    // Verificar se o médico do usuário corresponde ao médico da linha
                                                    $canConnect = $user->doctor && (string) $user->doctor->id === (string) $doctor->id;
                                                } elseif ($user->role === 'user') {
                                                    // Usuário comum não pode conectar, apenas visualizar
                                                    $canConnect = false;
                                                }
                                            @endphp
                                            
                                            @if ($canConnect)
                                                {{-- Admin e médico (para si mesmo) podem conectar/desconectar --}}
                                                @if (isset($hasAppleCalendarTable) && $hasAppleCalendarTable && $doctor->appleCalendarToken)
                                                    <div class="btn-group" role="group">
                                                        <button type="button" class="btn btn-info btn-sm" 
                                                                data-bs-toggle="tooltip" 
                                                                data-bs-placement="top" 
                                                                title="Status da integração">
                                                            <i class="mdi mdi-information-outline"></i>
                                                        </button>
                                                        <form action="{{ route('tenant.integrations.apple.disconnect', $doctor->id) }}" 
                                                              method="POST" 
                                                              class="d-inline"
                                                              onsubmit="return confirm('Tem certeza que deseja desconectar a integração do Apple Calendar para este médico?\n\nOs eventos já criados no Apple Calendar não serão removidos automaticamente.');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-danger btn-sm">
                                                                <i class="mdi mdi-link-variant-off me-1"></i>
                                                                Desconectar
                                                            </button>
                                                        </form>
                                                    </div>
                                                @else
                                                    @if (isset($hasAppleCalendarTable) && $hasAppleCalendarTable)
                                                        <a href="{{ route('tenant.integrations.apple.connect.form', $doctor->id) }}" 
                                                           class="btn btn-primary btn-sm">
                                                            <i class="mdi mdi-apple me-1"></i>
                                                            Conectar Apple
                                                        </a>
                                                    @else
                                                        <div class="alert alert-warning mb-0 p-2">
                                                            <small>
                                                                <i class="mdi mdi-alert me-1"></i>
                                                                Execute as migrations primeiro
                                                            </small>
                                                        </div>
                                                    @endif
                                                @endif
                                            @else
                                                {{-- Usuário comum: apenas visualiza status --}}
                                                <span class="text-muted">
                                                    <i class="mdi mdi-eye me-1"></i>
                                                    Apenas visualização
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">
                                            <i class="mdi mdi-information-outline me-2"></i>
                                            Nenhum médico cadastrado.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        <a href="{{ route('tenant.integrations.index') }}" class="btn btn-secondary">
                            <i class="mdi mdi-arrow-left me-1"></i>
                            Voltar para Integrações
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Inicializa tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>
@endpush

