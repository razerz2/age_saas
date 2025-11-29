<nav class="sidebar sidebar-offcanvas" id="sidebar">
    <ul class="nav">

        {{-- ============================================================
            MENU PRINCIPAL
        ============================================================ --}}
        <li class="nav-item nav-category">Menu Principal</li>

        {{-- DASHBOARD --}}
        <li class="nav-item {{ request()->routeIs('tenant.dashboard') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('tenant.dashboard') }}">
                <span class="icon-bg"><i class="mdi mdi-view-dashboard menu-icon"></i></span>
                <span class="menu-title">Dashboard</span>
            </a>
        </li>

        {{-- AGENDA -- Apenas para médicos --}}
        @php
            $user = auth('tenant')->user();
        @endphp
        @if ($user && $user->is_doctor)
            <li class="nav-item {{ request()->routeIs('tenant.calendars.events.*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('tenant.calendars.events.redirect') }}">
                    <span class="icon-bg"><i class="mdi mdi-calendar-check menu-icon"></i></span>
                    <span class="menu-title">Agenda</span>
                </a>
            </li>
        @endif

        {{-- AGENDAMENTOS --}}
        <li class="nav-item {{ request()->routeIs('tenant.appointments.*') ? 'active' : '' }}">
            <a class="nav-link" data-bs-toggle="collapse" href="#appointments-menu"
                aria-expanded="{{ request()->routeIs('tenant.appointments.*') ? 'true' : 'false' }}">
                <span class="icon-bg"><i class="mdi mdi-calendar-clock menu-icon"></i></span>
                <span class="menu-title">Agendamentos</span>
                <i class="menu-arrow"></i>
            </a>

            <div class="collapse {{ request()->routeIs('tenant.appointments.*') ? 'show' : '' }}" id="appointments-menu">
                <ul class="nav flex-column sub-menu">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('tenant.appointments.index') ? 'active' : '' }}" href="{{ route('tenant.appointments.index') }}">Listar</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('tenant.appointments.create') ? 'active' : '' }}" href="{{ route('tenant.appointments.create') }}">Novo Agendamento</a>
                    </li>
                </ul>
            </div>
        </li>

        {{-- ============================================================
            CADASTROS
        ============================================================ --}}
        <li class="nav-item nav-category">Cadastros</li>

        {{-- PACIENTES --}}
        <li class="nav-item {{ request()->routeIs('tenant.patients.*') ? 'active' : '' }}">
            <a class="nav-link" data-bs-toggle="collapse" href="#patients-menu"
                aria-expanded="{{ request()->routeIs('tenant.patients.*') ? 'true' : 'false' }}">
                <span class="icon-bg"><i class="mdi mdi-account-heart menu-icon"></i></span>
                <span class="menu-title">Pacientes</span>
                <i class="menu-arrow"></i>
            </a>

            <div class="collapse {{ request()->routeIs('tenant.patients.*') ? 'show' : '' }}" id="patients-menu">
                <ul class="nav flex-column sub-menu">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('tenant.patients.index') ? 'active' : '' }}" href="{{ route('tenant.patients.index') }}">Listar</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('tenant.patients.create') ? 'active' : '' }}" href="{{ route('tenant.patients.create') }}">Novo Paciente</a>
                    </li>
                </ul>
            </div>
        </li>

        {{-- MÉDICOS --}}
        <li class="nav-item {{ request()->routeIs('tenant.doctors.*') ? 'active' : '' }}">
            <a class="nav-link" data-bs-toggle="collapse" href="#doctors-menu"
                aria-expanded="{{ request()->routeIs('tenant.doctors.*') ? 'true' : 'false' }}">
                <span class="icon-bg"><i class="mdi mdi-stethoscope menu-icon"></i></span>
                <span class="menu-title">Médicos</span>
                <i class="menu-arrow"></i>
            </a>

            <div class="collapse {{ request()->routeIs('tenant.doctors.*') ? 'show' : '' }}" id="doctors-menu">
                <ul class="nav flex-column sub-menu">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('tenant.doctors.index') ? 'active' : '' }}" href="{{ route('tenant.doctors.index') }}">Listar</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('tenant.doctors.create') ? 'active' : '' }}" href="{{ route('tenant.doctors.create') }}">Novo Médico</a>
                    </li>
                </ul>
            </div>
        </li>

        {{-- USUÁRIOS --}}
        <li class="nav-item {{ request()->routeIs('tenant.users.*') ? 'active' : '' }}">
            <a class="nav-link" data-bs-toggle="collapse" href="#users-menu"
                aria-expanded="{{ request()->routeIs('tenant.users.*') ? 'true' : 'false' }}">
                <span class="icon-bg"><i class="mdi mdi-account-multiple menu-icon"></i></span>
                <span class="menu-title">Usuários</span>
                <i class="menu-arrow"></i>
            </a>

            <div class="collapse {{ request()->routeIs('tenant.users.*') ? 'show' : '' }}" id="users-menu">
                <ul class="nav flex-column sub-menu">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('tenant.users.index') ? 'active' : '' }}" href="{{ route('tenant.users.index') }}">Listar</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('tenant.users.create') ? 'active' : '' }}" href="{{ route('tenant.users.create') }}">Novo Usuário</a>
                    </li>
                </ul>
            </div>
        </li>

        {{-- ============================================================
            CONFIGURAÇÕES DE AGENDAMENTO
        ============================================================ --}}
        <li class="nav-item nav-category" title="Configurações de Agendamento">Configurações</li>

        {{-- CALENDÁRIOS --}}
        @php
            $user = auth('tenant')->user();
            $canCreateCalendar = $user && ($user->is_doctor || !$user->is_doctor); // Por enquanto, todos podem criar, mas pode ser ajustado
        @endphp
        <li class="nav-item {{ request()->routeIs('tenant.calendars.*') ? 'active' : '' }}">
            <a class="nav-link" data-bs-toggle="collapse" href="#calendars-menu"
                aria-expanded="{{ request()->routeIs('tenant.calendars.*') ? 'true' : 'false' }}">
                <span class="icon-bg"><i class="mdi mdi-calendar-month menu-icon"></i></span>
                <span class="menu-title">Calendários</span>
                <i class="menu-arrow"></i>
            </a>

            <div class="collapse {{ request()->routeIs('tenant.calendars.*') ? 'show' : '' }}" id="calendars-menu">
                <ul class="nav flex-column sub-menu">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('tenant.calendars.index') ? 'active' : '' }}" href="{{ route('tenant.calendars.index') }}">Listar</a>
                    </li>
                    @if ($canCreateCalendar)
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('tenant.calendars.create') ? 'active' : '' }}" href="{{ route('tenant.calendars.create') }}">Novo Calendário</a>
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
                        <a class="nav-link {{ request()->routeIs('tenant.business-hours.index') ? 'active' : '' }}" href="{{ route('tenant.business-hours.index') }}">Listar</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('tenant.business-hours.create') ? 'active' : '' }}" href="{{ route('tenant.business-hours.create') }}">Novo Horário</a>
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
                        <a class="nav-link {{ request()->routeIs('tenant.appointment-types.index') ? 'active' : '' }}" href="{{ route('tenant.appointment-types.index') }}">Listar</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('tenant.appointment-types.create') ? 'active' : '' }}" href="{{ route('tenant.appointment-types.create') }}">Novo Tipo</a>
                    </li>
                </ul>
            </div>
        </li>

        {{-- ESPECIALIDADES --}}
        <li class="nav-item {{ request()->routeIs('tenant.specialties.*') ? 'active' : '' }}">
            <a class="nav-link" data-bs-toggle="collapse" href="#specialties-menu"
                aria-expanded="{{ request()->routeIs('tenant.specialties.*') ? 'true' : 'false' }}"
                title="Especialidades Médicas">
                <span class="icon-bg"><i class="mdi mdi-pulse menu-icon"></i></span>
                <span class="menu-title">Especialidades</span>
                <i class="menu-arrow"></i>
            </a>

            <div class="collapse {{ request()->routeIs('tenant.specialties.*') ? 'show' : '' }}" id="specialties-menu">
                <ul class="nav flex-column sub-menu">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('tenant.specialties.index') ? 'active' : '' }}" href="{{ route('tenant.specialties.index') }}">Listar</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('tenant.specialties.create') ? 'active' : '' }}" href="{{ route('tenant.specialties.create') }}">Nova Especialidade</a>
                    </li>
                </ul>
            </div>
        </li>

        {{-- ============================================================
            FORMULÁRIOS
        ============================================================ --}}
        <li class="nav-item nav-category">Formulários</li>

        {{-- FORMULÁRIOS --}}
        <li class="nav-item {{ request()->routeIs('tenant.forms.*') ? 'active' : '' }}">
            <a class="nav-link" data-bs-toggle="collapse" href="#forms-menu"
                aria-expanded="{{ request()->routeIs('tenant.forms.*') ? 'true' : 'false' }}">
                <span class="icon-bg"><i class="mdi mdi-file-document-edit menu-icon"></i></span>
                <span class="menu-title">Formulários</span>
                <i class="menu-arrow"></i>
            </a>

            <div class="collapse {{ request()->routeIs('tenant.forms.*') ? 'show' : '' }}" id="forms-menu">
                <ul class="nav flex-column sub-menu">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('tenant.forms.index') ? 'active' : '' }}" href="{{ route('tenant.forms.index') }}">Listar</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('tenant.forms.create') ? 'active' : '' }}" href="{{ route('tenant.forms.create') }}">Novo Formulário</a>
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
                        <a class="nav-link {{ request()->routeIs('tenant.responses.index') ? 'active' : '' }}" href="{{ route('tenant.responses.index') }}">Listar</a>
                    </li>
                </ul>
            </div>
        </li>

        {{-- ============================================================
            INTEGRAÇÕES
        ============================================================ --}}
        <li class="nav-item nav-category">Integrações</li>

        {{-- INTEGRAÇÕES --}}
        <li class="nav-item {{ request()->routeIs('tenant.integrations.*') ? 'active' : '' }}">
            <a class="nav-link" data-bs-toggle="collapse" href="#integrations-menu"
                aria-expanded="{{ request()->routeIs('tenant.integrations.*') ? 'true' : 'false' }}">
                <span class="icon-bg"><i class="mdi mdi-puzzle menu-icon"></i></span>
                <span class="menu-title">Integrações</span>
                <i class="menu-arrow"></i>
            </a>

            <div class="collapse {{ request()->routeIs('tenant.integrations.*') ? 'show' : '' }}" id="integrations-menu">
                <ul class="nav flex-column sub-menu">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('tenant.integrations.index') ? 'active' : '' }}" href="{{ route('tenant.integrations.index') }}">Listar</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('tenant.integrations.create') ? 'active' : '' }}" href="{{ route('tenant.integrations.create') }}">Nova Integração</a>
                    </li>
                </ul>
            </div>
        </li>

        {{-- CONTAS OAUTH --}}
        <li class="nav-item {{ request()->routeIs('tenant.oauth-accounts.*') ? 'active' : '' }}">
            <a class="nav-link" data-bs-toggle="collapse" href="#oauth-accounts-menu"
                aria-expanded="{{ request()->routeIs('tenant.oauth-accounts.*') ? 'true' : 'false' }}">
                <span class="icon-bg"><i class="mdi mdi-key-variant menu-icon"></i></span>
                <span class="menu-title">Contas OAuth</span>
                <i class="menu-arrow"></i>
            </a>

            <div class="collapse {{ request()->routeIs('tenant.oauth-accounts.*') ? 'show' : '' }}" id="oauth-accounts-menu">
                <ul class="nav flex-column sub-menu">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('tenant.oauth-accounts.index') ? 'active' : '' }}" href="{{ route('tenant.oauth-accounts.index') }}">Listar</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('tenant.oauth-accounts.create') ? 'active' : '' }}" href="{{ route('tenant.oauth-accounts.create') }}">Nova Conta</a>
                    </li>
                </ul>
            </div>
        </li>

        {{-- SINCRONIZAÇÃO DE CALENDÁRIO --}}
        <li class="nav-item {{ request()->routeIs('tenant.calendar-sync.*') ? 'active' : '' }}">
            <a class="nav-link" data-bs-toggle="collapse" href="#calendar-sync-menu"
                aria-expanded="{{ request()->routeIs('tenant.calendar-sync.*') ? 'true' : 'false' }}"
                title="Sincronização de Calendário">
                <span class="icon-bg"><i class="mdi mdi-sync menu-icon"></i></span>
                <span class="menu-title">Sync Calendário</span>
                <i class="menu-arrow"></i>
            </a>

            <div class="collapse {{ request()->routeIs('tenant.calendar-sync.*') ? 'show' : '' }}" id="calendar-sync-menu">
                <ul class="nav flex-column sub-menu">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('tenant.calendar-sync.index') ? 'active' : '' }}" href="{{ route('tenant.calendar-sync.index') }}">Listar</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('tenant.calendar-sync.create') ? 'active' : '' }}" href="{{ route('tenant.calendar-sync.create') }}">Nova Sincronização</a>
                    </li>
                </ul>
            </div>
        </li>

    </ul>
</nav>
