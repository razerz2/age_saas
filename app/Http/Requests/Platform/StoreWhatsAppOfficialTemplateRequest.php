<?php

namespace App\Http\Requests\Platform;

use App\Models\Platform\WhatsAppOfficialTemplate;
use App\Support\WhatsAppOfficialTemplateValidator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreWhatsAppOfficialTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'key' => ['required', 'string', 'max:120'],
            'meta_template_name' => [
                'required',
                'string',
                'max:120',
                'regex:/^[a-z0-9_]+$/',
            ],
            'provider' => ['required', Rule::in([WhatsAppOfficialTemplate::PROVIDER])],
            'category' => ['required', Rule::in(['UTILITY', 'SECURITY'])],
            'language' => ['required', 'string', 'max:16', 'regex:/^[a-z]{2}_[A-Z]{2}$/'],
            'header_text' => ['nullable', 'string'],
            'body_text' => ['required', 'string'],
            'footer_text' => ['nullable', 'string'],
            'buttons' => ['nullable', 'array'],
            'variables' => ['nullable', 'array'],
            'sample_variables' => ['nullable', 'array'],
            'version' => ['nullable', 'integer', 'min:1'],
            'status' => [
                'nullable',
                Rule::in([
                    WhatsAppOfficialTemplate::STATUS_DRAFT,
                    WhatsAppOfficialTemplate::STATUS_PENDING,
                    WhatsAppOfficialTemplate::STATUS_APPROVED,
                    WhatsAppOfficialTemplate::STATUS_REJECTED,
                    WhatsAppOfficialTemplate::STATUS_ARCHIVED,
                ]),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'meta_template_name.regex' => 'Nome Meta inválido. Use apenas letras minúsculas, números e underscore.',
            'provider.in' => 'Este módulo suporta apenas o provider oficial whatsapp_business.',
            'language.regex' => 'Idioma inválido. Use o formato xx_YY (ex: pt_BR).',
        ];
    }

    protected function prepareForValidation(): void
    {
        $buttons = $this->input('buttons');
        if (is_string($buttons)) {
            if (trim($buttons) === '') {
                $buttons = null;
            } else {
                $decoded = json_decode($buttons, true);
                if (is_array($decoded)) {
                    $buttons = $decoded;
                }
            }
        }

        $variables = $this->input('variables');
        if (is_string($variables)) {
            if (trim($variables) === '') {
                $variables = null;
            } else {
                $decoded = json_decode($variables, true);
                if (is_array($decoded)) {
                    $variables = $decoded;
                }
            }
        }

        $sampleVariables = $this->input('sample_variables');
        if (is_string($sampleVariables)) {
            if (trim($sampleVariables) === '') {
                $sampleVariables = null;
            } else {
                $decoded = json_decode($sampleVariables, true);
                if (is_array($decoded)) {
                    $sampleVariables = $decoded;
                }
            }
        }

        $this->merge([
            'meta_template_name' => strtolower(trim((string) $this->input('meta_template_name'))),
            'provider' => (string) ($this->input('provider') ?: WhatsAppOfficialTemplate::PROVIDER),
            'category' => strtoupper(trim((string) ($this->input('category') ?: 'UTILITY'))),
            'language' => trim((string) ($this->input('language') ?: 'pt_BR')),
            'version' => $this->input('version') ?: 1,
            'status' => (string) ($this->input('status') ?: WhatsAppOfficialTemplate::STATUS_DRAFT),
            'buttons' => $buttons,
            'variables' => $variables,
            'sample_variables' => $sampleVariables,
        ]);
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $errors = WhatsAppOfficialTemplateValidator::validatePlaceholderConsistency(
                (string) $this->input('body_text', ''),
                $this->input('variables')
            );
            $sampleErrors = WhatsAppOfficialTemplateValidator::validateSampleVariablesConsistency(
                (string) $this->input('body_text', ''),
                $this->input('sample_variables'),
                false
            );
            $errors = array_merge($errors, $sampleErrors);

            foreach ($errors as $field => $message) {
                $validator->errors()->add($field, $message);
            }
        });
    }

    protected function passedValidation(): void
    {
        $this->merge([
            'variables' => WhatsAppOfficialTemplateValidator::normalizeVariables($this->input('variables')),
            'sample_variables' => WhatsAppOfficialTemplateValidator::normalizeSampleVariables($this->input('sample_variables')),
        ]);
    }
}
