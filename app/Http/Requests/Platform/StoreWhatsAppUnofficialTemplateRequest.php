<?php

namespace App\Http\Requests\Platform;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWhatsAppUnofficialTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'key' => [
                'required',
                'string',
                'max:120',
                'regex:/^[a-z0-9]+(?:[._][a-z0-9]+)*$/',
                Rule::unique('whatsapp_unofficial_templates', 'key'),
            ],
            'title' => ['required', 'string', 'max:160'],
            'category' => ['required', 'string', 'max:80'],
            'body' => ['required', 'string'],
            'variables' => ['nullable', 'array'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'key.regex' => 'Formato de key invalido. Use letras minusculas, numeros, ponto e underscore.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $variables = $this->input('variables');
        if (is_string($variables)) {
            $variables = trim($variables);
            if ($variables === '') {
                $variables = [];
            } else {
                $decoded = json_decode($variables, true);
                if (is_array($decoded)) {
                    $variables = $decoded;
                }
            }
        }

        $this->merge([
            'key' => trim((string) $this->input('key')),
            'title' => trim((string) $this->input('title')),
            'category' => strtolower(trim((string) $this->input('category'))),
            'variables' => $variables,
            'is_active' => $this->has('is_active') ? $this->boolean('is_active') : false,
        ]);
    }
}
