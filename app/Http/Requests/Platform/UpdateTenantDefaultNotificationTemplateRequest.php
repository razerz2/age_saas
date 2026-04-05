<?php

namespace App\Http\Requests\Platform;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateTenantDefaultNotificationTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'channel' => ['required', 'string', 'in:email,whatsapp'],
            'key' => ['required', 'string', 'max:120', 'regex:/^[a-z0-9]+(?:[._][a-z0-9]+)*$/'],
            'title' => ['required', 'string', 'max:160'],
            'category' => ['required', 'string', 'max:80'],
            'language' => ['required', 'string', 'max:16', 'regex:/^[a-z]{2}_[A-Z]{2}$/'],
            'subject' => ['nullable', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'variables' => ['nullable', 'array'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'key.regex' => 'Formato de chave inválido. Use letras minúsculas, números, ponto e underscore.',
            'language.regex' => 'Idioma inválido. Use o formato xx_YY (ex: pt_BR).',
        ];
    }

    protected function prepareForValidation(): void
    {
        $variables = $this->input('variables');
        if (is_string($variables)) {
            if (trim($variables) === '') {
                $variables = [];
            } else {
                $decoded = json_decode($variables, true);
                if (is_array($decoded)) {
                    $variables = $decoded;
                }
            }
        }

        $this->merge([
            'channel' => strtolower(trim((string) ($this->input('channel') ?: 'whatsapp'))),
            'key' => trim((string) $this->input('key')),
            'title' => trim((string) $this->input('title')),
            'category' => strtolower(trim((string) $this->input('category'))),
            'language' => trim((string) ($this->input('language') ?: 'pt_BR')),
            'variables' => $variables,
            'is_active' => $this->has('is_active') ? $this->boolean('is_active') : false,
        ]);
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $channel = (string) $this->input('channel');
            $subject = trim((string) ($this->input('subject') ?? ''));

            if ($channel === 'email' && $subject === '') {
                $validator->errors()->add('subject', 'O assunto é obrigatório para o canal e-mail.');
            }

            if ($channel === 'whatsapp' && $subject !== '') {
                $validator->errors()->add('subject', 'O assunto deve ficar vazio para o canal WhatsApp.');
            }
        });
    }
}
