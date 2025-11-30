<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Tenant\Doctor;

class UpdateDoctorRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'user_id'       => ['required', 'exists:tenant.users,id'],
            'crm_number'    => ['nullable', 'string', 'max:50'],
            'crm_state'     => ['nullable', 'string', 'max:2'],
            'signature'     => ['nullable', 'string', 'max:255'],
            'specialties'   => ['required', 'array', 'min:1'],
            'specialties.*' => ['required', 'uuid', 'exists:tenant.medical_specialties,id'],
            'label_singular' => ['nullable', 'string', 'max:60'],
            'label_plural' => ['nullable', 'string', 'max:60'],
            'registration_label' => ['nullable', 'string', 'max:40'],
            'registration_value' => ['nullable', 'string', 'max:100'],
        ];
    }

    /**
     * Validação adicional para evitar duplicidade
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $doctorId = $this->route('id');
            $currentDoctor = Doctor::find($doctorId);

            if (!$currentDoctor) {
                return;
            }

            // Verificar se já existe outro médico com o mesmo user_id (ignorando o atual)
            $existingDoctorByUser = Doctor::where('user_id', $this->user_id)
                ->where('id', '!=', $doctorId)
                ->first();
            
            if ($existingDoctorByUser) {
                $validator->errors()->add('user_id', 'Este usuário já está cadastrado como médico.');
            }

            // Verificar se já existe outro médico com o mesmo CRM (número + estado), ignorando o atual
            if ($this->filled('crm_number') && $this->filled('crm_state')) {
                $existingDoctorByCrm = Doctor::where('crm_number', $this->crm_number)
                    ->where('crm_state', $this->crm_state)
                    ->where('id', '!=', $doctorId)
                    ->first();
                
                if ($existingDoctorByCrm) {
                    $validator->errors()->add('crm_number', 'Este CRM já está cadastrado para outro médico.');
                }
            }
        });
    }

    /**
     * Personaliza as mensagens de erro de validação.
     */
    public function messages()
    {
        return [
            'user_id.required' => 'O usuário é obrigatório.',
            'user_id.exists' => 'O usuário selecionado não existe.',

            'crm_number.string' => 'O número do CRM deve ser uma string válida.',
            'crm_number.max' => 'O número do CRM não pode ter mais que 50 caracteres.',

            'crm_state.string' => 'O estado do CRM deve ser uma string válida.',
            'crm_state.max' => 'O estado do CRM não pode ter mais que 2 caracteres.',

            'signature.string' => 'A assinatura deve ser uma string válida.',
            'signature.max' => 'A assinatura não pode ter mais que 255 caracteres.',

            'specialties.required' => 'É obrigatório selecionar pelo menos uma especialidade médica.',
            'specialties.array' => 'As especialidades devem ser passadas como um array.',
            'specialties.min' => 'É obrigatório selecionar pelo menos uma especialidade médica.',

            'specialties.*.required' => 'Cada especialidade é obrigatória.',
            'specialties.*.uuid' => 'Cada especialidade deve ser um UUID válido.',
            'specialties.*.exists' => 'Uma ou mais especialidades selecionadas não existem.',
        ];
    }
}
