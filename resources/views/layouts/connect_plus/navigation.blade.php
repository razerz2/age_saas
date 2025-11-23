<nav class="sidebar sidebar-offcanvas" id="sidebar">
    <ul class="nav">

        {{-- Categoria principal --}}
        <li class="nav-item nav-category">Menu Principal</li>

        {{-- DASHBOARD --}}
        <li class="nav-item {{ request()->routeIs('tenant.dashboard') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('tenant.dashboard') }}">
                <span class="icon-bg"><i class="mdi mdi-view-dashboard menu-icon"></i></span>
                <span class="menu-title">Dashboard</span>
            </a>
        </li>

        {{-- AGENDA --}}
        <li class="nav-item {{ request()->routeIs('tenant.calendars.*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('tenant.calendars.index') }}">
                <span class="icon-bg"><i class="mdi mdi-calendar-month menu-icon"></i></span>
                <span class="menu-title">Agenda</span>
            </a>
        </li>

        {{-- ATENDIMENTOS --}}
        <li class="nav-item {{ request()->routeIs('tenant.appointments.*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('tenant.appointments.index') }}">
                <span class="icon-bg"><i class="mdi mdi-calendar-check menu-icon"></i></span>
                <span class="menu-title">Atendimentos</span>
            </a>
        </li>

        {{-- PACIENTES --}}
        <li class="nav-item {{ request()->routeIs('tenant.patients.*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('tenant.patients.index') }}">
                <span class="icon-bg"><i class="mdi mdi-account-heart menu-icon"></i></span>
                <span class="menu-title">Pacientes</span>
            </a>
        </li>

        {{-- MÉDICOS --}}
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="collapse" href="#doctors-menu"
                aria-expanded="{{ request()->routeIs('tenant.doctors.*') ? 'true' : 'false' }}">
                <span class="icon-bg"><i class="mdi mdi-stethoscope menu-icon"></i></span>
                <span class="menu-title">Médicos</span>
                <i class="menu-arrow"></i>
            </a>

            <div class="collapse {{ request()->routeIs('tenant.doctors.*') ? 'show' : '' }}" id="doctors-menu">
                <ul class="nav flex-column sub-menu">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('tenant.doctors.index') }}">Listar</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('tenant.doctors.create') }}">Adicionar Médico</a>
                    </li>
                </ul>
            </div>
        </li>

        {{-- USUÁRIOS --}}
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="collapse" href="#users-menu"
                aria-expanded="{{ request()->routeIs('tenant.users.*') ? 'true' : 'false' }}">
                <span class="icon-bg"><i class="mdi mdi-account-multiple menu-icon"></i></span>
                <span class="menu-title">Usuários</span>
                <i class="menu-arrow"></i>
            </a>

            <div class="collapse {{ request()->routeIs('tenant.users.*') ? 'show' : '' }}" id="users-menu">
                <ul class="nav flex-column sub-menu">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('tenant.users.index') }}">Listar</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('tenant.users.create') }}">Criar Usuário</a>
                    </li>
                </ul>
            </div>
        </li>

        {{-- CONFIGURAÇÕES --}}
        <li class="nav-item nav-category">Configurações</li>

        {{-- Especialidades --}}
        <li class="nav-item {{ request()->routeIs('tenant.specialties.*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('tenant.specialties.index') }}">
                <span class="icon-bg"><i class="mdi mdi-pulse menu-icon"></i></span>
                <span class="menu-title">Especialidades</span>
            </a>
        </li>

        {{-- Tipos de Atendimento --}}
        <li class="nav-item {{ request()->routeIs('tenant.appointment-types.*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('tenant.appointment-types.index') }}">
                <span class="icon-bg"><i class="mdi mdi-clipboard-pulse menu-icon"></i></span>
                <span class="menu-title">Tipos de Atendimento</span>
            </a>
        </li>

        {{-- Horários Médicos --}}
        <li class="nav-item {{ request()->routeIs('tenant.business-hours.*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('tenant.business-hours.index') }}">
                <span class="icon-bg"><i class="mdi mdi-clock-outline menu-icon"></i></span>
                <span class="menu-title">Horários Médicos</span>
            </a>
        </li>

        {{-- FORMULÁRIOS --}}
        <li class="nav-item nav-category">Formulários</li>

        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="collapse" href="#forms-menu"
                aria-expanded="{{ request()->routeIs('tenant.forms.*') ? 'true' : 'false' }}">
                <span class="icon-bg"><i class="mdi mdi-file-document-edit menu-icon"></i></span>
                <span class="menu-title">Formulários</span>
                <i class="menu-arrow"></i>
            </a>

            <div class="collapse {{ request()->routeIs('tenant.forms.*') ? 'show' : '' }}" id="forms-menu">
                <ul class="nav flex-column sub-menu">
                    <li class="nav-item"><a class="nav-link" href="{{ route('tenant.forms.index') }}">Listar</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('tenant.forms.create') }}">Criar
                            Formulário</a></li>
                </ul>
            </div>
        </li>

        {{-- Respostas --}}
        <li class="nav-item {{ request()->routeIs('tenant.responses.*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('tenant.responses.index') }}">
                <span class="icon-bg"><i class="mdi mdi-file-document-box menu-icon"></i></span>
                <span class="menu-title">Respostas</span>
            </a>
        </li>

        {{-- INTEGRAÇÕES --}}
        <li class="nav-item nav-category">Integrações</li>

        <li class="nav-item {{ request()->routeIs('tenant.integrations.*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('tenant.integrations.index') }}">
                <span class="icon-bg"><i class="mdi mdi-puzzle menu-icon"></i></span>
                <span class="menu-title">Integrações</span>
            </a>
        </li>

        <li class="nav-item {{ request()->routeIs('tenant.oauth-accounts.*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('tenant.oauth-accounts.index') }}">
                <span class="icon-bg"><i class="mdi mdi-key-variant menu-icon"></i></span>
                <span class="menu-title">Contas Conectadas</span>
            </a>
        </li>

        <li class="nav-item {{ request()->routeIs('tenant.calendar-sync.*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('tenant.calendar-sync.index') }}">
                <span class="icon-bg"><i class="mdi mdi-sync menu-icon"></i></span>
                <span class="menu-title">Sincronização</span>
            </a>
        </li>

    </ul>
</nav>
