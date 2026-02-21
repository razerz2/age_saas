@extends('layouts.tailadmin.app')

@section('title', 'Detalhes da Resposta')
@section('page', 'responses')

@section('content')
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-6">
        <div class="mb-6">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0 flex-1">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <x-icon name="file-document-check" size="text-xl" class="text-blue-600 dark:text-blue-400" />
                        Detalhes da Resposta
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
                                <a href="{{ workspace_route('tenant.responses.index') }}"
                                   class="text-gray-700 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">Respostas</a>
                            </li>
                            <li class="flex items-center gap-2">
                                <x-icon name="chevron-right" size="text-sm" class="text-gray-400" />
                                <span class="text-gray-900 dark:text-white font-semibold">Detalhes</span>
                            </li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <x-icon name="file-document" size="text-lg" class="text-blue-600 dark:text-blue-400" />
                    Informações da Resposta
                </h2>
            </div>

            <div class="p-6">
                <div class="mb-6">
                    @if($response->status == 'submitted')
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-200">
                            <x-icon name="check-circle-outline" size="text-sm" />
                            Enviado
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-semibold rounded-full bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-200">
                            <x-icon name="clock-outline" size="text-sm" />
                            Pendente
                        </span>
                    @endif
                </div>

                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <x-icon name="information-outline" size="text-lg" class="text-blue-600 dark:text-blue-400" />
                    Informações Gerais
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div class="bg-gray-50 dark:bg-gray-900/40 rounded-lg p-4 border border-gray-200/60 dark:border-gray-700/60">
                        <label class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1 block">ID</label>
                        <p class="text-gray-900 dark:text-white font-medium">{{ $response->id }}</p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-900/40 rounded-lg p-4 border border-gray-200/60 dark:border-gray-700/60">
                        <label class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1 block">Formulário</label>
                        <p class="text-gray-900 dark:text-white font-medium">{{ $response->form->name ?? 'N/A' }}</p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-900/40 rounded-lg p-4 border border-gray-200/60 dark:border-gray-700/60">
                        <label class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1 block">Paciente</label>
                        <p class="text-gray-900 dark:text-white font-medium">{{ $response->patient->full_name ?? 'N/A' }}</p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-900/40 rounded-lg p-4 border border-gray-200/60 dark:border-gray-700/60">
                        <label class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1 block">Agendamento</label>
                        <p class="text-gray-900 dark:text-white font-medium">{{ $response->appointment_id ?? 'N/A' }}</p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-900/40 rounded-lg p-4 border border-gray-200/60 dark:border-gray-700/60">
                        <label class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1 block">Data de Envio</label>
                        <p class="text-gray-900 dark:text-white font-medium">
                            {{ $response->submitted_at ? $response->submitted_at->format('d/m/Y H:i') : 'N/A' }}
                        </p>
                    </div>
                </div>

                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <x-icon name="text-box" size="text-lg" class="text-blue-600 dark:text-blue-400" />
                    Respostas
                </h3>

                @if($response->answers && $response->answers->count() > 0)
                    @if($response->form->sections && $response->form->sections->count() > 0)
                        @foreach($response->form->sections->sortBy('position') as $section)
                            <div class="bg-gray-50 dark:bg-gray-900/40 rounded-lg p-4 border border-gray-200/60 dark:border-gray-700/60 mb-4">
                                <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">
                                    {{ $section->title ?? 'Seção sem título' }}
                                </h4>

                                @foreach($section->questions->sortBy('position') as $question)
                                    @php
                                        $answer = $response->answers->firstWhere('question_id', $question->id);
                                    @endphp
                                    <div class="py-3 border-b border-gray-200 dark:border-gray-700 last:border-b-0">
                                        <label class="text-sm text-gray-600 dark:text-gray-300 block mb-1">
                                            <strong>{{ $question->label }}</strong>
                                        </label>
                                        @if($answer)
                                            <p class="text-sm text-gray-900 dark:text-white">{{ $answer->value ?? 'N/A' }}</p>
                                        @else
                                            <p class="text-sm text-gray-500 dark:text-gray-400">Não respondido</p>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    @else
                        <div class="bg-gray-50 dark:bg-gray-900/40 rounded-lg p-4 border border-gray-200/60 dark:border-gray-700/60">
                            @foreach($response->form->questions->sortBy('position') as $question)
                                @php
                                    $answer = $response->answers->firstWhere('question_id', $question->id);
                                @endphp
                                <div class="py-3 border-b border-gray-200 dark:border-gray-700 last:border-b-0">
                                    <label class="text-sm text-gray-600 dark:text-gray-300 block mb-1">
                                        <strong>{{ $question->label }}</strong>
                                    </label>
                                    @if($answer)
                                        <p class="text-sm text-gray-900 dark:text-white">{{ $answer->value ?? 'N/A' }}</p>
                                    @else
                                        <p class="text-sm text-gray-500 dark:text-gray-400">Não respondido</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                @else
                    <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg dark:bg-blue-900/20 dark:border-blue-800">
                        <p class="text-sm text-blue-800 dark:text-blue-200">Nenhuma resposta encontrada.</p>
                    </div>
                @endif

                <div class="border-t border-gray-200 dark:border-gray-700 pt-6 mt-6">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <a href="{{ workspace_route('tenant.responses.index') }}" class="btn btn-outline">
                            <x-icon name="arrow-left" size="text-sm" />
                            Voltar
                        </a>

                        <div class="flex flex-wrap items-center justify-end gap-3">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
