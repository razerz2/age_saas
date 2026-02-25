@php
    $channels = collect($channels ?? [])
        ->map(fn ($channel) => strtolower(trim((string) $channel)))
        ->filter()
        ->unique()
        ->values()
        ->all();

    $availableChannels = collect($availableChannels ?? [])
        ->map(fn ($channel) => strtolower(trim((string) $channel)))
        ->filter()
        ->unique()
        ->values()
        ->all();

    $labelMap = [
        'email' => 'Email',
        'whatsapp' => 'WhatsApp',
    ];

    $classMap = [
        'email' => 'border-sky-200 bg-sky-50 text-sky-700 dark:border-sky-900/40 dark:bg-sky-900/20 dark:text-sky-300',
        'whatsapp' => 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-900/40 dark:bg-emerald-900/20 dark:text-emerald-300',
    ];
@endphp

@if ($channels === [])
    <span class="text-sm text-gray-500 dark:text-gray-400">—</span>
@else
    <div class="inline-flex flex-wrap items-center gap-2">
        @foreach ($channels as $channel)
            @php
                $isAvailable = in_array($channel, $availableChannels, true);
                $label = $labelMap[$channel] ?? ucfirst($channel);
                $defaultClass = 'border-gray-200 bg-gray-100 text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300';
                $unavailableClass = 'border-red-200 bg-red-50 text-red-700 dark:border-red-900/40 dark:bg-red-900/20 dark:text-red-300';
                $classes = $isAvailable ? ($classMap[$channel] ?? $defaultClass) : $unavailableClass;
            @endphp
            <span class="inline-flex items-center gap-1 rounded-full border px-2.5 py-0.5 text-xs font-medium {{ $classes }}">
                <i class="mdi {{ $channel === 'whatsapp' ? 'mdi-whatsapp' : 'mdi-email-outline' }} text-xs"></i>
                {{ $label }}
                @unless ($isAvailable)
                    <i class="mdi mdi-alert-circle-outline text-xs"></i>
                @endunless
            </span>
        @endforeach
    </div>
@endif
