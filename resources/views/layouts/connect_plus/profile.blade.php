@php
    // Sempre usar o guard tenant na área tenant
    $user = auth('tenant')->user();
    $userName = $user->name ?? 'Usuário';
    $userNameFull = $user->name_full ?? $user->name ?? 'Usuário';
    $userEmail = $user->email ?? null;
    // Usar o accessor avatar_url que já trata a URL corretamente
    $userAvatar = $user ? $user->avatar_url : asset('connect_plus/assets/images/faces/default.jpg');
    $isDoctor = $user->is_doctor ?? false;
    $userRole = $user->role ?? 'user';
    
    // Determinar o texto do role para exibição
    $roleText = match($userRole) {
        'admin' => 'Administrador',
        'doctor' => 'Médico',
        default => 'Usuário'
    };
@endphp

<li class="nav-item nav-profile dropdown">
    <a class="nav-link dropdown-toggle d-flex align-items-center" id="profileDropdown" href="#" 
       data-bs-toggle="dropdown" aria-expanded="false" style="padding: 0.5rem 1rem;">

        <div class="nav-profile-img me-2" style="position: relative;">
            <img src="{{ $userAvatar }}" alt="{{ $userName }}" 
                 style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 2px solid #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            @if($isDoctor)
                <span class="badge bg-success" 
                      style="position: absolute; bottom: -2px; right: -2px; width: 14px; height: 14px; border-radius: 50%; border: 2px solid #fff; padding: 0;"></span>
            @endif
        </div>

        <div class="nav-profile-text d-none d-md-block">
            <p class="mb-0 fw-semibold text-dark" style="font-size: 0.9rem; line-height: 1.2;">
                {{ $userName }}
            </p>
        </div>

        <i class="mdi mdi-chevron-down ms-2 text-muted" style="font-size: 1.2rem;"></i>

    </a>

    <div class="dropdown-menu navbar-dropdown dropdown-menu-end p-0 border-0 shadow-lg" 
         aria-labelledby="profileDropdown" 
         style="min-width: 280px; border-radius: 12px; overflow: hidden; margin-top: 8px;">

        {{-- Header com gradiente --}}
        <div class="p-4 text-center text-white" 
             style="background: linear-gradient(135deg, #0062ff 0%, #0052d4 100%); position: relative;">
            <div style="position: relative; display: inline-block;">
                <img src="{{ $userAvatar }}" alt="{{ $userName }}" 
                     style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover; border: 4px solid rgba(255,255,255,0.3); box-shadow: 0 4px 12px rgba(0,0,0,0.2);">
                @if($isDoctor)
                    <span class="badge bg-success position-absolute" 
                          style="bottom: 0; right: 0; width: 24px; height: 24px; border-radius: 50%; border: 3px solid #fff; display: flex; align-items: center; justify-content: center;">
                        <i class="mdi mdi-check" style="font-size: 12px;"></i>
                    </span>
                @endif
            </div>
            <h6 class="mt-3 mb-1 fw-bold" style="font-size: 1.1rem;">{{ $userNameFull }}</h6>
            <span class="badge mt-2 px-3 py-1" style="font-size: 0.75rem; background: rgba(255, 255, 255, 0.2); color: #fff; border: 1px solid rgba(255, 255, 255, 0.3);">
                @if($userRole === 'admin')
                    <i class="mdi mdi-shield-account me-1"></i>Administrador
                @elseif($userRole === 'doctor' || $isDoctor)
                    <i class="mdi mdi-stethoscope me-1"></i>Médico
                @else
                    <i class="mdi mdi-account me-1"></i>Usuário
                @endif
            </span>
        </div>

        {{-- Menu Items --}}
        <div class="p-2">

            <h6 class="dropdown-header text-uppercase fw-bold mb-2" 
                style="font-size: 0.7rem; letter-spacing: 1px; color: #6c757d; padding: 0.75rem 1rem 0.5rem;">
                <i class="mdi mdi-account-circle-outline me-2"></i>Conta
            </h6>

            <a class="dropdown-item d-flex align-items-center py-3 px-3 rounded-2 mb-1" 
               href="{{ route('tenant.profile.edit') }}" 
               style="transition: all 0.2s ease; border-left: 3px solid transparent;"
               onmouseover="this.style.backgroundColor='#f8f9fa'; this.style.borderLeftColor='#0062ff';"
               onmouseout="this.style.backgroundColor='transparent'; this.style.borderLeftColor='transparent';">
                <div class="d-flex align-items-center justify-content-center me-3" 
                     style="width: 36px; height: 36px; background: #0062ff; border-radius: 8px;">
                    <i class="mdi mdi-account-outline text-white" style="font-size: 1.1rem;"></i>
                </div>
                <div class="flex-grow-1">
                    <span class="fw-semibold d-block" style="font-size: 0.9rem; color: #212529;">Meu Perfil</span>
                    <span class="text-muted" style="font-size: 0.75rem;">Visualizar e editar perfil</span>
                </div>
                <i class="mdi mdi-chevron-right text-muted"></i>
            </a>

            <a class="dropdown-item d-flex align-items-center py-3 px-3 rounded-2 mb-1" 
               href="{{ route('tenant.settings.index') }}" 
               style="transition: all 0.2s ease; border-left: 3px solid transparent;"
               onmouseover="this.style.backgroundColor='#f8f9fa'; this.style.borderLeftColor='#0062ff';"
               onmouseout="this.style.backgroundColor='transparent'; this.style.borderLeftColor='transparent';">
                <div class="d-flex align-items-center justify-content-center me-3" 
                     style="width: 36px; height: 36px; background: #a461d8; border-radius: 8px;">
                    <i class="mdi mdi-cog-outline text-white" style="font-size: 1.1rem;"></i>
                </div>
                <div class="flex-grow-1">
                    <span class="fw-semibold d-block" style="font-size: 0.9rem; color: #212529;">Configurações</span>
                    <span class="text-muted" style="font-size: 0.75rem;">Preferências e opções</span>
                </div>
                <i class="mdi mdi-chevron-right text-muted"></i>
            </a>

            <div class="dropdown-divider my-2"></div>

            <h6 class="dropdown-header text-uppercase fw-bold mb-2" 
                style="font-size: 0.7rem; letter-spacing: 1px; color: #6c757d; padding: 0.75rem 1rem 0.5rem;">
                <i class="mdi mdi-lightning-bolt-outline me-2"></i>Ações
            </h6>

            <a class="dropdown-item d-flex align-items-center py-3 px-3 rounded-2" 
                href="{{ route('tenant.logout', ['tenant' => tenant()->subdomain]) }}"
               onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
               style="transition: all 0.2s ease; border-left: 3px solid transparent; color: #fc5a5a;"
               onmouseover="this.style.backgroundColor='#fff5f5'; this.style.borderLeftColor='#fc5a5a';"
               onmouseout="this.style.backgroundColor='transparent'; this.style.borderLeftColor='transparent';">
                <div class="d-flex align-items-center justify-content-center me-3" 
                     style="width: 36px; height: 36px; background: #fc5a5a; border-radius: 8px;">
                    <i class="mdi mdi-logout text-white" style="font-size: 1.1rem;"></i>
                </div>
                <div class="flex-grow-1">
                    <span class="fw-semibold d-block" style="font-size: 0.9rem;">Sair</span>
                    <span class="text-muted" style="font-size: 0.75rem;">Encerrar sessão</span>
                </div>
                <i class="mdi mdi-chevron-right text-muted"></i>
            </a>

            <form id="logout-form" action="{{ route('tenant.logout', ['tenant' => tenant()->subdomain]) }}"
                method="POST" class="d-none">
                @csrf
            </form>
        </div>
    </div>
</li>
