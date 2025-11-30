<div class="form-group mb-3">
    <label>{{ $question->label }} @if($question->required) <span class="text-danger">*</span> @endif</label>
    @if($question->help_text)
        <small class="text-muted d-block mb-2">{{ $question->help_text }}</small>
    @endif

    @if($question->type == 'text')
        <input type="text" class="form-control" name="answers[{{ $question->id }}]" 
            value="{{ old("answers.{$question->id}") }}" 
            @if($question->required) required @endif>
    @elseif($question->type == 'number')
        <input type="number" step="any" class="form-control" name="answers[{{ $question->id }}]" 
            value="{{ old("answers.{$question->id}") }}" 
            @if($question->required) required @endif>
    @elseif($question->type == 'date')
        <input type="date" class="form-control" name="answers[{{ $question->id }}]" 
            value="{{ old("answers.{$question->id}") }}" 
            @if($question->required) required @endif>
    @elseif($question->type == 'boolean')
        <div class="form-check">
            <input class="form-check-input" type="radio" name="answers[{{ $question->id }}]" 
                value="1" id="question_{{ $question->id }}_yes" 
                {{ old("answers.{$question->id}") == '1' ? 'checked' : '' }}
                @if($question->required) required @endif>
            <label class="form-check-label" for="question_{{ $question->id }}_yes">Sim</label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="radio" name="answers[{{ $question->id }}]" 
                value="0" id="question_{{ $question->id }}_no" 
                {{ old("answers.{$question->id}") == '0' ? 'checked' : '' }}
                @if($question->required) required @endif>
            <label class="form-check-label" for="question_{{ $question->id }}_no">Não</label>
        </div>
    @elseif($question->type == 'single_choice')
        @foreach($question->options->sortBy('position') as $option)
            <div class="form-check">
                <input class="form-check-input" type="radio" name="answers[{{ $question->id }}]" 
                    value="{{ $option->value }}" id="option_{{ $option->id }}" 
                    {{ old("answers.{$question->id}") == $option->value ? 'checked' : '' }}
                    @if($question->required) required @endif>
                <label class="form-check-label" for="option_{{ $option->id }}">{{ $option->label }}</label>
            </div>
        @endforeach
    @elseif($question->type == 'multi_choice')
        @foreach($question->options->sortBy('position') as $option)
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="answers[{{ $question->id }}][]" 
                    value="{{ $option->value }}" id="option_{{ $option->id }}"
                    {{ in_array($option->value, old("answers.{$question->id}", [])) ? 'checked' : '' }}>
                <label class="form-check-label" for="option_{{ $option->id }}">{{ $option->label }}</label>
            </div>
        @endforeach
    @endif
</div>

