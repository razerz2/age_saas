<aside
    class="z-50 flex w-64 flex-col bg-white shadow-lg dark:bg-gray-900"
>
    <div class="flex items-center gap-2 px-6 py-4 border-b border-gray-200 dark:border-gray-700">
        <a href="{{ route('patient.dashboard', ['slug' => request()->route('slug')]) }}" class="flex items-center gap-2">
            <img src="{{ asset('tailadmin/assets/images/logo/logo.svg') }}" alt="Logo" class="h-8 w-auto">
            <span class="text-sm font-semibold text-gray-900 dark:text-white">Portal do Paciente</span>
        </a>
    </div>

    <nav class="flex-1 space-y-1 px-3 py-4 text-sm">
        <a href="{{ route('patient.dashboard', ['slug' => request()->route('slug')]) }}"
           class="flex items-center gap-2 rounded-lg px-3 py-2 text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-800 {{ request()->routeIs('patient.dashboard') ? 'bg-gray-100 dark:bg-gray-800 font-semibold' : '' }}">
            <span class="mdi mdi-view-dashboard-outline text-lg"></span>
            <span>Dashboard</span>
        </a>

        <a href="{{ route('patient.appointments.index', ['slug' => request()->route('slug')]) }}"
           class="flex items-center gap-2 rounded-lg px-3 py-2 text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-800 {{ request()->routeIs('patient.appointments.*') ? 'bg-gray-100 dark:bg-gray-800 font-semibold' : '' }}">
            <span class="mdi mdi-calendar-clock text-lg"></span>
            <span>Agendamentos</span>
        </a>

        <a href="{{ route('patient.notifications.index', ['slug' => request()->route('slug')]) }}"
           class="flex items-center gap-2 rounded-lg px-3 py-2 text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-800 {{ request()->routeIs('patient.notifications.*') ? 'bg-gray-100 dark:bg-gray-800 font-semibold' : '' }}">
            <span class="mdi mdi-bell-outline text-lg"></span>
            <span>Notificações</span>
        </a>

        <a href="{{ route('patient.profile.index', ['slug' => request()->route('slug')]) }}"
           class="flex items-center gap-2 rounded-lg px-3 py-2 text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-800 {{ request()->routeIs('patient.profile.*') ? 'bg-gray-100 dark:bg-gray-800 font-semibold' : '' }}">
            <span class="mdi mdi-account-circle-outline text-lg"></span>
            <span>Perfil</span>
        </a>
    </nav>

    <div class="border-t border-gray-200 px-3 py-4 dark:border-gray-700">
        <form method="POST" action="{{ route('patient.logout', ['slug' => request()->route('slug')]) }}">
            @csrf
            <button type="submit"
                    class="flex w-full items-center justify-center gap-2 rounded-lg bg-red-50 px-3 py-2 text-sm font-medium text-red-700 hover:bg-red-100 dark:bg-red-900/20 dark:text-red-200 dark:hover:bg-red-900/40">
                <span class="mdi mdi-logout text-lg"></span>
                <span>Sair</span>
            </button>
        </form>
    </div>
</aside>
