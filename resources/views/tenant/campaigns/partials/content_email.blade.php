@php
    $content = is_array($content ?? null) ? $content : [];
    $email = data_get($content, 'email');
    $email = is_array($email) ? $email : [];

    $subject = trim((string) ($email['subject'] ?? ''));
    $bodyHtml = (string) ($email['body_html'] ?? '');
    $bodyText = (string) ($email['body_text'] ?? '');
    $hasBodyHtml = trim($bodyHtml) !== '';
    $hasBodyText = trim($bodyText) !== '';
@endphp

<div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
    <div class="mb-3 flex items-center gap-2">
        <i class="mdi mdi-email-outline text-base text-sky-600 dark:text-sky-400"></i>
        <h4 class="text-sm font-semibold text-gray-900 dark:text-white">Email</h4>
    </div>

    <dl class="grid grid-cols-1 gap-3">
        <div>
            <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Assunto</dt>
            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $subject !== '' ? $subject : '—' }}</dd>
        </div>

        <div class="grid grid-cols-1 gap-3 lg:grid-cols-2">
            <div>
                <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Body HTML (texto)</dt>
                <dd class="mt-1 rounded-md border border-gray-200 bg-gray-50 p-3 text-xs text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                    @if ($hasBodyHtml)
                        <pre class="whitespace-pre-wrap break-words">{{ $bodyHtml }}</pre>
                    @else
                        —
                    @endif
                </dd>
                @if ($hasBodyHtml)
                    <details class="mt-2">
                        <summary class="cursor-pointer text-xs text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">
                            Ver HTML bruto
                        </summary>
                        <pre class="mt-2 whitespace-pre-wrap break-words rounded-md border border-gray-200 bg-gray-50 p-3 text-xs text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">{{ $bodyHtml }}</pre>
                    </details>
                @endif
            </div>

            <div>
                <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Body Texto</dt>
                <dd class="mt-1 rounded-md border border-gray-200 bg-gray-50 p-3 text-xs text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                    @if ($hasBodyText)
                        <pre class="whitespace-pre-wrap break-words">{{ $bodyText }}</pre>
                    @else
                        —
                    @endif
                </dd>
            </div>
        </div>
    </dl>
</div>
