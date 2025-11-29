<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;

class IdentifyPatientRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'identifier' => ['required', 'string', 'max:255'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'identifier.required' => 'Por favor, informe seu CPF ou E-mail.',
            'identifier.string' => 'O campo de identificação deve ser um texto válido.',
            'identifier.max' => 'O campo de identificação não pode ter mais de 255 caracteres.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('identifier')) {
            $this->merge([
                'identifier' => trim($this->identifier),
            ]);
        }
    }
}

