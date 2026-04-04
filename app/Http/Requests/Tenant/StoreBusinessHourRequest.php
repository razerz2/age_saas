<?php

namespace App\Http\Requests\Tenant;

use App\Models\Tenant\BusinessHour;
use App\Models\Tenant\Doctor;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Validator;

class StoreBusinessHourRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'weekdays'   => ['required', 'array', 'min:1'],
            'weekdays.*' => ['required', 'integer', 'min:0', 'max:6', 'distinct'],
            'doctor_id' => ['nullable', 'uuid', 'exists:tenant.doctors,id'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time'   => ['required', 'date_format:H:i', 'after:start_time'],
            'break_start_time' => ['nullable', 'date_format:H:i', 'required_with:break_end_time'],
            'break_end_time'   => ['nullable', 'date_format:H:i', 'required_with:break_start_time', 'after:break_start_time'],
        ];
    }

    protected function prepareForValidation()
    {
        if ($this->has('weekdays') && is_array($this->weekdays)) {
            $this->merge([
                'weekdays' => array_map('intval', $this->weekdays),
            ]);
        }
    }

    public function messages()
    {
        return [
            'weekdays.required' => 'Selecione pelo menos um dia da semana.',
            'weekdays.array' => 'Os dias da semana devem ser enviados como array.',
            'weekdays.min' => 'Selecione pelo menos um dia da semana.',
            'weekdays.*.required' => 'Cada dia da semana é obrigatório.',
            'weekdays.*.integer' => 'Cada dia da semana deve ser um número inteiro.',
            'weekdays.*.min' => 'Cada dia da semana deve ser no mínimo 0 (domingo).',
            'weekdays.*.max' => 'Cada dia da semana deve ser no máximo 6 (sábado).',
            'weekdays.*.distinct' => 'Não é possível selecionar o mesmo dia mais de uma vez.',
            'doctor_id.exists' => 'Médico inválido.',

            'start_time.required' => 'A hora de início é obrigatória.',
            'start_time.date_format' => 'A hora de início deve estar no formato HH:mm.',

            'end_time.required' => 'A hora de fim é obrigatória.',
            'end_time.date_format' => 'A hora de fim deve estar no formato HH:mm.',
            'end_time.after' => 'O horário de término deve ser maior que o horário de início.',

            'break_start_time.date_format' => 'A hora de início do intervalo deve estar no formato HH:mm.',
            'break_start_time.required_with' => 'Informe os dois campos do intervalo ou deixe ambos em branco.',
            'break_end_time.date_format' => 'A hora de fim do intervalo deve estar no formato HH:mm.',
            'break_end_time.required_with' => 'Informe os dois campos do intervalo ou deixe ambos em branco.',
            'break_end_time.after' => 'O intervalo deve terminar depois de começar.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function (Validator $validator): void {
            $data = $this->validatedSafeData();

            $start = (string) ($data['start_time'] ?? '');
            $end = (string) ($data['end_time'] ?? '');
            $breakStart = (string) ($data['break_start_time'] ?? '');
            $breakEnd = (string) ($data['break_end_time'] ?? '');

            if ($start !== '' && $end !== '' && $start >= $end) {
                $validator->errors()->add('end_time', 'O horário de término deve ser maior que o horário de início.');
            }

            if (($breakStart !== '' && $breakEnd === '') || ($breakStart === '' && $breakEnd !== '')) {
                $validator->errors()->add('break_end_time', 'Informe os dois campos do intervalo ou deixe ambos em branco.');
            }

            if ($breakStart !== '' && $breakEnd !== '') {
                if ($breakStart >= $breakEnd) {
                    $validator->errors()->add('break_end_time', 'O intervalo deve terminar depois de começar.');
                }

                if ($start !== '' && $end !== '' && ($breakStart <= $start || $breakEnd >= $end)) {
                    $validator->errors()->add('break_start_time', 'O intervalo deve estar dentro do horário de atendimento.');
                }
            }

            $doctorId = $this->resolveDoctorId();
            $weekdays = $data['weekdays'] ?? [];
            if (!$doctorId || !is_array($weekdays) || empty($weekdays)) {
                return;
            }

            $alreadyRegistered = BusinessHour::query()
                ->where('doctor_id', $doctorId)
                ->whereIn('weekday', $weekdays)
                ->exists();

            if ($alreadyRegistered) {
                $validator->errors()->add('weekdays', 'Já existe horário cadastrado para este dia da semana.');
            }
        });
    }

    private function resolveDoctorId(): ?string
    {
        $doctorId = (string) $this->input('doctor_id', '');
        if ($doctorId !== '') {
            return $doctorId;
        }

        $user = Auth::guard('tenant')->user();
        if (!$user) {
            return null;
        }

        if ($user->role === 'doctor' && $user->doctor) {
            return (string) $user->doctor->id;
        }

        if ($user->role === 'user') {
            $allowedDoctors = $user->allowedDoctors()->limit(2)->get();
            if ($allowedDoctors->count() === 1) {
                $allowedDoctor = $allowedDoctors->first();
                if ($allowedDoctor instanceof Doctor) {
                    return (string) $allowedDoctor->id;
                }
            }
        }

        return null;
    }

    private function validatedSafeData(): array
    {
        return [
            'weekdays' => is_array($this->input('weekdays')) ? array_map('intval', $this->input('weekdays')) : [],
            'start_time' => $this->input('start_time'),
            'end_time' => $this->input('end_time'),
            'break_start_time' => $this->input('break_start_time'),
            'break_end_time' => $this->input('break_end_time'),
        ];
    }
}