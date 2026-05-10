@php
    $status = strtolower((string) ($appointment->status ?? ''));
    $canCancel = !in_array($status, ['canceled', 'cancelled', 'expired', 'attended', 'completed', 'no_show'], true);

    $hasEmail = filled(optional($appointment->patient)->email);
    $hasPhone = filled(optional($appointment->patient)->phone);

    $canResendEmail = (bool) ($canSendEmail ?? false) && $hasEmail;
    $canResendWhatsapp = (bool) ($canSendWhatsapp ?? false) && $hasPhone;
@endphp

<div class="actions-wrap" onclick="event.stopPropagation()">
    <a
        href="{{ workspace_route('tenant.online-appointments.show', ['appointment' => $appointment->id]) }}"
        title="Visualizar"
        onclick="event.stopPropagation()"
        class="inline-flex items-center justify-center rounded-xl border border-blue-100 bg-blue-50 px-2.5 py-1.5 text-xs font-medium text-blue-700 hover:bg-blue-100 dark:border-blue-900/40 dark:bg-blue-900/20 dark:text-blue-300 table-action-btn tenant-action-view"
    >
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
        </svg>
    </a>

    @if($canResendEmail)
        <form action="{{ workspace_route('tenant.online-appointments.send-email', ['appointment' => $appointment->id]) }}" method="POST" class="inline-flex" onclick="event.stopPropagation()">
            @csrf
            <button
                type="submit"
                title="Reenviar por e-mail"
                class="inline-flex items-center justify-center rounded-xl border border-amber-100 bg-amber-50 px-2.5 py-1.5 text-xs font-medium text-amber-700 hover:bg-amber-100 dark:border-amber-900/40 dark:bg-amber-900/20 dark:text-amber-300 table-action-btn tenant-action-edit"
            >
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8m-16 8h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2z"></path>
                </svg>
            </button>
        </form>
    @endif

    @if($canResendWhatsapp)
        <form action="{{ workspace_route('tenant.online-appointments.send-whatsapp', ['appointment' => $appointment->id]) }}" method="POST" class="inline-flex" onclick="event.stopPropagation()">
            @csrf
            <button
                type="submit"
                title="Reenviar por WhatsApp"
                class="inline-flex items-center justify-center rounded-xl border border-amber-100 bg-amber-50 px-2.5 py-1.5 text-xs font-medium text-amber-700 hover:bg-amber-100 dark:border-amber-900/40 dark:bg-amber-900/20 dark:text-amber-300 table-action-btn tenant-action-edit"
            >
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.95.68l1.5 4.5a1 1 0 01-.24 1.02l-1.35 1.35a16 16 0 006.36 6.36l1.35-1.35a1 1 0 011.02-.24l4.5 1.5a1 1 0 01.68.95V19a2 2 0 01-2 2h-1C10.16 21 3 13.84 3 5V5z"></path>
                </svg>
            </button>
        </form>
    @endif

    @if(!$canResendEmail && !$canResendWhatsapp)
        <button
            type="button"
            title="Nenhum canal de reenvio disponível"
            disabled
            class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-gray-100 px-2.5 py-1.5 text-xs font-medium text-gray-400 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-500 table-action-btn"
            onclick="event.stopPropagation()"
        >
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M4.93 19h14.14c1.54 0 2.5-1.67 1.73-3l-7.07-12.2c-.77-1.33-2.69-1.33-3.46 0L3.2 16c-.77 1.33.19 3 1.73 3z"></path>
            </svg>
        </button>
    @endif

    @if($canCancel)
        <form
            action="{{ workspace_route('tenant.appointments.cancel', ['appointment' => $appointment->id]) }}"
            method="POST"
            class="inline-flex"
            onclick="event.stopPropagation()"
            onsubmit="event.preventDefault(); event.stopPropagation(); confirmAction({ title: 'Cancelar consulta online', message: 'Tem certeza que deseja cancelar esta consulta online? Esta ação também marcará a reunião online como cancelada.', confirmText: 'Cancelar consulta', cancelText: 'Voltar', type: 'warning', onConfirm: () => this.submit() }); return false;"
        >
            @csrf
            <input type="hidden" name="reason" value="Cancelado pelo módulo de consultas online.">
            <button
                type="submit"
                title="Cancelar"
                class="inline-flex items-center justify-center rounded-xl border border-red-100 bg-red-50 px-2.5 py-1.5 text-xs font-medium text-red-700 hover:bg-red-100 dark:border-red-900/40 dark:bg-red-900/20 dark:text-red-300 table-action-btn tenant-action-delete"
            >
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </form>
    @endif
</div>