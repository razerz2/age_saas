<?php

namespace App\Http\Requests\Tenant;

use App\Services\Tenant\CampaignTemplateProviderResolver;
use Illuminate\Foundation\Http\FormRequest;

class StoreCampaignTemplateRequest extends FormRequest
{
    private const OFFICIAL_BLOCK_MESSAGE = 'O provedor efetivo de WhatsApp para campanhas está em modo oficial. Gerencie os templates no catálogo oficial sincronizado com a Meta.';

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $variables = $this->input('variables_json');
        if (!is_array($variables)) {
            $variables = $this->parseVariablesFromText((string) $this->input('variables_json_text', ''));
        } else {
            $variables = $this->sanitizeVariables($variables);
        }

        $this->merge([
            'name' => trim((string) $this->input('name', '')),
            'content' => trim((string) $this->input('content', '')),
            'is_active' => $this->has('is_active') ? $this->boolean('is_active') : false,
            'variables_json' => $variables,
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'content' => ['required', 'string'],
            'is_active' => ['boolean'],
            'variables_json' => ['nullable', 'array'],
            'variables_json.*' => ['string', 'max:120'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O nome do template é obrigatório.',
            'name.max' => 'O nome do template deve ter no máximo 150 caracteres.',
            'content.required' => 'O conteúdo do template é obrigatório.',
            'variables_json.array' => 'As variáveis devem ser enviadas em formato de lista.',
            'variables_json.*.max' => 'Cada variável deve ter no máximo 120 caracteres.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            if (app(CampaignTemplateProviderResolver::class)->isOfficialWhatsApp()) {
                $validator->errors()->add('provider', self::OFFICIAL_BLOCK_MESSAGE);
            }
        });
    }

    /**
     * @param array<int, mixed> $variables
     * @return array<int, string>
     */
    private function sanitizeVariables(array $variables): array
    {
        $normalized = [];

        foreach ($variables as $variable) {
            $value = trim((string) $variable);
            if ($value === '') {
                continue;
            }

            if (!in_array($value, $normalized, true)) {
                $normalized[] = $value;
            }
        }

        return $normalized;
    }

    /**
     * @return array<int, string>
     */
    private function parseVariablesFromText(string $variablesText): array
    {
        $parts = preg_split('/[\r\n,;]+/u', $variablesText) ?: [];

        return $this->sanitizeVariables($parts);
    }
}
