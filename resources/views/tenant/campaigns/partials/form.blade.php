@php
    $campaign = $campaign ?? null;
    $availableChannels = collect($availableChannels ?? [])
        ->map(fn ($channel) => strtolower(trim((string) $channel)))
        ->filter(fn ($channel) => in_array($channel, ['email', 'whatsapp'], true))
        ->values()
        ->all();

    $normalizeChannels = function ($channels): array {
        if ($channels === null) {
            return [];
        }

        if (!is_array($channels)) {
            $channels = [$channels];
        }

        $normalized = [];
        foreach ($channels as $channel) {
            $value = strtolower(trim((string) $channel));
            if ($value === '' || in_array($value, $normalized, true)) {
                continue;
            }

            $normalized[] = $value;
        }

        return $normalized;
    };

    $oldChannels = old('channels', old('channels_json'));
    $campaignChannels = is_array($campaign?->channels_json) ? $campaign->channels_json : [];
    $selectedChannels = $normalizeChannels($oldChannels ?? $campaignChannels);
    $selectedChannels = array_values(array_intersect($selectedChannels, $availableChannels));

    if ($selectedChannels === [] && count($availableChannels) === 1) {
        $selectedChannels = [$availableChannels[0]];
    }

    $hasAvailableChannels = count($availableChannels) > 0;
    $singleAvailableChannel = count($availableChannels) === 1 ? $availableChannels[0] : null;
    $canSelectMultipleChannels = count($availableChannels) > 1;

    $emailAvailable = in_array('email', $availableChannels, true);
    $whatsappAvailable = in_array('whatsapp', $availableChannels, true);
    $emailSelected = in_array('email', $selectedChannels, true);
    $whatsappSelected = in_array('whatsapp', $selectedChannels, true);

    $typeValue = old('type', $campaign?->type ?? 'manual');
    $scheduledAtFallback = $campaign?->scheduled_at ? $campaign->scheduled_at->format('Y-m-d\TH:i') : null;
    $scheduledAtValue = old('scheduled_at', $scheduledAtFallback);

    $contentData = is_array($campaign?->content_json) ? $campaign->content_json : [];
    $emailSubject = old('content_json.email.subject', data_get($contentData, 'email.subject'));
    $emailBodyHtml = old('content_json.email.body_html', data_get($contentData, 'email.body_html'));
    $emailBodyText = old('content_json.email.body_text', data_get($contentData, 'email.body_text'));
    $rawEmailAttachments = old('content_json.email.attachments', data_get($contentData, 'email.attachments', []));
    $rawEmailAttachments = is_array($rawEmailAttachments) ? $rawEmailAttachments : [];

    $emailAttachments = [];
    foreach ($rawEmailAttachments as $attachment) {
        if (!is_array($attachment)) {
            continue;
        }

        $source = strtolower(trim((string) ($attachment['source'] ?? 'upload')));
        $assetId = trim((string) ($attachment['asset_id'] ?? ''));

        if ($source !== 'upload' || $assetId === '') {
            continue;
        }

        $emailAttachments[] = [
            'source' => 'upload',
            'asset_id' => $assetId,
            'filename' => trim((string) ($attachment['filename'] ?? ('asset_' . $assetId))),
            'mime' => trim((string) ($attachment['mime'] ?? 'application/octet-stream')),
            'size' => (int) ($attachment['size'] ?? 0),
        ];
    }

    $whatsappMessageType = old('content_json.whatsapp.message_type', data_get($contentData, 'whatsapp.message_type', 'text'));
    $whatsappText = old('content_json.whatsapp.text', data_get($contentData, 'whatsapp.text'));
    $whatsappMediaKind = old('content_json.whatsapp.media.kind', data_get($contentData, 'whatsapp.media.kind', 'image'));
    $whatsappMediaSource = old('content_json.whatsapp.media.source', data_get($contentData, 'whatsapp.media.source', 'url'));
    $whatsappMediaUrl = old('content_json.whatsapp.media.url', data_get($contentData, 'whatsapp.media.url'));
    $whatsappMediaAssetId = old('content_json.whatsapp.media.asset_id', data_get($contentData, 'whatsapp.media.asset_id'));
    $whatsappMediaCaption = old('content_json.whatsapp.media.caption', data_get($contentData, 'whatsapp.media.caption'));

    $automationData = is_array($campaign?->automation_json) ? $campaign->automation_json : [];
    $automationEnabled = $typeValue === 'automated';
    $automationTrigger = old('automation_json.trigger', data_get($automationData, 'trigger', 'birthday'));
    $automationTimeRaw = old('automation_json.schedule.time', data_get($automationData, 'schedule.time', '09:00'));
    $automationTime = preg_match('/^\d{2}:\d{2}/', (string) $automationTimeRaw)
        ? substr((string) $automationTimeRaw, 0, 5)
        : '09:00';

    $requireEmail = $emailSelected ? 1 : 0;
    $requireWhatsapp = $whatsappSelected ? 1 : 0;

    $tenantSlug = request()->route('slug') ?? tenant()?->subdomain ?? '';
    $integrationsUrl = \Illuminate\Support\Facades\Route::has('tenant.integrations.index')
        ? workspace_route('tenant.integrations.index')
        : url('/workspace/' . $tenantSlug . '/integrations');
    $assetUploadUrl = \Illuminate\Support\Facades\Route::has('tenant.campaigns.assets.store')
        ? workspace_route('tenant.campaigns.assets.store')
        : '';
    $campaignVariables = is_array($campaignVariables ?? null) ? $campaignVariables : [];
@endphp

<div class="mx-auto max-w-7xl">
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $campaign ? 'Editar Campanha' : 'Nova Campanha' }}</h1>
                <nav class="mt-2 flex" aria-label="Breadcrumb">
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
                                    class="ml-1 text-gray-700 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">Campanhas</a>
                            </div>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <x-icon name="chevron-right" size="text-sm" class="text-gray-400" />
                                <span class="ml-1 text-gray-500 dark:text-gray-400">{{ $breadcrumbCurrent ?? ($campaign ? 'Editar' : 'Criar') }}</span>
                            </div>
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    @if ($errors->any())
        <div class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-900/20">
            <div class="flex">
                <x-icon name="alert-circle-outline" size="text-lg" class="text-red-600 dark:text-red-400" />
                <div class="ml-3">
                    <p class="text-sm font-medium text-red-800 dark:text-red-200">Revise os campos abaixo.</p>
                    <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-red-700 dark:text-red-300">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    @if (!$hasAvailableChannels)
        <div class="mb-6 rounded-lg border border-amber-200 bg-amber-50 p-4 dark:border-amber-800 dark:bg-amber-900/20">
            <div class="flex items-start justify-between gap-4">
                <div class="flex">
                    <x-icon name="alert-circle-outline" size="text-lg" class="text-amber-600 dark:text-amber-400" />
                    <p class="ml-3 text-sm text-amber-800 dark:text-amber-200">
                        Campanhas indisponíveis: configure sua API de Email e/ou WhatsApp em Integrações.
                    </p>
                </div>
                <a href="{{ $integrationsUrl }}" class="btn btn-outline whitespace-nowrap">Configurar Integrações</a>
            </div>
        </div>
    @endif

    <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $campaign ? 'Editar campanha' : 'Configurar campanha' }}</h2>
        </div>

        <form
            id="campaign-form"
            action="{{ $formAction }}"
            method="POST"
            class="space-y-8 p-6"
            data-asset-upload-url="{{ $assetUploadUrl }}"
        >
            @csrf
            @if (($httpMethod ?? 'POST') !== 'POST')
                @method($httpMethod)
            @endif

            <input type="hidden" name="content_json[version]" value="{{ old('content_json.version', 1) }}">
            <input type="hidden" name="audience_json[version]" value="{{ old('audience_json.version', 1) }}">
            <input type="hidden" name="audience_json[source]" value="{{ old('audience_json.source', 'patients') }}">
            <input type="hidden" name="audience_json[filters][patient][is_active]" value="{{ old('audience_json.filters.patient.is_active', 1) }}">
            <input type="hidden" id="audience-require-email" name="audience_json[require][email]" value="{{ $requireEmail }}">
            <input type="hidden" id="audience-require-whatsapp" name="audience_json[require][whatsapp]" value="{{ $requireWhatsapp }}">

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <div>
                    <label for="campaign-name" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Nome <span class="text-red-500">*</span></label>
                    <input id="campaign-name" type="text" name="name" maxlength="150" required
                        value="{{ old('name', $campaign?->name) }}"
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white @error('name') border-red-500 @enderror">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="campaign-type" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Tipo <span class="text-red-500">*</span></label>
                    <select id="campaign-type" name="type" required
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white @error('type') border-red-500 @enderror">
                        <option value="manual" @selected($typeValue === 'manual')>Manual</option>
                        <option value="automated" @selected($typeValue === 'automated')>Automatizada</option>
                    </select>
                    @error('type')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="campaign-scheduled-at" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Agendado para</label>
                    <input id="campaign-scheduled-at" type="datetime-local" name="scheduled_at" value="{{ $scheduledAtValue }}"
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white @error('scheduled_at') border-red-500 @enderror">
                    @error('scheduled_at')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <h3 class="mb-3 text-base font-semibold text-gray-900 dark:text-white">Canais</h3>

                @if ($singleAvailableChannel)
                    <input type="hidden" name="channels[]" value="{{ $singleAvailableChannel }}" data-fixed-channel="true">
                    <span class="inline-flex items-center rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-medium text-blue-700 dark:border-blue-900/40 dark:bg-blue-900/20 dark:text-blue-300">
                        {{ $singleAvailableChannel === 'email' ? 'Email' : 'WhatsApp' }}
                    </span>
                @elseif ($canSelectMultipleChannels)
                    <div class="flex flex-wrap gap-3">
                        @if ($emailAvailable)
                            <label class="inline-flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700 dark:border-gray-600 dark:text-gray-200">
                                <input type="checkbox" name="channels[]" value="email" class="js-channel-checkbox h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                    @checked($emailSelected)>
                                Email
                            </label>
                        @endif
                        @if ($whatsappAvailable)
                            <label class="inline-flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700 dark:border-gray-600 dark:text-gray-200">
                                <input type="checkbox" name="channels[]" value="whatsapp" class="js-channel-checkbox h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                    @checked($whatsappSelected)>
                                WhatsApp
                            </label>
                        @endif
                    </div>
                @endif
                @error('channels')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-between gap-3">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Conteúdo da Campanha</h3>
                <button
                    type="button"
                    class="btn btn-primary btn-sm inline-flex items-center gap-2"
                    data-variables-modal-open="campaign-variables-modal"
                    title="Variáveis disponíveis"
                    aria-label="Variáveis disponíveis"
                    aria-expanded="false"
                >
                    <i class="mdi mdi-code-braces text-sm" aria-hidden="true"></i>
                    Variáveis
                </button>
            </div>

            <div id="campaign-email-section" data-channel-section="email" class="{{ $emailSelected ? '' : 'hidden' }}">
                <h3 class="mb-3 text-base font-semibold text-gray-900 dark:text-white">Conteúdo de Email</h3>
                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Assunto <span class="text-red-500">*</span></label>
                        <input type="text" name="content_json[email][subject]" maxlength="150" value="{{ $emailSubject }}" {{ $emailSelected ? '' : 'disabled' }}
                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white @error('content_json.email.subject') border-red-500 @enderror">
                        @error('content_json.email.subject')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Body HTML</label>
                            <textarea name="content_json[email][body_html]" rows="6" {{ $emailSelected ? '' : 'disabled' }}
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white @error('content_json.email.body_html') border-red-500 @enderror">{{ $emailBodyHtml }}</textarea>
                            @error('content_json.email.body_html')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Body Texto</label>
                            <textarea name="content_json[email][body_text]" rows="6" {{ $emailSelected ? '' : 'disabled' }}
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white @error('content_json.email.body_text') border-red-500 @enderror">{{ $emailBodyText }}</textarea>
                            @error('content_json.email.body_text')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Preencha ao menos um entre Body HTML ou Body Texto.</p>

                    <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                        <div class="mb-3 flex items-center justify-between gap-3">
                            <div>
                                <h4 class="text-sm font-medium text-gray-800 dark:text-gray-100">Anexos de Email</h4>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Máximo de 3 anexos. Executáveis são bloqueados.</p>
                            </div>
                        </div>

                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                            <input
                                id="email-attachments-upload-input"
                                type="file"
                                multiple
                                {{ $emailSelected ? '' : 'disabled' }}
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            >
                            <button
                                type="button"
                                id="email-attachments-upload-btn"
                                {{ $emailSelected ? '' : 'disabled' }}
                                class="btn btn-outline whitespace-nowrap"
                            >
                                Enviar anexos
                            </button>
                        </div>

                        <p id="email-attachments-upload-feedback" class="mt-2 text-xs text-gray-500 dark:text-gray-400"></p>

                        <ul
                            id="email-attachments-list"
                            data-max-items="3"
                            data-next-index="{{ count($emailAttachments) }}"
                            class="mt-3 space-y-2"
                        >
                            @foreach ($emailAttachments as $index => $attachment)
                                <li class="js-email-attachment-item rounded-md border border-gray-200 bg-gray-50 p-3 dark:border-gray-700 dark:bg-gray-900/30">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <p class="truncate text-sm font-medium text-gray-800 dark:text-gray-100">{{ $attachment['filename'] ?: ('asset_' . $attachment['asset_id']) }}</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                                ID {{ $attachment['asset_id'] }} · {{ $attachment['mime'] ?: 'application/octet-stream' }}
                                                @if (($attachment['size'] ?? 0) > 0)
                                                    · {{ number_format((int) $attachment['size'] / 1024, 1, ',', '.') }} KB
                                                @endif
                                            </p>
                                        </div>
                                        <button type="button" class="js-remove-email-attachment text-xs text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300">
                                            Remover
                                        </button>
                                    </div>
                                    <input type="hidden" name="content_json[email][attachments][{{ $index }}][source]" value="upload">
                                    <input type="hidden" name="content_json[email][attachments][{{ $index }}][asset_id]" value="{{ $attachment['asset_id'] }}">
                                    <input type="hidden" name="content_json[email][attachments][{{ $index }}][filename]" value="{{ $attachment['filename'] }}">
                                    <input type="hidden" name="content_json[email][attachments][{{ $index }}][mime]" value="{{ $attachment['mime'] }}">
                                    <input type="hidden" name="content_json[email][attachments][{{ $index }}][size]" value="{{ (int) ($attachment['size'] ?? 0) }}">
                                </li>
                            @endforeach
                        </ul>

                        @error('content_json.email.attachments')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                        @error('content_json.email.attachments.*.asset_id')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div id="campaign-whatsapp-section" data-channel-section="whatsapp" class="{{ $whatsappSelected ? '' : 'hidden' }}">
                <h3 class="mb-3 text-base font-semibold text-gray-900 dark:text-white">Conteúdo de WhatsApp</h3>
                <input type="hidden" name="content_json[whatsapp][provider]" value="waha" {{ $whatsappSelected ? '' : 'disabled' }}>
                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label for="whatsapp-message-type" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Tipo da mensagem <span class="text-red-500">*</span></label>
                        <select id="whatsapp-message-type" name="content_json[whatsapp][message_type]" {{ $whatsappSelected ? '' : 'disabled' }}
                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white @error('content_json.whatsapp.message_type') border-red-500 @enderror">
                            <option value="text" @selected($whatsappMessageType === 'text')>Texto</option>
                            <option value="media" @selected($whatsappMessageType === 'media')>Mídia</option>
                        </select>
                        @error('content_json.whatsapp.message_type')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    <div id="whatsapp-text-wrapper" class="{{ $whatsappMessageType === 'text' ? '' : 'hidden' }}">
                        <div class="mb-2 flex items-center justify-between gap-3">
                            <label for="campaign-whatsapp-text" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Texto <span class="text-red-500">*</span></label>
                            <div class="relative" data-emoji-picker="whatsapp">
                                <button
                                    type="button"
                                    class="btn btn-outline btn-sm inline-flex items-center gap-2 whitespace-nowrap"
                                    data-emoji-toggle="1"
                                    aria-label="Abrir seletor de emojis"
                                    aria-haspopup="dialog"
                                    aria-expanded="false"
                                    aria-controls="campaign-whatsapp-emoji-popover"
                                >
                                    <i class="mdi mdi-emoticon-happy-outline text-base" aria-hidden="true"></i>
                                    <span>Emojis</span>
                                </button>
                                <div
                                    id="campaign-whatsapp-emoji-popover"
                                    class="absolute right-0 top-full z-50 mt-2 hidden w-[360px] max-w-[92vw] overflow-hidden rounded-xl border border-stroke bg-white p-2 shadow-lg dark:border-strokedark dark:bg-boxdark"
                                    data-emoji-popover="1"
                                    role="dialog"
                                    aria-modal="false"
                                    aria-label="Seletor de emojis"
                                >
                                    <div class="mb-2 flex gap-2 overflow-x-auto pb-2">
                                        <button type="button" data-emoji-tab="recent" class="whitespace-nowrap rounded-md border border-transparent px-2 py-1 text-xs font-medium text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-boxdark-2" aria-pressed="false">Recentes</button>
                                        <button type="button" data-emoji-tab="faces" class="whitespace-nowrap rounded-md border border-transparent px-2 py-1 text-xs font-medium text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-boxdark-2" aria-pressed="true">Carinhas</button>
                                        <button type="button" data-emoji-tab="gestures" class="whitespace-nowrap rounded-md border border-transparent px-2 py-1 text-xs font-medium text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-boxdark-2" aria-pressed="false">Gestos</button>
                                        <button type="button" data-emoji-tab="objects" class="whitespace-nowrap rounded-md border border-transparent px-2 py-1 text-xs font-medium text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-boxdark-2" aria-pressed="false">Objetos</button>
                                        <button type="button" data-emoji-tab="symbols" class="whitespace-nowrap rounded-md border border-transparent px-2 py-1 text-xs font-medium text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-boxdark-2" aria-pressed="false">Simbolos</button>
                                    </div>
                                    <div class="max-h-[300px] overflow-y-auto pr-1">
                                        <div data-emoji-grid="1" class="grid grid-cols-10 gap-1 sm:grid-cols-12"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <textarea id="campaign-whatsapp-text" name="content_json[whatsapp][text]" rows="4" {{ $whatsappSelected && $whatsappMessageType === 'text' ? '' : 'disabled' }}
                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white @error('content_json.whatsapp.text') border-red-500 @enderror">{{ $whatsappText }}</textarea>
                        @error('content_json.whatsapp.text')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    <div id="whatsapp-media-wrapper" class="{{ $whatsappMessageType === 'media' ? '' : 'hidden' }}">
                        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Tipo de mídia <span class="text-red-500">*</span></label>
                                <select name="content_json[whatsapp][media][kind]" {{ $whatsappSelected && $whatsappMessageType === 'media' ? '' : 'disabled' }}
                                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white @error('content_json.whatsapp.media.kind') border-red-500 @enderror">
                                    <option value="image" @selected($whatsappMediaKind === 'image')>Imagem</option>
                                    <option value="video" @selected($whatsappMediaKind === 'video')>Vídeo</option>
                                    <option value="document" @selected($whatsappMediaKind === 'document')>Documento</option>
                                    <option value="audio" @selected($whatsappMediaKind === 'audio')>Áudio</option>
                                </select>
                            </div>
                            <div>
                                <label for="whatsapp-media-source" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Origem da mídia <span class="text-red-500">*</span></label>
                                <select id="whatsapp-media-source" name="content_json[whatsapp][media][source]" {{ $whatsappSelected && $whatsappMessageType === 'media' ? '' : 'disabled' }}
                                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white @error('content_json.whatsapp.media.source') border-red-500 @enderror">
                                    <option value="url" @selected($whatsappMediaSource === 'url')>URL</option>
                                    <option value="upload" @selected($whatsappMediaSource === 'upload')>Upload</option>
                                </select>
                            </div>
                        </div>
                        <div id="whatsapp-media-url-wrapper" class="mt-4 {{ $whatsappMediaSource === 'url' ? '' : 'hidden' }}">
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">URL da mídia <span class="text-red-500">*</span></label>
                            <input type="url" name="content_json[whatsapp][media][url]" value="{{ $whatsappMediaUrl }}" {{ $whatsappSelected && $whatsappMessageType === 'media' && $whatsappMediaSource === 'url' ? '' : 'disabled' }}
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white @error('content_json.whatsapp.media.url') border-red-500 @enderror">
                        </div>
                        <div id="whatsapp-media-asset-wrapper" class="mt-4 {{ $whatsappMediaSource === 'upload' ? '' : 'hidden' }}">
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Arquivo (upload) <span class="text-red-500">*</span></label>
                            <input
                                id="whatsapp-media-asset-id"
                                type="hidden"
                                name="content_json[whatsapp][media][asset_id]"
                                value="{{ $whatsappMediaAssetId }}"
                                {{ $whatsappSelected && $whatsappMessageType === 'media' && $whatsappMediaSource === 'upload' ? '' : 'disabled' }}
                            >
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                                <input
                                    id="whatsapp-media-upload-file"
                                    type="file"
                                    {{ $whatsappSelected && $whatsappMessageType === 'media' && $whatsappMediaSource === 'upload' ? '' : 'disabled' }}
                                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white @error('content_json.whatsapp.media.asset_id') border-red-500 @enderror"
                                >
                                <button
                                    type="button"
                                    id="whatsapp-media-upload-btn"
                                    {{ $whatsappSelected && $whatsappMessageType === 'media' && $whatsappMediaSource === 'upload' ? '' : 'disabled' }}
                                    class="btn btn-outline whitespace-nowrap"
                                >
                                    Enviar arquivo
                                </button>
                            </div>
                            <p id="whatsapp-media-upload-feedback" class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                @if ($whatsappMediaAssetId)
                                    Arquivo vinculado (asset_id: {{ $whatsappMediaAssetId }}).
                                @endif
                            </p>
                            @error('content_json.whatsapp.media.asset_id')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="mt-4">
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Legenda</label>
                            <textarea name="content_json[whatsapp][media][caption]" rows="3" {{ $whatsappSelected && $whatsappMessageType === 'media' ? '' : 'disabled' }}
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">{{ $whatsappMediaCaption }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <h3 class="mb-3 text-base font-semibold text-gray-900 dark:text-white">Audiência</h3>
                <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                    <p class="text-sm text-gray-600 dark:text-gray-300">Origem fixa: Pacientes ativos.</p>
                    <div class="mt-3 flex flex-wrap gap-4 text-sm text-gray-700 dark:text-gray-300">
                        @if ($emailAvailable)
                            <label class="inline-flex items-center gap-2">
                                <input id="audience-require-email-check" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500" {{ $requireEmail ? 'checked' : '' }} disabled>
                                Exigir email do destinatário
                            </label>
                        @endif
                        @if ($whatsappAvailable)
                            <label class="inline-flex items-center gap-2">
                                <input id="audience-require-whatsapp-check" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500" {{ $requireWhatsapp ? 'checked' : '' }} disabled>
                                Exigir WhatsApp do destinatário
                            </label>
                        @endif
                    </div>
                </div>
            </div>

            <div id="campaign-automation-section" class="{{ $automationEnabled ? '' : 'hidden' }}">
                <h3 class="mb-3 text-base font-semibold text-gray-900 dark:text-white">Automação</h3>
                <input type="hidden" class="js-automation-input" name="automation_json[version]" value="{{ old('automation_json.version', 1) }}" {{ $automationEnabled ? '' : 'disabled' }}>
                <input type="hidden" class="js-automation-input" name="automation_json[schedule][type]" value="{{ old('automation_json.schedule.type', 'daily') }}" {{ $automationEnabled ? '' : 'disabled' }}>
                <input type="hidden" class="js-automation-input" name="automation_json[timezone]" value="{{ old('automation_json.timezone', 'America/Campo_Grande') }}" {{ $automationEnabled ? '' : 'disabled' }}>
                <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Trigger <span class="text-red-500">*</span></label>
                        <select class="js-automation-input w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white @error('automation_json.trigger') border-red-500 @enderror"
                            name="automation_json[trigger]" {{ $automationEnabled ? '' : 'disabled' }}>
                            <option value="birthday" @selected($automationTrigger === 'birthday')>Aniversário</option>
                            <option value="inactive_patients" @selected($automationTrigger === 'inactive_patients')>Pacientes inativos</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Horário diário <span class="text-red-500">*</span></label>
                        <input class="js-automation-input w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white @error('automation_json.schedule.time') border-red-500 @enderror"
                            type="time" name="automation_json[schedule][time]" value="{{ $automationTime }}" {{ $automationEnabled ? '' : 'disabled' }}>
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-3 border-t border-gray-200 pt-6 dark:border-gray-700 sm:flex-row sm:items-center sm:justify-between">
                <a href="{{ $cancelUrl ?? workspace_route('tenant.campaigns.index') }}" class="btn btn-outline">Cancelar</a>
                <button type="submit" class="btn btn-primary" {{ $hasAvailableChannels ? '' : 'disabled' }}>
                    {{ $submitLabel ?? 'Salvar Campanha' }}
                </button>
            </div>
        </form>
    </div>
</div>

@include('tenant.components.variables-modal', [
    'modalId' => 'campaign-variables-modal',
    'title' => 'Variáveis disponíveis',
    'hint' => 'Copie e cole no conteúdo da campanha usando o formato',
    'variables' => $campaignVariables,
])

@once
    @push('styles')
        <style>
            [data-page="campaigns"] [data-emoji-grid="1"] {
                display: grid !important;
                grid-template-columns: repeat(10, minmax(0, 1fr)) !important;
                gap: 0.25rem !important;
            }

            @media (min-width: 640px) {
                [data-page="campaigns"] [data-emoji-grid="1"] {
                    grid-template-columns: repeat(12, minmax(0, 1fr)) !important;
                }
            }

            [data-page="campaigns"] .emoji-btn {
                display: inline-flex !important;
                width: 32px !important;
                height: 32px !important;
                align-items: center !important;
                justify-content: center !important;
                border: 0 !important;
                border-radius: 8px !important;
                line-height: 1 !important;
                padding: 0 !important;
                background: transparent !important;
                font-size: 1.125rem !important;
            }

            [data-page="campaigns"] .emoji-btn:hover {
                background-color: #f3f4f6 !important;
            }

            .dark [data-page="campaigns"] .emoji-btn:hover,
            [data-page="campaigns"].dark .emoji-btn:hover {
                background-color: rgba(255, 255, 255, 0.06) !important;
            }

            [data-page="campaigns"] .emoji-tab-active {
                border-color: #3b82f6 !important;
                background-color: #eff6ff !important;
                color: #1d4ed8 !important;
            }

            .dark [data-page="campaigns"] .emoji-tab-active,
            [data-page="campaigns"].dark .emoji-tab-active {
                border-color: rgba(96, 165, 250, 0.7) !important;
                background-color: rgba(37, 99, 235, 0.3) !important;
                color: #bfdbfe !important;
            }
        </style>
    @endpush
@endonce


