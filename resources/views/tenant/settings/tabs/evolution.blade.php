@php
    $evolutionInstance = is_array($evolutionGlobalStatus ?? null) ? ($evolutionGlobalStatus['instance'] ?? null) : null;
    $evolutionSession = is_array($evolutionGlobalStatus ?? null) ? ($evolutionGlobalStatus['session'] ?? null) : null;
    $instanceName = is_array($evolutionInstance) ? (string) ($evolutionInstance['instance_name'] ?? '') : '';
    $instanceStatus = is_array($evolutionSession) ? (string) ($evolutionSession['status'] ?? 'UNKNOWN') : 'UNKNOWN';
    $friendlyStatus = is_array($evolutionSession) ? (string) ($evolutionSession['friendly_status'] ?? '') : '';
    $lastError = is_array($evolutionInstance) ? (string) ($evolutionInstance['last_error'] ?? '') : '';
    $webhook = is_array($evolutionGlobalStatus ?? null) ? ($evolutionGlobalStatus['webhook'] ?? null) : null;
    $webhookExpectedUrl = is_array($webhook) ? (string) ($webhook['expected_url'] ?? '') : '';
    $webhookCurrentUrl = is_array($webhook) ? (string) ($webhook['current_url'] ?? '') : '';
    $webhookConfigured = is_array($webhook) ? ((bool) ($webhook['configured'] ?? false)) : false;
    $webhookStatusText = $webhookConfigured
        ? 'Configurado'
        : ($webhookCurrentUrl !== '' ? 'Divergente' : 'Nao configurado');
@endphp

<div class="space-y-8" id="tenant-evolution-global-panel"
     data-status-url="{{ workspace_route('tenant.settings.evolution.status') }}"
     data-qr-url="{{ workspace_route('tenant.settings.evolution.qr-code') }}"
     data-action-bind-webhook-url="{{ workspace_route('tenant.settings.evolution.webhook.bind') }}"
     data-action-start-url="{{ workspace_route('tenant.settings.evolution.start') }}"
     data-action-restart-url="{{ workspace_route('tenant.settings.evolution.restart') }}"
     data-action-logout-url="{{ workspace_route('tenant.settings.evolution.logout') }}">

    <div class="mb-2">
        <div class="inline-flex items-center gap-2 rounded-full border border-cyan-200 bg-cyan-50 px-3 py-1 text-xs font-medium text-cyan-700 dark:border-cyan-700/50 dark:bg-cyan-900/20 dark:text-cyan-300">
            <x-icon name="whatsapp" size="text-sm" />
            Instancia Global Evolution
        </div>
        <h2 class="mt-3 text-xl font-semibold text-gray-900 dark:text-white">Evolution</h2>
        <p class="text-sm text-gray-600 dark:text-gray-400">
            Instancia global do WhatsApp gerenciada pelo sistema. O nome da instancia e derivado automaticamente da clinica.
        </p>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-900/40">
                <div class="flex items-center gap-2 text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                    <x-icon name="office-building-outline" size="text-sm" />
                    Instancia Evolution
                </div>
                <div id="evolution-instance-name" class="mt-2 w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 dark:text-white px-3 py-2 text-sm font-semibold">
                    {{ $instanceName !== '' ? $instanceName : '-' }}
                </div>
                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Nome controlado exclusivamente pelo sistema.</p>
            </div>
            <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-900/40">
                <div class="flex items-center gap-2 text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                    <x-icon name="signal" size="text-sm" />
                    Status da Instancia
                </div>
                <div id="evolution-session-status" class="mt-2 w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 dark:text-white px-3 py-2 text-sm font-semibold">
                    {{ $instanceStatus !== '' ? $instanceStatus : 'UNKNOWN' }}
                </div>
                <p id="evolution-session-friendly-status" class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                    {{ $friendlyStatus !== '' ? $friendlyStatus : 'Origem: Instancia gerenciada pelo sistema global.' }}
                </p>
            </div>
        </div>

        <div class="mt-4 rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-900/40">
            <div class="flex items-center gap-2 text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                <x-icon name="link-variant" size="text-sm" />
                Webhook do Bot
            </div>

            <div class="mt-3 grid grid-cols-1 gap-3 md:grid-cols-2">
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">URL esperada</p>
                    <div id="evolution-webhook-expected-url" class="mt-1 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-xs break-all text-gray-700 dark:text-gray-200">
                        {{ $webhookExpectedUrl !== '' ? $webhookExpectedUrl : '-' }}
                    </div>
                </div>
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">URL atual na instancia</p>
                    <div id="evolution-webhook-current-url" class="mt-1 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-xs break-all text-gray-700 dark:text-gray-200">
                        {{ $webhookCurrentUrl !== '' ? $webhookCurrentUrl : 'Nao configurado' }}
                    </div>
                </div>
            </div>

            <div class="mt-3 flex flex-wrap items-center gap-3">
                <span id="evolution-webhook-status" class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-medium {{ $webhookConfigured ? 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-700/40 dark:bg-emerald-900/20 dark:text-emerald-300' : 'border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-700/40 dark:bg-amber-900/20 dark:text-amber-300' }}">
                    {{ $webhookStatusText }}
                </span>
                <x-tailadmin-button
                    type="button"
                    variant="secondary"
                    size="sm"
                    class="shrink-0 {{ $webhookConfigured ? 'hidden' : '' }}"
                    data-evolution-bind-webhook>
                    <x-icon name="link-variant" size="text-sm" />
                    Vincular webhook
                </x-tailadmin-button>
            </div>
        </div>

        <div class="mt-4 rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-900/40">
            <div class="flex items-center gap-2 text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                <x-icon name="alert-circle-outline" size="text-sm" />
                Ultimo Erro
            </div>
            <div id="evolution-last-error" class="mt-2 w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm {{ $lastError !== '' ? 'text-red-600 dark:text-red-400' : 'text-gray-500 dark:text-gray-400' }}">
                {{ $lastError !== '' ? $lastError : 'Sem erros recentes.' }}
            </div>
        </div>

        <div class="mt-6 rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-900/40">
            <div class="mb-3 flex items-center gap-2 text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                <x-icon name="cog-outline" size="text-sm" />
                Acoes da Instancia
            </div>

            <div class="flex flex-wrap md:flex-nowrap md:overflow-x-auto gap-2">
                <x-tailadmin-button type="button" variant="primary" size="sm" class="shrink-0" data-evolution-action="start">
                    <x-icon name="play-circle-outline" size="text-sm" />
                    Conectar
                </x-tailadmin-button>
                <x-tailadmin-button type="button" variant="secondary" size="sm" class="shrink-0" data-evolution-action="restart">
                    <x-icon name="refresh" size="text-sm" />
                    Restart
                </x-tailadmin-button>
                <x-tailadmin-button type="button" variant="danger" size="sm" class="shrink-0" data-evolution-action="logout">
                    <x-icon name="logout-variant" size="text-sm" />
                    Logout
                </x-tailadmin-button>
                <x-tailadmin-button type="button" variant="secondary" size="sm" class="shrink-0" data-evolution-refresh-status>
                    <x-icon name="sync" size="text-sm" />
                    Atualizar status
                </x-tailadmin-button>
                <x-tailadmin-button type="button" variant="secondary" size="sm" class="shrink-0" data-evolution-refresh-qr>
                    <x-icon name="qrcode-scan" size="text-sm" />
                    Atualizar QR
                </x-tailadmin-button>
            </div>
        </div>

        <div id="evolution-action-feedback" class="mt-4 min-h-[40px] rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-600 dark:border-gray-700 dark:bg-gray-900/40 dark:text-gray-300"></div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="inline-flex items-center gap-2 text-lg font-semibold text-gray-900 dark:text-white">
                <x-icon name="qrcode" size="text-base" />
                QR Code
            </h3>
            <span class="text-xs text-gray-500 dark:text-gray-400">Exibido quando a instancia exigir autenticacao.</span>
        </div>

        <div id="evolution-qr-empty" class="rounded-lg border border-dashed border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-900/40 p-8 text-center">
            <x-icon name="qrcode-scan" size="text-4xl" class="text-gray-400 dark:text-gray-500" />
            <p class="mt-3 text-sm font-medium text-gray-600 dark:text-gray-300">QR Code indisponivel no momento.</p>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Use "Atualizar QR" para tentar carregar novamente.</p>
        </div>

        <div id="evolution-qr-wrapper" class="hidden mt-2">
            <div class="inline-flex rounded-xl border border-gray-200 dark:border-gray-700 bg-white p-3 shadow-sm">
                <img id="evolution-qr-image" src="" alt="QR Code da instancia Evolution" class="max-w-xs rounded-lg border border-gray-200 dark:border-gray-700 bg-white p-2" />
            </div>
        </div>

        <div id="evolution-qr-text-wrapper" class="hidden mt-4 rounded-lg border border-gray-200 bg-gray-50 p-3 dark:border-gray-700 dark:bg-gray-900/40">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Codigo de pareamento</p>
            <pre id="evolution-qr-text" class="mt-2 whitespace-pre-wrap break-all rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-xs text-gray-700 dark:text-gray-200"></pre>
        </div>
    </div>
</div>
