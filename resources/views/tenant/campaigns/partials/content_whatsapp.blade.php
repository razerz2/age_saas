@php
    $content = is_array($content ?? null) ? $content : [];
    $whatsapp = data_get($content, 'whatsapp');
    $whatsapp = is_array($whatsapp) ? $whatsapp : [];

    $provider = strtolower(trim((string) ($whatsapp['provider'] ?? '')));
    $messageType = strtolower(trim((string) ($whatsapp['message_type'] ?? '')));
    $text = (string) ($whatsapp['text'] ?? '');
    $media = data_get($whatsapp, 'media');
    $media = is_array($media) ? $media : [];

    $mediaKind = strtolower(trim((string) ($media['kind'] ?? '')));
    $mediaSource = strtolower(trim((string) ($media['source'] ?? '')));
    $mediaUrl = (string) ($media['url'] ?? '');
    $mediaAssetId = (string) ($media['asset_id'] ?? '');
    $mediaCaption = (string) ($media['caption'] ?? '');

    $typeLabel = $messageType === 'media' ? 'Mídia' : ($messageType === 'text' ? 'Texto' : '—');
@endphp

<div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
    <div class="mb-3 flex items-center gap-2">
        <i class="mdi mdi-whatsapp text-base text-emerald-600 dark:text-emerald-400"></i>
        <h4 class="text-sm font-semibold text-gray-900 dark:text-white">WhatsApp</h4>
    </div>

    <dl class="grid grid-cols-1 gap-3">
        <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
            <div>
                <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Provider</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $provider !== '' ? strtoupper($provider) : '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Tipo</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $typeLabel }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Source</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $mediaSource !== '' ? $mediaSource : '—' }}</dd>
            </div>
        </div>

        @if ($messageType === 'text')
            <div>
                <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Mensagem</dt>
                <dd class="mt-1 rounded-md border border-gray-200 bg-gray-50 p-3 text-xs text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                    @if (trim($text) !== '')
                        <pre class="whitespace-pre-wrap break-words">{{ $text }}</pre>
                    @else
                        —
                    @endif
                </dd>
            </div>
        @elseif ($messageType === 'media')
            <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Mídia kind</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $mediaKind !== '' ? $mediaKind : '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Mídia source</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $mediaSource !== '' ? $mediaSource : '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">URL</dt>
                    <dd class="mt-1 break-all text-sm text-gray-900 dark:text-gray-100">{{ trim($mediaUrl) !== '' ? $mediaUrl : '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Asset ID</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ trim($mediaAssetId) !== '' ? $mediaAssetId : '—' }}</dd>
                </div>
            </div>
            <div>
                <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Legenda</dt>
                <dd class="mt-1 rounded-md border border-gray-200 bg-gray-50 p-3 text-xs text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                    @if (trim($mediaCaption) !== '')
                        <pre class="whitespace-pre-wrap break-words">{{ $mediaCaption }}</pre>
                    @else
                        —
                    @endif
                </dd>
            </div>
        @else
            <div>
                <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Mensagem</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">—</dd>
            </div>
        @endif
    </dl>
</div>
