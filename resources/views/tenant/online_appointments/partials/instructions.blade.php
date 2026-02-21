@php
    $instructions = $appointment->onlineInstructions;
    $sentByEmail = (bool) optional($instructions)->sent_by_email_at;
    $sentByWhatsapp = (bool) optional($instructions)->sent_by_whatsapp_at;
    $sent = $sentByEmail || $sentByWhatsapp;
@endphp

@if ($sent)
    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400">
        Enviadas
    </span>
@else
    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
        Pendente
    </span>
@endif
