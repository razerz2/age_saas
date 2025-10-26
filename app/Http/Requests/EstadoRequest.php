<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EstadoRequest extends FormRequest
{
    public function authorize(): bool
    {
        // ajuste se quiser limitar o acesso
        return true;
    }

    public function rules(): array
    {
        $estadoId = $this->route('estado'); // usado no update

        return [
            'uf'           => ['required', 'string', 'size:2', 'unique:estados,uf,' . $estadoId . ',id_estado'],
            'nome_estado'  => ['required', 'string', 'max:255', 'unique:estados,nome_estado,' . $estadoId . ',id_estado'],
            'pais_id'      => ['required', 'integer', 'exists:paises,id_pais'],
        ];
    }

    public function messages(): array
    {
        return [
            'uf.required' => 'A sigla (UF) é obrigatória.',
            'uf.size'     => 'A sigla (UF) deve ter exatamente 2 caracteres.',
            'uf.unique'   => 'Já existe um estado com essa sigla.',

            'nome_estado.required' => 'O nome do estado é obrigatório.',
            'nome_estado.unique'   => 'Já existe um estado com esse nome.',

            'pais_id.required' => 'Selecione o país ao qual o estado pertence.',
            'pais_id.exists'   => 'O país informado é inválido.',
        ];
    }
}
