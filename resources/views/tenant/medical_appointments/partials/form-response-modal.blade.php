@if($formResponse)
    <div class="form-response-content">
        {{-- Informações Gerais --}}
        <div class="mb-4">
            <h5 class="text-primary mb-3">
                <i class="mdi mdi-information-outline me-2"></i>
                Informações Gerais
            </h5>
            <div class="row mb-3">
                <div class="col-md-6 mb-2">
                    <div class="border rounded p-2">
                        <label class="text-muted small mb-1 d-block">
                            <i class="mdi mdi-file-document-edit me-1"></i> Formulário
                        </label>
                        <p class="mb-0 fw-semibold">{{ $formResponse->form->name ?? 'N/A' }}</p>
                    </div>
                </div>
                <div class="col-md-6 mb-2">
                    <div class="border rounded p-2">
                        <label class="text-muted small mb-1 d-block">
                            <i class="mdi mdi-account-heart me-1"></i> Paciente
                        </label>
                        <p class="mb-0 fw-semibold">{{ $formResponse->patient->full_name ?? 'N/A' }}</p>
                    </div>
                </div>
                <div class="col-md-6 mb-2">
                    <div class="border rounded p-2">
                        <label class="text-muted small mb-1 d-block">
                            <i class="mdi mdi-calendar-check me-1"></i> Data de Envio
                        </label>
                        <p class="mb-0 fw-semibold">
                            {{ $formResponse->submitted_at ? $formResponse->submitted_at->format('d/m/Y H:i') : 'N/A' }}
                        </p>
                    </div>
                </div>
                <div class="col-md-6 mb-2">
                    <div class="border rounded p-2">
                        <label class="text-muted small mb-1 d-block">
                            <i class="mdi mdi-check-circle me-1"></i> Status
                        </label>
                        <p class="mb-0">
                            <span class="badge bg-success">Enviado</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <hr>

        {{-- Respostas --}}
        <h5 class="text-primary mb-3">
            <i class="mdi mdi-text-box me-2"></i>
            Respostas
        </h5>
        
        @if($formResponse->answers && $formResponse->answers->count() > 0)
            @if($formResponse->form->sections && $formResponse->form->sections->count() > 0)
                @foreach($formResponse->form->sections->sortBy('position') as $section)
                    <div class="border rounded p-3 mb-3">
                        <h6 class="text-primary mb-3">
                            <i class="mdi mdi-folder-outline me-1"></i>
                            {{ $section->title ?? 'Seção sem título' }}
                        </h6>
                        
                        @foreach($section->questions->sortBy('position') as $question)
                            @php
                                $answer = $formResponse->answers->firstWhere('question_id', $question->id);
                            @endphp
                            <div class="mb-3 pb-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                                <label class="text-muted small d-block mb-1">
                                    <strong>{{ $question->label }}</strong>
                                </label>
                                @if($answer)
                                    <p class="mb-0">{{ \App\Support\FormAnswerFormatter::format($question->type, $answer->value) }}</p>
                                @else
                                    <p class="mb-0 text-muted">
                                        <i class="mdi mdi-minus-circle me-1"></i>
                                        Não respondido
                                    </p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endforeach
            @else
                <div class="border rounded p-3">
                    @foreach($formResponse->form->questions->sortBy('position') as $question)
                        @php
                            $answer = $formResponse->answers->firstWhere('question_id', $question->id);
                        @endphp
                        <div class="mb-3 pb-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                            <label class="text-muted small d-block mb-1">
                                <strong>{{ $question->label }}</strong>
                            </label>
                            @if($answer)
                                <p class="mb-0">{{ \App\Support\FormAnswerFormatter::format($question->type, $answer->value) }}</p>
                            @else
                                <p class="mb-0 text-muted">
                                    <i class="mdi mdi-minus-circle me-1"></i>
                                    Não respondido
                                </p>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        @else
            <div class="alert alert-warning">
                <i class="mdi mdi-alert-outline me-2"></i>
                Nenhuma resposta encontrada para este formulário.
            </div>
        @endif
    </div>
@else
    <div class="alert alert-danger">
        <i class="mdi mdi-alert-circle me-2"></i>
        Resposta do formulário não encontrada.
    </div>
@endif
