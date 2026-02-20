@extends('layouts.tailadmin.app')

@section('title', 'Detalhes do Formulário')
@section('page', 'forms')

@section('content')
    <div class="page-header mb-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <nav class="min-w-0 flex-1" aria-label="breadcrumb">
                <ol class="flex flex-wrap items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                    <li>
                        <a href="{{ workspace_route('tenant.dashboard') }}" class="inline-flex items-center gap-2 text-gray-700 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">
                            <x-icon name="home-outline" size="text-base" />
                            Dashboard
                        </a>
                    </li>
                    <li class="flex items-center gap-2">
                        <x-icon name="chevron-right" size="text-sm" class="text-gray-400" />
                        <a href="{{ workspace_route('tenant.forms.index') }}" class="text-gray-700 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">Formulários</a>
                    </li>
                    <li class="flex items-center gap-2">
                        <x-icon name="chevron-right" size="text-sm" class="text-gray-400" />
                        <span class="text-gray-900 dark:text-white font-semibold">Detalhes</span>
                    </li>
                </ol>
            </nav>
            <div class="flex-shrink-0">
                <x-help-button module="forms" />
            </div>
        </div>
    </div>

    <div class="max-w-6xl mx-auto">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Informações do Formulário</h2>
            </div>

            <div class="p-6">
                <div class="flex flex-wrap gap-2 mb-6">
                    @if ($form->is_active)
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
                        <label class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1 block">ID</label>
                        <p class="text-gray-900 dark:text-white font-medium">{{ $form->id }}</p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-900/40 rounded-lg p-4 border border-gray-200/60 dark:border-gray-700/60">
                        <label class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1 block">Nome</label>
                        <p class="text-gray-900 dark:text-white font-medium">{{ $form->name }}</p>
                    </div>
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
                            <p class="text-gray-900 dark:text-white mb-0">{{ $form->description }}</p>
                        </div>
                    </div>
                @endif

                <div class="mb-6">
                    @if($sectionsCount > 0 || $questionsCount > 0)
                        <div class="p-4 bg-green-50 border border-green-200 rounded-lg dark:bg-green-900/20 dark:border-green-800">
                            <p class="text-sm text-green-800 dark:text-green-200 font-semibold">Formulário configurado</p>
                            <p class="text-sm text-green-700 dark:text-green-300">Possui {{ $sectionsCount }} seção(ões) e {{ $questionsCount }} pergunta(s).</p>
                        </div>
                    @else
                        <div class="p-4 bg-amber-50 border border-amber-200 rounded-lg dark:bg-amber-900/20 dark:border-amber-800">
                            <p class="text-sm text-amber-800 dark:text-amber-200">Este formulário ainda não possui conteúdo. Use "Construir Formulário" para adicionar seções e perguntas.</p>
                        </div>
                    @endif
                </div>

                <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <a href="{{ workspace_route('tenant.forms.index') }}" class="btn btn-outline">
                            <x-icon name="arrow-left" size="text-sm" />
                            Voltar
                        </a>

                        <div class="flex flex-wrap items-center justify-end gap-3">
                            @if($sectionsCount > 0 || $questionsCount > 0)
                                <a href="{{ workspace_route('tenant.forms.preview', $form->id) }}" target="_blank" class="btn btn-outline">
                                    <x-icon name="eye-outline" size="text-sm" />
                                    Visualizar
                                </a>
                            @endif

                            <a href="{{ workspace_route('tenant.forms.builder', $form->id) }}" class="btn btn-outline">
                                <x-icon name="tools" size="text-sm" />
                                Construir Formulário
                            </a>

                            <a href="{{ workspace_route('tenant.forms.edit', ['form' => $form->id]) }}" class="btn btn-outline">
                                <x-icon name="pencil-outline" size="text-sm" />
                                Editar
                            </a>

                            <form action="{{ workspace_route('tenant.forms.destroy', $form->id) }}" method="POST" class="inline"
                                  data-confirm-form-delete="true" data-form-name="{{ $form->name }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">
                                    <x-icon name="trash-can-outline" size="text-sm" />
                                    Excluir
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
