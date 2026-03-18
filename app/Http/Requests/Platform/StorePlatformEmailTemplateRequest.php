<?php

namespace App\Http\Requests\Platform;

use App\Models\Platform\NotificationTemplate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePlatformEmailTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:120',
                'regex:/^[a-z0-9]+(?:[._][a-z0-9]+)*$/',
                Rule::unique('notification_templates', 'name')
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
        $name = strtolower(trim((string) $this->input('name')));
        $displayName = trim((string) $this->input('display_name'));
        $subject = trim((string) $this->input('subject'));
        $body = (string) $this->input('body');

        $this->merge([
            'name' => $name,
            'display_name' => $displayName,
            'subject' => $subject,
            'body' => $body,
            'default_subject' => $subject,
            'default_body' => $body,
            'variables' => [],
            'enabled' => $this->has('enabled') ? $this->boolean('enabled') : true,
            'scope' => NotificationTemplate::SCOPE_PLATFORM,
            'channel' => NotificationTemplate::CHANNEL_EMAIL,
        ]);
    }
}

