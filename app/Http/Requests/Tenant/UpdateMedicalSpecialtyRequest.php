<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMedicalSpecialtyRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $id = $this->route('specialty')->id;

        return [
            'name' => ['required', 'string', 'max:255', "unique:medical_specialties,name,{$id}"],
            'code' => ['nullable', 'string', 'max:50'],
        ];
    }
}
