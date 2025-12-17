<nav class="sidebar sidebar-offcanvas" id="sidebar">
    <ul class="nav">

        {{-- ============================================================
            MENU PRINCIPAL
        ============================================================ --}}
        <li class="nav-item nav-category">Menu Principal</li>

        {{-- DASHBOARD --}}
        <li class="nav-item {{ request()->routeIs('tenant.dashboard') ? 'active' : '' }}">
            <a class="nav-link" href="{{ workspace_route('tenant.dashboard') }}" title="Dashboard">
                <span class="icon-bg"><i class="mdi mdi-view-dashboard menu-icon"></i></span>
                <span class="menu-title">Dashboard</span>
            </a>
        </li>

        {{-- AGENDA -- Apenas para médicos --}}
        @php
            $user = auth('tenant')->user();
        @endphp
        @if ($user && $user->role === 'doctor')
            <li class="nav-item {{ request()->routeIs('tenant.calendars.events.*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ workspace_route('tenant.calendars.events.redirect') }}" title="Agenda">
                    <span class="icon-bg"><i class="mdi mdi-calendar-check menu-icon"></i></span>
                    <span class="menu-title">Agenda</span>
                </a>
            </li>
        @endif

        {{-- AGENDAMENTOS --}}
        <li class="nav-item {{ request()->routeIs('tenant.appointments.*') && !request()->routeIs('tenant.recurring-appointments.*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ workspace_route('tenant.appointments.index') }}" title="Agendamentos">
                <span class="icon-bg"><i class="mdi mdi-calendar-clock menu-icon"></i></span>
                <span class="menu-title">Agendamentos</span>
            </a>
        </li>

        {{-- AGENDAMENTOS RECORRENTES --}}
        <li class="nav-item {{ request()->routeIs('tenant.recurring-appointments.*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ workspace_route('tenant.recurring-appointments.index') }}" title="Agendamentos Recorrentes">
                <span class="icon-bg"><i class="mdi mdi-calendar-repeat menu-icon"></i></span>
                <span class="menu-title">Agend. Recorrentes</span>
            </a>
        </li>

        {{-- CONSULTAS ONLINE --}}
        @php
            $user = auth('tenant')->user();
            // Garantir que modules seja sempre um array
            $userModules = [];
            if ($user && $user->modules) {
                if (is_array($user->modules)) {
                    $userModules = $user->modules;
                } elseif (is_string($user->modules)) {
                    $decoded = json_decode($user->modules, true);
                    $userModules = is_array($decoded) ? $decoded : [];
                }
            }
            $settings = \App\Models\Tenant\TenantSetting::getAll();
            $defaultMode = $settings['appointments.default_appointment_mode'] ?? 'user_choice';
            // Admin tem acesso a todos os módulos (mesma lógica do middleware CheckModuleAccess)
            $hasAccess = ($user && $user->role === 'admin') || in_array('online_appointments', $userModules);
        @endphp
        @if(
            $hasAccess &&
            $defaultMode !== 'presencial'
        )
            <li class="nav-item {{ request()->routeIs('tenant.online-appointments.*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ workspace_route('tenant.online-appointments.index') }}" title="Consultas Online">
                    <span class="icon-bg"><i class="mdi mdi-video-account menu-icon"></i></span>
                    <span class="menu-title">Consultas Online</span>
                </a>
            </li>
        @endif

        {{-- ATENDIMENTO MÉDICO --}}
        @if(has_module('medical_appointments'))
            <li class="nav-item {{ request()->routeIs('tenant.medical-appointments.*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ workspace_route('tenant.medical-appointments.index') }}" title="Atendimento Médico">
                    <span class="icon-bg"><i class="mdi mdi-account-heart menu-icon"></i></span>
                    <span class="menu-title">Atendimento</span>
                </a>
            </li>
        @endif

        {{-- ============================================================
            CADASTROS
        ============================================================ --}}
        <li class="nav-item nav-category">Cadastros</li>

        {{-- PACIENTES --}}
        <li class="nav-item {{ request()->routeIs('tenant.patients.*') ? 'active' : '' }}">
            <a class="nav-link" data-bs-toggle="collapse" href="#patients-menu"
                aria-expanded="{{ request()->routeIs('tenant.patients.*') ? 'true' : 'false' }}" title="Pacientes">
                <span class="icon-bg"><i class="mdi mdi-account-heart menu-icon"></i></span>
                <span class="menu-title">Pacientes</span>
                <i class="menu-arrow"></i>
            </a>

            <div class="collapse {{ request()->routeIs('tenant.patients.*') ? 'show' : '' }}" id="patients-menu">
                <ul class="nav flex-column sub-menu">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('tenant.patients.index') ? 'active' : '' }}" href="{{ workspace_route('tenant.patients.index') }}" title="Todos os Pacientes">Todos os Pacientes</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('tenant.patients.create') ? 'active' : '' }}" href="{{ workspace_route('tenant.patients.create') }}" title="Novo Paciente">Novo Paciente</a>
                    </li>
                </ul>
            </div>
        </li>

        {{-- MÉDICOS / PROFISSIONAIS --}}
        <li class="nav-item {{ request()->routeIs('tenant.doctors.*') ? 'active' : '' }}">
            <a class="nav-link" data-bs-toggle="collapse" href="#doctors-menu"
                aria-expanded="{{ request()->routeIs('tenant.doctors.*') ? 'true' : 'false' }}" title="{{ professional_label_plural() }}">
                <span class="icon-bg"><i class="mdi mdi-stethoscope menu-icon"></i></span>
                <span class="menu-title">{{ professional_label_plural() }}</span>
                <i class="menu-arrow"></i>
            </a>

            <div class="collapse {{ request()->routeIs('tenant.doctors.*') ? 'show' : '' }}" id="doctors-menu">
                <ul class="nav flex-column sub-menu">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('tenant.doctors.index') ? 'active' : '' }}" href="{{ workspace_route('tenant.doctors.index') }}" title="Todos os {{ professional_label_plural() }}">Todos os {{ professional_label_plural() }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('tenant.doctors.create') ? 'active' : '' }}" href="{{ workspace_route('tenant.doctors.create') }}" title="Novo {{ professional_label_singular() }}">Novo {{ professional_label_singular() }}</a>
                    </li>
                </ul>
            </div>
        </li>

        {{-- ESPECIALIDADES --}}
        <li class="nav-item {{ request()->routeIs('tenant.specialties.*') ? 'active' : '' }}">
            <a class="nav-link" data-bs-toggle="collapse" href="#specialties-menu"
                aria-expanded="{{ request()->routeIs('tenant.specialties.*') ? 'true' : 'false' }}"
                title="Especialidades">
                <span class="icon-bg"><i class="mdi mdi-pulse menu-icon"></i></span>
                <span class="menu-title">Especialidades</span>
                <i class="menu-arrow"></i>
            </a>

            <div class="collapse {{ request()->routeIs('tenant.specialties.*') ? 'show' : '' }}" id="specialties-menu">
                <ul class="nav flex-column sub-menu">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('tenant.specialties.index') ? 'active' : '' }}" href="{{ workspace_route('tenant.specialties.index') }}" title="Todas as Especialidades">Todas as Especialidades</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('tenant.specialties.create') ? 'active' : '' }}" href="{{ workspace_route('tenant.specialties.create') }}" title="Nova Especialidade">Nova Especialidade</a>
                    </li>
                </ul>
            </div>
        </li>

        {{-- USUÁRIOS -- Apenas para admin --}}
        @if ($user && $user->role === 'admin')
            <li class="nav-item {{ request()->routeIs('tenant.users.*') ? 'active' : '' }}">
                <a class="nav-link" data-bs-toggle="collapse" href="#users-menu"
                    aria-expanded="{{ request()->routeIs('tenant.users.*') ? 'true' : 'false' }}" title="Usuários">
                    <span class="icon-bg"><i class="mdi mdi-account-multiple menu-icon"></i></span>
                    <span class="menu-title">Usuários</span>
                    <i class="menu-arrow"></i>
                </a>

                <div class="collapse {{ request()->routeIs('tenant.users.*') ? 'show' : '' }}" id="users-menu">
                    <ul class="nav flex-column sub-menu">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('tenant.users.index') ? 'active' : '' }}" href="{{ workspace_route('tenant.users.index') }}" title="Todos os Usuários">Todos os Usuários</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('tenant.users.create') ? 'active' : '' }}" href="{{ workspace_route('tenant.users.create') }}" title="Novo Usuário">Novo Usuário</a>
                        </li>
                    </ul>
                </div>
            </li>
        @endif

        {{-- ============================================================
            CONFIGURAÇÕES DE AGENDAMENTO
        ============================================================ --}}
        <li class="nav-item nav-category" title="Configurações de Agendamento">Config. de Calendários</li>

        @php
            $user = auth('tenant')->user();
            $showUnifiedPage = false;
            
            // Verificar se deve mostrar página única ou menu separado
            if ($user) {
                if ($user->role === 'doctor' && $user->doctor) {
                    // Médico logado sempre vê página única
                    $showUnifiedPage = true;
                } elseif ($user->role === 'user') {
                    // Usuário comum: verificar quantidade de médicos relacionados
                    $allowedDoctorsCount = $user->allowedDoctors()->count();
                    if ($allowedDoctorsCount === 1) {
                        // Usuário com 1 médico relacionado vê página única
                        $showUnifiedPage = true;
                    }
                    // Usuário com mais de 1 médico ou admin vê menu separado (showUnifiedPage = false)
                }
                // Admin sempre vê menu separado (showUnifiedPage = false)
            }
        @endphp

        @if($showUnifiedPage)
            {{-- Página única de configurações --}}
            <li class="nav-item {{ request()->routeIs('tenant.doctor-settings.*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ workspace_route('tenant.doctor-settings.index') }}" title="Calendário">
                    <span class="icon-bg"><i class="mdi mdi-calendar-month menu-icon"></i></span>
                    <span class="menu-title">Calendário</span>
                </a>
            </li>
        @else
            {{-- Menu separado (admin ou usuário com mais de 1 médico) --}}
            {{-- CALENDÁRIOS --}}
            @php
                $canCreateCalendar = $user && ($user->is_doctor || !$user->is_doctor); // Por enquanto, todos podem criar, mas pode ser ajustado
            @endphp
            <li class="nav-item {{ request()->routeIs('tenant.calendars.*') ? 'active' : '' }}">
                <a class="nav-link" data-bs-toggle="collapse" href="#calendars-menu"
                    aria-expanded="{{ request()->routeIs('tenant.calendars.*') ? 'true' : 'false' }}" title="Calendários">
                    <span class="icon-bg"><i class="mdi mdi-calendar-month menu-icon"></i></span>
                    <span class="menu-title">Calendários</span>
                    <i class="menu-arrow"></i>
                </a>

                <div class="collapse {{ request()->routeIs('tenant.calendars.*') ? 'show' : '' }}" id="calendars-menu">
                    <ul class="nav flex-column sub-menu">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('tenant.calendars.index') ? 'active' : '' }}" href="{{ workspace_route('tenant.calendars.index') }}" title="Todos os Calendários">Todos os Calendários</a>
                        </li>
                        @if ($canCreateCalendar)
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('tenant.calendars.create') ? 'active' : '' }}" href="{{ workspace_route('tenant.calendars.create') }}" title="Novo Calendário">Novo Calendário</a>
                            </li>
                        @endif
                    </ul>
                </div>
            </li>

            {{-- HORÁRIOS DE ATENDIMENTO --}}
            <li class="nav-item {{ request()->routeIs('tenant.business-hours.*') ? 'active' : '' }}">
                <a class="nav-link" data-bs-toggle="collapse" href="#business-hours-menu"
                    aria-expanded="{{ request()->routeIs('tenant.business-hours.*') ? 'true' : 'false' }}"
                    title="Horários de Atendimento">
                    <span class="icon-bg"><i class="mdi mdi-clock-outline menu-icon"></i></span>
                    <span class="menu-title">Horários</span>
                    <i class="menu-arrow"></i>
                </a>

                <div class="collapse {{ request()->routeIs('tenant.business-hours.*') ? 'show' : '' }}" id="business-hours-menu">
                    <ul class="nav flex-column sub-menu">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('tenant.business-hours.index') ? 'active' : '' }}" href="{{ workspace_route('tenant.business-hours.index') }}" title="Todos os Horários">Todos os Horários</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('tenant.business-hours.create') ? 'active' : '' }}" href="{{ workspace_route('tenant.business-hours.create') }}" title="Novo Horário de Atendimento">Novo Horário</a>
                    </li>
                    </ul>
                </div>
            </li>

            {{-- TIPOS DE ATENDIMENTO --}}
            <li class="nav-item {{ request()->routeIs('tenant.appointment-types.*') ? 'active' : '' }}">
                <a class="nav-link" data-bs-toggle="collapse" href="#appointment-types-menu"
                    aria-expanded="{{ request()->routeIs('tenant.appointment-types.*') ? 'true' : 'false' }}"
                    title="Tipos de Atendimento">
                    <span class="icon-bg"><i class="mdi mdi-clipboard-pulse menu-icon"></i></span>
                    <span class="menu-title">Tipos</span>
                    <i class="menu-arrow"></i>
                </a>

                <div class="collapse {{ request()->routeIs('tenant.appointment-types.*') ? 'show' : '' }}" id="appointment-types-menu">
                    <ul class="nav flex-column sub-menu">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('tenant.appointment-types.index') ? 'active' : '' }}" href="{{ workspace_route('tenant.appointment-types.index') }}" title="Todos os Tipos">Todos os Tipos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('tenant.appointment-types.create') ? 'active' : '' }}" href="{{ workspace_route('tenant.appointment-types.create') }}" title="Novo Tipo de Atendimento">Novo Tipo</a>
                    </li>
                    </ul>
                </div>
            </li>
        @endif

        {{-- ============================================================
            FORMULÁRIOS
        ============================================================ --}}
        <li class="nav-item nav-category">Formulários</li>

        {{-- FORMULÁRIOS --}}
        <li class="nav-item {{ request()->routeIs('tenant.forms.*') ? 'active' : '' }}">
            <a class="nav-link" data-bs-toggle="collapse" href="#forms-menu"
                aria-expanded="{{ request()->routeIs('tenant.forms.*') ? 'true' : 'false' }}" title="Formulários">
                <span class="icon-bg"><i class="mdi mdi-file-document-edit menu-icon"></i></span>
                <span class="menu-title">Formulários</span>
                <i class="menu-arrow"></i>
            </a>

            <div class="collapse {{ request()->routeIs('tenant.forms.*') ? 'show' : '' }}" id="forms-menu">
                <ul class="nav flex-column sub-menu">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('tenant.forms.index') ? 'active' : '' }}" href="{{ workspace_route('tenant.forms.index') }}" title="Todos os Formulários">Todos os Formulários</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('tenant.forms.create') ? 'active' : '' }}" href="{{ workspace_route('tenant.forms.create') }}" title="Novo Formulário">Novo Formulário</a>
                    </li>
                </ul>
            </div>
        </li>

        {{-- RESPOSTAS --}}
        <li class="nav-item {{ request()->routeIs('tenant.responses.*') ? 'active' : '' }}">
            <a class="nav-link" data-bs-toggle="collapse" href="#responses-menu"
                aria-expanded="{{ request()->routeIs('tenant.responses.*') ? 'true' : 'false' }}"
                title="Respostas de Formulários">
                <span class="icon-bg"><i class="mdi mdi-file-document-box-check menu-icon"></i></span>
                <span class="menu-title">Respostas</span>
                <i class="menu-arrow"></i>
            </a>

            <div class="collapse {{ request()->routeIs('tenant.responses.*') ? 'show' : '' }}" id="responses-menu">
                <ul class="nav flex-column sub-menu">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('tenant.responses.index') ? 'active' : '' }}" href="{{ workspace_route('tenant.responses.index') }}" title="Todas as Respostas">Todas as Respostas</a>
                    </li>
                </ul>
            </div>
        </li>

        {{-- ============================================================
            FINANCEIRO
        ============================================================ --}}
        @php
            $user = auth('tenant')->user();
            // Garantir que modules seja sempre um array
            $userModules = [];
            if ($user && $user->modules) {
                if (is_array($user->modules)) {
                    $userModules = $user->modules;
                } elseif (is_string($user->modules)) {
                    $decoded = json_decode($user->modules, true);
                    $userModules = is_array($decoded) ? $decoded : [];
                }
            }
            // Verificar se módulo financeiro está habilitado e usuário tem acesso
            $financeEnabled = tenant_setting('finance.enabled') === 'true';
            $hasFinanceAccess = $financeEnabled && (($user && $user->role === 'admin') || in_array('finance', $userModules));
        @endphp
        @if($hasFinanceAccess)
            <li class="nav-item nav-category">Financeiro</li>

            {{-- FINANCEIRO --}}
            <li class="nav-item {{ request()->routeIs('tenant.finance.*') ? 'active' : '' }}">
                <a class="nav-link" data-bs-toggle="collapse" href="#finance-menu"
                    aria-expanded="{{ request()->routeIs('tenant.finance.*') ? 'true' : 'false' }}" title="Financeiro">
                    <span class="icon-bg"><i class="mdi mdi-cash menu-icon"></i></span>
                    <span class="menu-title">Financeiro</span>
                    <i class="menu-arrow"></i>
                </a>

                <div class="collapse {{ request()->routeIs('tenant.finance.*') ? 'show' : '' }}" id="finance-menu">
                    <ul class="nav flex-column sub-menu">
                        @if($user && $user->role === 'admin')
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('tenant.finance.index') ? 'active' : '' }}" href="{{ workspace_route('tenant.finance.index') }}" title="Dashboard Financeiro">Dashboard</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('tenant.finance.accounts.*') ? 'active' : '' }}" href="{{ workspace_route('tenant.finance.accounts.index') }}" title="Contas Financeiras">Contas</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('tenant.finance.categories.*') ? 'active' : '' }}" href="{{ workspace_route('tenant.finance.categories.index') }}" title="Categorias Financeiras">Categorias</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('tenant.finance.transactions.*') ? 'active' : '' }}" href="{{ workspace_route('tenant.finance.transactions.index') }}" title="Transações Financeiras">Transações</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('tenant.finance.charges.*') ? 'active' : '' }}" href="{{ workspace_route('tenant.finance.charges.index') }}" title="Cobranças">Cobranças</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('tenant.finance.commissions.*') ? 'active' : '' }}" href="{{ workspace_route('tenant.finance.commissions.index') }}" title="Comissões">Comissões</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('tenant.finance.reports.*') ? 'active' : '' }}" href="{{ workspace_route('tenant.finance.reports.index') }}" title="Relatórios Financeiros">Relatórios</a>
                            </li>
                        @elseif($user && $user->role === 'doctor')
                            {{-- Médico vê apenas comissões --}}
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('tenant.finance.commissions.*') ? 'active' : '' }}" href="{{ workspace_route('tenant.finance.commissions.index') }}" title="Comissões">Comissões</a>
                            </li>
                        @endif
                    </ul>
                </div>
            </li>
        @endif

        {{-- ============================================================
            CONFIGURAÇÕES DO SISTEMA
        ============================================================ --}}
        @php
            $user = auth('tenant')->user();
            // Garantir que modules seja sempre um array
            $userModules = [];
            if ($user && $user->modules) {
                if (is_array($user->modules)) {
                    $userModules = $user->modules;
                } elseif (is_string($user->modules)) {
                    $decoded = json_decode($user->modules, true);
                    $userModules = is_array($decoded) ? $decoded : [];
                }
            }
            // Admin tem acesso a todos os módulos (mesma lógica do middleware CheckModuleAccess)
            $hasSettingsAccess = ($user && $user->role === 'admin') || in_array('settings', $userModules);
        @endphp
        @if($hasSettingsAccess)
            <li class="nav-item nav-category">Sistema</li>

            {{-- CONFIGURAÇÕES --}}
            <li class="nav-item {{ request()->routeIs('tenant.settings.*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ workspace_route('tenant.settings.index') }}" title="Configurações">
                    <span class="icon-bg"><i class="mdi mdi-settings menu-icon"></i></span>
                    <span class="menu-title">Configurações</span>
                </a>
            </li>
        @endif

        {{-- ============================================================
            INTEGRAÇÕES
        ============================================================ --}}
        <li class="nav-item nav-category">Integrações</li>

        {{-- INTEGRAÇÕES --}}
        <li class="nav-item {{ request()->routeIs('tenant.integrations.*') || request()->routeIs('tenant.integrations.google.*') ? 'active' : '' }}">
            <a class="nav-link" data-bs-toggle="collapse" href="#integrations-menu"
                aria-expanded="{{ request()->routeIs('tenant.integrations.*') || request()->routeIs('tenant.integrations.google.*') ? 'true' : 'false' }}" title="Integrações">
                <span class="icon-bg"><i class="mdi mdi-puzzle menu-icon"></i></span>
                <span class="menu-title">Integrações</span>
                <i class="menu-arrow"></i>
            </a>

            <div class="collapse {{ request()->routeIs('tenant.integrations.*') || request()->routeIs('tenant.integrations.google.*') || request()->routeIs('tenant.integrations.apple.*') ? 'show' : '' }}" id="integrations-menu">
                <ul class="nav flex-column sub-menu">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('tenant.integrations.google.*') ? 'active' : '' }}" href="{{ workspace_route('tenant.integrations.google.index') }}" title="Google Calendar">
                            <i class="mdi mdi-google me-1"></i> Google Calendar
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('tenant.integrations.apple.*') ? 'active' : '' }}" href="{{ workspace_route('tenant.integrations.apple.index') }}" title="Apple Calendar">
                            <i class="mdi mdi-apple me-1"></i> Apple Calendar
                        </a>
                    </li>
                </ul>
            </div>
        </li>

        {{-- 
            NOTA: Contas OAuth e Sync Calendário foram removidos do menu pois:
            - Google Calendar usa sua própria estrutura (google_calendar_tokens vinculado a doctor_id)
            - Essas estruturas genéricas (oauth_accounts vinculado a user_id) não são usadas atualmente
            - Mantidas no banco de dados para possível uso futuro com Apple Calendar ou outras integrações
        --}}

        {{-- ============================================================
            RELATÓRIOS
        ============================================================ --}}
        <li class="nav-item nav-category">Relatórios</li>

        <li class="nav-item {{ request()->routeIs('tenant.reports.*') ? 'active' : '' }}">
            <a class="nav-link" data-bs-toggle="collapse" href="#reports-menu"
               aria-expanded="{{ request()->routeIs('tenant.reports.*') ? 'true' : 'false' }}" title="Relatórios">
                <span class="icon-bg"><i class="mdi mdi-chart-bar menu-icon"></i></span>
                <span class="menu-title">Relatórios</span>
                <i class="menu-arrow"></i>
            </a>
            <div class="collapse {{ request()->routeIs('tenant.reports.*') ? 'show' : '' }}" id="reports-menu">
                <ul class="nav flex-column sub-menu">
                    <li><a class="nav-link" href="{{ workspace_route('tenant.reports.appointments') }}" title="Relatório de Agendamentos">Agendamentos</a></li>
                    <li><a class="nav-link" href="{{ workspace_route('tenant.reports.patients') }}" title="Relatório de Pacientes">Pacientes</a></li>
                    <li><a class="nav-link" href="{{ workspace_route('tenant.reports.doctors') }}" title="Relatório de Médicos">Médicos</a></li>
                    <li><a class="nav-link" href="{{ workspace_route('tenant.reports.recurring') }}" title="Relatório de Recorrências">Recorrências</a></li>
                    <li><a class="nav-link" href="{{ workspace_route('tenant.reports.forms') }}" title="Relatório de Formulários">Formulários</a></li>
                    <li><a class="nav-link" href="{{ workspace_route('tenant.reports.portal') }}" title="Relatório do Portal do Paciente">Portal do Paciente</a></li>
                    <li><a class="nav-link" href="{{ workspace_route('tenant.reports.notifications') }}" title="Relatório de Notificações">Notificações</a></li>
                </ul>
            </div>
        </li>

    </ul>
</nav>
