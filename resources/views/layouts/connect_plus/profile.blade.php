<li class="nav-item nav-profile dropdown">
    <a class="nav-link dropdown-toggle" id="profileDropdown" href="#" data-bs-toggle="dropdown" aria-expanded="false">

        <div class="nav-profile-img">
            <img src="{{ asset('connect_plus/assets/images/faces/face28.png') }}" alt="image">
        </div>

        @php
            // Sempre usar o guard tenant na área tenant
            $user = auth('tenant')->user();
        @endphp

        <div class="nav-profile-text">
            <p class="mb-1 text-black">
                {{ $user->name ?? 'Usuário' }}
            </p>
        </div>

    </a>

    <div class="dropdown-menu navbar-dropdown dropdown-menu-right p-0 border-0 font-size-sm"
        aria-labelledby="profileDropdown">

        <div class="p-3 text-center bg-primary">
            <img class="img-avatar img-avatar48 img-avatar-thumb"
                src="{{ asset('connect_plus/assets/images/faces/face28.png') }}" alt="">
        </div>

        <div class="p-2">

            <h5 class="dropdown-header text-uppercase ps-2 text-dark">Conta</h5>

            <a class="dropdown-item py-1 d-flex align-items-center justify-content-between" href="#">
                <span>Meu Perfil</span>
                <i class="mdi mdi-account-outline"></i>
            </a>

            <a class="dropdown-item py-1 d-flex align-items-center justify-content-between" href="#">
                <span>Configurações</span>
                <i class="mdi mdi-settings"></i>
            </a>

            <div class="dropdown-divider"></div>

            <h5 class="dropdown-header text-uppercase ps-2 text-dark mt-2">Ações</h5>

            <a class="dropdown-item py-1 d-flex align-items-center justify-content-between"
                href="{{ route('tenant.logout', ['tenant' => tenant()->subdomain]) }}"
                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <span>Sair</span>
                <i class="mdi mdi-logout"></i>
            </a>

            <form id="logout-form" action="{{ route('tenant.logout', ['tenant' => tenant()->subdomain]) }}"
                method="POST" class="d-none">
                @csrf
            </form>
        </div>
    </div>
</li>
