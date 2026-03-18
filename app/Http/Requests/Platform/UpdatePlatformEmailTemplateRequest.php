<?php

namespace App\Http\Requests\Platform;

use App\Models\Platform\NotificationTemplate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePlatformEmailTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $templateId = (string) optional($this->route('platformEmailTemplate'))->id;

        return [
            'name' => [
                'required',
                'string',
                'max:120',
                'regex:/^[a-z0-9]+(?:[._][a-z0-9]+)*$/',
                Rule::unique('notification_templates', 'name')
                    ->ignore($templateId)
                    ->where(fn ($query) => $query
                        ->where('scope', NotificationTemplate::SCOPE_PLATFORM)
                        ->where('channel', NotificationTemplate::CHANNEL_EMAIL)),
            ],
            'display_name' => ['required', 'string', 'max:160'],
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'enabled' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.regex' => 'Formato de key invalido. Use letras minusculas, numeros, ponto e underscore.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => strtolower(trim((string) $this->input('name'))),
            'display_name' => trim((string) $this->input('display_name')),
            'subject' => trim((string) $this->input('subject')),
            'body' => (string) $this->input('body'),
            'enabled' => $this->has('enabled') ? $this->boolean('enabled') : false,
        ]);
    }
}

