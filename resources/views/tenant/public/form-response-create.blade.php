@extends('layouts.tailadmin.public')

@section('title', 'Responder Formulário — ' . ($tenant->trade_name ?? $tenant->legal_name ?? 'Sistema'))
@section('page', 'public')

@section('content')
    <div class="min-h-screen bg-slate-50">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 py-8">
            <div class="pt-10 pb-6 sm:pt-12 sm:pb-8 lg:pt-14 text-center">
                <div class="mx-auto mb-4 inline-flex h-10 w-10 items-center justify-center rounded-full bg-indigo-50 text-indigo-700 ring-1 ring-indigo-100">
                    <i class="mdi mdi-file-document-edit text-xl"></i>
                </div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">{{ $form->name }}</h1>
                @if($form->description)
                    <p class="mt-2 text-sm text-slate-600">{{ $form->description }}</p>
                @endif
            </div>

            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                @if(($existingResponse && !$editMode) || ($existingResponse && $editMode) || session('success') || $errors->any())
                    <div class="border-b border-slate-200 px-6 py-4">
                        <div class="space-y-3">
                            @if($existingResponse && !$editMode)
                                <div class="rounded-xl border border-indigo-100 bg-indigo-50 px-4 py-3 text-sm text-indigo-900">
                                    <div class="flex items-start gap-2">
                                        <i class="mdi mdi-information-outline text-base text-indigo-600"></i>
                                        <p><strong>Formulário já respondido.</strong> Você está visualizando suas respostas. Clique em "Editar" para fazer alterações.</p>
                                    </div>
                                </div>
                            @elseif($existingResponse && $editMode)
                                <div class="rounded-xl border border-amber-100 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                                    <div class="flex items-start gap-2">
                                        <i class="mdi mdi-pencil text-base text-amber-600"></i>
                                        <p><strong>Modo de Edição:</strong> Você pode atualizar suas respostas abaixo.</p>
                                    </div>
                                </div>
                            @endif

                            @if(session('success'))
                                <div class="rounded-xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
                                    <div class="flex items-start gap-2">
                                        <i class="mdi mdi-check-circle text-base text-emerald-600"></i>
                                        <p>{{ session('success') }}</p>
                                    </div>
                                </div>
                            @endif

                            @if($errors->any())
                                <div class="rounded-xl border border-red-100 bg-red-50 px-4 py-3 text-sm text-red-900">
                                    <div class="flex items-start gap-2">
                                        <i class="mdi mdi-alert-circle text-base text-red-600"></i>
                                        <div>
                                            <p class="font-semibold">Verifique os campos abaixo:</p>
                                            <ul class="mt-2 list-disc space-y-1 pl-5">
                                                @foreach($errors->all() as $error)
                                                    <li>{{ $error }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <form action="{{ tenant_route($tenant, 'public.form.response.store', ['form' => $form->id]) }}" method="POST" id="formResponseForm" data-readonly-form="{{ ($existingResponse && !$editMode) ? 'true' : 'false' }}">
                    @csrf
                    <input type="hidden" name="form_id" value="{{ $form->id }}">

                    @if($appointment)
                        <input type="hidden" name="appointment_id" value="{{ $appointment->id }}">
                        <input type="hidden" name="patient_id" value="{{ $appointment->patient_id }}">
                    @endif

                    <input type="hidden" name="status" value="submitted">

                    <div class="px-6 py-5">
                        @if($appointment)
                            <div class="rounded-xl border border-indigo-100 bg-indigo-50 px-4 py-3 text-sm text-indigo-900">
                                <div class="flex items-start gap-2">
                                    <i class="mdi mdi-calendar-clock text-base text-indigo-600"></i>
                                    <div>
                                        <p><strong>Agendamento:</strong> {{ $appointment->starts_at->format('d/m/Y \à\s H:i') }}</p>
                                        <p class="mt-1"><strong>Paciente:</strong> {{ $appointment->patient->full_name ?? 'N/A' }}</p>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-slate-700" for="patient_id">
                                    <span class="inline-flex items-center gap-1">
                                        <i class="mdi mdi-account text-slate-500 text-base"></i>
                                        <span>Paciente</span>
                                        <span class="text-red-500">*</span>
                                    </span>
                                </label>
                                <select
                                    name="patient_id"
                                    id="patient_id"
                                    required
                                    class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('patient_id') border-red-300 focus:ring-red-500 @enderror"
                                >
                                    <option value="">Selecione um paciente</option>
                                    @foreach(\App\Models\Tenant\Patient::orderBy('full_name')->get() as $patient)
                                        <option value="{{ $patient->id }}" {{ old('patient_id') == $patient->id ? 'selected' : '' }}>
                                            {{ $patient->full_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('patient_id')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        @endif
                    </div>

                    <div class="border-t border-slate-200 px-6 py-5">
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
                                <div class="{{ $loop->first ? '' : 'mt-8 border-t border-slate-200 pt-8' }}">
                                    <h2 class="text-base font-semibold text-slate-900 text-center sm:text-left">
                                        {{ $section->title ?? 'Seção sem título' }}
                                    </h2>

                                    <div class="mt-5">
                                        @foreach($section->questions->sortBy('position') as $question)
                                            @include('tenant.public.partials.form-question', [
                                                'question' => $question,
                                                'existingValue' => $existingAnswers[$question->id] ?? old("answers.{$question->id}"),
                                                'readonly' => $existingResponse && !$editMode
                                            ])
                                        @endforeach
                                    </div>
                                </div>
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
                    </div>

                    <div class="border-t border-slate-200 px-6 py-5">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-center">
                            @if($existingResponse && !$editMode)
                                @php
                                    $editUrl = tenant_route($tenant, 'public.form.response.create', ['form' => $form->id]);
                                    if ($appointment && $appointment->id) {
                                        $editUrl .= '?appointment=' . $appointment->id . '&edit=1';
                                    } else {
                                        $editUrl .= '?edit=1';
                                    }
                                @endphp
                                <x-tailadmin-button
                                    variant="primary"
                                    size="lg"
                                    href="{{ $editUrl }}"
                                    class="!inline-flex !w-auto min-w-[180px] !items-center !justify-center !gap-2 !rounded-lg !bg-indigo-600 !px-6 !py-2.5 !text-sm !font-semibold !text-white shadow-sm hover:!bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1"
                                >
                                    <i class="mdi mdi-pencil text-white"></i>
                                    Editar Formulário
                                </x-tailadmin-button>
                            @else
                                <x-tailadmin-button
                                    type="submit"
                                    variant="primary"
                                    size="lg"
                                    id="submitBtn"
                                    class="!inline-flex !w-auto min-w-[180px] !items-center !justify-center !gap-2 !rounded-lg !bg-indigo-600 !px-6 !py-2.5 !text-sm !font-semibold !text-white shadow-sm hover:!bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1 disabled:opacity-60 disabled:cursor-not-allowed"
                                >
                                    <i class="mdi {{ $existingResponse ? 'mdi-content-save' : 'mdi-send' }} text-white"></i>
                                    {{ $existingResponse ? 'Atualizar Formulário' : 'Enviar Formulário' }}
                                </x-tailadmin-button>
                            @endif

                            @if($appointment)
                                <x-tailadmin-button
                                    variant="secondary"
                                    size="lg"
                                    href="{{ tenant_route($tenant, 'public.appointment.show', ['appointment_id' => $appointment->id]) }}"
                                    class="!inline-flex !w-auto min-w-[180px] !items-center !justify-center !gap-2 !rounded-lg !border !border-slate-200 !bg-white !px-6 !py-2.5 !text-sm !font-semibold !text-slate-900 shadow-sm hover:!bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1"
                                >
                                    <i class="mdi mdi-arrow-left text-slate-900"></i>
                                    Voltar
                                </x-tailadmin-button>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

