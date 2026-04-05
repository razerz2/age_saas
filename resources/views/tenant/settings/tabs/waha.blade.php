@php
    $wahaInstance = is_array($wahaGlobalStatus ?? null) ? ($wahaGlobalStatus['instance'] ?? null) : null;
    $wahaSession = is_array($wahaGlobalStatus ?? null) ? ($wahaGlobalStatus['session'] ?? null) : null;
    $instanceName = is_array($wahaInstance) ? (string) ($wahaInstance['instance_name'] ?? '') : '';
    $instanceStatus = is_array($wahaSession) ? (string) ($wahaSession['status'] ?? 'UNKNOWN') : 'UNKNOWN';
    $lastError = is_array($wahaInstance) ? (string) ($wahaInstance['last_error'] ?? '') : '';
    $webhook = is_array($wahaGlobalStatus ?? null) ? ($wahaGlobalStatus['webhook'] ?? null) : null;
    $webhookExpectedUrl = is_array($webhook) ? (string) ($webhook['expected_url'] ?? '') : '';
    $webhookCurrentUrl = is_array($webhook) ? (string) ($webhook['current_url'] ?? '') : '';
    $webhookConfigured = is_array($webhook) ? ((bool) ($webhook['configured'] ?? false)) : false;
    $webhookStatusText = $webhookConfigured
        ? 'Configurado'
        : ($webhookCurrentUrl !== '' ? 'Divergente' : 'Não configurado');
@endphp

<div class="space-y-8" id="tenant-waha-global-panel"
     data-status-url="{{ workspace_route('tenant.settings.waha.status') }}"
     data-qr-url="{{ workspace_route('tenant.settings.waha.qr-code') }}"
     data-action-bind-webhook-url="{{ workspace_route('tenant.settings.waha.webhook.bind') }}"
     data-action-start-url="{{ workspace_route('tenant.settings.waha.start') }}"
     data-action-restart-url="{{ workspace_route('tenant.settings.waha.restart') }}"
     data-action-stop-url="{{ workspace_route('tenant.settings.waha.stop') }}"
     data-action-logout-url="{{ workspace_route('tenant.settings.waha.logout') }}">

    <div class="mb-2">
        <div class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-700 dark:border-emerald-700/50 dark:bg-emerald-900/20 dark:text-emerald-300">
            <x-icon name="whatsapp" size="text-sm" />
            Instância Global WAHA
        </div>
        <h2 class="mt-3 text-xl font-semibold text-gray-900 dark:text-white">WAHA</h2>
        <p class="text-sm text-gray-600 dark:text-gray-400">
            Instância global do WhatsApp gerenciada pelo sistema. O nome da instância é derivado automaticamente da clínica.
        </p>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-900/40">
                <div class="flex items-center gap-2 text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                    <x-icon name="office-building-outline" size="text-sm" />
                    Instância WAHA
                </div>
                <div id="waha-instance-name" class="mt-2 w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 dark:text-white px-3 py-2 text-sm font-semibold">
                    {{ $instanceName !== '' ? $instanceName : '-' }}
                </div>
                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Nome controlado exclusivamente pelo sistema.</p>
            </div>
            <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-900/40">
                <div class="flex items-center gap-2 text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                    <x-icon name="signal" size="text-sm" />
                    Status da Sessão
                </div>
                <div id="waha-session-status" class="mt-2 w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 dark:text-white px-3 py-2 text-sm font-semibold">
                    {{ $instanceStatus !== '' ? $instanceStatus : 'UNKNOWN' }}
                </div>
                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Origem: Instância gerenciada pelo sistema global.</p>
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
                    <div id="waha-webhook-expected-url" class="mt-1 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-xs break-all text-gray-700 dark:text-gray-200">
                        {{ $webhookExpectedUrl !== '' ? $webhookExpectedUrl : '-' }}
                    </div>
                </div>
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">URL atual na instância</p>
                    <div id="waha-webhook-current-url" class="mt-1 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-xs break-all text-gray-700 dark:text-gray-200">
                        {{ $webhookCurrentUrl !== '' ? $webhookCurrentUrl : 'Não configurado' }}
                    </div>
                </div>
            </div>

            <div class="mt-3 flex flex-wrap items-center gap-3">
                <span id="waha-webhook-status" class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-medium {{ $webhookConfigured ? 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-700/40 dark:bg-emerald-900/20 dark:text-emerald-300' : 'border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-700/40 dark:bg-amber-900/20 dark:text-amber-300' }}">
                    {{ $webhookStatusText }}
                </span>
                <x-tailadmin-button
                    type="button"
                    variant="secondary"
                    size="sm"
                    class="shrink-0 {{ $webhookConfigured ? 'hidden' : '' }}"
                    data-waha-bind-webhook>
                    <x-icon name="link-variant" size="text-sm" />
                    Vincular webhook
                </x-tailadmin-button>
            </div>
        </div>

        <div class="mt-4 rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-900/40">
            <div class="flex items-center gap-2 text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                <x-icon name="alert-circle-outline" size="text-sm" />
                Último Erro
            </div>
            <div id="waha-last-error" class="mt-2 w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm {{ $lastError !== '' ? 'text-red-600 dark:text-red-400' : 'text-gray-500 dark:text-gray-400' }}">
                {{ $lastError !== '' ? $lastError : 'Sem erros recentes.' }}
            </div>
        </div>

        <div class="mt-6 rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-900/40">
            <div class="mb-3 flex items-center gap-2 text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                <x-icon name="cog-outline" size="text-sm" />
                Ações da Sessão
            </div>

            <div class="flex flex-wrap md:flex-nowrap md:overflow-x-auto gap-2">
                <x-tailadmin-button type="button" variant="primary" size="sm" class="shrink-0" data-waha-action="start">
                    <x-icon name="play-circle-outline" size="text-sm" />
                    Start
                </x-tailadmin-button>
                <x-tailadmin-button type="button" variant="secondary" size="sm" class="shrink-0" data-waha-action="restart">
                    <x-icon name="refresh" size="text-sm" />
                    Restart
                </x-tailadmin-button>
                <x-tailadmin-button
                    type="button"
                    variant="secondary"
                    size="sm"
                    class="shrink-0 border-amber-300 text-amber-700 hover:bg-amber-50 dark:border-amber-600/70 dark:text-amber-300 dark:hover:bg-amber-900/20"
                    data-waha-action="stop">
                    <x-icon name="stop-circle-outline" size="text-sm" />
                    Stop
                </x-tailadmin-button>
                <x-tailadmin-button type="button" variant="danger" size="sm" class="shrink-0" data-waha-action="logout">
                    <x-icon name="logout-variant" size="text-sm" />
                    Logout
                </x-tailadmin-button>
                <x-tailadmin-button type="button" variant="secondary" size="sm" class="shrink-0" data-waha-refresh-status>
                    <x-icon name="sync" size="text-sm" />
                    Atualizar status
                </x-tailadmin-button>
                <x-tailadmin-button type="button" variant="secondary" size="sm" class="shrink-0" data-waha-refresh-qr>
                    <x-icon name="qrcode-scan" size="text-sm" />
                    Atualizar QR
                </x-tailadmin-button>
            </div>
        </div>

        <div id="waha-action-feedback" class="mt-4 min-h-[40px] rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-600 dark:border-gray-700 dark:bg-gray-900/40 dark:text-gray-300"></div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="inline-flex items-center gap-2 text-lg font-semibold text-gray-900 dark:text-white">
                <x-icon name="qrcode" size="text-base" />
                QR Code
            </h3>
            <span class="text-xs text-gray-500 dark:text-gray-400">Exibido quando a sessão exigir autenticação.</span>
        </div>

        <div id="waha-qr-empty" class="rounded-lg border border-dashed border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-900/40 p-8 text-center">
            <x-icon name="qrcode-scan" size="text-4xl" class="text-gray-400 dark:text-gray-500" />
            <p class="mt-3 text-sm font-medium text-gray-600 dark:text-gray-300">QR Code indisponível no momento.</p>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Use "Atualizar QR" para tentar carregar novamente.</p>
        </div>

        <div id="waha-qr-wrapper" class="hidden mt-2">
            <div class="inline-flex rounded-xl border border-gray-200 dark:border-gray-700 bg-white p-3 shadow-sm">
                <img id="waha-qr-image" src="" alt="QR Code da sessão WAHA" class="max-w-xs rounded-lg border border-gray-200 dark:border-gray-700 bg-white p-2" />
            </div>
        </div>
    </div>
</div>

