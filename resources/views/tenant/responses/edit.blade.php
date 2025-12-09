@extends('layouts.connect_plus.app')

@section('title', 'Editar Resposta')

@section('content')

    <div class="page-header">
        <h3 class="page-title"> Editar Resposta </h3>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ workspace_route('tenant.dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('tenant.responses.index') }}">Respostas</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Editar</li>
            </ol>
        </nav>
    </div>

    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Editar Resposta</h4>
                    <p class="card-description"> Atualize as informações abaixo </p>

                    <form class="forms-sample" action="{{ route('tenant.responses.update', $response->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="form-group mb-3">
                            <label>Paciente</label>
                            <input type="text" class="form-control" value="{{ $response->patient->full_name ?? 'N/A' }}" disabled>
                        </div>

                        <div class="form-group mb-3">
                            <label>Formulário</label>
                            <input type="text" class="form-control" value="{{ $response->form->name ?? 'N/A' }}" disabled>
                        </div>

                        <div class="form-group mb-3">
                            <label for="status">Status <span class="text-danger">*</span></label>
                            <select name="status" id="status" class="form-control @error('status') is-invalid @enderror" required>
                                <option value="pending" {{ old('status', $response->status) == 'pending' ? 'selected' : '' }}>Pendente</option>
                                <option value="submitted" {{ old('status', $response->status) == 'submitted' ? 'selected' : '' }}>Enviado</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr class="my-4">

                        <h5 class="mb-3">Respostas</h5>

                        @if($response->form->sections && $response->form->sections->count() > 0)
                            @foreach($response->form->sections->sortBy('position') as $section)
                                <h6 class="mt-4 mb-3">{{ $section->title ?? 'Seção sem título' }}</h6>
                                
                                @foreach($section->questions->sortBy('position') as $question)
                                    @php
                                        $answer = $response->answers->firstWhere('question_id', $question->id);
                                        $currentValue = $answer ? $answer->value : old("answers.{$question->id}");
                                    @endphp

                                    <div class="form-group mb-3">
                                        <label>{{ $question->label }} @if($question->required) <span class="text-danger">*</span> @endif</label>
                                        @if($question->help_text)
                                            <small class="text-muted d-block mb-2">{{ $question->help_text }}</small>
                                        @endif

                                        @if($question->type == 'text')
                                            <input type="text" class="form-control" name="answers[{{ $question->id }}]" 
                                                value="{{ $currentValue }}" 
                                                @if($question->required) required @endif>
                                        @elseif($question->type == 'number')
                                            <input type="number" step="any" class="form-control" name="answers[{{ $question->id }}]" 
                                                value="{{ $currentValue }}" 
                                                @if($question->required) required @endif>
                                        @elseif($question->type == 'date')
                                            <input type="date" class="form-control" name="answers[{{ $question->id }}]" 
                                                value="{{ $answer && $answer->value_date ? $answer->value_date->format('Y-m-d') : $currentValue }}" 
                                                @if($question->required) required @endif>
                                        @elseif($question->type == 'boolean')
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="answers[{{ $question->id }}]" 
                                                    value="1" id="question_{{ $question->id }}_yes" 
                                                    {{ ($answer && $answer->value_boolean === true) || $currentValue == '1' ? 'checked' : '' }}
                                                    @if($question->required) required @endif>
                                                <label class="form-check-label" for="question_{{ $question->id }}_yes">Sim</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="answers[{{ $question->id }}]" 
                                                    value="0" id="question_{{ $question->id }}_no" 
                                                    {{ ($answer && $answer->value_boolean === false) || $currentValue == '0' ? 'checked' : '' }}
                                                    @if($question->required) required @endif>
                                                <label class="form-check-label" for="question_{{ $question->id }}_no">Não</label>
                                            </div>
                                        @elseif($question->type == 'single_choice')
                                            @foreach($question->options->sortBy('position') as $option)
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="answers[{{ $question->id }}]" 
                                                        value="{{ $option->value }}" id="option_{{ $option->id }}" 
                                                        {{ ($answer && $answer->value_text == $option->value) || $currentValue == $option->value ? 'checked' : '' }}
                                                        @if($question->required) required @endif>
                                                    <label class="form-check-label" for="option_{{ $option->id }}">{{ $option->label }}</label>
                                                </div>
                                            @endforeach
                                        @elseif($question->type == 'multi_choice')
                                            @php
                                                $selectedValues = [];
                                                if ($answer && $answer->value_text) {
                                                    $selectedValues = is_array(json_decode($answer->value_text, true)) 
                                                        ? json_decode($answer->value_text, true) 
                                                        : explode(',', $answer->value_text);
                                                }
                                                $selectedValues = old("answers.{$question->id}", $selectedValues);
                                            @endphp
                                            @foreach($question->options->sortBy('position') as $option)
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="answers[{{ $question->id }}][]" 
                                                        value="{{ $option->value }}" id="option_{{ $option->id }}"
                                                        {{ in_array($option->value, (array)$selectedValues) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="option_{{ $option->id }}">{{ $option->label }}</label>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                @endforeach
                            @endforeach
                        @else
                            {{-- Se não houver seções, mostra todas as perguntas diretamente --}}
                            @foreach($response->form->questions->sortBy('position') as $question)
                                @php
                                    $answer = $response->answers->firstWhere('question_id', $question->id);
                                    $currentValue = $answer ? $answer->value : old("answers.{$question->id}");
                                @endphp

                                <div class="form-group mb-3">
                                    <label>{{ $question->label }} @if($question->required) <span class="text-danger">*</span> @endif</label>
                                    @if($question->help_text)
                                        <small class="text-muted d-block mb-2">{{ $question->help_text }}</small>
                                    @endif

                                    @if($question->type == 'text')
                                        <input type="text" class="form-control" name="answers[{{ $question->id }}]" 
                                            value="{{ $currentValue }}" 
                                            @if($question->required) required @endif>
                                    @elseif($question->type == 'number')
                                        <input type="number" step="any" class="form-control" name="answers[{{ $question->id }}]" 
                                            value="{{ $currentValue }}" 
                                            @if($question->required) required @endif>
                                    @elseif($question->type == 'date')
                                        <input type="date" class="form-control" name="answers[{{ $question->id }}]" 
                                            value="{{ $answer && $answer->value_date ? $answer->value_date->format('Y-m-d') : $currentValue }}" 
                                            @if($question->required) required @endif>
                                    @elseif($question->type == 'boolean')
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="answers[{{ $question->id }}]" 
                                                value="1" id="question_{{ $question->id }}_yes" 
                                                {{ ($answer && $answer->value_boolean === true) || $currentValue == '1' ? 'checked' : '' }}
                                                @if($question->required) required @endif>
                                            <label class="form-check-label" for="question_{{ $question->id }}_yes">Sim</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="answers[{{ $question->id }}]" 
                                                value="0" id="question_{{ $question->id }}_no" 
                                                {{ ($answer && $answer->value_boolean === false) || $currentValue == '0' ? 'checked' : '' }}
                                                @if($question->required) required @endif>
                                            <label class="form-check-label" for="question_{{ $question->id }}_no">Não</label>
                                        </div>
                                    @elseif($question->type == 'single_choice')
                                        @foreach($question->options->sortBy('position') as $option)
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="answers[{{ $question->id }}]" 
                                                    value="{{ $option->value }}" id="option_{{ $option->id }}" 
                                                    {{ ($answer && $answer->value_text == $option->value) || $currentValue == $option->value ? 'checked' : '' }}
                                                    @if($question->required) required @endif>
                                                <label class="form-check-label" for="option_{{ $option->id }}">{{ $option->label }}</label>
                                            </div>
                                        @endforeach
                                    @elseif($question->type == 'multi_choice')
                                        @php
                                            $selectedValues = [];
                                            if ($answer && $answer->value_text) {
                                                $selectedValues = is_array(json_decode($answer->value_text, true)) 
                                                    ? json_decode($answer->value_text, true) 
                                                    : explode(',', $answer->value_text);
                                            }
                                            $selectedValues = old("answers.{$question->id}", $selectedValues);
                                        @endphp
                                        @foreach($question->options->sortBy('position') as $option)
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="answers[{{ $question->id }}][]" 
                                                    value="{{ $option->value }}" id="option_{{ $option->id }}"
                                                    {{ in_array($option->value, (array)$selectedValues) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="option_{{ $option->id }}">{{ $option->label }}</label>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            @endforeach
                        @endif

                        <button type="submit" class="btn btn-primary me-2">Salvar</button>
                        <a href="{{ route('tenant.responses.show', $response->id) }}" class="btn btn-light">Cancelar</a>
                    </form>

                </div>
            </div>
        </div>
    </div>

@endsection
