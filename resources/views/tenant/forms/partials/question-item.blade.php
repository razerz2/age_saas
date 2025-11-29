<div class="question-item" data-question-id="{{ $question->id }}" data-question-type="{{ $question->type }}" data-question-required="{{ $question->required ? 'true' : 'false' }}" data-question-section-id="{{ $question->section_id ?? '' }}">
    <div class="question-header">
        <div class="flex-grow-1">
            <span class="question-label">
                {{ $question->label }}
                @if($question->required)
                    <span class="text-danger">*</span>
                @endif
            </span>
            <span class="badge bg-info question-type-badge ms-2">
                @switch($question->type)
                    @case('text')
                        Texto
                        @break
                    @case('number')
                        Número
                        @break
                    @case('date')
                        Data
                        @break
                    @case('boolean')
                        Sim/Não
                        @break
                    @case('single_choice')
                        Escolha Única
                        @break
                    @case('multi_choice')
                        Escolha Múltipla
                        @break
                @endswitch
            </span>
        </div>
        <div>
            <button class="btn btn-sm btn-warning edit-question-btn" data-question-id="{{ $question->id }}">
                <i class="mdi mdi-pencil"></i>
            </button>
            <button class="btn btn-sm btn-danger delete-question-btn" data-question-id="{{ $question->id }}">
                <i class="mdi mdi-delete"></i>
            </button>
        </div>
    </div>
    
    @if($question->help_text)
        <p class="text-muted small mb-2">
            <i class="mdi mdi-information-outline me-1"></i>
            {{ $question->help_text }}
        </p>
    @endif
    
    @if($question->options->isNotEmpty())
        <div class="options-list">
            <strong class="small">Opções:</strong>
            @foreach($question->options->sortBy('position') as $option)
                <div class="option-item">
                    <i class="mdi mdi-circle-small me-1"></i>
                    {{ $option->label }} 
                    <span class="text-muted">({{ $option->value }})</span>
                    <button class="btn btn-sm btn-link text-danger p-0 ms-2 delete-option-btn" data-option-id="{{ $option->id }}" title="Deletar opção">
                        <i class="mdi mdi-delete" style="font-size: 0.875rem;"></i>
                    </button>
                </div>
            @endforeach
            <button class="btn btn-sm btn-success mt-2 add-option-to-question-btn" data-question-id="{{ $question->id }}">
                <i class="mdi mdi-plus me-1"></i>
                Adicionar Opção
            </button>
        </div>
    @elseif(in_array($question->type, ['single_choice', 'multi_choice']))
        <div class="options-list">
            <p class="text-muted small mb-2">Nenhuma opção adicionada ainda.</p>
            <button class="btn btn-sm btn-success add-option-to-question-btn" data-question-id="{{ $question->id }}">
                <i class="mdi mdi-plus me-1"></i>
                Adicionar Opção
            </button>
        </div>
    @endif
</div>

