<?php

namespace App\Http\Requests\Tenant;

use App\Models\Tenant\BusinessHour;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateBusinessHourRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'doctor_id' => ['nullable', 'uuid', 'exists:tenant.doctors,id'],
            'weekday'    => ['required', 'integer', 'min:0', 'max:6'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time'   => ['required', 'date_format:H:i', 'after:start_time'],
            'break_start_time' => ['nullable', 'date_format:H:i', 'required_with:break_end_time'],
            'break_end_time'   => ['nullable', 'date_format:H:i', 'required_with:break_start_time', 'after:break_start_time'],
        ];
    }

    public function messages()
    {
        return [
            'doctor_id.exists' => 'Médico inválido.',
            'weekday.required' => 'O dia da semana é obrigatório.',
            'weekday.integer' => 'O dia da semana deve ser um número inteiro.',
            'weekday.min' => 'O dia da semana deve ser no mínimo 0 (domingo).',
            'weekday.max' => 'O dia da semana deve ser no máximo 6 (sábado).',

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
            $start = (string) $this->input('start_time', '');
            $end = (string) $this->input('end_time', '');
            $breakStart = (string) $this->input('break_start_time', '');
            $breakEnd = (string) $this->input('break_end_time', '');

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

            $currentId = (string) $this->route('id');
            $weekday = $this->input('weekday');
            if ($currentId === '' || $weekday === null || $weekday === '') {
                return;
            }

            $doctorId = $this->resolveDoctorId($currentId);
            if (!$doctorId) {
                return;
            }

            $duplicateDay = BusinessHour::query()
                ->where('doctor_id', $doctorId)
                ->where('weekday', (int) $weekday)
                ->where('id', '!=', $currentId)
                ->exists();

            if ($duplicateDay) {
                $validator->errors()->add('weekday', 'Já existe horário cadastrado para este dia da semana.');
            }
        });
    }

    private function resolveDoctorId(string $currentId): ?string
    {
        $doctorId = (string) $this->input('doctor_id', '');
        if ($doctorId !== '') {
            return $doctorId;
        }

        return BusinessHour::query()
            ->where('id', $currentId)
            ->value('doctor_id');
    }
}