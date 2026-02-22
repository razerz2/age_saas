@extends('layouts.tailadmin.app')

@section('title', 'Visualizar Formulário')
@section('page', 'forms')

@section('content')
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-6">
        <div class="mb-6">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0 flex-1">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <x-icon name="file-document-text-outline" size="text-xl" class="text-blue-600 dark:text-blue-400" />
                        Visualizar Formulário
                    </h1>
                    <nav class="flex mt-2" aria-label="Breadcrumb">
                        <ol class="flex flex-wrap items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                            <li>
                                <a href="{{ workspace_route('tenant.dashboard') }}"
                                   class="inline-flex items-center gap-2 text-gray-700 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">
                                    <x-icon name="home-outline" size="text-base" />
                                    Dashboard
                                </a>
                            </li>
                            <li class="flex items-center gap-2">
                                <x-icon name="chevron-right" size="text-sm" class="text-gray-400" />
                                <a href="{{ workspace_route('tenant.forms.index') }}"
                                   class="text-gray-700 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">Formulários</a>
                            </li>
                            <li class="flex items-center gap-2">
                                <x-icon name="chevron-right" size="text-sm" class="text-gray-400" />
                                <span class="text-gray-900 dark:text-white font-semibold">{{ $form->name }}</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <x-icon name="chevron-right" size="text-sm" class="text-gray-400" />
                                <span class="text-gray-900 dark:text-white font-semibold">Visualizar</span>
                            </li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $form->name }}</h2>
            </div>

            <div class="p-6">
                <div class="flex flex-wrap gap-2 mb-6">
                    @if($form->is_active)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-200">
                            <x-icon name="check-circle-outline" size="text-sm" />
                            Ativo
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-200">
                            <x-icon name="close-circle-outline" size="text-sm" />
                            Inativo
                        </span>
                    @endif
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div class="bg-gray-50 dark:bg-gray-900/40 rounded-lg p-4 border border-gray-200/60 dark:border-gray-700/60">
                        <label class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1 block">Médico</label>
                        <p class="text-gray-900 dark:text-white font-medium">{{ $form->doctor->user->name ?? 'N/A' }}</p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-900/40 rounded-lg p-4 border border-gray-200/60 dark:border-gray-700/60">
                        <label class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1 block">Especialidade</label>
                        <p class="text-gray-900 dark:text-white font-medium">{{ $form->specialty->name ?? 'N/A' }}</p>
                    </div>
                </div>

                @if($form->description)
                    <div class="mb-6">
                        <label class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1 block">Descrição</label>
                        <div class="bg-gray-50 dark:bg-gray-900/40 rounded-lg p-4 border border-gray-200/60 dark:border-gray-700/60">
                            <p class="text-gray-900 dark:text-white">{{ $form->description }}</p>
                        </div>
                    </div>
                @endif

                @php
                    $generalQuestions = $form->questions->where('section_id', null)->sortBy('position');
                    $sections = $form->sections->sortBy('position');
                    $hasContent = $generalQuestions->isNotEmpty() || $sections->isNotEmpty();
                @endphp

                @if(!$hasContent)
                    <div class="p-4 bg-amber-50 border border-amber-200 rounded-lg dark:bg-amber-900/20 dark:border-amber-800 mb-6">
                        <p class="text-sm text-amber-800 dark:text-amber-200">Formulário sem conteúdo.</p>
                    </div>
                @else
                    <div class="space-y-6 mb-6">
                        @if($generalQuestions->isNotEmpty())
                            <div class="rounded-xl border border-gray-200 dark:border-gray-700">
                                <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/40">
                                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Perguntas Gerais</h3>
                                </div>
                                <div class="p-4 space-y-4">
                                    @foreach($generalQuestions as $question)
                                        <div class="rounded-lg border border-gray-200/70 dark:border-gray-700/70 p-4">
                                            <label class="text-sm font-medium text-gray-900 dark:text-white block mb-1">
                                                {{ $question->label }}
                                                @if($question->required)
                                                    <span class="text-red-500">*</span>
                                                @endif
                                            </label>
                                            @if($question->help_text)
                                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">{{ $question->help_text }}</p>
                                            @endif

                                            @if($question->type === 'text')
                                                <input type="text" disabled class="w-full rounded-lg border border-gray-300 bg-gray-100 px-3 py-2 text-sm text-gray-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400" placeholder="Resposta de texto" />
                                            @elseif($question->type === 'number')
                                                <input type="number" disabled class="w-full rounded-lg border border-gray-300 bg-gray-100 px-3 py-2 text-sm text-gray-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400" placeholder="Número" />
                                            @elseif($question->type === 'date')
                                                <input type="date" disabled class="w-full rounded-lg border border-gray-300 bg-gray-100 px-3 py-2 text-sm text-gray-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400" />
                                            @elseif($question->type === 'boolean')
                                                <div class="space-y-2">
                                                    <label class="inline-flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                                                        <input type="radio" disabled class="h-4 w-4" />
                                                        Sim
                                                    </label>
                                                    <label class="inline-flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                                                        <input type="radio" disabled class="h-4 w-4" />
                                                        Não
                                                    </label>
                                                </div>
                                            @elseif($question->type === 'single_choice')
                                                <select disabled class="w-full rounded-lg border border-gray-300 bg-gray-100 px-3 py-2 text-sm text-gray-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400">
                                                    <option>Selecione uma opção</option>
                                                    @foreach($question->options->sortBy('position') as $option)
                                                        <option>{{ $option->label }}</option>
                                                    @endforeach
                                                </select>
                                            @elseif($question->type === 'multi_choice')
                                                <div class="space-y-2">
                                                    @forelse($question->options->sortBy('position') as $option)
                                                        <label class="inline-flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                                                            <input type="checkbox" disabled class="h-4 w-4" />
                                                            {{ $option->label }}
                                                        </label>
                                                    @empty
                                                        <p class="text-xs text-gray-500 dark:text-gray-400">Nenhuma opção configurada.</p>
                                                    @endforelse
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @foreach($sections as $section)
                            <div class="rounded-xl border border-gray-200 dark:border-gray-700">
                                <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/40">
                                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ $section->title ?: 'Seção sem título' }}</h3>
                                </div>
                                <div class="p-4 space-y-4">
                                    @forelse($section->questions->sortBy('position') as $question)
                                        <div class="rounded-lg border border-gray-200/70 dark:border-gray-700/70 p-4">
                                            <label class="text-sm font-medium text-gray-900 dark:text-white block mb-1">
                                                {{ $question->label }}
                                                @if($question->required)
                                                    <span class="text-red-500">*</span>
                                                @endif
                                            </label>
                                            @if($question->help_text)
                                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">{{ $question->help_text }}</p>
                                            @endif

                                            @if($question->type === 'text')
                                                <input type="text" disabled class="w-full rounded-lg border border-gray-300 bg-gray-100 px-3 py-2 text-sm text-gray-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400" placeholder="Resposta de texto" />
                                            @elseif($question->type === 'number')
                                                <input type="number" disabled class="w-full rounded-lg border border-gray-300 bg-gray-100 px-3 py-2 text-sm text-gray-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400" placeholder="Número" />
                                            @elseif($question->type === 'date')
                                                <input type="date" disabled class="w-full rounded-lg border border-gray-300 bg-gray-100 px-3 py-2 text-sm text-gray-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400" />
                                            @elseif($question->type === 'boolean')
                                                <div class="space-y-2">
                                                    <label class="inline-flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                                                        <input type="radio" disabled class="h-4 w-4" />
                                                        Sim
                                                    </label>
                                                    <label class="inline-flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                                                        <input type="radio" disabled class="h-4 w-4" />
                                                        Não
                                                    </label>
                                                </div>
                                            @elseif($question->type === 'single_choice')
                                                <select disabled class="w-full rounded-lg border border-gray-300 bg-gray-100 px-3 py-2 text-sm text-gray-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400">
                                                    <option>Selecione uma opção</option>
                                                    @foreach($question->options->sortBy('position') as $option)
                                                        <option>{{ $option->label }}</option>
                                                    @endforeach
                                                </select>
                                            @elseif($question->type === 'multi_choice')
                                                <div class="space-y-2">
                                                    @forelse($question->options->sortBy('position') as $option)
                                                        <label class="inline-flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                                                            <input type="checkbox" disabled class="h-4 w-4" />
                                                            {{ $option->label }}
                                                        </label>
                                                    @empty
                                                        <p class="text-xs text-gray-500 dark:text-gray-400">Nenhuma opção configurada.</p>
                                                    @endforelse
                                                </div>
                                            @endif
                                        </div>
                                    @empty
                                        <p class="text-sm text-gray-500 dark:text-gray-400">Nenhuma pergunta nesta seção.</p>
                                    @endforelse
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <a href="{{ workspace_route('tenant.forms.index') }}" class="btn btn-outline">
                            <x-icon name="arrow-left" size="text-sm" />
                            Voltar
                        </a>

                        <div class="flex flex-wrap items-center justify-end gap-3">
                            <a href="{{ workspace_route('tenant.forms.builder', ['id' => $form->id]) }}" class="btn btn-outline">
                                <x-icon name="pencil-outline" size="text-sm" />
                                Editar Formulário
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
