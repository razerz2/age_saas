@php
    $options = $question->options->sortBy('position');

    $rawText = $answer?->value_text;
    $decodedTextArray = null;

    if (is_string($rawText)) {
        $decoded = json_decode($rawText, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            $decodedTextArray = array_map(static fn ($item) => (string) $item, $decoded);
        }
    }

    $booleanValue = null;
    if ($answer !== null) {
        if ($answer->value_boolean !== null) {
            $booleanValue = (bool) $answer->value_boolean;
        } elseif (is_string($rawText) || is_numeric($rawText)) {
            $normalizedBoolean = strtolower(trim((string) $rawText));
            if (in_array($normalizedBoolean, ['1', 'true', 'sim', 'yes'], true)) {
                $booleanValue = true;
            } elseif (in_array($normalizedBoolean, ['0', 'false', 'nao', 'não', 'no'], true)) {
                $booleanValue = false;
            }
        }
    }

    $singleChoiceValue = null;
    if ($answer !== null) {
        if (is_string($rawText) && $decodedTextArray === null) {
            $singleChoiceValue = $rawText;
        } elseif ($answer->value_number !== null) {
            $singleChoiceValue = (string) $answer->value_number;
        }
    }

    $multiChoiceValues = [];
    if (is_array($decodedTextArray)) {
        $multiChoiceValues = $decodedTextArray;
    } elseif (is_string($rawText) && trim($rawText) !== '') {
        $multiChoiceValues = array_map('trim', explode(',', $rawText));
    }

    $textValue = is_string($rawText) && $decodedTextArray === null ? $rawText : '';
    $numberValue = $answer?->value_number !== null ? (string) $answer->value_number : '';
    $dateValue = $answer?->value_date ? $answer->value_date->format('Y-m-d') : '';

    $hasAnswer = $answer !== null && (
        ($answer->value_text !== null && trim((string) $answer->value_text) !== '') ||
        $answer->value_number !== null ||
        $answer->value_date !== null ||
        $answer->value_boolean !== null
    );
@endphp

<div class="rounded-lg border border-gray-200/70 dark:border-gray-700/70 p-4">
    <label class="text-sm font-medium text-gray-900 dark:text-white block mb-1">
        {{ $question->label }}
        @if($question->required)
            <span class="text-red-500">*</span>
        @endif
    </label>

    @if($question->help_text)
        <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">{{ $question->help_text }}</p>
    @endif

    @if($question->type === 'text')
        <input
            type="text"
            disabled
            value="{{ $textValue }}"
            placeholder="{{ $hasAnswer ? '' : 'Não respondido' }}"
            class="w-full rounded-lg border border-gray-300 bg-gray-100 px-3 py-2 text-sm text-gray-700 placeholder-gray-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:placeholder-gray-400"
        />
    @elseif($question->type === 'number')
        <input
            type="text"
            disabled
            value="{{ $numberValue }}"
            placeholder="{{ $hasAnswer ? '' : 'Não respondido' }}"
            class="w-full rounded-lg border border-gray-300 bg-gray-100 px-3 py-2 text-sm text-gray-700 placeholder-gray-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:placeholder-gray-400"
        />
    @elseif($question->type === 'date')
        <input
            type="date"
            disabled
            value="{{ $dateValue }}"
            class="w-full rounded-lg border border-gray-300 bg-gray-100 px-3 py-2 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200"
        />
        @if(!$hasAnswer)
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Não respondido</p>
        @endif
    @elseif($question->type === 'boolean')
        <div class="space-y-2">
            <label class="flex items-center gap-2 rounded-md border px-3 py-2 text-sm {{ $booleanValue === true ? 'border-green-300 bg-green-50 text-green-800 dark:border-green-700 dark:bg-green-900/20 dark:text-green-300' : 'border-gray-300 bg-gray-50 text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300' }}">
                <input type="radio" disabled class="h-4 w-4" @checked($booleanValue === true)>
                Sim
            </label>
            <label class="flex items-center gap-2 rounded-md border px-3 py-2 text-sm {{ $booleanValue === false ? 'border-green-300 bg-green-50 text-green-800 dark:border-green-700 dark:bg-green-900/20 dark:text-green-300' : 'border-gray-300 bg-gray-50 text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300' }}">
                <input type="radio" disabled class="h-4 w-4" @checked($booleanValue === false)>
                Não
            </label>
        </div>
        @if($booleanValue === null)
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Não respondido</p>
        @endif
    @elseif($question->type === 'single_choice')
        @if($options->isEmpty())
            <p class="text-xs text-gray-500 dark:text-gray-400">Nenhuma opção configurada.</p>
        @else
            <div class="space-y-2">
                @foreach($options as $option)
                    @php
                        $checked = $singleChoiceValue !== null && (string) $singleChoiceValue === (string) $option->value;
                    @endphp
                    <label class="flex items-center gap-2 rounded-md border px-3 py-2 text-sm {{ $checked ? 'border-green-300 bg-green-50 text-green-800 dark:border-green-700 dark:bg-green-900/20 dark:text-green-300' : 'border-gray-300 bg-gray-50 text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300' }}">
                        <input type="radio" disabled class="h-4 w-4" @checked($checked)>
                        {{ $option->label }}
                    </label>
                @endforeach
            </div>
            @if(!$hasAnswer)
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Não respondido</p>
            @endif
        @endif
    @elseif($question->type === 'multi_choice')
        @if($options->isEmpty())
            <p class="text-xs text-gray-500 dark:text-gray-400">Nenhuma opção configurada.</p>
        @else
            <div class="space-y-2">
                @foreach($options as $option)
                    @php
                        $checked = in_array((string) $option->value, $multiChoiceValues, true);
                    @endphp
                    <label class="flex items-center gap-2 rounded-md border px-3 py-2 text-sm {{ $checked ? 'border-green-300 bg-green-50 text-green-800 dark:border-green-700 dark:bg-green-900/20 dark:text-green-300' : 'border-gray-300 bg-gray-50 text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300' }}">
                        <input type="checkbox" disabled class="h-4 w-4" @checked($checked)>
                        {{ $option->label }}
                    </label>
                @endforeach
            </div>
            @if(empty($multiChoiceValues))
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Não respondido</p>
            @endif
        @endif
    @else
        <textarea
            rows="3"
            disabled
            class="w-full rounded-lg border border-gray-300 bg-gray-100 px-3 py-2 text-sm text-gray-700 placeholder-gray-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:placeholder-gray-400"
            placeholder="{{ $hasAnswer ? '' : 'Não respondido' }}"
        >{{ $textValue }}</textarea>
    @endif
</div>
