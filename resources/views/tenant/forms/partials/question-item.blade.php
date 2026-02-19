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
        <div class="flex items-center gap-2">
            <x-tailadmin-button type="button" variant="warning" size="xs" class="edit-question-btn px-2 py-1" data-question-id="{{ $question->id }}">
                <x-icon name="pencil" class="" />
            </x-tailadmin-button>
            <x-tailadmin-button type="button" variant="danger" size="xs" class="delete-question-btn px-2 py-1" data-question-id="{{ $question->id }}">
                <x-icon name="delete" class="" />
            </x-tailadmin-button>
        </div>
    </div>
    
    @if($question->help_text)
        <p class="text-muted small mb-2">
            <x-icon name="information-outline" class=" me-1" />
            {{ $question->help_text }}
        </p>
    @endif
    
    @if($question->options->isNotEmpty())
        <div class="options-list">
            <strong class="small">Opções:</strong>
            @foreach($question->options->sortBy('position') as $option)
                <div class="option-item">
                    <x-icon name="circle-small" class=" me-1" />
                    {{ $option->label }} 
                    <span class="text-muted">({{ $option->value }})</span>
                    <x-tailadmin-button type="button" variant="danger" size="xs" class="delete-option-btn px-2 py-1 ms-2" data-option-id="{{ $option->id }}" title="Deletar opção">
                        <x-icon name="delete" class="" />
                    </x-tailadmin-button>
                </div>
            @endforeach
            <x-tailadmin-button type="button" variant="success" size="sm" class="mt-2 add-option-to-question-btn" data-question-id="{{ $question->id }}">
                <x-icon name="plus" class="" />
                Adicionar Opção
            </x-tailadmin-button>
        </div>
    @elseif(in_array($question->type, ['single_choice', 'multi_choice']))
        <div class="options-list">
            <p class="text-muted small mb-2">Nenhuma opção adicionada ainda.</p>
            <x-tailadmin-button type="button" variant="success" size="sm" class="add-option-to-question-btn" data-question-id="{{ $question->id }}">
                <x-icon name="plus" class="" />
                Adicionar Opção
            </x-tailadmin-button>
        </div>
    @endif
</div>

