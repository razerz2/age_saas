<!-- My Profile -->
<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="javascript:void(0)" data-bs-toggle="dropdown" aria-haspopup="true"
        aria-expanded="false">
        <img src="{{ asset('freedash/assets/images/users/profile-pic.jpg') }}" alt="user" class="rounded-circle" width="40">
        <span class="ms-2 d-none d-lg-inline-block"><span class="text-dark"> {{ __('Profile') }} </span> <svg
                xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="feather feather-chevron-down svg-icon">
                <polyline points="6 9 12 15 18 9"></polyline>
            </svg></span>
    </a>
    <div class="dropdown-menu dropdown-menu-end dropdown-menu-right user-dd animated flipInY">
        <a class="dropdown-item" href="{{ route('profile.edit') }}"> <i class="fas fa-user"></i> - My Profile</a>
        <div class="dropdown-divider"></div>
        <a class="dropdown-item" href="javascript:void(0)"> <i class="fas fa-cog"></i> - Settings</a>
        <div class="dropdown-divider"></div>
        <!-- Logout -->
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="dropdown-item text-danger">
                <i class="fas fa-power-off me-2 text-danger"></i> {{ __('Sair') }}
            </button>
        </form>
    </div>
</li>
