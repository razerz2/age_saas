<?php

namespace App\Http\Requests\Platform;

use Illuminate\Foundation\Http\FormRequest;

class EstadoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $estadoId = $this->route('estado');

        return [
            'uf' => ['required', 'string', 'size:2', 'unique:estados,uf,' . $estadoId . ',id_estado'],
            'nome_estado' => ['required', 'string', 'max:255', 'unique:estados,nome_estado,' . $estadoId . ',id_estado'],
            'ibge_id' => ['nullable', 'integer'],
        ];
    }

    public function messages(): array
    {
        return [
            'uf.required' => 'A sigla (UF) e obrigatoria.',
            'uf.size' => 'A sigla (UF) deve ter exatamente 2 caracteres.',
            'uf.unique' => 'Ja existe um estado com essa sigla.',
            'nome_estado.required' => 'O nome do estado e obrigatorio.',
            'nome_estado.unique' => 'Ja existe um estado com esse nome.',
        ];
    }
}
