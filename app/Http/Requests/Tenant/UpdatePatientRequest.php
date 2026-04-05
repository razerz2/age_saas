<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePatientRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $id = $this->route('id');

        return [
            'full_name'  => ['required', 'string', 'max:255'],
            'cpf'        => ['required', 'string', 'max:14', Rule::unique('tenant.patients', 'cpf')->ignore($id)],
            'birth_date' => ['nullable', 'date'],
            'gender_id'  => ['nullable', 'exists:tenant.genders,id'],
            'email'      => ['nullable', 'email'],
            'phone'      => ['nullable', 'string', 'max:20'],
            'is_active'  => ['required', 'boolean'],

            // Campos de endereço (opcionais)
            'postal_code'    => ['nullable', 'string', 'max:10'],
            'street'         => ['nullable', 'string', 'max:255'],
            'number'         => ['nullable', 'string', 'max:20'],
            'complement'     => ['nullable', 'string', 'max:255'],
            'neighborhood'   => ['nullable', 'string', 'max:255'],
            'city'           => ['nullable', 'string', 'max:255'],
            'state'          => ['nullable', 'string', 'max:2'],
            'estado_id'      => ['nullable', 'integer'],
            'cidade_id'      => ['nullable', 'integer'],
        ];
    }

    /**
     * Personaliza as mensagens de erro de validação.
     */
    public function messages()
    {
        return [
            'full_name.required' => 'O nome completo é obrigatório.',
            'full_name.string' => 'O nome completo deve ser um texto válido.',
            'full_name.max' => 'O nome completo não pode ter mais de 255 caracteres.',

            'cpf.required' => 'O CPF é obrigatório.',
            'cpf.string' => 'O CPF deve ser um texto válido.',
            'cpf.max' => 'O CPF não pode ter mais de 14 caracteres.',
            'cpf.unique' => 'Este CPF já está cadastrado.',

            'birth_date.date' => 'A data de nascimento informada é inválida.',

            'gender_id.exists' => 'O gênero selecionado é inválido.',

            'email.email' => 'Informe um e-mail válido.',

            'phone.string' => 'O telefone deve ser um texto válido.',
            'phone.max' => 'O telefone não pode ter mais de 20 caracteres.',
            'is_active.required' => 'O status do paciente é obrigatório.',
            'is_active.boolean' => 'O campo status do paciente deve ser verdadeiro ou falso.',
        ];
    }
}
