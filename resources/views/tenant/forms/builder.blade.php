@extends('layouts.tailadmin.app')

@section('title', 'Construir Formulário')
@section('page', 'forms')

@section('content')

    <div class="page-header">
        <h3 class="page-title"> Construir Formulário </h3>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ workspace_route('tenant.dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ workspace_route('tenant.forms.index') }}">Formulários</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ workspace_route('tenant.forms.show', ['form' => $form->id]) }}">{{ $form->name }}</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Construir</li>
            </ol>
        </nav>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <div>
                            <h4 class="card-title mb-1">
                                <x-icon name="file-document-edit" class=" text-primary me-2" />
                                {{ $form->name }}
                            </h4>
                            <p class="card-description mb-0 text-muted">Adicione seções, perguntas e opções ao formulário</p>
                        </div>
                        <x-tailadmin-button variant="secondary" size="md" href="{{ workspace_route('tenant.forms.show', ['form' => $form->id]) }}"
                            class="bg-transparent border-gray-300 text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-white/5">
                            <x-icon name="arrow-left" class="" />
                            Voltar
                        </x-tailadmin-button>
                    </div>

                    {{-- Alertas --}}
                    <div id="alert-container"></div>

                    {{-- Botões de Ação --}}
                    <div class="mb-4 flex flex-wrap items-center gap-2">
                        <x-tailadmin-button type="button" variant="primary" size="md" data-bs-toggle="modal" data-bs-target="#addSectionModal">
                            <x-icon name="plus" class="" />
                            Adicionar Seção
                        </x-tailadmin-button>
                        <x-tailadmin-button type="button" variant="success" size="md" data-bs-toggle="modal" data-bs-target="#addQuestionModal" id="addQuestionBtn" disabled>
                            <x-icon name="plus-circle" class="" />
                            Adicionar Pergunta
                        </x-tailadmin-button>
                    </div>

                    {{-- Lista de Seções e Perguntas --}}
                    <div id="form-builder" data-tenant-slug="{{ tenant()->subdomain }}" data-form-id="{{ $form->id }}" data-csrf-token="{{ csrf_token() }}">
                        @if($form->sections->isEmpty() && $form->questions->where('section_id', null)->isEmpty())
                            <div class="alert alert-info">
                                <x-icon name="information" class=" me-2" />
                                Nenhuma seção ou pergunta adicionada ainda. Comece adicionando uma seção ou pergunta.
                            </div>
                        @else
                            {{-- Perguntas sem seção --}}
                            @if($form->questions->where('section_id', null)->isNotEmpty())
                                <div class="section-container mb-4" data-section-id="null">
                                    <div class="card border-primary">
                                        <div class="card-header bg-primary text-white">
                                            <h5 class="mb-0">
                                                <x-icon name="file-document" class=" me-2" />
                                                Perguntas Gerais
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="questions-list" data-section-id="null">
                                                @foreach($form->questions->where('section_id', null)->sortBy('position') as $question)
                                                    @include('tenant.forms.partials.question-item', ['question' => $question])
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            {{-- Seções com perguntas --}}
                            @foreach($form->sections->sortBy('position') as $section)
                                <div class="section-container mb-4" data-section-id="{{ $section->id }}">
                                    <div class="card">
                                        <div class="card-header d-flex justify-content-between align-items-center">
                                            <h5 class="mb-0">
                                                <x-icon name="folder" class=" me-2" />
                                                <span class="section-title">{{ $section->title ?: 'Seção sem título' }}</span>
                                            </h5>
                                            <div class="flex items-center gap-2">
                                                <x-tailadmin-button type="button" variant="warning" size="xs"
                                                    class="edit-section-btn px-2 py-1" data-section-id="{{ $section->id }}" data-section-title="{{ $section->title }}">
                                                    <x-icon name="pencil" class="" />
                                                </x-tailadmin-button>
                                                <x-tailadmin-button type="button" variant="danger" size="xs"
                                                    class="delete-section-btn px-2 py-1" data-section-id="{{ $section->id }}">
                                                    <x-icon name="delete" class="" />
                                                </x-tailadmin-button>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div class="questions-list" data-section-id="{{ $section->id }}">
                                                @if($section->questions->isEmpty())
                                                    <p class="text-muted mb-0">Nenhuma pergunta nesta seção.</p>
                                                @else
                                                    @foreach($section->questions->sortBy('position') as $question)
                                                        @include('tenant.forms.partials.question-item', ['question' => $question])
                                                    @endforeach
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal: Adicionar Seção --}}
    <div class="modal fade" id="addSectionModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Adicionar Seção</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="addSectionForm">
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Título da Seção</label>
                            <input type="text" class="form-control" name="title" placeholder="Ex: Dados Pessoais, Sintomas, etc.">
                            <small class="form-text text-muted">Opcional - Deixe em branco para uma seção sem título</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <x-tailadmin-button type="button" variant="secondary" size="md" class="bg-transparent border-gray-300 text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-white/5" data-bs-dismiss="modal">
                            Cancelar
                        </x-tailadmin-button>
                        <x-tailadmin-button type="submit" variant="primary" size="md">
                            Adicionar
                        </x-tailadmin-button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal: Editar Seção --}}
    <div class="modal fade" id="editSectionModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Editar Seção</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="editSectionForm">
                    <input type="hidden" name="section_id" id="edit_section_id">
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Título da Seção</label>
                            <input type="text" class="form-control" name="title" id="edit_section_title" placeholder="Ex: Dados Pessoais, Sintomas, etc.">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <x-tailadmin-button type="button" variant="secondary" size="md" class="bg-transparent border-gray-300 text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-white/5" data-bs-dismiss="modal">
                            Cancelar
                        </x-tailadmin-button>
                        <x-tailadmin-button type="submit" variant="primary" size="md">
                            Salvar
                        </x-tailadmin-button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal: Adicionar Pergunta --}}
    <div class="modal fade" id="addQuestionModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Adicionar Pergunta</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="addQuestionForm">
                    <div class="modal-body">
                        <div class="form-group mb-3">
                            <label>Seção</label>
                            <select class="form-control" id="question_section_select">
                                <option value="">Pergunta Geral (sem seção)</option>
                                @foreach($form->sections->sortBy('position') as $section)
                                    <option value="{{ $section->id }}">{{ $section->title ?: 'Seção sem título' }}</option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">Selecione uma seção ou deixe em branco para pergunta geral</small>
                        </div>
                        <div class="form-group mb-3">
                            <label>Pergunta <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="label" required placeholder="Ex: Qual é o seu nome?">
                        </div>
                        <div class="form-group mb-3">
                            <label>Texto de Ajuda</label>
                            <textarea class="form-control" name="help_text" rows="2" placeholder="Texto explicativo opcional"></textarea>
                        </div>
                        <div class="form-group mb-3">
                            <label>Tipo de Resposta <span class="text-danger">*</span></label>
                            <select class="form-control" name="type" id="question_type" required>
                                <option value="text">Texto</option>
                                <option value="number">Número</option>
                                <option value="date">Data</option>
                                <option value="boolean">Sim/Não</option>
                                <option value="single_choice">Escolha Única</option>
                                <option value="multi_choice">Escolha Múltipla</option>
                            </select>
                        </div>
                        <div class="form-group mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="required" id="question_required" value="1">
                                <label class="form-check-label" for="question_required">
                                    Campo obrigatório
                                </label>
                            </div>
                        </div>
                        <div id="options-container" style="display: none;">
                            <hr>
                            <h6>Opções de Resposta</h6>
                            <div id="options-list"></div>
                            <x-tailadmin-button type="button" variant="success" size="sm" class="mt-2" id="add-option-btn">
                                <x-icon name="plus" class="" />
                                Adicionar Opção
                            </x-tailadmin-button>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <x-tailadmin-button type="button" variant="secondary" size="md" class="bg-transparent border-gray-300 text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-white/5" data-bs-dismiss="modal">
                            Cancelar
                        </x-tailadmin-button>
                        <x-tailadmin-button type="submit" variant="primary" size="md">
                            Adicionar
                        </x-tailadmin-button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal: Adicionar Opção a Pergunta --}}
    <div class="modal fade" id="addOptionModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Adicionar Opção</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="addOptionForm">
                    <input type="hidden" name="question_id" id="option_question_id">
                    <div class="modal-body">
                        <div class="form-group mb-3">
                            <label>Rótulo <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="label" required placeholder="Ex: Sim, Não, etc.">
                        </div>
                        <div class="form-group mb-3">
                            <label>Valor <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="value" required placeholder="Ex: sim, nao, etc.">
                            <small class="form-text text-muted">Valor usado internamente (geralmente em minúsculas, sem espaços)</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <x-tailadmin-button type="button" variant="secondary" size="md" class="bg-transparent border-gray-300 text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-white/5" data-bs-dismiss="modal">
                            Cancelar
                        </x-tailadmin-button>
                        <x-tailadmin-button type="submit" variant="primary" size="md">
                            Adicionar
                        </x-tailadmin-button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal: Editar Pergunta --}}
    <div class="modal fade" id="editQuestionModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Editar Pergunta</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="editQuestionForm">
                    <input type="hidden" name="question_id" id="edit_question_id">
                    <div class="modal-body">
                        <div class="form-group mb-3">
                            <label>Seção</label>
                            <select class="form-control" name="section_id" id="edit_question_section_select">
                                <option value="">Pergunta Geral (sem seção)</option>
                                @foreach($form->sections->sortBy('position') as $section)
                                    <option value="{{ $section->id }}">{{ $section->title ?: 'Seção sem título' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group mb-3">
                            <label>Pergunta <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="label" id="edit_question_label" required>
                        </div>
                        <div class="form-group mb-3">
                            <label>Texto de Ajuda</label>
                            <textarea class="form-control" name="help_text" id="edit_question_help_text" rows="2"></textarea>
                        </div>
                        <div class="form-group mb-3">
                            <label>Tipo de Resposta <span class="text-danger">*</span></label>
                            <select class="form-control" name="type" id="edit_question_type" required>
                                <option value="text">Texto</option>
                                <option value="number">Número</option>
                                <option value="date">Data</option>
                                <option value="boolean">Sim/Não</option>
                                <option value="single_choice">Escolha Única</option>
                                <option value="multi_choice">Escolha Múltipla</option>
                            </select>
                        </div>
                        <div class="form-group mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="required" id="edit_question_required" value="1">
                                <label class="form-check-label" for="edit_question_required">
                                    Campo obrigatório
                                </label>
                            </div>
                        </div>
                        <div id="edit-options-container">
                            <hr>
                            <h6>Opções de Resposta</h6>
                            <div id="edit-options-list"></div>
                            <x-tailadmin-button type="button" variant="success" size="sm" class="mt-2" id="add-edit-option-btn">
                                <x-icon name="plus" class="" />
                                Adicionar Opção
                            </x-tailadmin-button>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <x-tailadmin-button type="button" variant="secondary" size="md" class="bg-transparent border-gray-300 text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-white/5" data-bs-dismiss="modal">
                            Cancelar
                        </x-tailadmin-button>
                        <x-tailadmin-button type="submit" variant="primary" size="md">
                            Salvar
                        </x-tailadmin-button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

