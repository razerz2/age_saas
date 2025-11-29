<div class="preview-question">
    <label class="question-label">
        {{ $question->label }}
        @if($question->required)
            <span class="text-danger">*</span>
        @endif
    </label>
    
    @if($question->help_text)
        <div class="help-text">
            <i class="mdi mdi-information-outline me-1"></i>
            {{ $question->help_text }}
        </div>
    @endif

    @if($question->type == 'text')
        <input type="text" class="form-control" placeholder="Resposta de texto..." disabled>
        
    @elseif($question->type == 'number')
        <input type="number" class="form-control" placeholder="Número..." disabled>
        
    @elseif($question->type == 'date')
        <input type="date" class="form-control" disabled>
        
    @elseif($question->type == 'boolean')
        <div class="boolean-options">
            <div class="form-check">
                <input class="form-check-input" type="radio" name="preview_{{ $question->id }}" id="preview_{{ $question->id }}_yes" disabled>
                <label class="form-check-label" for="preview_{{ $question->id }}_yes">Sim</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="preview_{{ $question->id }}" id="preview_{{ $question->id }}_no" disabled>
                <label class="form-check-label" for="preview_{{ $question->id }}_no">Não</label>
            </div>
        </div>
        
    @elseif($question->type == 'single_choice')
        @if($question->options->isEmpty())
            <p class="text-muted small mb-0">Nenhuma opção configurada</p>
        @else
            <div class="options-container">
                @foreach($question->options->sortBy('position') as $option)
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="preview_{{ $question->id }}" id="preview_option_{{ $option->id }}" disabled>
                        <label class="form-check-label" for="preview_option_{{ $option->id }}">
                            {{ $option->label }}
                        </label>
                    </div>
                @endforeach
            </div>
        @endif
        
    @elseif($question->type == 'multi_choice')
        @if($question->options->isEmpty())
            <p class="text-muted small mb-0">Nenhuma opção configurada</p>
        @else
            <div class="options-container">
                @foreach($question->options->sortBy('position') as $option)
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="preview_option_{{ $option->id }}" disabled>
                        <label class="form-check-label" for="preview_option_{{ $option->id }}">
                            {{ $option->label }}
                        </label>
                    </div>
                @endforeach
            </div>
        @endif
    @endif

    <small class="text-muted d-block">
        <i class="mdi mdi-tag-outline me-1"></i>
        Tipo: 
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
        @if($question->required)
            | <span class="text-danger">Obrigatório</span>
        @endif
    </small>
</div>

