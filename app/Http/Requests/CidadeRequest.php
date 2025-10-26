<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CidadeRequest extends FormRequest
{
    public function authorize(): bool
    {
        // ajuste se quiser restringir a usuários autenticados
        return true;
    }

    public function rules(): array
    {
        $cidadeId = $this->route('cidade'); // usado no update

        return [
            'nome_cidade' => ['required', 'string', 'max:255', 'unique:cidades,nome_cidade,' . $cidadeId . ',id_cidade'],
            'uf'          => ['nullable', 'string', 'size:2'],
            'estado_id'   => ['required', 'integer', 'exists:estados,id_estado'],
        ];
    }

    public function messages(): array
    {
        return [
            'nome_cidade.required' => 'O nome da cidade é obrigatório.',
            'nome_cidade.unique'   => 'Já existe uma cidade com este nome.',
            'uf.size'              => 'A sigla (UF) deve ter exatamente 2 caracteres.',
            'estado_id.required'   => 'Selecione o estado da cidade.',
            'estado_id.exists'     => 'O estado selecionado é inválido.',
        ];
    }
}
