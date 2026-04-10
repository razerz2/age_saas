@extends('layouts.tailadmin.app')

@section('title', 'Templates de Campanhas')
@section('page', 'campaign-templates')

@section('content')
    @php
        $providerLabels = [
            'whatsapp_business' => 'WhatsApp Oficial',
            'zapi' => 'Z-API',
            'waha' => 'WAHA',
            'evolution' => 'Evolution',
        ];
        $providerLabel = $providerLabels[$provider] ?? strtoupper($provider);
        $officialBadge = [
            'approved' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300',
            'pending' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300',
            'draft' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200',
            'rejected' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300',
            'archived' => 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-200',
        ];
    @endphp

    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
        <div class="mb-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Templates de Campanhas</h1>
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
                                    <span class="ml-1 text-gray-500 dark:text-gray-400">Templates</span>
                                </div>
                            </li>
                        </ol>
                    </nav>
                </div>

                <div class="flex items-center gap-2">
                    <x-help-button module="campaign-templates" />
                    @if (!$isOfficialMode)
                        <a href="{{ workspace_route('tenant.campaign-templates.create') }}" class="btn btn-primary inline-flex items-center">
                            <x-icon name="plus" size="text-sm" class="mr-2" />
                            Novo template
                        </a>
                    @elseif ($officialManagementUrl)
                        <a href="{{ $officialManagementUrl }}" class="btn btn-primary inline-flex items-center">
                            <x-icon name="open-in-new" size="text-sm" class="mr-2" />
                            Abrir módulo oficial
                        </a>
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

        @if ($errors->any())
            <div class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-900/20">
                <ul class="list-disc space-y-1 pl-5 text-sm text-red-700 dark:text-red-300">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($isOfficialMode)
            <div class="mb-6 rounded-xl border border-blue-200 bg-blue-50 p-4 dark:border-blue-900/50 dark:bg-blue-900/20">
                <p class="text-sm text-blue-800 dark:text-blue-200">
                    O provider efetivo para campanhas está em <strong>{{ $providerLabel }}</strong>.
                    Os Templates Oficiais são sincronizados com a Meta e o gerenciamento completo acontece no módulo oficial.
                </p>
                @if ($officialManagementUrl)
                    <a href="{{ $officialManagementUrl }}"
                        class="mt-3 inline-flex items-center rounded-lg border border-blue-300 px-3 py-2 text-sm font-medium text-blue-700 hover:bg-blue-100 dark:border-blue-700 dark:text-blue-300 dark:hover:bg-blue-900/40">
                        Ir para Templates Oficiais
                    </a>
                @endif
            </div>

            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Templates Oficiais</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Sincronizados com a Meta e com status aprovado.</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900/40">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Nome</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Idioma</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Status</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Categoria</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Versão</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse ($officialTemplates as $template)
                                <tr>
                                    <td class="px-4 py-3 text-gray-800 dark:text-gray-100">
                                        {{ $template->meta_template_name ?: $template->key }}
                                    </td>
                                    <td class="px-4 py-3 text-gray-700 dark:text-gray-200">{{ $template->language ?: 'pt_BR' }}</td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium {{ $officialBadge[(string) $template->status] ?? 'bg-gray-100 text-gray-700' }}">
                                            {{ strtoupper((string) $template->status) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-gray-700 dark:text-gray-200">{{ strtoupper((string) $template->category) }}</td>
                                    <td class="px-4 py-3 text-gray-700 dark:text-gray-200">v{{ (int) $template->version }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                        Nenhum template oficial aprovado encontrado para este tenant.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($officialTemplates && method_exists($officialTemplates, 'links'))
                    <div class="border-t border-gray-200 px-4 py-3 dark:border-gray-700">
                        {{ $officialTemplates->links() }}
                    </div>
                @endif
            </div>
        @else
            <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 p-4 dark:border-emerald-900/50 dark:bg-emerald-900/20">
                <p class="text-sm text-emerald-800 dark:text-emerald-200">
                    O provider efetivo para campanhas está em <strong>{{ $providerLabel }}</strong>.
                    Este módulo está em modo <strong>Templates Não Oficiais</strong> para campanhas.
                </p>
            </div>

            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Templates Não Oficiais</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900/40">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Nome</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Conteúdo</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Ativo</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Atualizado em</th>
                                <th class="px-4 py-3 text-right font-semibold text-gray-600 dark:text-gray-300">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse ($campaignTemplates as $template)
                                <tr>
                                    <td class="px-4 py-3 text-gray-800 dark:text-gray-100">{{ $template->name }}</td>
                                    <td class="px-4 py-3 text-gray-700 dark:text-gray-200">{{ \Illuminate\Support\Str::limit((string) $template->content, 90) }}</td>
                                    <td class="px-4 py-3">
                                        @if ($template->is_active)
                                            <span class="inline-flex rounded-full bg-green-100 px-2.5 py-1 text-xs font-medium text-green-700 dark:bg-green-900/30 dark:text-green-300">Ativo</span>
                                        @else
                                            <span class="inline-flex rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-700 dark:bg-gray-700 dark:text-gray-200">Inativo</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-gray-700 dark:text-gray-200">{{ $template->updated_at?->format('d/m/Y H:i') ?: '-' }}</td>
                                    <td class="px-4 py-3 text-right">
                                        <a href="{{ workspace_route('tenant.campaign-templates.edit', ['campaignTemplate' => $template->id]) }}"
                                            class="inline-flex items-center rounded-lg border border-gray-300 px-2.5 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700">
                                            Editar
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                        Nenhum template de campanha cadastrado.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($campaignTemplates && method_exists($campaignTemplates, 'links'))
                    <div class="border-t border-gray-200 px-4 py-3 dark:border-gray-700">
                        {{ $campaignTemplates->links() }}
                    </div>
                @endif
            </div>
        @endif
    </div>
@endsection
