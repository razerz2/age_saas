@extends('layouts.tailadmin.app')

@section('title', 'Construir Formulário')

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
                                <i class="mdi mdi-file-document-edit text-primary me-2"></i>
                                {{ $form->name }}
                            </h4>
                            <p class="card-description mb-0 text-muted">Adicione seções, perguntas e opções ao formulário</p>
                        </div>
                        <x-tailadmin-button variant="secondary" size="md" href="{{ workspace_route('tenant.forms.show', ['form' => $form->id]) }}"
                            class="bg-transparent border-gray-300 text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-white/5">
                            <i class="mdi mdi-arrow-left"></i>
                            Voltar
                        </x-tailadmin-button>
                    </div>

                    {{-- Alertas --}}
                    <div id="alert-container"></div>

                    {{-- Botões de Ação --}}
                    <div class="mb-4 flex flex-wrap items-center gap-2">
                        <x-tailadmin-button type="button" variant="primary" size="md" data-bs-toggle="modal" data-bs-target="#addSectionModal">
                            <i class="mdi mdi-plus"></i>
                            Adicionar Seção
                        </x-tailadmin-button>
                        <x-tailadmin-button type="button" variant="success" size="md" data-bs-toggle="modal" data-bs-target="#addQuestionModal" id="addQuestionBtn" disabled>
                            <i class="mdi mdi-plus-circle"></i>
                            Adicionar Pergunta
                        </x-tailadmin-button>
                    </div>

                    {{-- Lista de Seções e Perguntas --}}
                    <div id="form-builder">
                        @if($form->sections->isEmpty() && $form->questions->where('section_id', null)->isEmpty())
                            <div class="alert alert-info">
                                <i class="mdi mdi-information me-2"></i>
                                Nenhuma seção ou pergunta adicionada ainda. Comece adicionando uma seção ou pergunta.
                            </div>
                        @else
                            {{-- Perguntas sem seção --}}
                            @if($form->questions->where('section_id', null)->isNotEmpty())
                                <div class="section-container mb-4" data-section-id="null">
                                    <div class="card border-primary">
                                        <div class="card-header bg-primary text-white">
                                            <h5 class="mb-0">
                                                <i class="mdi mdi-file-document me-2"></i>
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
                                                <i class="mdi mdi-folder me-2"></i>
                                                <span class="section-title">{{ $section->title ?: 'Seção sem título' }}</span>
                                            </h5>
                                            <div class="flex items-center gap-2">
                                                <x-tailadmin-button type="button" variant="warning" size="xs"
                                                    class="edit-section-btn px-2 py-1" data-section-id="{{ $section->id }}" data-section-title="{{ $section->title }}">
                                                    <i class="mdi mdi-pencil"></i>
                                                </x-tailadmin-button>
                                                <x-tailadmin-button type="button" variant="danger" size="xs"
                                                    class="delete-section-btn px-2 py-1" data-section-id="{{ $section->id }}">
                                                    <i class="mdi mdi-delete"></i>
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
                                <i class="mdi mdi-plus"></i>
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
                                <i class="mdi mdi-plus"></i>
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

@push('styles')
    <link href="{{ asset('css/tenant-forms.css') }}" rel="stylesheet">
@endpush

@push('scripts')
<script>
    const tenantSlug = '{{ tenant()->subdomain }}';
const formId = '{{ $form->id }}';
const csrfToken = '{{ csrf_token() }}';

// Função para mostrar alertas
function showAlert(message, type = 'success') {
    const alertContainer = document.getElementById('alert-container');
    const alert = document.createElement('div');
    alert.className = `alert alert-${type} alert-dismissible fade show`;
    alert.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    alertContainer.appendChild(alert);
    setTimeout(() => alert.remove(), 5000);
}

// Adicionar Seção
document.getElementById('addSectionForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    formData.append('position', document.querySelectorAll('.section-container').length);

    try {
        const response = await fetch(`/workspace/${tenantSlug}/forms/${formId}/sections`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken
            },
            body: formData
        });

        const data = await response.json();
        if (response.ok) {
            location.reload();
        } else {
            showAlert('Erro ao adicionar seção: ' + (data.message || 'Erro desconhecido'), 'danger');
        }
    } catch (error) {
        showAlert('Erro ao adicionar seção: ' + error.message, 'danger');
    }
});

// Editar Seção
document.querySelectorAll('.edit-section-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const sectionId = this.dataset.sectionId;
        const sectionTitle = this.dataset.sectionTitle || '';
        document.getElementById('edit_section_id').value = sectionId;
        document.getElementById('edit_section_title').value = sectionTitle;
        new bootstrap.Modal(document.getElementById('editSectionModal')).show();
    });
});

document.getElementById('editSectionForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const sectionId = document.getElementById('edit_section_id').value;
    const formData = new FormData(this);

    try {
        const response = await fetch(`/workspace/${tenantSlug}/sections/${sectionId}`, {
            method: 'PUT',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                title: formData.get('title')
            })
        });

        const data = await response.json();
        if (response.ok) {
            location.reload();
        } else {
            showAlert('Erro ao editar seção: ' + (data.message || 'Erro desconhecido'), 'danger');
        }
    } catch (error) {
        showAlert('Erro ao editar seção: ' + error.message, 'danger');
    }
});

// Deletar Seção
document.querySelectorAll('.delete-section-btn').forEach(btn => {
    btn.addEventListener('click', async function() {
        const sectionId = this.dataset.sectionId;
        confirmAction({
            title: 'Deletar seção',
            message: 'Tem certeza que deseja deletar esta seção? Todas as perguntas serão movidas para "Perguntas Gerais".',
            confirmText: 'Deletar',
            cancelText: 'Cancelar',
            type: 'error',
            onConfirm: async () => {
                try {
                    const response = await fetch(`/workspace/${tenantSlug}/sections/${sectionId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken
                        }
                    });

                    const data = await response.json();
                    if (response.ok) {
                        location.reload();
                    } else {
                        showAlert('Erro ao deletar seção: ' + (data.message || 'Erro desconhecido'), 'danger');
                    }
                } catch (error) {
                    showAlert('Erro ao deletar seção: ' + error.message, 'danger');
                }
            }
        });
    });
});

// Habilitar botão de adicionar pergunta quando houver seções
if (document.querySelectorAll('.section-container').length > 0 || document.querySelectorAll('.questions-list').length > 0) {
    document.getElementById('addQuestionBtn').disabled = false;
}

// Adicionar Pergunta - Configurar seção quando clicar no botão
document.getElementById('addQuestionBtn').addEventListener('click', function() {
    // Se houver seções, permitir seleção; caso contrário, usar null
    const sections = document.querySelectorAll('.section-container');
    const select = document.getElementById('question_section_select');
    if (sections.length === 0) {
        select.value = '';
        select.disabled = true;
    } else {
        select.disabled = false;
    }
});

// Mostrar/ocultar opções baseado no tipo
document.getElementById('question_type').addEventListener('change', function() {
    const optionsContainer = document.getElementById('options-container');
    if (this.value === 'single_choice' || this.value === 'multi_choice') {
        optionsContainer.style.display = 'block';
    } else {
        optionsContainer.style.display = 'none';
        document.getElementById('options-list').innerHTML = '';
    }
});

// Adicionar opção dinamicamente
let optionIndex = 0;
document.getElementById('add-option-btn').addEventListener('click', function() {
    const optionsList = document.getElementById('options-list');
    const optionDiv = document.createElement('div');
    optionDiv.className = 'option-input-group';
    optionDiv.innerHTML = `
        <input type="text" class="form-control" name="options[${optionIndex}][label]" placeholder="Rótulo" required>
        <input type="text" class="form-control" name="options[${optionIndex}][value]" placeholder="Valor" required>
        <button type="button" class="remove-option-btn inline-flex items-center justify-center gap-1 rounded-md bg-error text-white text-xs font-semibold transition hover:bg-error/90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-error/50 focus-visible:ring-offset-white dark:focus-visible:ring-offset-gray-900">
            <i class="mdi mdi-delete"></i>
        </button>
    `;
    optionsList.appendChild(optionDiv);
    optionIndex++;

    // Remover opção
    optionDiv.querySelector('.remove-option-btn').addEventListener('click', function() {
        optionDiv.remove();
    });
});

// Adicionar Pergunta
document.getElementById('addQuestionForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    // Pegar o valor da seção diretamente do select
    const sectionSelect = document.getElementById('question_section_select');
    const sectionId = sectionSelect.value || null;
    
    // Processar opções
    const options = [];
    document.querySelectorAll('#options-list .option-input-group').forEach(group => {
        const label = group.querySelector('input[name*="[label]"]').value;
        const value = group.querySelector('input[name*="[value]"]').value;
        if (label && value) {
            options.push({ label, value });
        }
    });

    const questionData = {
        section_id: sectionId,
        label: formData.get('label'),
        help_text: formData.get('help_text') || null,
        type: formData.get('type'),
        required: formData.get('required') === '1',
        position: 0
    };

    try {
        const response = await fetch(`/workspace/${tenantSlug}/forms/${formId}/questions`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(questionData)
        });

        const data = await response.json();
        if (response.ok) {
            const questionId = data.question.id;
            
            // Adicionar opções se houver
            if (options.length > 0) {
                for (const option of options) {
                    await fetch(`/workspace/${tenantSlug}/questions/${questionId}/options`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            label: option.label,
                            value: option.value,
                            position: 0
                        })
                    });
                }
            }
            
            location.reload();
        } else {
            showAlert('Erro ao adicionar pergunta: ' + (data.message || 'Erro desconhecido'), 'danger');
        }
    } catch (error) {
        showAlert('Erro ao adicionar pergunta: ' + error.message, 'danger');
    }
});

// Editar Pergunta
document.querySelectorAll('.edit-question-btn').forEach(btn => {
    btn.addEventListener('click', async function() {
        const questionId = this.dataset.questionId;
        
        try {
            // Buscar dados da pergunta
            const questionItem = this.closest('.question-item');
            const label = questionItem.querySelector('.question-label').textContent;
            const type = questionItem.dataset.questionType;
            const required = questionItem.dataset.questionRequired === 'true';
            // Pegar section_id diretamente do data attribute da pergunta
            const sectionId = questionItem.dataset.questionSectionId || '';
            
            document.getElementById('edit_question_id').value = questionId;
            document.getElementById('edit_question_label').value = label;
            document.getElementById('edit_question_section_select').value = sectionId;
            document.getElementById('edit_question_type').value = type;
            document.getElementById('edit_question_required').checked = required;
            
            // Carregar opções existentes
            const optionsList = document.getElementById('edit-options-list');
            optionsList.innerHTML = '';
            const existingOptions = questionItem.querySelectorAll('.option-item');
            let editOptionIndex = 0;
            
            existingOptions.forEach(opt => {
                const optionDiv = document.createElement('div');
                optionDiv.className = 'option-input-group';
                const text = opt.textContent.trim();
                optionDiv.innerHTML = `
                    <input type="text" class="form-control" name="edit_options[${editOptionIndex}][label]" value="${text}" placeholder="Rótulo" required>
                    <input type="text" class="form-control" name="edit_options[${editOptionIndex}][value]" value="${text.toLowerCase().replace(/\s+/g, '_')}" placeholder="Valor" required>
                    <button type="button" class="remove-option-btn inline-flex items-center justify-center gap-1 rounded-md bg-error text-white text-xs font-semibold transition hover:bg-error/90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-error/50 focus-visible:ring-offset-white dark:focus-visible:ring-offset-gray-900">
                        <i class="mdi mdi-delete"></i>
                    </button>
                `;
                optionsList.appendChild(optionDiv);
                editOptionIndex++;
                
                optionDiv.querySelector('.remove-option-btn').addEventListener('click', function() {
                    optionDiv.remove();
                });
            });
            
            // Mostrar/ocultar opções
            const editOptionsContainer = document.getElementById('edit-options-container');
            if (type === 'single_choice' || type === 'multi_choice') {
                editOptionsContainer.style.display = 'block';
            } else {
                editOptionsContainer.style.display = 'none';
            }
            
            new bootstrap.Modal(document.getElementById('editQuestionModal')).show();
        } catch (error) {
            showAlert('Erro ao carregar pergunta: ' + error.message, 'danger');
        }
    });
});

// Deletar Pergunta
document.querySelectorAll('.delete-question-btn').forEach(btn => {
    btn.addEventListener('click', async function() {
        const questionId = this.dataset.questionId;
        confirmAction({
            title: 'Deletar pergunta',
            message: 'Tem certeza que deseja deletar esta pergunta?',
            confirmText: 'Deletar',
            cancelText: 'Cancelar',
            type: 'error',
            onConfirm: async () => {
                try {
                    const response = await fetch(`/workspace/${tenantSlug}/questions/${questionId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken
                        }
                    });

                    const data = await response.json();
                    if (response.ok) {
                        location.reload();
                    } else {
                        showAlert('Erro ao deletar pergunta: ' + (data.message || 'Erro desconhecido'), 'danger');
                    }
                } catch (error) {
                    showAlert('Erro ao deletar pergunta: ' + error.message, 'danger');
                }
            }
        });
    });
});

// Adicionar Opção a Pergunta Existente
document.addEventListener('click', function(e) {
    if (e.target.closest('.add-option-to-question-btn')) {
        const btn = e.target.closest('.add-option-to-question-btn');
        const questionId = btn.dataset.questionId;
        document.getElementById('option_question_id').value = questionId;
        new bootstrap.Modal(document.getElementById('addOptionModal')).show();
    }
});

document.getElementById('addOptionForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const questionId = document.getElementById('option_question_id').value;
    const formData = new FormData(this);

    try {
        const response = await fetch(`/tenant/questions/${questionId}/options`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                label: formData.get('label'),
                value: formData.get('value'),
                position: 0
            })
        });

        const data = await response.json();
        if (response.ok) {
            bootstrap.Modal.getInstance(document.getElementById('addOptionModal')).hide();
            location.reload();
        } else {
            showAlert('Erro ao adicionar opção: ' + (data.message || 'Erro desconhecido'), 'danger');
        }
    } catch (error) {
        showAlert('Erro ao adicionar opção: ' + error.message, 'danger');
    }
});

// Deletar Opção
document.addEventListener('click', function(e) {
    if (e.target.closest('.delete-option-btn')) {
        const btn = e.target.closest('.delete-option-btn');
        const optionId = btn.dataset.optionId;
        confirmAction({
            title: 'Deletar opção',
            message: 'Tem certeza que deseja deletar esta opção?',
            confirmText: 'Deletar',
            cancelText: 'Cancelar',
            type: 'error',
            onConfirm: () => {
                fetch(`/workspace/${tenantSlug}/options/${optionId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.message) {
                        location.reload();
                    } else {
                        showAlert('Erro ao deletar opção: ' + (data.message || 'Erro desconhecido'), 'danger');
                    }
                })
                .catch(error => {
                    showAlert('Erro ao deletar opção: ' + error.message, 'danger');
                });
            }
        });
    }
});

// Editar Pergunta - Salvar
document.getElementById('editQuestionForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const questionId = document.getElementById('edit_question_id').value;
    const formData = new FormData(this);

    const questionData = {
        section_id: formData.get('section_id') || null,
        label: formData.get('label'),
        help_text: formData.get('help_text') || null,
        type: formData.get('type'),
        required: formData.get('required') === '1',
        position: 0
    };

    try {
        // Atualizar pergunta
        const response = await fetch(`/workspace/${tenantSlug}/questions/${questionId}`, {
            method: 'PUT',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(questionData)
        });

        const data = await response.json();
        if (response.ok) {
            // Processar opções editadas (por enquanto, apenas recarregar a página)
            // Em uma versão mais avançada, você poderia atualizar/remover opções individualmente
            location.reload();
        } else {
            showAlert('Erro ao editar pergunta: ' + (data.message || 'Erro desconhecido'), 'danger');
        }
    } catch (error) {
        showAlert('Erro ao editar pergunta: ' + error.message, 'danger');
    }
});

// Atualizar tipo de pergunta no modal de edição
document.getElementById('edit_question_type').addEventListener('change', function() {
    const editOptionsContainer = document.getElementById('edit-options-container');
    if (this.value === 'single_choice' || this.value === 'multi_choice') {
        editOptionsContainer.style.display = 'block';
    } else {
        editOptionsContainer.style.display = 'none';
    }
});
</script>
@endpush

