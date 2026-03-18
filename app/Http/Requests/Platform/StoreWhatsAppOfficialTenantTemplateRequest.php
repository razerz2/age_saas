<?php

namespace App\Http\Requests\Platform;

use App\Models\Platform\WhatsAppOfficialTemplate;
use App\Support\WhatsAppOfficialTenantEventCatalog;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreWhatsAppOfficialTenantTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tenant_id' => ['required', 'uuid', Rule::exists('tenants', 'id')],
            'event_key' => [
                'required',
                'string',
                'max:120',
                Rule::in(WhatsAppOfficialTenantEventCatalog::keys()),
                Rule::unique('tenant_whatsapp_official_templates', 'event_key')->where(function ($query): void {
                    $query
                        ->where('tenant_id', (string) $this->input('tenant_id'))
                        ->where('language', (string) $this->input('language'));
                }),
            ],
            'whatsapp_official_template_id' => ['required', 'uuid', Rule::exists('whatsapp_official_templates', 'id')],
            'language' => ['required', 'string', 'max:16'],
            'is_active' => ['nullable', 'boolean'],
            'metadata' => ['nullable', 'array'],
            'metadata.*' => ['nullable'],
        ];
    }

    public function messages(): array
    {
        return [
            'event_key.in' => 'Evento nao suportado para configuracao oficial tenant.',
            'event_key.unique' => 'Ja existe mapeamento para este tenant, evento e idioma.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $templateId = (string) $this->input('whatsapp_official_template_id');
            if ($templateId === '') {
                return;
            }

            $template = WhatsAppOfficialTemplate::query()->find($templateId);
            if (!$template) {
                return;
            }

            if ((string) $template->provider !== WhatsAppOfficialTemplate::PROVIDER) {
                $validator->errors()->add('whatsapp_official_template_id', 'Template oficial invalido: provider incompativel.');
            }

            if ((string) $template->key !== (string) $this->input('event_key')) {
                $validator->errors()->add('whatsapp_official_template_id', 'Template selecionado nao corresponde ao evento informado.');
            }

            if ((string) $template->language !== (string) $this->input('language')) {
                $validator->errors()->add('language', 'Idioma deve coincidir com o idioma do template oficial selecionado.');
            }
        });
    }

    protected function prepareForValidation(): void
    {
        $metadata = $this->input('metadata');
        if (is_string($metadata)) {
            $metadata = trim($metadata);
            if ($metadata === '') {
                $metadata = null;
            } else {
                $decoded = json_decode($metadata, true);
                $metadata = is_array($decoded) ? $decoded : null;
            }
        }

        $this->merge([
            'tenant_id' => trim((string) $this->input('tenant_id')),
            'event_key' => trim((string) $this->input('event_key')),
            'whatsapp_official_template_id' => trim((string) $this->input('whatsapp_official_template_id')),
            'language' => trim((string) $this->input('language', 'pt_BR')),
            'is_active' => $this->has('is_active') ? $this->boolean('is_active') : false,
            'metadata' => $metadata,
        ]);
    }
}
