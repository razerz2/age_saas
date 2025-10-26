<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MedicalSpecialtyCatalogRequest extends FormRequest
{
    public function authorize(): bool
    {
        // ajuste se quiser permitir apenas administradores
        return true;
    }

    public function rules(): array
    {
        $specialtyId = $this->route('medical_specialty_catalog'); // usado no update

        return [
            'name' => ['required', 'string', 'max:255', 'unique:medical_specialties_catalog,name,' . $specialtyId],
            'code' => ['nullable', 'string', 'max:50', 'unique:medical_specialties_catalog,code,' . $specialtyId],
            'type' => ['required', 'in:medical,surgical,diagnostic,therapeutic,other'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O nome da especialidade é obrigatório.',
            'name.unique' => 'Já existe uma especialidade com este nome.',
            'code.unique' => 'Já existe uma especialidade com este código.',
            'type.required' => 'O tipo da especialidade é obrigatório.',
            'type.in' => 'O tipo informado é inválido.',
        ];
    }
}
