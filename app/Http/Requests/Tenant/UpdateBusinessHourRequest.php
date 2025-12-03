<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBusinessHourRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'weekday'    => ['required', 'integer', 'min:0', 'max:6'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time'   => ['required', 'date_format:H:i', 'after:start_time'],
            'break_start_time' => ['nullable', 'date_format:H:i'],
            'break_end_time'   => ['nullable', 'date_format:H:i', 'required_with:break_start_time', 'after:break_start_time'],
        ];
    }

    /**
     * Personaliza as mensagens de erro de validação.
     */
    public function messages()
    {
        return [
            'weekday.required' => 'O dia da semana é obrigatório.',
            'weekday.integer' => 'O dia da semana deve ser um número inteiro.',
            'weekday.min' => 'O dia da semana deve ser no mínimo 0 (domingo).',
            'weekday.max' => 'O dia da semana deve ser no máximo 6 (sábado).',

            'start_time.required' => 'A hora de início é obrigatória.',
            'start_time.date_format' => 'A hora de início deve estar no formato HH:mm.',

            'end_time.required' => 'A hora de fim é obrigatória.',
            'end_time.date_format' => 'A hora de fim deve estar no formato HH:mm.',
            'end_time.after' => 'A hora de fim deve ser posterior à hora de início.',

            'break_start_time.date_format' => 'A hora de início do intervalo deve estar no formato HH:mm.',
            'break_end_time.date_format' => 'A hora de fim do intervalo deve estar no formato HH:mm.',
            'break_end_time.required_with' => 'A hora de fim do intervalo é obrigatória quando há hora de início.',
            'break_end_time.after' => 'A hora de fim do intervalo deve ser posterior à hora de início do intervalo.',
        ];
    }
}
