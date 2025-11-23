<li class="nav-item dropdown">
    <a class="nav-link count-indicator dropdown-toggle" id="notificationDropdown" href="#" data-bs-toggle="dropdown">
        <i class="mdi mdi-bell-outline"></i>
        <span class="count-symbol bg-danger"></span>
    </a>

    <div class="dropdown-menu dropdown-menu-end navbar-dropdown preview-list" aria-labelledby="notificationDropdown">

        <h6 class="p-3 mb-0 bg-primary text-white py-4">Notificações</h6>

        <div class="dropdown-divider"></div>

        {{-- Exemplo 1 --}}
        <a class="dropdown-item preview-item">
            <div class="preview-thumbnail">
                <div class="preview-icon bg-success">
                    <i class="mdi mdi-calendar"></i>
                </div>
            </div>
            <div class="preview-item-content d-flex flex-column">
                <h6 class="preview-subject font-weight-normal mb-1">Evento Hoje</h6>
                <p class="text-gray ellipsis mb-0">
                    Você possui um agendamento hoje.
                </p>
            </div>
        </a>

        <div class="dropdown-divider"></div>

        {{-- Exemplo 2 --}}
        <a class="dropdown-item preview-item">
            <div class="preview-thumbnail">
                <div class="preview-icon bg-warning">
                    <i class="mdi mdi-settings"></i>
                </div>
            </div>
            <div class="preview-item-content d-flex flex-column">
                <h6 class="preview-subject font-weight-normal mb-1">Configurações</h6>
                <p class="text-gray ellipsis mb-0">Atualize seu painel</p>
            </div>
        </a>

        <div class="dropdown-divider"></div>

        <h6 class="p-3 mb-0 text-center">Ver todas notificações</h6>
    </div>
</li>
