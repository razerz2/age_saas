@extends('layouts.tailadmin.app')

@section('title', 'Campanha: ' . ($campaign->name ?? 'Detalhes'))
@section('page', 'campaigns')

@section('content')
    @php
        $availableChannels = collect($availableChannels ?? [])
            ->map(fn ($channel) => strtolower(trim((string) $channel)))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $campaignChannels = collect(is_array($campaign->channels_json) ? $campaign->channels_json : [])
            ->map(fn ($channel) => strtolower(trim((string) $channel)))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $moduleEnabled = $moduleEnabled ?? (count($availableChannels) > 0);
        $campaignType = strtolower((string) ($campaign->type ?? 'manual'));
        $campaignStatus = strtolower((string) ($campaign->status ?? ''));

        $typeLabel = $campaignType === 'automated' ? 'Agendada' : 'Manual';
        $typeClasses = $campaignType === 'automated'
            ? 'bg-violet-100 text-violet-800 dark:bg-violet-900/20 dark:text-violet-300'
            : 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-300';

        $channelLabels = array_map(
            fn ($channel) => $channel === 'whatsapp' ? 'WhatsApp' : ($channel === 'email' ? 'Email' : ucfirst($channel)),
            $campaignChannels
        );
        $channelsText = $channelLabels === []
            ? '—'
            : (count($channelLabels) > 1 ? 'Ambos (' . implode(', ', $channelLabels) . ')' : $channelLabels[0]);

        $contentJson = is_array($campaign->content_json) ? $campaign->content_json : [];
        $audienceJson = is_array($campaign->audience_json) ? $campaign->audience_json : [];
        $automationJson = is_array($campaign->automation_json) ? $campaign->automation_json : [];

        $hasEmailChannel = in_array('email', $campaignChannels, true);
        $hasWhatsappChannel = in_array('whatsapp', $campaignChannels, true);

        $scheduledAt = $campaign->scheduled_at ? $campaign->scheduled_at->format('d/m/Y H:i') : '—';
        $createdAt = $campaign->created_at ? $campaign->created_at->format('d/m/Y H:i') : '—';
        $createdByLabel = $createdByDisplay ?? ($campaign->created_by ? 'ID ' . $campaign->created_by : '—');

        $unavailableChannels = collect($unavailableChannels ?? [])
            ->map(fn ($channel) => strtolower(trim((string) $channel)))
            ->filter()
            ->unique()
            ->values()
            ->all();
        $hasUnavailableChannels = (bool) ($hasUnavailableChannels ?? ($unavailableChannels !== []));
        $unavailableChannelsLabel = collect($unavailableChannels)
            ->map(fn ($channel) => $channel === 'whatsapp' ? 'WhatsApp' : ($channel === 'email' ? 'Email' : ucfirst($channel)))
            ->implode(', ');

        $tenantSlug = request()->route('slug') ?? tenant()?->subdomain ?? '';
        $integrationsUrl = \Illuminate\Support\Facades\Route::has('tenant.integrations.index')
            ? workspace_route('tenant.integrations.index')
            : url('/workspace/' . $tenantSlug . '/integrations');

        $dispatchActionsEnabled = $moduleEnabled && !$hasUnavailableChannels;
        $sendTestRoute = workspace_route('tenant.campaigns.sendTest', ['campaign' => $campaign->id]);
        $startRoute = workspace_route('tenant.campaigns.start', ['campaign' => $campaign->id]);
        $scheduleRoute = workspace_route('tenant.campaigns.schedule', ['campaign' => $campaign->id]);
        $pauseRoute = workspace_route('tenant.campaigns.pause', ['campaign' => $campaign->id]);
        $resumeRoute = workspace_route('tenant.campaigns.resume', ['campaign' => $campaign->id]);
        $runsIndexRoute = workspace_route('tenant.campaigns.runs.index', ['campaign' => $campaign->id]);
        $recipientsIndexRoute = workspace_route('tenant.campaigns.recipients.index', ['campaign' => $campaign->id]);
        $defaultScheduleInput = $campaign->scheduled_at ? $campaign->scheduled_at->format('Y-m-d\TH:i') : now()->addHour()->format('Y-m-d\TH:i');
        $lastAutomationRun = $lastAutomationRun ?? null;
        $nextAutomationRun = $nextAutomationRun ?? null;
        $automationTimezone = $automationTimezone ?? null;
    @endphp

    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
        <div class="mb-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <nav class="flex" aria-label="Breadcrumb">
                        <ol class="inline-flex items-center space-x-1 md:space-x-3">
                            <li class="inline-flex items-center">
                                <a href="{{ workspace_route('tenant.dashboard') }}"
                                    class="inline-flex items-center text-gray-700 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
                                    <x-icon name="home-outline" size="text-base" class="mr-2" />
                                    Dashboard
                                </a>
                            </li>
                            <li>
                                <div class="flex items-center">
                                    <x-icon name="chevron-right" size="text-sm" class="text-gray-400" />
                                    <a href="{{ workspace_route('tenant.campaigns.index') }}"
                                        class="ml-1 text-gray-700 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">
                                        Campanhas
                                    </a>
                                </div>
                            </li>
                            <li>
                                <div class="flex items-center">
                                    <x-icon name="chevron-right" size="text-sm" class="text-gray-400" />
                                    <span class="ml-1 text-gray-500 dark:text-gray-400">Detalhes</span>
                                </div>
                            </li>
                        </ol>
                    </nav>

                    <h1 class="mt-3 text-2xl font-bold text-gray-900 dark:text-white">{{ $campaign->name ?: '—' }}</h1>

                    <div class="mt-3 flex flex-wrap items-center gap-2">
                        @include('tenant.campaigns.partials.status_badge', ['campaign' => $campaign])
                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $typeClasses }}">
                            {{ $typeLabel }}
                        </span>
                        @include('tenant.campaigns.partials.channel_badges', [
                            'channels' => $campaignChannels,
                            'availableChannels' => $availableChannels,
                        ])
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ workspace_route('tenant.campaigns.index') }}" class="btn btn-outline inline-flex items-center">
                        <i class="mdi mdi-arrow-left text-sm mr-1"></i>
                        Voltar
                    </a>

                    @if ($moduleEnabled)
                        <a href="{{ workspace_route('tenant.campaigns.edit', ['campaign' => $campaign->id]) }}" class="btn btn-outline tenant-action-edit inline-flex items-center">
                            <i class="mdi mdi-pencil-outline text-sm mr-1"></i>
                            Editar
                        </a>

                        <form id="campaigns-delete-form-{{ $campaign->id }}" action="{{ workspace_route('tenant.campaigns.destroy', ['campaign' => $campaign->id]) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="button" class="btn btn-outline tenant-action-delete inline-flex items-center"
                                data-delete-trigger="1"
                                data-delete-form="#campaigns-delete-form-{{ $campaign->id }}"
                                data-delete-title="Excluir campanha"
                                data-delete-message="Tem certeza que deseja excluir esta campanha?">
                                <i class="mdi mdi-delete-outline text-sm mr-1"></i>
                                Excluir
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        @if (session('success'))
            <div class="mb-6 rounded-lg border border-green-200 bg-green-50 p-4 dark:border-green-800 dark:bg-green-900/20">
                <p class="text-sm text-green-800 dark:text-green-200">{{ session('success') }}</p>
            </div>
        @endif

        @if (session('warning'))
            <div class="mb-6 rounded-lg border border-amber-200 bg-amber-50 p-4 dark:border-amber-800 dark:bg-amber-900/20">
                <p class="text-sm text-amber-800 dark:text-amber-200">{{ session('warning') }}</p>
            </div>
        @endif

        @if ($hasUnavailableChannels)
            <div class="mb-6 rounded-lg border border-amber-200 bg-amber-50 p-4 dark:border-amber-800 dark:bg-amber-900/20">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex">
                        <x-icon name="alert-circle-outline" size="text-lg" class="text-amber-600 dark:text-amber-400" />
                        <p class="ml-3 text-sm text-amber-800 dark:text-amber-200">
                            Esta campanha utiliza canais que não estão configurados para este tenant: {{ $unavailableChannelsLabel ?: '—' }}.
                            Configure em Integrações ou edite a campanha para remover o canal.
                        </p>
                    </div>
                    <a href="{{ $integrationsUrl }}" class="btn btn-outline whitespace-nowrap">Configurar Integrações</a>
                </div>
            </div>
        @endif

        @if ($campaignStatus === 'blocked')
            <div class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-900/20">
                <div class="flex">
                    <x-icon name="alert-circle-outline" size="text-lg" class="text-red-600 dark:text-red-400" />
                    <p class="ml-3 text-sm text-red-800 dark:text-red-200">
                        Esta campanha está bloqueada. Revise canais e conteúdo antes de novas operações.
                    </p>
                </div>
            </div>
        @endif

        @if ($campaignType === 'automated' && $campaignStatus === 'paused')
            <div class="mb-6 rounded-lg border border-amber-200 bg-amber-50 p-4 dark:border-amber-800 dark:bg-amber-900/20">
                <div class="flex">
                    <x-icon name="pause-circle-outline" size="text-lg" class="text-amber-600 dark:text-amber-400" />
                    <p class="ml-3 text-sm text-amber-800 dark:text-amber-200">
                        Campanha pausada: a programação agendada não será executada enquanto o status permanecer pausado.
                    </p>
                </div>
            </div>
        @endif

        @if (!$moduleEnabled)
            <div class="mb-6 rounded-lg border border-amber-200 bg-amber-50 p-4 dark:border-amber-800 dark:bg-amber-900/20">
                <div class="flex">
                    <x-icon name="alert-circle-outline" size="text-lg" class="text-amber-600 dark:text-amber-400" />
                    <p class="ml-3 text-sm text-amber-800 dark:text-amber-200">
                        Campanhas indisponíveis: configure sua API de Email e/ou WhatsApp em Integrações.
                    </p>
                </div>
            </div>
        @endif

        <div class="mb-6 rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Ações</h2>
            </div>
            <div class="space-y-5 p-6">
                @if ($errors->any())
                    <div class="rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700 dark:border-red-800 dark:bg-red-900/20 dark:text-red-300">
                        Existem erros de validação. Revise os campos de ação e tente novamente.
                    </div>
                @endif

                <form action="{{ $sendTestRoute }}" method="POST" class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                    @csrf
                    <h3 class="mb-3 text-sm font-semibold text-gray-900 dark:text-white">Enviar teste</h3>
                    <div class="grid grid-cols-1 gap-3 lg:grid-cols-3">
                        @if (count($campaignChannels) > 1)
                            <div>
                                <label for="test-channel" class="mb-1 block text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Canal</label>
                                <select id="test-channel" name="channel" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 dark:border-gray-600 dark:bg-gray-700 dark:text-white" {{ $dispatchActionsEnabled ? '' : 'disabled' }}>
                                    <option value="">Selecione</option>
                                    @foreach ($campaignChannels as $channel)
                                        <option value="{{ $channel }}" @selected(old('channel') === $channel)>
                                            {{ $channel === 'email' ? 'Email' : 'WhatsApp' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @else
                            <input type="hidden" name="channel" value="{{ $campaignChannels[0] ?? '' }}">
                        @endif

                        <div class="lg:col-span-2">
                            <label for="test-destination" class="mb-1 block text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Destino</label>
                            <input
                                id="test-destination"
                                type="text"
                                name="destination"
                                value="{{ old('destination') }}"
                                placeholder="email@dominio.com ou +5567999999999"
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                {{ $dispatchActionsEnabled ? '' : 'disabled' }}
                            >
                        </div>
                    </div>

                    <div class="mt-3">
                        <button type="submit" class="btn btn-outline inline-flex items-center" {{ $dispatchActionsEnabled ? '' : 'disabled' }}>
                            <i class="mdi mdi-send-outline mr-1 text-sm"></i>
                            Enviar teste
                        </button>
                    </div>
                </form>

                <div class="grid grid-cols-1 gap-3 lg:grid-cols-3">
                    @if ($campaignType === 'automated')
                        <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700 lg:col-span-2">
                            <h3 class="mb-2 text-sm font-semibold text-gray-900 dark:text-white">Disparo automático</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-300">
                                Campanhas agendadas são disparadas automaticamente pela programação configurada.
                                Use Pausar/Retomar para controlar a execução.
                            </p>
                        </div>
                    @else
                        <form action="{{ $startRoute }}" method="POST" class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                            @csrf
                            <h3 class="mb-2 text-sm font-semibold text-gray-900 dark:text-white">Iniciar envio</h3>
                            <button type="submit" class="btn btn-primary w-full" {{ $dispatchActionsEnabled ? '' : 'disabled' }}>
                                Iniciar agora
                            </button>
                        </form>

                        <form action="{{ $scheduleRoute }}" method="POST" class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                            @csrf
                            <h3 class="mb-2 text-sm font-semibold text-gray-900 dark:text-white">Agendar envio</h3>
                            <input
                                type="datetime-local"
                                name="scheduled_at"
                                value="{{ old('scheduled_at', $defaultScheduleInput) }}"
                                class="mb-2 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                {{ $dispatchActionsEnabled ? '' : 'disabled' }}
                            >
                            <button type="submit" class="btn btn-outline w-full" {{ $dispatchActionsEnabled ? '' : 'disabled' }}>
                                Agendar
                            </button>
                        </form>
                    @endif

                    <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                        <h3 class="mb-2 text-sm font-semibold text-gray-900 dark:text-white">Status da campanha</h3>
                        @if ($campaignStatus === 'paused')
                            <form action="{{ $resumeRoute }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-outline w-full" {{ $dispatchActionsEnabled ? '' : 'disabled' }}>
                                    Retomar campanha
                                </button>
                            </form>
                        @else
                            <form action="{{ $pauseRoute }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-outline w-full" {{ $dispatchActionsEnabled ? '' : 'disabled' }}>
                                    Pausar campanha
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

                @if (!$dispatchActionsEnabled)
                    <p class="text-xs text-amber-700 dark:text-amber-300">
                        Ações de envio estão indisponíveis enquanto houver canais não configurados neste tenant.
                    </p>
                @endif

                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ $runsIndexRoute }}" class="btn btn-outline inline-flex items-center">
                        <i class="mdi mdi-history mr-1 text-sm"></i>
                        Ver execuções
                    </a>
                    <a href="{{ $recipientsIndexRoute }}" class="btn btn-outline inline-flex items-center">
                        <i class="mdi mdi-account-multiple-outline mr-1 text-sm"></i>
                        Ver destinatários
                    </a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
            <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800 xl:col-span-1">
                <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Resumo</h2>
                </div>
                <div class="space-y-4 p-6">
                    <dl class="space-y-3">
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Nome</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $campaign->name ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Tipo</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $typeLabel }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</dt>
                            <dd class="mt-1">@include('tenant.campaigns.partials.status_badge', ['campaign' => $campaign])</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Canais</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $channelsText }}</dd>
                            <div class="mt-2">
                                @include('tenant.campaigns.partials.channel_badges', [
                                    'channels' => $campaignChannels,
                                    'availableChannels' => $availableChannels,
                                ])
                            </div>
                        </div>
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Agendado para</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $scheduledAt }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Criado em</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $createdAt }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Criado por</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $createdByLabel }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <div class="space-y-6 xl:col-span-2">
                <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Conteúdo</h2>
                    </div>
                    <div class="space-y-4 p-6">
                        @if ($hasEmailChannel)
                            @include('tenant.campaigns.partials.content_email', ['content' => $contentJson])
                        @endif

                        @if ($hasWhatsappChannel)
                            @include('tenant.campaigns.partials.content_whatsapp', ['content' => $contentJson])
                        @endif

                        @if (!$hasEmailChannel && !$hasWhatsappChannel)
                            <p class="text-sm text-gray-500 dark:text-gray-400">Nenhum canal configurado nesta campanha.</p>
                        @endif
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Público-alvo</h2>
                    </div>
                    <div class="p-6">
                        @include('tenant.campaigns.partials.audience_summary', ['audience' => $audienceJson])
                    </div>
                </div>

                @if ($campaignType === 'automated')
                    <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Programação</h2>
                        </div>
                        <div class="p-6">
                            @include('tenant.campaigns.partials.automation_summary', [
                                'automation' => $automationJson,
                                'lastAutomationRun' => $lastAutomationRun,
                                'nextAutomationRun' => $nextAutomationRun,
                                'automationTimezone' => $automationTimezone,
                            ])
                        </div>
                    </div>

                    @if (!empty($rulesSummary['conditions']))
                        <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                            <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Regras</h2>
                            </div>
                            <div class="p-6">
                                @include('tenant.campaigns.partials.rules_summary', [
                                    'rulesSummary' => $rulesSummary,
                                ])
                            </div>
                        </div>
                    @endif
                @endif
            </div>
        </div>

        <div class="mt-6 rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Histórico</h2>
            </div>
            <div class="grid grid-cols-1 gap-4 p-6 lg:grid-cols-2">
                <div class="rounded-lg border border-dashed border-gray-300 p-4 dark:border-gray-600">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Execuções (Runs)</h3>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                        Acesse a listagem completa de execuções da campanha.
                    </p>
                    <a href="{{ $runsIndexRoute }}" class="mt-3 inline-flex items-center text-sm font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">
                        Abrir execuções
                        <i class="mdi mdi-arrow-right ml-1 text-sm"></i>
                    </a>
                </div>
                <div class="rounded-lg border border-dashed border-gray-300 p-4 dark:border-gray-600">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Destinatários</h3>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                        Acesse os destinatários gerados e o status de envio por canal.
                    </p>
                    <a href="{{ $recipientsIndexRoute }}" class="mt-3 inline-flex items-center text-sm font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">
                        Abrir destinatários
                        <i class="mdi mdi-arrow-right ml-1 text-sm"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
