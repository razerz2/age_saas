@extends('layouts.tailadmin.portal')

@section('title', 'Dashboard')

@section('portal-content')
    <div class="page-header mb-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Dashboard do Portal do Paciente</h1>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Resumo rápido dos seus agendamentos e notificações.</p>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-xs text-gray-500 uppercase tracking-wide">Meus Agendamentos</p>
                <span class="text-2xl font-semibold text-blue-600">{{ $totalAppointments ?? 0 }}</span>
            </div>
            <p class="text-sm text-gray-500 mt-2">Total de agendamentos cadastrados no portal.</p>
        </div>
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-xs text-gray-500 uppercase tracking-wide">Próximos Agendamentos</p>
                <span class="text-2xl font-semibold text-blue-600">{{ $upcomingAppointments ?? 0 }}</span>
            </div>
            <p class="text-sm text-gray-500 mt-2">Agendamentos futuros na agenda.</p>
        </div>
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-xs text-gray-500 uppercase tracking-wide">Notificações</p>
                <span class="text-2xl font-semibold text-blue-600">{{ $unreadNotifications ?? 0 }}</span>
            </div>
            <p class="text-sm text-gray-500 mt-2">Mensagens e alertas não lidos.</p>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Próximos Agendamentos</h2>
        </div>
        <div class="p-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Data</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Horário</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Médico</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($recentAppointments ?? [] as $appointment)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">{{ $appointment->appointment_date->format('d/m/Y') }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">{{ $appointment->appointment_time }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">{{ $appointment->doctor->name ?? 'N/A' }}</td>
                                <td class="px-6 py-4 text-sm">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $appointment->status === 'confirmed' ? 'bg-green-100 text-green-800' : ($appointment->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                        {{ ucfirst($appointment->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <a href="{{ route('patient.appointments.edit', $appointment->id) }}" class="btn-patient-primary text-xs">
                                        Ver
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">Nenhum agendamento encontrado</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

