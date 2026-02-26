<?php

namespace App\Http\Requests\Tenant;

use App\Models\Tenant\Doctor;
use App\Rules\StrongPassword;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $user = auth()->guard('tenant')->user();

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'name_full' => ['required', 'string', 'max:255'],
            'telefone' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', Rule::unique('tenant.users', 'email')],
            'password' => ['nullable', 'string', 'min:8', new StrongPassword()],
            'password_confirmation' => ['nullable', 'string'],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'is_doctor' => ['nullable', 'boolean'],
            'role' => ['required', 'in:admin,user,doctor'],

            'doctor' => ['exclude_unless:role,doctor', 'array'],
            'doctor.crm_number' => ['exclude_unless:role,doctor', 'nullable', 'string', 'max:50'],
            'doctor.crm_state' => ['exclude_unless:role,doctor', 'nullable', 'string', 'max:2'],
            'doctor.signature' => ['exclude_unless:role,doctor', 'nullable', 'string', 'max:255'],
            'doctor.label_singular' => ['exclude_unless:role,doctor', 'nullable', 'string', 'max:60'],
            'doctor.label_plural' => ['exclude_unless:role,doctor', 'nullable', 'string', 'max:60'],
            'doctor.registration_label' => ['exclude_unless:role,doctor', 'nullable', 'string', 'max:40'],
            'doctor.registration_value' => ['exclude_unless:role,doctor', 'nullable', 'string', 'max:100'],
            'doctor.specialties' => ['exclude_unless:role,doctor', 'required', 'array', 'min:1'],
            'doctor.specialties.*' => ['exclude_unless:role,doctor', 'required', 'uuid', 'exists:tenant.medical_specialties,id'],
        ];

        // Se o usuário logado não é médico nem admin, permite validar doctor_ids.
        if ($user && $user->role !== 'doctor' && $user->role !== 'admin') {
            $rules['doctor_ids'] = ['nullable', 'array'];
            $rules['doctor_ids.*'] = ['exists:tenant.doctors,id'];
        }

        // Permite validar modules para todos os usuários.
        $rules['modules_present'] = ['nullable', 'in:1'];
        $rules['modules'] = ['nullable', 'array'];
        $rules['modules.*'] = ['string'];

        return $rules;
    }

    /**
     * Validação adicional para confirmação de senha e CRM duplicado.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $password = $this->input('password');

            if (!empty($password) && $password !== $this->input('password_confirmation')) {
                $validator->errors()->add('password_confirmation', 'A confirmação da senha não coincide.');
            }

            if ($this->input('role') !== 'doctor') {
                return;
            }

            $crmNumber = data_get($this->input('doctor', []), 'crm_number');
            $crmState = data_get($this->input('doctor', []), 'crm_state');

            if (empty($crmNumber) || empty($crmState)) {
                return;
            }

            $existingDoctorByCrm = Doctor::query()
                ->where('crm_number', $crmNumber)
                ->where('crm_state', $crmState)
                ->first();

            if ($existingDoctorByCrm) {
                $validator->errors()->add('doctor.crm_number', 'Este CRM já está cadastrado para outro médico.');
            }
        });
    }

    /**
     * Personaliza as mensagens de erro de validação.
     */
    public function messages()
    {
        return [
            'name.required' => 'O nome de exibição é obrigatório.',
            'name.string' => 'O nome de exibição deve ser uma string válida.',
            'name.max' => 'O nome de exibição não pode ter mais que 255 caracteres.',

            'name_full.required' => 'O nome completo é obrigatório.',
            'name_full.string' => 'O nome completo deve ser uma string válida.',
            'name_full.max' => 'O nome completo não pode ter mais que 255 caracteres.',

            'telefone.required' => 'O telefone é obrigatório.',
            'telefone.string' => 'O telefone deve ser uma string válida.',
            'telefone.max' => 'O telefone não pode ter mais que 255 caracteres.',

            'email.email' => 'Por favor, insira um e-mail válido.',
            'email.unique' => 'Este e-mail já está em uso.',

            'password.min' => 'A senha deve ter pelo menos 8 caracteres.',

            'avatar.image' => 'O arquivo deve ser uma imagem.',
            'avatar.mimes' => 'A imagem deve ser do tipo: jpeg, png, jpg ou gif.',
            'avatar.max' => 'A imagem não pode ter mais de 2MB.',

            'is_doctor.boolean' => 'O campo "É médico?" deve ser verdadeiro ou falso.',

            'doctor.specialties.required' => 'É obrigatório selecionar pelo menos uma especialidade médica.',
            'doctor.specialties.array' => 'As especialidades do médico devem ser passadas como um array.',
            'doctor.specialties.min' => 'É obrigatório selecionar pelo menos uma especialidade médica.',
            'doctor.specialties.*.required' => 'Cada especialidade do médico é obrigatória.',
            'doctor.specialties.*.uuid' => 'Cada especialidade do médico deve ser um UUID válido.',
            'doctor.specialties.*.exists' => 'Uma ou mais especialidades selecionadas não existem.',

            'modules.array' => 'Os módulos devem ser passados como um array.',
        ];
    }

    /**
     * Nomes amigáveis para mensagens de erro.
     */
    public function attributes()
    {
        return [
            'doctor.crm_number' => 'número CRM, CRP ou CRO',
            'doctor.crm_state' => 'estado CRM, CRP ou CRO',
            'doctor.signature' => 'assinatura do médico',
            'doctor.label_singular' => 'tipo do profissional (singular)',
            'doctor.label_plural' => 'tipo do profissional (plural)',
            'doctor.registration_label' => 'rótulo do registro profissional',
            'doctor.registration_value' => 'valor do registro profissional',
            'doctor.specialties' => 'especialidades do médico',
            'doctor.specialties.*' => 'especialidade do médico',
        ];
    }
}
