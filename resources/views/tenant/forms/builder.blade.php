@extends('layouts.tailadmin.app')

@section('title', 'Construir Formulário')
@section('page', 'forms')

@section('content')
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-6">
        <div class="mb-6">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0 flex-1">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <x-icon name="file-document-edit" size="text-xl" class="text-blue-600 dark:text-blue-400" />
                        Construir Formulário
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
                                <a href="{{ workspace_route('tenant.forms.show', ['form' => $form->id]) }}"
                                    class="text-gray-700 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">{{ $form->name }}</a>
                            </li>
                            <li class="flex items-center gap-2">
                                <x-icon name="chevron-right" size="text-sm" class="text-gray-400" />
                                <span class="text-gray-900 dark:text-white font-semibold">Construir</span>
                            </li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Construtor do Formulário</h2>
            </div>

            <div class="p-6">
                <div id="alert-container" class="mb-6"></div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6" id="builder-summary">
                    <div class="bg-gray-50 dark:bg-gray-900/40 rounded-lg p-4 border border-gray-200/60 dark:border-gray-700/60">
                        <label class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1 block">Seções</label>
                        <p class="text-gray-900 dark:text-white font-medium">{{ $form->sections->count() }} seção(ões)</p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-900/40 rounded-lg p-4 border border-gray-200/60 dark:border-gray-700/60">
                        <label class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1 block">Perguntas</label>
                        <p class="text-gray-900 dark:text-white font-medium">{{ $form->questions->count() }} pergunta(s)</p>
                    </div>
                </div>

                <div id="form-builder" data-tenant-slug="{{ tenant()->subdomain }}" data-form-id="{{ $form->id }}" data-csrf-token="{{ csrf_token() }}" class="space-y-4 mb-6">
                    @if($form->sections->isEmpty() && $form->questions->where('section_id', null)->isEmpty())
                        <div class="p-4 bg-amber-50 border border-amber-200 rounded-lg dark:bg-amber-900/20 dark:border-amber-800">
                            <p class="text-sm text-amber-800 dark:text-amber-200">Nenhuma seção ou pergunta adicionada ainda.</p>
                        </div>
                    @else
                        @if($form->questions->where('section_id', null)->isNotEmpty())
                            <div class="section-container rounded-xl border border-gray-200 dark:border-gray-700" data-section-id="null">
                                <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/40">
                                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Perguntas Gerais</h3>
                                </div>
                                <div class="p-4">
                                    <div class="questions-list space-y-3" data-section-id="null">
                                        @foreach($form->questions->where('section_id', null)->sortBy('position') as $question)
                                            @include('tenant.forms.partials.question-item', ['question' => $question])
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif

                        @foreach($form->sections->sortBy('position') as $section)
                            <div class="section-container rounded-xl border border-gray-200 dark:border-gray-700" data-section-id="{{ $section->id }}">
                                <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/40 flex items-start justify-between gap-3">
                                    <div>
                                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white section-title">{{ $section->title ?: 'Seção sem título' }}</h3>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Seções</p>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <button type="button" class="btn btn-outline edit-section-btn" data-section-id="{{ $section->id }}" data-section-title="{{ $section->title }}">Editar</button>
                                        <button type="button" class="btn btn-danger delete-section-btn" data-section-id="{{ $section->id }}">Excluir</button>
                                    </div>
                                </div>
                                <div class="p-4">
                                    <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Perguntas</h4>
                                    <div class="questions-list space-y-3" data-section-id="{{ $section->id }}">
                                        @if($section->questions->isEmpty())
                                            <p class="text-sm text-gray-500 dark:text-gray-400">Nenhuma pergunta nesta seção.</p>
                                        @else
                                            @foreach($section->questions->sortBy('position') as $question)
                                                @include('tenant.forms.partials.question-item', ['question' => $question])
                                            @endforeach
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>

                <div id="builder-panels" class="space-y-4">
                    <div id="addSectionPanel" class="hidden rounded-xl border border-gray-200 dark:border-gray-700">
                        <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/40">
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Adicionar Seção</h3>
                        </div>
                        <form id="addSectionForm" class="p-4 space-y-4">
                            <div>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 block">Título da Seção</label>
                                <input type="text" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white" name="title" placeholder="Ex: Dados Pessoais, Sintomas, etc.">
                                <small class="text-xs text-gray-500 dark:text-gray-400">Opcional. Deixe em branco para uma seção sem título.</small>
                            </div>
                            <div class="flex flex-wrap items-center justify-end gap-3">
                                <button type="button" class="btn btn-outline" data-builder-cancel>Cancelar</button>
                                <button type="submit" class="btn btn-primary">Adicionar</button>
                            </div>
                        </form>
                    </div>

                    <div id="editSectionPanel" class="hidden rounded-xl border border-gray-200 dark:border-gray-700">
                        <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/40">
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Editar Seção</h3>
                        </div>
                        <form id="editSectionForm" class="p-4 space-y-4">
                            <input type="hidden" name="section_id" id="edit_section_id">
                            <div>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 block">Título da Seção</label>
                                <input type="text" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white" name="title" id="edit_section_title" placeholder="Ex: Dados Pessoais, Sintomas, etc.">
                            </div>
                            <div class="flex flex-wrap items-center justify-end gap-3">
                                <button type="button" class="btn btn-outline" data-builder-cancel>Cancelar</button>
                                <button type="submit" class="btn btn-primary">Salvar</button>
                            </div>
                        </form>
                    </div>

                    <div id="addQuestionPanel" class="hidden rounded-xl border border-gray-200 dark:border-gray-700">
                        <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/40">
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Adicionar Pergunta</h3>
                        </div>
                        <form id="addQuestionForm" class="p-4 space-y-4">
                            <div>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 block">Seção</label>
                                <select class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white" id="question_section_select">
                                    <option value="">Pergunta Geral (sem seção)</option>
                                    @foreach($form->sections->sortBy('position') as $section)
                                        <option value="{{ $section->id }}">{{ $section->title ?: 'Seção sem título' }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 block">Pergunta <span class="text-red-500">*</span></label>
                                <input type="text" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white" name="label" required placeholder="Ex: Qual é o seu nome?">
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 block">Texto de Ajuda</label>
                                <textarea class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white" name="help_text" rows="2" placeholder="Texto explicativo opcional"></textarea>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 block">Tipo de Resposta <span class="text-red-500">*</span></label>
                                <select class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white" name="type" id="question_type" required>
                                    <option value="text">Texto</option>
                                    <option value="number">Número</option>
                                    <option value="date">Data</option>
                                    <option value="boolean">Sim/Não</option>
                                    <option value="single_choice">Escolha Única</option>
                                    <option value="multi_choice">Escolha Múltipla</option>
                                </select>
                            </div>
                            <div>
                                <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                                    <input class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500" type="checkbox" name="required" id="question_required" value="1">
                                    Campo obrigatório
                                </label>
                            </div>

                            <div id="options-container" class="hidden border-t border-gray-200 dark:border-gray-700 pt-4 mt-4">
                                <h6 class="text-sm font-semibold text-gray-900 dark:text-white mb-2">Opções</h6>
                                <div id="options-list" class="space-y-2"></div>
                                <button type="button" class="btn btn-primary mt-3" id="add-option-btn">Adicionar Opção</button>
                            </div>

                            <div class="flex flex-wrap items-center justify-end gap-3">
                                <button type="button" class="btn btn-outline" data-builder-cancel>Cancelar</button>
                                <button type="submit" class="btn btn-primary">Adicionar</button>
                            </div>
                        </form>
                    </div>

                    <div id="addOptionPanel" class="hidden rounded-xl border border-gray-200 dark:border-gray-700">
                        <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/40">
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Adicionar Opção</h3>
                        </div>
                        <form id="addOptionForm" class="p-4 space-y-4">
                            <input type="hidden" name="question_id" id="option_question_id">
                            <div>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 block">Rótulo <span class="text-red-500">*</span></label>
                                <input type="text" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white" name="label" required placeholder="Ex: Sim, Não, etc.">
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 block">Valor <span class="text-red-500">*</span></label>
                                <input type="text" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white" name="value" required placeholder="Ex: sim, nao, etc.">
                            </div>
                            <div class="flex flex-wrap items-center justify-end gap-3">
                                <button type="button" class="btn btn-outline" data-builder-cancel>Cancelar</button>
                                <button type="submit" class="btn btn-primary">Adicionar</button>
                            </div>
                        </form>
                    </div>

                    <div id="editQuestionPanel" class="hidden rounded-xl border border-gray-200 dark:border-gray-700">
                        <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/40">
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Editar Pergunta</h3>
                        </div>
                        <form id="editQuestionForm" class="p-4 space-y-4">
                            <input type="hidden" name="question_id" id="edit_question_id">
                            <div>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 block">Seção</label>
                                <select class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white" name="section_id" id="edit_question_section_select">
                                    <option value="">Pergunta Geral (sem seção)</option>
                                    @foreach($form->sections->sortBy('position') as $section)
                                        <option value="{{ $section->id }}">{{ $section->title ?: 'Seção sem título' }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 block">Pergunta <span class="text-red-500">*</span></label>
                                <input type="text" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white" name="label" id="edit_question_label" required>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 block">Texto de Ajuda</label>
                                <textarea class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white" name="help_text" id="edit_question_help_text" rows="2"></textarea>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 block">Tipo de Resposta <span class="text-red-500">*</span></label>
                                <select class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white" name="type" id="edit_question_type" required>
                                    <option value="text">Texto</option>
                                    <option value="number">Número</option>
                                    <option value="date">Data</option>
                                    <option value="boolean">Sim/Não</option>
                                    <option value="single_choice">Escolha Única</option>
                                    <option value="multi_choice">Escolha Múltipla</option>
                                </select>
                            </div>
                            <div>
                                <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                                    <input class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500" type="checkbox" name="required" id="edit_question_required" value="1">
                                    Campo obrigatório
                                </label>
                            </div>

                            <div id="edit-options-container" class="hidden mt-6 border-t border-gray-200 dark:border-gray-700 pt-4">
                                <h6 class="text-sm font-semibold text-gray-900 dark:text-white mb-2">Opções</h6>
                                <div id="edit-options-list" class="space-y-2"></div>
                                <button type="button" class="btn btn-primary mt-3" id="add-edit-option-btn">Adicionar Opção</button>
                            </div>

                            <div class="flex flex-wrap items-center justify-end gap-3">
                                <button type="button" class="btn btn-outline" data-builder-cancel>Cancelar</button>
                                <button type="submit" class="btn btn-primary">Salvar</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="border-t border-gray-200 dark:border-gray-700 pt-6 mt-6">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <a href="{{ workspace_route('tenant.forms.show', ['form' => $form->id]) }}" class="btn btn-outline">
                            <x-icon name="arrow-left" size="text-sm" />
                            Voltar
                        </a>

                        <div class="flex flex-wrap items-center justify-end gap-3">
                            <button type="button" class="btn btn-primary" id="addSectionBtn">Adicionar Seção</button>
                            <button type="button" class="btn btn-primary" id="addQuestionBtn" disabled>Adicionar Pergunta</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection