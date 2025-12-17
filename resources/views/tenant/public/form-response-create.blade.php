<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Responder Formulário — {{ $tenant->trade_name ?? $tenant->legal_name ?? 'Sistema' }}</title>
    <link rel="stylesheet" href="{{ asset('connect_plus/assets/vendors/mdi/css/materialdesignicons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('connect_plus/assets/vendors/css/vendor.bundle.base.css') }}">
    <link rel="stylesheet" href="{{ asset('connect_plus/assets/css/style.css') }}">
    <link rel="shortcut icon" href="{{ asset('connect_plus/assets/images/favicon.png') }}">
    <style>
        .page-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 2rem 0;
        }
        .form-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            padding: 2rem;
        }
    </style>
</head>
<body>
    <div class="page-wrapper">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="form-card">
                        <h2 class="mb-4">
                            <i class="mdi mdi-file-document-edit text-primary me-2"></i>
                            {{ $form->name }}
                        </h2>
                        @if($form->description)
                            <p class="text-muted mb-4">{{ $form->description }}</p>
                        @endif

                        @if($existingResponse && !$editMode)
                            <div class="alert alert-info mb-4">
                                <i class="mdi mdi-information-outline me-2"></i>
                                <strong>Formulário já respondido.</strong> Você está visualizando suas respostas. Clique em "Editar" para fazer alterações.
                            </div>
                        @elseif($existingResponse && $editMode)
                            <div class="alert alert-warning mb-4">
                                <i class="mdi mdi-pencil me-2"></i>
                                <strong>Modo de Edição:</strong> Você pode atualizar suas respostas abaixo.
                            </div>
                        @endif

                        @if(session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif

                        @if($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ tenant_route($tenant, 'public.form.response.store', ['form' => $form->id]) }}" method="POST" id="formResponseForm">
                            @csrf
                            <input type="hidden" name="form_id" value="{{ $form->id }}">
                            
                            @if($appointment)
                                <input type="hidden" name="appointment_id" value="{{ $appointment->id }}">
                                <input type="hidden" name="patient_id" value="{{ $appointment->patient_id }}">
                                <div class="alert alert-info mb-4">
                                    <i class="mdi mdi-calendar-clock me-2"></i>
                                    <strong>Agendamento:</strong> {{ $appointment->starts_at->format('d/m/Y \à\s H:i') }}
                                    <br>
                                    <strong>Paciente:</strong> {{ $appointment->patient->full_name ?? 'N/A' }}
                                </div>
                            @else
                                <div class="form-group mb-3">
                                    <label for="patient_id">Paciente <span class="text-danger">*</span></label>
                                    <select name="patient_id" id="patient_id" class="form-control @error('patient_id') is-invalid @enderror" required>
                                        <option value="">Selecione um paciente</option>
                                        @foreach(\App\Models\Tenant\Patient::orderBy('full_name')->get() as $patient)
                                            <option value="{{ $patient->id }}" {{ old('patient_id') == $patient->id ? 'selected' : '' }}>
                                                {{ $patient->full_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('patient_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            @endif

                            <input type="hidden" name="status" value="submitted">

                            <hr class="my-4">

                            @php
                                // Preparar valores existentes das respostas
                                $existingAnswers = [];
                                if ($existingResponse && $existingResponse->answers) {
                                    foreach ($existingResponse->answers as $answer) {
                                        $value = null;
                                        if ($answer->value_text !== null) {
                                            // Verificar se é JSON (multi_choice)
                                            $decoded = json_decode($answer->value_text, true);
                                            $value = json_last_error() === JSON_ERROR_NONE ? $decoded : $answer->value_text;
                                        } elseif ($answer->value_number !== null) {
                                            $value = $answer->value_number;
                                        } elseif ($answer->value_date !== null) {
                                            $value = $answer->value_date;
                                        } elseif ($answer->value_boolean !== null) {
                                            $value = $answer->value_boolean ? '1' : '0';
                                        }
                                        $existingAnswers[$answer->question_id] = $value;
                                    }
                                }
                            @endphp
                            
                            @if($form->sections && $form->sections->count() > 0)
                                @foreach($form->sections->sortBy('position') as $section)
                                    <h5 class="mt-4 mb-3">{{ $section->title ?? 'Seção sem título' }}</h5>
                                    
                                    @foreach($section->questions->sortBy('position') as $question)
                                        @include('tenant.public.partials.form-question', [
                                            'question' => $question,
                                            'existingValue' => $existingAnswers[$question->id] ?? old("answers.{$question->id}"),
                                            'readonly' => $existingResponse && !$editMode
                                        ])
                                    @endforeach
                                @endforeach
                            @else
                                @foreach($form->questions->sortBy('position') as $question)
                                    @include('tenant.public.partials.form-question', [
                                        'question' => $question,
                                        'existingValue' => $existingAnswers[$question->id] ?? old("answers.{$question->id}"),
                                        'readonly' => $existingResponse && !$editMode
                                    ])
                                @endforeach
                            @endif

                            <div class="mt-4 d-flex justify-content-center gap-3">
                                @if($existingResponse && !$editMode)
                                    {{-- Modo Visualização: Mostrar botão Editar --}}
                                    @php
                                        $editUrl = tenant_route($tenant, 'public.form.response.create', ['form' => $form->id]);
                                        if ($appointment && $appointment->id) {
                                            $editUrl .= '?appointment=' . $appointment->id . '&edit=1';
                                        } else {
                                            $editUrl .= '?edit=1';
                                        }
                                    @endphp
                                    <a href="{{ $editUrl }}" 
                                       class="btn btn-primary btn-lg">
                                        <i class="mdi mdi-pencil me-2"></i>
                                        Editar Formulário
                                    </a>
                                @else
                                    {{-- Modo Edição ou Novo: Mostrar botão de submit --}}
                                    <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                                        <i class="mdi {{ $existingResponse ? 'mdi-content-save' : 'mdi-send' }} me-2"></i>
                                        {{ $existingResponse ? 'Atualizar Formulário' : 'Enviar Formulário' }}
                                    </button>
                                @endif
                                
                                @if($appointment)
                                    <a href="{{ tenant_route($tenant, 'public.appointment.show', ['appointment_id' => $appointment->id]) }}" class="btn btn-light btn-lg">
                                        <i class="mdi mdi-arrow-left me-2"></i>
                                        Voltar
                                    </a>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    @if($existingResponse && !$editMode)
    <script>
        // Prevenir submissão do formulário em modo de visualização
        document.getElementById('formResponseForm').addEventListener('submit', function(e) {
            e.preventDefault();
            return false;
        });
    </script>
    @endif
</body>
</html>

