@extends('layouts.tailadmin.app')

@section('page', 'dashboard')

@section('content')
<!-- Filtro Global de Período -->
<div class="mb-6 rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
    <form method="GET" action="{{ route('tenant.dashboard', tenant('slug')) }}" class="flex flex-wrap items-center gap-4">
        <div class="flex items-center gap-2">
            <svg class="h-5 w-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Período:</span>
        </div>
        
        <div class="flex flex-wrap gap-2">
            <button type="submit" name="period" value="today" 
                class="px-3 py-1.5 text-sm font-medium rounded-lg transition-colors
                {{ request('period') == 'today' ? 'bg-primary text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700' }}">
                Hoje
            </button>
            <button type="submit" name="period" value="week" 
                class="px-3 py-1.5 text-sm font-medium rounded-lg transition-colors
                {{ request('period') == 'week' ? 'bg-primary text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700' }}">
                Semana
            </button>
            <button type="submit" name="period" value="month" 
                class="px-3 py-1.5 text-sm font-medium rounded-lg transition-colors
                {{ request('period') == 'month' ? 'bg-primary text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700' }}">
                Mês
            </button>
            <button type="submit" name="period" value="year" 
                class="px-3 py-1.5 text-sm font-medium rounded-lg transition-colors
                {{ request('period') == 'year' ? 'bg-primary text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700' }}">
                Ano
            </button>
        </div>
        
        <div class="flex items-center gap-2">
            <input type="date" name="start_date" value="{{ request('start_date') ?? '' }}" 
                class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white">
            <span class="text-gray-500 dark:text-gray-400">até</span>
            <input type="date" name="end_date" value="{{ request('end_date') ?? '' }}" 
                class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white">
            <button type="submit" 
                class="px-3 py-1.5 text-sm font-medium bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                Aplicar
            </button>
        </div>
        
        @if(request()->hasAny(['period', 'start_date', 'end_date']))
            <a href="{{ route('tenant.dashboard', tenant('slug')) }}" 
                class="px-3 py-1.5 text-sm font-medium text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200 transition-colors">
                Limpar filtro
            </a>
        @endif
    </form>
</div>

<div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h2 class="text-title-md font-bold text-gray-900 dark:text-white">Dashboard</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400">
            @if($periodDescription)
                {{ $periodDescription }}
            @else
                Visão geral do tenant
            @endif
        </p>
    </div>
    <div class="flex items-center gap-3">
        <span class="text-xs text-gray-400 dark:text-gray-300">Atualizado agora</span>
        <span class="inline-flex items-center rounded-full bg-primary/10 px-2 py-1 text-xs font-medium text-primary dark:bg-primary/20 dark:text-primary">
            Dados Reais
        </span>
    </div>
</div>

<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4 md:gap-6">
    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gray-100 text-primary dark:bg-gray-800 dark:text-primary">
            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor">
                <path d="M8 7V3h2v4h4V3h2v4h3a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V9a2 2 0 0 1 2-2h3zm11 6H5v6h14v-6z"/>
            </svg>
        </div>
        <div class="mt-5 flex items-end justify-between">
            <div>
                <h3 class="text-title-md font-bold text-gray-900 dark:text-white">{{ number_format($stats['period']['total'], 0, ',', '.') }}</h3>
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">
                    @if(request('period') == 'today')
                        Consultas Hoje
                    @elseif(request('period') == 'week')
                        Consultas na Semana
                    @elseif(request('period') == 'month')
                        Consultas no Mês
                    @elseif(request('period') == 'year')
                        Consultas no Ano
                    @else
                        Consultas no Período
                    @endif
                </span>
            </div>
            @if($stats['period']['variation'] != 0)
                <span class="flex items-center gap-1 rounded-full {{ $stats['period']['variation'] > 0 ? 'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-500' : 'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-500' }} py-0.5 pl-2 pr-2.5 text-xs font-medium">
                    {{ $stats['period']['variation'] > 0 ? '+' : '' }}{{ $stats['period']['variation'] }}%
                </span>
            @endif
        </div>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gray-100 text-success dark:bg-gray-800 dark:text-success">
            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-3.33 0-8 1.67-8 5v2h16v-2c0-3.33-4.67-5-8-5z"/>
            </svg>
        </div>
        <div class="mt-5 flex items-end justify-between">
            <div>
                <h3 class="text-title-md font-bold text-gray-900 dark:text-white">{{ number_format($stats['patients']['total'], 0, ',', '.') }}</h3>
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Pacientes Ativos</span>
            </div>
            @if($stats['patients']['variation'] != 0)
                <span class="flex items-center gap-1 rounded-full {{ $stats['patients']['variation'] > 0 ? 'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-500' : 'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-500' }} py-0.5 pl-2 pr-2.5 text-xs font-medium">
                    {{ $stats['patients']['variation'] > 0 ? '+' : '' }}{{ $stats['patients']['variation'] }}%
                </span>
            @endif
        </div>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gray-100 text-warning dark:bg-gray-800 dark:text-warning">
            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 11c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm-7 9a7 7 0 0 1 14 0v1H5v-1z"/>
            </svg>
        </div>
        <div class="mt-5 flex items-end justify-between">
            <div>
                <h3 class="text-title-md font-bold text-gray-900 dark:text-white">{{ number_format($stats['doctors']['total'], 0, ',', '.') }}</h3>
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Profissionais</span>
            </div>
            @if($stats['doctors']['variation'] != 0)
                <span class="flex items-center gap-1 rounded-full bg-gray-100 py-0.5 pl-2 pr-2.5 text-xs font-medium text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                    {{ $stats['doctors']['variation'] > 0 ? '+' : '' }}{{ $stats['doctors']['variation'] }}
                </span>
            @endif
        </div>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gray-100 text-secondary dark:bg-gray-800 dark:text-secondary">
            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor">
                <path d="M7 3h10a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2zm0 6h10V7H7v2zm0 4h10v-2H7v2z"/>
            </svg>
        </div>
        <div class="mt-5 flex items-end justify-between">
            <div>
                <h3 class="text-title-md font-bold text-gray-900 dark:text-white">{{ number_format($stats['specialties']['total'], 0, ',', '.') }}</h3>
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Especialidades</span>
            </div>
            @if($stats['specialties']['variation'] != 0)
                <span class="flex items-center gap-1 rounded-full {{ $stats['specialties']['variation'] > 0 ? 'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-500' : 'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-500' }} py-0.5 pl-2 pr-2.5 text-xs font-medium">
                    {{ $stats['specialties']['variation'] > 0 ? '+' : '' }}{{ $stats['specialties']['variation'] }}%
                </span>
            @endif
        </div>
    </div>
</div>

<div class="mt-6 grid grid-cols-12 gap-4 md:gap-6">
    <div class="col-span-12 space-y-6 xl:col-span-7">
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white px-5 pt-5 dark:border-gray-800 dark:bg-white/[0.03] sm:px-6 sm:pt-6">
                <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Evolução de Agendamentos</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Últimos 12 meses</p>
                </div>
                    <span class="text-xs text-gray-400 dark:text-gray-300">Dados reais</span>
            </div>
            <div class="relative mt-4 h-72 overflow-hidden rounded-xl border border-gray-200 bg-gradient-to-br from-primary/5 via-transparent to-secondary/10 dark:border-gray-800">
                <canvas id="appointmentsChart" width="400" height="250"></canvas>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="mb-4 flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Próximas consultas</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        @if(request('period') == 'today')
                            Agenda do dia
                        @else
                            Próximas 24 horas
                        @endif
                    </p>
                </div>
                <a href="{{ route('tenant.appointments.index', tenant('slug')) }}" class="text-sm font-medium text-primary hover:underline dark:text-primary">Ver agenda</a>
            </div>
            <div class="space-y-3">
                @forelse($appointmentsNext as $appointment)
                    <div class="flex items-center justify-between rounded-lg border border-gray-200 p-3 dark:border-gray-800">
                        <div class="flex items-center gap-3">
                            @if($appointment->patient && $appointment->patient->user)
                                @if($appointment->patient->user->avatar)
                                    <img class="h-10 w-10 rounded-full object-cover" src="{{ asset('storage/' . $appointment->patient->user->avatar) }}" alt="{{ $appointment->patient->user->name }}">
                                @else
                                    <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center dark:bg-gray-700">
                                        <span class="text-gray-600 font-medium dark:text-gray-200">{{ substr($appointment->patient->user->name, 0, 1) }}</span>
                                    </div>
                                @endif
                            @else
                                <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center dark:bg-gray-700">
                                    <span class="text-gray-600 font-medium dark:text-gray-200">?</span>
                                </div>
                            @endif
                            <div>
                                <p class="font-medium text-gray-900 dark:text-white">
                                    {{ $appointment->patient->user->name ?? 'Paciente não identificado' }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $appointment->specialty->name ?? 'Sem especialidade' }} • 
                                    {{ $appointment->starts_at->format('H:i') }}
                                </p>
                            </div>
                        </div>
                        <span class="rounded-full 
                            @if($appointment->status == 'confirmed')
                                bg-success-50 px-2 py-1 text-xs font-medium text-success-600 dark:bg-success-500/15 dark:text-success-500
                            @elseif($appointment->status == 'scheduled')
                                bg-primary/10 px-2 py-1 text-xs font-medium text-primary dark:bg-primary/20 dark:text-primary
                            @elseif($appointment->status == 'arrived')
                                bg-warning/10 px-2 py-1 text-xs font-medium text-warning dark:bg-warning/20 dark:text-warning
                            @else
                                bg-gray-100 px-2 py-1 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-400
                            @endif
                        ">
                            {{ $appointment->status_translated }}
                        </span>
                    </div>
                @empty
                    <div class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                            Nenhuma consulta encontrada para este período
                        </p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-span-12 xl:col-span-5">
        <div class="rounded-2xl border border-gray-200 bg-gray-100 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="shadow-default rounded-2xl bg-white px-5 pb-8 pt-5 dark:bg-gray-900 sm:px-6 sm:pt-6">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Resumo do período</h3>
                    <span class="text-xs text-gray-400">Performance geral</span>
                </div>
                <div class="mt-6 space-y-4">
                    <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-800">
                        <p class="text-xs text-gray-500 dark:text-gray-400">Taxa de comparecimento</p>
                        <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($summary['attendance_rate'], 1, ',', '.') }}%</p>
                    </div>
                    <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-800">
                        <p class="text-xs text-gray-500 dark:text-gray-400">Cancelamentos</p>
                        <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($summary['cancellations'], 0, ',', '.') }}</p>
                    </div>
                    <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-800">
                        <p class="text-xs text-gray-500 dark:text-gray-400">Tempo médio de espera</p>
                        <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($summary['avg_wait_time'], 0, ',', '.') }} min</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-6 rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="mb-4 flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Profissionais em destaque</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Atendimento por especialidade</p>
                </div>
                    <span class="text-xs text-gray-400 dark:text-gray-300">Top 4</span>
            </div>
            <div class="space-y-4">
                @forelse($activeDoctorsToday as $doctor)
                    <div class="flex items-center gap-4">
                        @if($doctor['avatar'])
                            <img class="h-12 w-12 rounded-full object-cover" src="{{ asset('storage/' . $doctor['avatar']) }}" alt="{{ $doctor['doctor'] }}">
                        @else
                            <div class="h-12 w-12 rounded-full bg-gray-300 flex items-center justify-center dark:bg-gray-700">
                                <span class="text-gray-600 font-medium dark:text-gray-200">{{ substr($doctor['doctor'], 0, 1) }}</span>
                            </div>
                        @endif
                        <div class="flex-1">
                            <p class="font-medium text-gray-900 dark:text-white">{{ $doctor['doctor'] }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $doctor['specialty'] }}</p>
                        </div>
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $doctor['count'] }}</p>
                    </div>
                @empty
                    <div class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                            Nenhum profissional com agendamentos neste período
                        </p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
	window.dashboardChartData = @json($chartLast12Months);
</script>
@endpush
