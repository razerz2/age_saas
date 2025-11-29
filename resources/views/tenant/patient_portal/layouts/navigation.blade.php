<nav class="sidebar sidebar-offcanvas" id="sidebar">
    <ul class="nav">

        {{-- ============================================================
            MENU PRINCIPAL
        ============================================================ --}}
        <li class="nav-item nav-category">Menu Principal</li>

        {{-- DASHBOARD --}}
        <li class="nav-item {{ request()->routeIs('patient.dashboard') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('patient.dashboard') }}">
                <span class="icon-bg"><i class="mdi mdi-view-dashboard menu-icon"></i></span>
                <span class="menu-title">Dashboard</span>
            </a>
        </li>

        {{-- AGENDAMENTOS --}}
        <li class="nav-item {{ request()->routeIs('patient.appointments.*') ? 'active' : '' }}">
            <a class="nav-link" data-bs-toggle="collapse" href="#appointments-menu"
                aria-expanded="{{ request()->routeIs('patient.appointments.*') ? 'true' : 'false' }}">
                <span class="icon-bg"><i class="mdi mdi-calendar-clock menu-icon"></i></span>
                <span class="menu-title">Meus Agendamentos</span>
                <i class="menu-arrow"></i>
            </a>

            <div class="collapse {{ request()->routeIs('patient.appointments.*') ? 'show' : '' }}" id="appointments-menu">
                <ul class="nav flex-column sub-menu">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('patient.appointments.index') ? 'active' : '' }}" href="{{ route('patient.appointments.index') }}">Listar</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('patient.appointments.create') ? 'active' : '' }}" href="{{ route('patient.appointments.create') }}">Novo Agendamento</a>
                    </li>
                </ul>
            </div>
        </li>

        {{-- NOTIFICAÇÕES --}}
        <li class="nav-item {{ request()->routeIs('patient.notifications.*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('patient.notifications.index') }}">
                <span class="icon-bg"><i class="mdi mdi-bell-outline menu-icon"></i></span>
                <span class="menu-title">Notificações</span>
            </a>
        </li>

        {{-- PERFIL --}}
        <li class="nav-item {{ request()->routeIs('patient.profile.*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('patient.profile.index') }}">
                <span class="icon-bg"><i class="mdi mdi-account-circle menu-icon"></i></span>
                <span class="menu-title">Meu Perfil</span>
            </a>
        </li>

    </ul>
</nav>

