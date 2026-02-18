<div class="mb-5">
    <label class="block text-sm font-medium text-slate-700">
        {{ $question->label }}
        @if($question->required)
            <span class="text-red-500">*</span>
        @endif
    </label>

    @if($question->help_text)
        <p class="mt-1 text-xs text-slate-500">{{ $question->help_text }}</p>
    @endif

    @php
        $value = $existingValue ?? old("answers.{$question->id}");
        $isReadonly = isset($readonly) && $readonly;
    @endphp

    @if($isReadonly)
        {{-- Modo Visualização: Mostrar valores como texto --}}
        <div class="mt-2 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700">
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
            <input
                type="text"
                name="answers[{{ $question->id }}]"
                value="{{ $value }}"
                class="mt-2 w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                @if($question->required) required @endif
            >
        @elseif($question->type == 'number')
            <input
                type="number"
                step="any"
                name="answers[{{ $question->id }}]"
                value="{{ $value }}"
                class="mt-2 w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                @if($question->required) required @endif
            >
        @elseif($question->type == 'date')
            <input
                type="date"
                name="answers[{{ $question->id }}]"
                value="{{ $value }}"
                class="mt-2 w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                @if($question->required) required @endif
            >
        @elseif($question->type == 'boolean')
            <div class="mt-2 space-y-2">
                <label class="flex items-center gap-2 text-sm text-slate-700" for="question_{{ $question->id }}_yes">
                    <input
                        class="h-4 w-4 border-slate-300 text-indigo-600 focus:ring-indigo-500"
                        type="radio"
                        name="answers[{{ $question->id }}]"
                        value="1"
                        id="question_{{ $question->id }}_yes"
                        {{ $value == '1' || $value === 1 || $value === true ? 'checked' : '' }}
                        @if($question->required) required @endif
                    >
                    <span>Sim</span>
                </label>
                <label class="flex items-center gap-2 text-sm text-slate-700" for="question_{{ $question->id }}_no">
                    <input
                        class="h-4 w-4 border-slate-300 text-indigo-600 focus:ring-indigo-500"
                        type="radio"
                        name="answers[{{ $question->id }}]"
                        value="0"
                        id="question_{{ $question->id }}_no"
                        {{ $value == '0' || $value === 0 || $value === false ? 'checked' : '' }}
                        @if($question->required) required @endif
                    >
                    <span>Não</span>
                </label>
            </div>
        @elseif($question->type == 'single_choice')
            <div class="mt-2 space-y-2">
                @foreach($question->options->sortBy('position') as $option)
                    <label class="flex items-center gap-2 text-sm text-slate-700" for="option_{{ $option->id }}">
                        <input
                            class="h-4 w-4 border-slate-300 text-indigo-600 focus:ring-indigo-500"
                            type="radio"
                            name="answers[{{ $question->id }}]"
                            value="{{ $option->value }}"
                            id="option_{{ $option->id }}"
                            {{ $value == $option->value ? 'checked' : '' }}
                            @if($question->required) required @endif
                        >
                        <span>{{ $option->label }}</span>
                    </label>
                @endforeach
            </div>
        @elseif($question->type == 'multi_choice')
            @php
                $selectedValues = is_array($value) ? $value : (is_string($value) ? json_decode($value, true) : []);
                if (!is_array($selectedValues)) {
                    $selectedValues = [];
                }
            @endphp
            <div class="mt-2 space-y-2">
                @foreach($question->options->sortBy('position') as $option)
                    <label class="flex items-center gap-2 text-sm text-slate-700" for="option_{{ $option->id }}">
                        <input
                            class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                            type="checkbox"
                            name="answers[{{ $question->id }}][]"
                            value="{{ $option->value }}"
                            id="option_{{ $option->id }}"
                            {{ in_array($option->value, $selectedValues) ? 'checked' : '' }}
                        >
                        <span>{{ $option->label }}</span>
                    </label>
                @endforeach
            </div>
        @endif
    @endif
</div>

