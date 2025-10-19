<!-- Sidebar navigation-->
<nav class="sidebar-nav">
    <ul id="sidebarnav" class="in">
        <li class="sidebar-item selected"> <a class="sidebar-link sidebar-link" href="{{ route('dashboard') }}" aria-expanded="false"><svg
                    xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="feather feather-home feather-icon">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                    <polyline points="9 22 9 12 15 12 15 22"></polyline>
                </svg><span class="hide-menu">Dashboard</span></a></li>
        <li class="list-divider"></li>
        <li class="nav-small-cap"><span class="hide-menu"> Categorias </span></li>

        <li class="sidebar-item"> <a class="sidebar-link has-arrow" href="javascript:void(0)" aria-expanded="false"><i
                    class="fas fa-briefcase"></i><span class="hide-menu"> Empresa </span></a>
            <ul aria-expanded="false" class="collapse first-level base-level-line">
                <li class="sidebar-item"><a href="{{ route('Platform.tenants.index') }}" class="sidebar-link"><span
                            class="hide-menu">
                            <i class="fas fa-list-alt"></i> Empresas
                        </span></a>
                </li>
                <li class="sidebar-item"><a href="{{ route('Platform.tenants.create') }}" class="sidebar-link"><span
                            class="hide-menu"> <i class="fas fa-plus-circle"></i> Nova Empresa
                        </span></a>
                </li>
            </ul>
        </li>

        <li class="sidebar-item"> <a class="sidebar-link has-arrow" href="javascript:void(0)" aria-expanded="false"><i
                    class="fas fa-briefcase"></i><span class="hide-menu"> Plano </span></a>
            <ul aria-expanded="false" class="collapse first-level base-level-line">
                <li class="sidebar-item"><a href="{{ route('Platform.plans.index') }}" class="sidebar-link"><span
                            class="hide-menu">
                            <i class="fas fa-list-alt"></i> Planos
                        </span></a>
                </li>
                <li class="sidebar-item"><a href="{{ route('Platform.plans.create') }}" class="sidebar-link"><span
                            class="hide-menu"> <i class="fas fa-plus-circle"></i> Novo Plano
                        </span></a>
                </li>
            </ul>
        </li>

        <li class="sidebar-item"> <a class="sidebar-link has-arrow" href="javascript:void(0)" aria-expanded="false">
            <i class="fas fa-sync"></i><span class="hide-menu"> Assinaturas </span></a>
            <ul aria-expanded="false" class="collapse first-level base-level-line">
                <li class="sidebar-item"><a href="{{ route('Platform.subscriptions.index') }}" class="sidebar-link"><span
                            class="hide-menu">
                            <i class="fas fa-list-alt"></i> Assinaturas
                        </span></a>
                </li>
                <li class="sidebar-item"><a href="{{ route('Platform.subscriptions.create') }}" class="sidebar-link"><span
                            class="hide-menu"> <i class="fas fa-plus-circle"></i> Nova Assinatura
                        </span></a>
                </li>
            </ul>
        </li>

        <li class="sidebar-item"> <a class="sidebar-link has-arrow" href="javascript:void(0)" aria-expanded="false">
            <i class="fas fa-money-bill-alt"></i><span class="hide-menu"> Faturas </span></a>
            <ul aria-expanded="false" class="collapse first-level base-level-line">
                <li class="sidebar-item"><a href="{{ route('Platform.invoices.index') }}" class="sidebar-link"><span
                            class="hide-menu">
                            <i class="fas fa-list-alt"></i> Faturas
                        </span></a>
                </li>
                <li class="sidebar-item"><a href="{{ route('Platform.invoices.create') }}" class="sidebar-link"><span
                            class="hide-menu"> <i class="fas fa-plus-circle"></i> Nova Fatura
                        </span></a>
                </li>
            </ul>
        </li>

        <li class="sidebar-item"> <a class="sidebar-link has-arrow" href="javascript:void(0)" aria-expanded="false">
            <i class="fas fa-stethoscope"></i><span class="hide-menu"> Especialidade Médica </span></a>
            <ul aria-expanded="false" class="collapse first-level base-level-line">
                <li class="sidebar-item"><a href="{{ route('Platform.medical_specialties_catalog.index') }}" class="sidebar-link"><span
                            class="hide-menu">
                            <i class="fas fa-list-alt"></i> Especialidades
                        </span></a>
                </li>
                <li class="sidebar-item"><a href="{{ route('Platform.medical_specialties_catalog.create') }}" class="sidebar-link"><span
                            class="hide-menu"> <i class="fas fa-plus-circle"></i> Nova Especialidade
                        </span></a>
                </li>
            </ul>
        </li>

        <li class="nav-small-cap"><span class="hide-menu"> Sistema </span></li>

        <li class="sidebar-item"> <a class="sidebar-link has-arrow" href="javascript:void(0)" aria-expanded="false">
            <i class="fas fa-bell"></i><span class="hide-menu"> Notificações </span></a>
            <ul aria-expanded="false" class="collapse first-level base-level-line">
                <li class="sidebar-item"><a href="{{ route('Platform.notifications_outbox.index') }}" class="sidebar-link"><span
                            class="hide-menu">
                            <i class="fas fa-list-alt"></i> Notificações
                        </span></a>
                </li>
                <li class="sidebar-item"><a href="{{ route('Platform.notifications_outbox.create') }}" class="sidebar-link"><span
                            class="hide-menu"> <i class="fas fa-plus-circle"></i> Nova Notificação
                        </span></a>
                </li>
            </ul>
        </li>

        <li class="sidebar-item"> <a class="sidebar-link has-arrow" href="javascript:void(0)" aria-expanded="false">
            <i class="fas fa-user"></i><span class="hide-menu"> Usuário </span></a>
            <ul aria-expanded="false" class="collapse first-level base-level-line">
                <li class="sidebar-item"><a href="{{ route('Platform.users.index') }}" class="sidebar-link"><span
                            class="hide-menu">
                            <i class="fas fa-list-alt"></i> Usuários
                        </span></a>
                </li>
                <li class="sidebar-item"><a href="{{ route('Platform.users.create') }}" class="sidebar-link"><span
                            class="hide-menu"> <i class="fas fa-plus-circle"></i> Novo Usuário
                        </span></a>
                </li>
            </ul>
        </li>

        <li class="sidebar-item"> <a class="sidebar-link has-arrow" href="javascript:void(0)" aria-expanded="false">
            <i class="fas fa-map"></i><span class="hide-menu"> Locais </span></a>
            <ul aria-expanded="false" class="collapse first-level base-level-line">
                <li class="sidebar-item"><a href="{{ route('Platform.paises.index') }}" class="sidebar-link"><span
                            class="hide-menu">
                            <i class="fas fa-list-alt"></i> 
                            Paises
                        </span></a>
                </li>
                <li class="sidebar-item"><a href="{{ route('Platform.estados.index') }}" class="sidebar-link"><span
                            class="hide-menu"> 
                            <i class="fas fa-list-alt"></i> 
                            Estados
                        </span></a>
                </li>
                <li class="sidebar-item"><a href="{{ route('Platform.cidades.index') }}" class="sidebar-link"><span
                            class="hide-menu"> 
                            <i class="fas fa-list-alt"></i> 
                            Cidades
                        </span></a>
                </li>
            </ul>
        </li>
    </ul>
</nav>
<!-- End Sidebar navigation -->
