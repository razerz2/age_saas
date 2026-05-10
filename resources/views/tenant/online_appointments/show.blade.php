@extends('layouts.tailadmin.app')

@section('title', 'Consulta Online')
@section('page', 'online_appointments')

@section('content')
    @php
        $instructions = $appointment->onlineInstructions;
        $meetingStatus = (string) ($instructions->meeting_status ?? '');

        $statusMap = [
            'generated' => 'Gerada',
            'pending' => 'Pendente',
            'failed' => 'Falhou',
            'manual_required' => 'Ação manual necessária',
            'cancelled' => 'Cancelada',
            'skipped' => 'Ignorada',
        ];

        $statusClassMap = [
            'generated' => 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400',
            'pending' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/20 dark:text-amber-300',
            'failed' => 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-300',
            'manual_required' => 'bg-orange-100 text-orange-800 dark:bg-orange-900/20 dark:text-orange-300',
            'cancelled' => 'bg-slate-200 text-slate-700 dark:bg-slate-800 dark:text-slate-300',
            'skipped' => 'bg-sky-100 text-sky-800 dark:bg-sky-900/20 dark:text-sky-300',
        ];

        $statusLabel = $statusMap[$meetingStatus] ?? 'Não gerada';
        $statusClass = $statusClassMap[$meetingStatus] ?? 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300';

        $provider = (string) ($instructions->meeting_provider ?? '');
        $providerLabel = match ($provider) {
            'google_meet' => 'Google Meet',
            'manual' => 'Manual',
            '' => 'Não definido',
            default => $provider,
        };

        $appointmentStatus = strtolower((string) ($appointment->status ?? ''));
        $isFinishedStatus = in_array($appointmentStatus, ['canceled', 'cancelled', 'expired'], true);
        $canCancel = !in_array($appointmentStatus, ['canceled', 'cancelled', 'expired', 'attended', 'completed', 'no_show'], true);
        $hasMeetingLink = filled($instructions->meeting_link ?? null);
        $canRetry = in_array($meetingStatus, ['failed', 'manual_required'], true) && !$isFinishedStatus;
        $canGenerate = $appointment->appointment_mode === 'online'
            && !$isFinishedStatus
            && (!$hasMeetingLink || $meetingStatus !== 'generated');

        $emailAvailableForResend = $canSendEmail && filled($appointment->patient->email ?? null);
        $whatsappAvailableForResend = $canSendWhatsapp && filled($appointment->patient->phone ?? null);
        $hasCommunicationAction = $emailAvailableForResend || $whatsappAvailableForResend;
    @endphp

    <div class="page-header mb-6">
        <div class="flex items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white mb-2">Consulta Online</h1>
                <nav class="flex" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 md:space-x-3">
                        <li class="inline-flex items-center">
                            <a href="{{ workspace_route('tenant.dashboard') }}" class="text-gray-700 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">
                                <x-icon name="home-outline" class="w-5 h-5 mr-2" />
                                Dashboard
                            </a>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <x-icon name="chevron-right" class="w-6 h-6 text-gray-400" />
                                <a href="{{ workspace_route('tenant.online-appointments.index') }}" class="ml-1 text-gray-700 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white md:ml-2">Consultas Online</a>
                            </div>
                        </li>
                        <li aria-current="page">
                            <div class="flex items-center">
                                <x-icon name="chevron-right" class="w-6 h-6 text-gray-400" />
                                <span class="ml-1 text-gray-500 dark:text-gray-400 md:ml-2">Visualizar</span>
                            </div>
                        </li>
                    </ol>
                </nav>
            </div>
            <div class="flex-shrink-0">
                <x-help-button module="online-appointments" />
            </div>
        </div>
    </div>

    @foreach (['success', 'warning', 'info', 'error'] as $flashType)
        @if (session($flashType))
            @php
                $flashClass = match ($flashType) {
                    'success' => 'border-green-200 bg-green-50 text-green-800 dark:border-green-800 dark:bg-green-900/20 dark:text-green-300',
                    'warning' => 'border-amber-200 bg-amber-50 text-amber-800 dark:border-amber-800 dark:bg-amber-900/20 dark:text-amber-300',
                    'info' => 'border-blue-200 bg-blue-50 text-blue-800 dark:border-blue-800 dark:bg-blue-900/20 dark:text-blue-300',
                    default => 'border-red-200 bg-red-50 text-red-800 dark:border-red-800 dark:bg-red-900/20 dark:text-red-300',
                };
            @endphp
            <div class="mb-4 rounded-lg border px-4 py-3 text-sm {{ $flashClass }}">
                {{ session($flashType) }}
            </div>
        @endif
    @endforeach

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="p-6 space-y-8">
            <div class="bg-blue-50 border border-blue-200 text-blue-800 rounded-md p-4 dark:bg-blue-900/20 dark:border-blue-800 dark:text-blue-200">
                <h3 class="text-sm font-semibold mb-2">Informações da Consulta</h3>
                <p class="text-sm"><strong>Paciente:</strong> {{ $appointment->patient->full_name ?? 'N/A' }}</p>
                <p class="text-sm"><strong>Data/Hora:</strong> {{ $appointment->starts_at ? $appointment->starts_at->format('d/m/Y H:i') : 'N/A' }}</p>
                @if($appointment->calendar && $appointment->calendar->doctor && $appointment->calendar->doctor->user)
                    <p class="text-sm"><strong>Médico:</strong> {{ $appointment->calendar->doctor->user->name }}</p>
                @endif
            </div>

            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 space-y-4">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Reunião online</h3>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusClass }}">
                        {{ $statusLabel }}
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-gray-500 dark:text-gray-400">Provedor</p>
                        <p class="font-medium text-gray-900 dark:text-white">{{ $providerLabel }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500 dark:text-gray-400">Gerada em</p>
                        <p class="font-medium text-gray-900 dark:text-white">
                            {{ $instructions && $instructions->meeting_generated_at ? $instructions->meeting_generated_at->format('d/m/Y H:i') : 'Não informado' }}
                        </p>
                    </div>
                </div>

                @if($meetingStatus === 'manual_required')
                    <div class="rounded-md border border-amber-200 bg-amber-50 px-4 py-3 text-amber-900 dark:border-amber-700 dark:bg-amber-900/20 dark:text-amber-200">
                        <h4 class="text-sm font-semibold mb-1">Ação manual necessária</h4>
                        <p class="text-sm">
                            O link da reunião ainda não foi gerado porque o profissional responsável não conectou o Google Calendar.
                            Conecte a conta Google do profissional ou informe o link manualmente em Editar instruções.
                        </p>
                        <div class="mt-3 flex flex-col gap-2 sm:flex-row sm:flex-wrap sm:items-center">
                            <a href="{{ workspace_route('tenant.online-appointments.edit', ['appointment' => $appointment->id]) }}" class="btn btn-outline">
                                Editar instruções
                            </a>
                            @if(!$isFinishedStatus)
                                <form action="{{ workspace_route('tenant.online-appointments.generate-meeting', ['appointment' => $appointment->id]) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-primary">
                                        Tentar novamente
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endif

                @if($instructions && $instructions->meeting_generation_error)
                    <div class="rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700 dark:border-red-800 dark:bg-red-900/20 dark:text-red-300">
                        {{ $instructions->meeting_generation_error }}
                    </div>
                @endif

                <div>
                    <p class="text-gray-500 dark:text-gray-400 text-sm mb-2">Link da reunião</p>
                    @if($hasMeetingLink)
                        <div class="flex flex-col sm:flex-row gap-2">
                            <input id="generated_meeting_link"
                                   type="text"
                                   readonly
                                   value="{{ $instructions->meeting_link }}"
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-gray-50 dark:bg-gray-700 dark:text-white">
                            <a href="{{ $instructions->meeting_link }}" target="_blank" rel="noopener noreferrer" class="btn btn-primary">
                                Abrir reunião
                            </a>
                            <button type="button" data-copy-link="{{ $instructions->meeting_link }}" class="btn btn-outline">
                                Copiar link
                            </button>
                        </div>
                    @else
                        <p class="text-sm text-gray-700 dark:text-gray-300">Não informado</p>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                    <p class="text-gray-500 dark:text-gray-400 text-sm mb-2">Aplicativo</p>
                    <p class="text-sm text-gray-900 dark:text-white">{{ $instructions->meeting_app ?? 'Não informado' }}</p>
                </div>
                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                    <p class="text-gray-500 dark:text-gray-400 text-sm mb-2">Observações para o Paciente</p>
                    <p class="text-sm text-gray-900 dark:text-white whitespace-pre-line">{{ $instructions->patient_instructions ?? 'Não informado' }}</p>
                </div>
            </div>

            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                <p class="text-gray-500 dark:text-gray-400 text-sm mb-2">Instruções Gerais</p>
                <p class="text-sm text-gray-900 dark:text-white whitespace-pre-line">{{ $instructions->general_instructions ?? 'Não informado' }}</p>
            </div>

            <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Ações da reunião</h3>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ workspace_route('tenant.online-appointments.edit', ['appointment' => $appointment->id]) }}" class="btn btn-outline inline-flex items-center">
                        <x-icon name="pencil-outline" class="w-4 h-4 mr-2" />
                        Editar instruções
                    </a>

                    @if($canGenerate || $canRetry)
                        <form action="{{ workspace_route('tenant.online-appointments.generate-meeting', ['appointment' => $appointment->id]) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-primary inline-flex items-center">
                                <x-icon name="refresh" class="w-4 h-4 mr-2" />
                                {{ $canRetry ? 'Tentar novamente' : 'Gerar reunião agora' }}
                            </button>
                        </form>
                    @endif

                    @if($hasMeetingLink)
                        <a href="{{ $instructions->meeting_link }}" target="_blank" rel="noopener noreferrer" class="btn btn-primary inline-flex items-center">
                            <x-icon name="open-in-new" class="w-4 h-4 mr-2" />
                            Abrir reunião
                        </a>
                        <button type="button" data-copy-link="{{ $instructions->meeting_link }}" class="btn btn-outline inline-flex items-center">
                            <x-icon name="content-copy" class="w-4 h-4 mr-2" />
                            Copiar link
                        </button>
                    @endif
                </div>
            </div>

            <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Ações de comunicação</h3>

                @if(!$canSendEmail && !$canSendWhatsapp)
                    <div class="bg-amber-50 border border-amber-200 text-amber-800 rounded-md p-4 dark:bg-amber-900/20 dark:border-amber-800 dark:text-amber-300">
                        <strong>Atenção:</strong> Configure os canais de notificação para habilitar o reenvio.
                    </div>
                @else
                    <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-start">
                        @if($canSendEmail)
                            <div class="flex flex-col gap-2">
                                @if($emailAvailableForResend)
                                    <form action="{{ workspace_route('tenant.online-appointments.send-email', ['appointment' => $appointment->id]) }}" method="POST" class="inline-flex">
                                        @csrf
                                        <button type="submit" class="btn btn-primary inline-flex items-center">
                                            <x-icon name="email-outline" class="w-4 h-4 mr-2" />
                                            Reenviar por e-mail
                                        </button>
                                    </form>
                                @else
                                    <p class="rounded-md border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                        Nenhum canal de comunicação disponível para reenvio.
                                    </p>
                                @endif

                                @if($appointment->onlineInstructions && $appointment->onlineInstructions->sent_by_email_at)
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                                        Último envio: {{ $appointment->onlineInstructions->sent_by_email_at->format('d/m/Y H:i') }}
                                    </p>
                                @endif
                            </div>
                        @endif

                        @if($canSendWhatsapp)
                            <div class="flex flex-col gap-2">
                                @if($whatsappAvailableForResend)
                                    <form action="{{ workspace_route('tenant.online-appointments.send-whatsapp', ['appointment' => $appointment->id]) }}" method="POST" class="inline-flex">
                                        @csrf
                                        <button type="submit" class="btn btn-primary inline-flex items-center">
                                            <x-icon name="whatsapp" class="w-4 h-4 mr-2" />
                                            Reenviar por WhatsApp
                                        </button>
                                    </form>
                                @else
                                    <p class="rounded-md border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                        Nenhum canal de comunicação disponível para reenvio.
                                    </p>
                                @endif

                                @if($appointment->onlineInstructions && $appointment->onlineInstructions->sent_by_whatsapp_at)
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                                        Último envio: {{ $appointment->onlineInstructions->sent_by_whatsapp_at->format('d/m/Y H:i') }}
                                    </p>
                                @endif
                            </div>
                        @endif
                    </div>

                    @if(!$hasCommunicationAction)
                        <p class="mt-3 text-sm text-gray-700 dark:text-gray-300">Nenhum canal de comunicação disponível para reenvio.</p>
                    @endif
                @endif
            </div>

            <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <a href="{{ workspace_route('tenant.online-appointments.index') }}" class="btn btn-outline inline-flex items-center">
                        <x-icon name="arrow-left" class="w-4 h-4 mr-2" />
                        Voltar
                    </a>

                    @if($canCancel)
                        <form
                            action="{{ workspace_route('tenant.appointments.cancel', ['appointment' => $appointment->id]) }}"
                            method="POST"
                            onsubmit="event.preventDefault(); confirmAction({ title: 'Cancelar consulta online', message: 'Tem certeza que deseja cancelar esta consulta online? Esta ação também marcará a reunião online como cancelada.', confirmText: 'Cancelar consulta', cancelText: 'Voltar', type: 'warning', onConfirm: () => this.submit() }); return false;"
                        >
                            @csrf
                            <input type="hidden" name="reason" value="Cancelado pelo módulo de consultas online.">
                            <button type="submit" class="btn btn-danger inline-flex items-center">
                                <x-icon name="close-circle-outline" class="w-4 h-4 mr-2" />
                                Cancelar consulta
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
(function () {
    const copyButtons = document.querySelectorAll('[data-copy-link]');

    copyButtons.forEach(function (button) {
        button.addEventListener('click', async function () {
            const link = button.getAttribute('data-copy-link') || '';
            if (!link) {
                return;
            }

            try {
                await navigator.clipboard.writeText(link);
                button.textContent = 'Copiado';
                setTimeout(function () {
                    button.textContent = 'Copiar link';
                }, 1500);
            } catch (error) {
                const input = document.createElement('input');
                input.value = link;
                document.body.appendChild(input);
                input.select();
                document.execCommand('copy');
                document.body.removeChild(input);

                button.textContent = 'Copiado';
                setTimeout(function () {
                    button.textContent = 'Copiar link';
                }, 1500);
            }
        });
    });
})();
</script>
@endpush
