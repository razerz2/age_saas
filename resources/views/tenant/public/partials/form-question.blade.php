<div class="form-group mb-3">
    <label>{{ $question->label }} @if($question->required) <span class="text-danger">*</span> @endif</label>
    @if($question->help_text)
        <small class="text-muted d-block mb-2">{{ $question->help_text }}</small>
    @endif

    @php
        $value = $existingValue ?? old("answers.{$question->id}");
        $isReadonly = isset($readonly) && $readonly;
    @endphp
    
    @if($isReadonly)
        {{-- Modo Visualização: Mostrar valores como texto --}}
        <div class="form-control-plaintext bg-light p-3 rounded border">
            @if($question->type == 'text' || $question->type == 'number')
                {{ $value ?? '—' }}
            @elseif($question->type == 'date')
                {{ $value ? \Carbon\Carbon::parse($value)->format('d/m/Y') : '—' }}
            @elseif($question->type == 'boolean')
                {{ $value == '1' || $value === 1 || $value === true ? 'Sim' : ($value == '0' || $value === 0 || $value === false ? 'Não' : '—') }}
            @elseif($question->type == 'single_choice')
                @php
                    $selectedOption = $question->options->firstWhere('value', $value);
                @endphp
                {{ $selectedOption ? $selectedOption->label : '—' }}
            @elseif($question->type == 'multi_choice')
                @php
                    $selectedValues = is_array($value) ? $value : (is_string($value) ? json_decode($value, true) : []);
                    if (!is_array($selectedValues)) {
                        $selectedValues = [];
                    }
                    $selectedLabels = $question->options->whereIn('value', $selectedValues)->pluck('label')->toArray();
                @endphp
                {{ !empty($selectedLabels) ? implode(', ', $selectedLabels) : '—' }}
            @else
                {{ $value ?? '—' }}
            @endif
        </div>
        {{-- Campos hidden para manter os valores no formulário --}}
        @if($question->type == 'text' || $question->type == 'number' || $question->type == 'date')
            <input type="hidden" name="answers[{{ $question->id }}]" value="{{ $value }}">
        @elseif($question->type == 'boolean')
            <input type="hidden" name="answers[{{ $question->id }}]" value="{{ $value == '1' || $value === 1 || $value === true ? '1' : '0' }}">
        @elseif($question->type == 'single_choice')
            <input type="hidden" name="answers[{{ $question->id }}]" value="{{ $value }}">
        @elseif($question->type == 'multi_choice')
            @php
                $selectedValues = is_array($value) ? $value : (is_string($value) ? json_decode($value, true) : []);
                if (!is_array($selectedValues)) {
                    $selectedValues = [];
                }
            @endphp
            @foreach($selectedValues as $selectedValue)
                <input type="hidden" name="answers[{{ $question->id }}][]" value="{{ $selectedValue }}">
            @endforeach
        @endif
    @else
        {{-- Modo Edição: Campos editáveis --}}
        @if($question->type == 'text')
            <input type="text" class="form-control" name="answers[{{ $question->id }}]" 
                value="{{ $value }}" 
                @if($question->required) required @endif>
        @elseif($question->type == 'number')
            <input type="number" step="any" class="form-control" name="answers[{{ $question->id }}]" 
                value="{{ $value }}" 
                @if($question->required) required @endif>
        @elseif($question->type == 'date')
            <input type="date" class="form-control" name="answers[{{ $question->id }}]" 
                value="{{ $value }}" 
                @if($question->required) required @endif>
        @elseif($question->type == 'boolean')
            <div class="form-check">
                <input class="form-check-input" type="radio" name="answers[{{ $question->id }}]" 
                    value="1" id="question_{{ $question->id }}_yes" 
                    {{ $value == '1' || $value === 1 || $value === true ? 'checked' : '' }}
                    @if($question->required) required @endif>
                <label class="form-check-label" for="question_{{ $question->id }}_yes">Sim</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="answers[{{ $question->id }}]" 
                    value="0" id="question_{{ $question->id }}_no" 
                    {{ $value == '0' || $value === 0 || $value === false ? 'checked' : '' }}
                    @if($question->required) required @endif>
                <label class="form-check-label" for="question_{{ $question->id }}_no">Não</label>
            </div>
        @elseif($question->type == 'single_choice')
            @foreach($question->options->sortBy('position') as $option)
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="answers[{{ $question->id }}]" 
                        value="{{ $option->value }}" id="option_{{ $option->id }}" 
                        {{ $value == $option->value ? 'checked' : '' }}
                        @if($question->required) required @endif>
                    <label class="form-check-label" for="option_{{ $option->id }}">{{ $option->label }}</label>
                </div>
            @endforeach
        @elseif($question->type == 'multi_choice')
            @php
                $selectedValues = is_array($value) ? $value : (is_string($value) ? json_decode($value, true) : []);
                if (!is_array($selectedValues)) {
                    $selectedValues = [];
                }
            @endphp
            @foreach($question->options->sortBy('position') as $option)
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="answers[{{ $question->id }}][]" 
                        value="{{ $option->value }}" id="option_{{ $option->id }}"
                        {{ in_array($option->value, $selectedValues) ? 'checked' : '' }}>
                    <label class="form-check-label" for="option_{{ $option->id }}">{{ $option->label }}</label>
                </div>
            @endforeach
        @endif
    @endif
</div>

