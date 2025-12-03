<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;

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
            'start_time' => ['required', 'date_format:H:i'],
            'end_time'   => ['required', 'date_format:H:i', 'after:start_time'],
            'break_start_time' => ['nullable', 'date_format:H:i'],
            'break_end_time'   => ['nullable', 'date_format:H:i', 'required_with:break_start_time', 'after:break_start_time'],
        ];
    }

    /**
     * Prepare os dados para validação.
     */
    protected function prepareForValidation()
    {
        // Converter weekdays de string para inteiro se necessário
        if ($this->has('weekdays') && is_array($this->weekdays)) {
            $this->merge([
                'weekdays' => array_map('intval', $this->weekdays),
            ]);
        }
    }

    /**
     * Personaliza as mensagens de erro de validação.
     */
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
