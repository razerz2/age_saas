@php
    $patientLogin = auth('patient')->user();
    $patient = $patientLogin->patient ?? null;
    $patientName = $patient->full_name ?? 'Paciente';
    $patientEmail = $patientLogin->email ?? null;
@endphp

<li class="nav-item nav-profile dropdown">
    <a class="nav-link dropdown-toggle d-flex align-items-center" id="profileDropdown" href="#" 
       data-bs-toggle="dropdown" aria-expanded="false" style="padding: 0.5rem 1rem;">

        <div class="nav-profile-img me-2" style="position: relative;">
            <img src="{{ asset('tailadmin/assets/images/user/user-01.jpg') }}" alt="{{ $patientName }}" 
                 style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 2px solid #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
        </div>

        <div class="nav-profile-text d-none d-md-block">
            <p class="mb-0 fw-semibold text-dark" style="font-size: 0.9rem; line-height: 1.2;">
                {{ $patientName }}
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
                <img src="{{ asset('tailadmin/assets/images/user/user-01.jpg') }}" alt="{{ $patientName }}"
                     style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover; border: 4px solid rgba(255,255,255,0.3); box-shadow: 0 4px 12px rgba(0,0,0,0.2);">
            </div>
            <h6 class="mt-3 mb-1 fw-bold" style="font-size: 1.1rem;">{{ $patientName }}</h6>
            @if($patientEmail)
                <small class="opacity-75" style="font-size: 0.85rem;">{{ $patientEmail }}</small>
            @endif
        </div>

        {{-- Menu Items --}}
        <div class="p-2">

            <h6 class="dropdown-header text-uppercase fw-bold mb-2" 
                style="font-size: 0.7rem; letter-spacing: 1px; color: #6c757d; padding: 0.75rem 1rem 0.5rem;">
                <i class="mdi mdi-account-circle-outline me-2"></i>Conta
            </h6>

            <a class="dropdown-item d-flex align-items-center py-3 px-3 rounded-2 mb-1" 
               href="{{ route('patient.profile.index') }}" 
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

            <div class="dropdown-divider my-2"></div>

            <h6 class="dropdown-header text-uppercase fw-bold mb-2" 
                style="font-size: 0.7rem; letter-spacing: 1px; color: #6c757d; padding: 0.75rem 1rem 0.5rem;">
                <i class="mdi mdi-lightning-bolt-outline me-2"></i>Ações
            </h6>

            <a class="dropdown-item d-flex align-items-center py-3 px-3 rounded-2" 
                href="{{ route('patient.logout') }}"
               onclick="event.preventDefault(); document.getElementById('patient-logout-form').submit();"
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

            <form id="patient-logout-form" action="{{ route('patient.logout') }}"
                method="POST" class="d-none">
                @csrf
            </form>
        </div>
    </div>
</li>

