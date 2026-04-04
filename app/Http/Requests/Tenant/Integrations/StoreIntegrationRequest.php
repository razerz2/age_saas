<?php

namespace App\Http\Requests\Tenant\Integrations;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreIntegrationRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'key' => ['required', 'string', 'max:255', Rule::unique('tenant.integrations', 'key')],
            'is_enabled' => ['required', 'boolean'],
            'config' => [
                'nullable',
                'string',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (!is_string($value)) {
                        return;
                    }

                    $trimmed = trim($value);
                    if ($trimmed === '') {
                        return;
                    }

                    if (self::normalizeConfig($value) === null) {
                        $fail('A configuracao deve ser um JSON valido (objeto ou lista).');
                    }
                },
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function payload(): array
    {
        $data = $this->validated();
        $rawConfig = $this->input('config');

        if (is_string($rawConfig)) {
            $trimmed = trim($rawConfig);
            $data['config'] = $trimmed === '' ? null : self::normalizeConfig($rawConfig);
        } else {
            $data['config'] = null;
        }

        return $data;
    }

    public static function normalizeConfig(mixed $rawConfig): ?array
    {
        if (!is_string($rawConfig)) {
            return null;
        }

        $trimmed = trim($rawConfig);
        if ($trimmed === '') {
            return null;
        }

        $decoded = json_decode($trimmed, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
            return null;
        }

        return $decoded;
    }

    public function messages()
    {
        return [
            'key.required' => 'A chave da integracao e obrigatoria.',
            'key.string' => 'A chave da integracao deve ser uma string valida.',
            'key.max' => 'A chave da integracao nao pode ter mais que 255 caracteres.',
            'key.unique' => 'Esta chave de integracao ja esta cadastrada.',
            'is_enabled.required' => 'O campo habilitado e obrigatorio.',
            'is_enabled.boolean' => 'O campo habilitado deve ser verdadeiro ou falso.',
            'config.string' => 'A configuracao deve ser informada como JSON em texto.',
        ];
    }
}
