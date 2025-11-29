@extends('layouts.connect_plus.app')

@section('title', 'Preencher Formulário')

@section('content')

    <div class="page-header">
        <h3 class="page-title"> Preencher Formulário </h3>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('tenant.dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('tenant.forms.index') }}">Formulários</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('tenant.responses.index') }}">Respostas</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Preencher</li>
            </ol>
        </nav>
    </div>

    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Formulário: {{ $form->name }}</h4>
                    <p class="card-description"> {{ $form->description ?? '' }} </p>

                    <form class="forms-sample" action="{{ route('tenant.responses.store', $form->id) }}" method="POST">
                        @csrf

                        <input type="hidden" name="form_id" value="{{ $form->id }}">

                        <div class="form-group mb-3">
                            <label for="patient_id">Paciente <span class="text-danger">*</span></label>
                            <select name="patient_id" id="patient_id" class="form-control @error('patient_id') is-invalid @enderror" required>
                                <option value="">Selecione um paciente</option>
                                @foreach($patients as $patient)
                                    <option value="{{ $patient->id }}" {{ old('patient_id') == $patient->id ? 'selected' : '' }}>
                                        {{ $patient->full_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('patient_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="appointment_id">Agendamento (opcional)</label>
                            <input type="text" name="appointment_id" id="appointment_id" class="form-control" 
                                value="{{ old('appointment_id') }}" placeholder="ID do agendamento (opcional)">
                        </div>

                        <div class="form-group mb-3">
                            <label for="status">Status</label>
                            <select name="status" id="status" class="form-control">
                                <option value="pending" {{ old('status', 'submitted') == 'pending' ? 'selected' : '' }}>Pendente</option>
                                <option value="submitted" {{ old('status', 'submitted') == 'submitted' ? 'selected' : '' }}>Enviado</option>
                            </select>
                        </div>

                        <hr class="my-4">

                        @if($form->sections && $form->sections->count() > 0)
                            @foreach($form->sections->sortBy('position') as $section)
                                <h5 class="mt-4 mb-3">{{ $section->title ?? 'Seção sem título' }}</h5>
                                
                                @foreach($section->questions->sortBy('position') as $question)
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
                                @endforeach
                            @endforeach
                        @else
                            {{-- Se não houver seções, mostra todas as perguntas diretamente --}}
                            @foreach($form->questions->sortBy('position') as $question)
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
                            @endforeach
                        @endif

                        <button type="submit" class="btn btn-primary me-2">Enviar</button>
                        <a href="{{ route('tenant.responses.index') }}" class="btn btn-light">Cancelar</a>
                    </form>

                </div>
            </div>
        </div>
    </div>

@endsection

