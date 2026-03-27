@extends('layouts.tailadmin.app')

@section('title', 'Templates Oficiais do WhatsApp')

@php
    $statusBadge = [
        'approved' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300',
        'pending' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300',
        'draft' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200',
        'rejected' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300',
        'archived' => 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-200',
    ];
@endphp

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Templates Oficiais do WhatsApp</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Gestao tenant-aware de templates oficiais para eventos clinicos padrao.
            </p>
        </div>
        <div class="flex gap-2">
            <a href="{{ workspace_route('tenant.settings.index', ['tab' => 'templates-oficiais']) }}"
               class="inline-flex items-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700">
                Voltar para Configuracoes
            </a>
            <a href="{{ workspace_route('tenant.settings.whatsapp-official-tenant-templates.create') }}"
               class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                Novo template
            </a>
        </div>
    </div>

    <div class="rounded-xl border border-blue-200 bg-blue-50 p-4 text-sm text-blue-800 dark:border-blue-900/50 dark:bg-blue-900/20 dark:text-blue-200">
        Este modulo usa as credenciais oficiais do proprio tenant (token, phone number id e WABA ID).
    </div>

    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <form method="GET" action="{{ workspace_route('tenant.settings.whatsapp-official-tenant-templates.index') }}" class="grid grid-cols-1 gap-3 md:grid-cols-4">
            <input type="text" name="key" value="{{ $filters['key'] ?? '' }}" placeholder="Filtrar por evento"
                   class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
            <select name="status" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                <option value="">Todos os status</option>
                @foreach($statusOptions as $status)
                    <option value="{{ $status }}" {{ ($filters['status'] ?? '') === $status ? 'selected' : '' }}>{{ strtoupper($status) }}</option>
                @endforeach
            </select>
            <select name="language" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                <option value="">Todos os idiomas</option>
                @foreach($languageOptions as $language)
                    <option value="{{ $language }}" {{ ($filters['language'] ?? '') === $language ? 'selected' : '' }}>{{ $language }}</option>
                @endforeach
            </select>
            <div class="flex gap-2">
                <button type="submit" class="w-full rounded-lg border border-blue-500 px-3 py-2 text-sm font-medium text-blue-600 hover:bg-blue-50 dark:text-blue-300 dark:hover:bg-blue-900/20">
                    Filtrar
                </button>
                <a href="{{ workspace_route('tenant.settings.whatsapp-official-tenant-templates.index') }}"
                   class="w-full rounded-lg border border-gray-300 px-3 py-2 text-center text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700">
                    Limpar
                </a>
            </div>
        </form>
    </div>

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900/40">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Evento</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Nome Meta</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Idioma</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Status</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Versao</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Atualizado</th>
                        <th class="px-4 py-3 text-right font-semibold text-gray-600 dark:text-gray-300">Acoes</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($mappings as $mapping)
                        @php $template = $mapping->officialTemplate; @endphp
                        <tr>
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900 dark:text-white">{{ $eventLabels[$mapping->event_key] ?? $mapping->event_key }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400"><code>{{ $mapping->event_key }}</code></div>
                            </td>
                            <td class="px-4 py-3 text-gray-700 dark:text-gray-200">{{ $template?->meta_template_name ?? '-' }}</td>
                            <td class="px-4 py-3 text-gray-700 dark:text-gray-200">{{ $mapping->language }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium {{ $statusBadge[(string) ($template?->status)] ?? 'bg-gray-100 text-gray-700' }}">
                                    {{ strtoupper((string) ($template?->status ?? 'draft')) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-700 dark:text-gray-200">v{{ (int) ($template?->version ?? 1) }}</td>
                            <td class="px-4 py-3 text-gray-700 dark:text-gray-200">{{ $template?->updated_at?->format('d/m/Y H:i') ?? '-' }}</td>
                            <td class="px-4 py-3">
                                @if($template)
                                <div class="flex justify-end gap-2">
                                    <a href="{{ workspace_route('tenant.settings.whatsapp-official-tenant-templates.show', $template->id) }}"
                                       class="rounded-lg border border-gray-300 px-2.5 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700">
                                        Ver
                                    </a>
                                    <a href="{{ workspace_route('tenant.settings.whatsapp-official-tenant-templates.edit', $template->id) }}"
                                       class="rounded-lg border border-yellow-300 px-2.5 py-1.5 text-xs font-medium text-yellow-700 hover:bg-yellow-50 dark:border-yellow-700 dark:text-yellow-300 dark:hover:bg-yellow-900/20">
                                        Editar
                                    </a>
                                    <form method="POST" action="{{ workspace_route('tenant.settings.whatsapp-official-tenant-templates.sync', $template->id) }}">
                                        @csrf
                                        <button type="submit"
                                                class="rounded-lg border border-blue-300 px-2.5 py-1.5 text-xs font-medium text-blue-700 hover:bg-blue-50 dark:border-blue-700 dark:text-blue-300 dark:hover:bg-blue-900/20">
                                            Sincronizar
                                        </button>
                                    </form>
                                </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-6 text-center text-sm text-gray-500 dark:text-gray-400">
                                Nenhum template oficial do tenant encontrado.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-gray-200 px-4 py-3 dark:border-gray-700">
            {{ $mappings->links() }}
        </div>
    </div>
</div>
@endsection

