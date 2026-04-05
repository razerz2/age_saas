<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;

class TestWhatsAppSendRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'number' => ['required', 'string', 'max:30'],
            'message' => ['required', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'number.required' => 'Informe o número de destino.',
            'number.string' => 'Informe um número de destino válido.',
            'number.max' => 'O número de destino não pode ter mais de 30 caracteres.',
            'message.required' => 'Informe a mensagem.',
            'message.string' => 'A mensagem deve ser um texto válido.',
            'message.max' => 'A mensagem não pode ter mais de 1000 caracteres.',
        ];
    }

    public function attributes(): array
    {
        return [
            'number' => 'número de destino',
            'message' => 'mensagem',
        ];
    }
}
