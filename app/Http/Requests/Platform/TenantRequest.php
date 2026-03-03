<?php

namespace App\Http\Requests\Platform;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TenantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * 🔄 Antes de validar, remove caracteres especiais do campo document
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('document')) {
            $this->merge([
                'document' => preg_replace('/\D/', '', $this->input('document')),
            ]);
        }
    }

    public function rules(): array
    {
        // Pode vir um model, uma string UUID, ou até uma string vazia
        $tenantParam = $this->route('tenant');

        // 🔒 Força nulo se for vazio, string vazia, ou não tiver id
        $tenantId = null;
        if ($tenantParam instanceof \App\Models\Platform\Tenant) {
            $tenantId = $tenantParam->id;
        } elseif (is_string($tenantParam) && trim($tenantParam) !== '') {
            $tenantId = $tenantParam;
        }

        // ⚙️ Agora definimos as regras
        return [
            'legal_name' => ['required', 'string', 'max:255'],
            'trade_name' => ['nullable', 'string', 'max:255'],

            'document' => [
                'required',
                'string',
                'min:11',
                'max:14',
                Rule::unique('tenants', 'document')->ignore($tenantId),
                function ($attribute, $value, $fail) {
                    if (!$this->isValidCpfOrCnpj($value)) {
                        $fail('O campo documento deve conter um CPF ou CNPJ válido.');
                    }
                },
            ],

            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('tenants', 'email')->ignore($tenantId),
            ],

            'subdomain' => [
                'required',
                'string',
                'max:50',
                'alpha_dash',
                Rule::unique('tenants', 'subdomain')->ignore($tenantId),
            ],

            'phone' => ['nullable', 'string', 'max:20'],
            'status' => ['required', 'in:active,inactive,pending,trial,suspended,cancelled'],
            'network_id' => ['nullable', 'uuid', 'exists:clinic_networks,id'],
            'plan_id' => ['nullable', 'uuid', 'exists:plans,id'],
            'trial_ends_at' => ['nullable', 'date'],
            'asaas_customer_id' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'legal_name.required' => 'O nome legal é obrigatório.',
            'document.required' => 'O CPF ou CNPJ é obrigatório.',
            'document.unique' => 'Já existe um tenant com este CPF/CNPJ.',
            'email.unique' => 'Já existe um tenant com este e-mail.',
            'subdomain.unique' => 'Este subdomínio já está em uso.',
            'status.in' => 'O status deve ser ativo, inativo ou pendente.',
        ];
    }

    /**
     * 🔍 Valida se o documento é CPF ou CNPJ válido.
     */
    private function isValidCpfOrCnpj(string $value): bool
    {
        if (strlen($value) === 11) {
            return $this->validateCpf($value);
        }

        if (strlen($value) === 14) {
            return $this->validateCnpj($value);
        }

        return false;
    }

    /**
     * ✅ Valida CPF
     */
    private function validateCpf(string $cpf): bool
    {
        if (preg_match('/(\d)\1{10}/', $cpf)) return false;

        for ($t = 9; $t < 11; $t++) {
            $sum = 0;
            for ($i = 0; $i < $t; $i++) {
                $sum += $cpf[$i] * (($t + 1) - $i);
            }
            $digit = ((10 * $sum) % 11) % 10;
            if ($cpf[$t] != $digit) return false;
        }

        return true;
    }

    /**
     * ✅ Valida CNPJ
     */
    private function validateCnpj(string $cnpj): bool
    {
        if (preg_match('/(\d)\1{13}/', $cnpj)) return false;

        $weights1 = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $weights2 = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];

        $sum1 = 0;
        for ($i = 0; $i < 12; $i++) {
            $sum1 += $cnpj[$i] * $weights1[$i];
        }
        $digit1 = ($sum1 % 11 < 2) ? 0 : 11 - ($sum1 % 11);

        $sum2 = 0;
        for ($i = 0; $i < 13; $i++) {
            $sum2 += $cnpj[$i] * $weights2[$i];
        }
        $digit2 = ($sum2 % 11 < 2) ? 0 : 11 - ($sum2 % 11);

        return $cnpj[12] == $digit1 && $cnpj[13] == $digit2;
    }
}

