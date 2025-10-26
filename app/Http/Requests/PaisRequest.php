<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaisRequest extends FormRequest
{
    public function authorize(): bool
    {
        // ajuste se quiser restringir a usuários específicos
        return true;
    }

    public function rules(): array
    {
        $paisId = $this->route('pai') ?? $this->route('pais'); // compatível com singular/plural

        return [
            'nome'   => ['required', 'string', 'max:255', 'unique:paises,nome,' . $paisId . ',id_pais'],
            'sigla2' => ['required', 'string', 'size:2', 'unique:paises,sigla2,' . $paisId . ',id_pais'],
            'sigla3' => ['nullable', 'string', 'size:3', 'unique:paises,sigla3,' . $paisId . ',id_pais'],
            'codigo' => ['required', 'numeric', 'unique:paises,codigo,' . $paisId . ',id_pais'],
        ];
    }

    public function messages(): array
    {
        return [
            'nome.required' => 'O nome do país é obrigatório.',
            'nome.unique'   => 'Já existe um país com esse nome.',
            'sigla2.required' => 'A sigla de 2 letras é obrigatória.',
            'sigla2.size'   => 'A sigla de 2 letras deve ter exatamente 2 caracteres.',
            'sigla2.unique' => 'Essa sigla de 2 letras já está em uso.',
            'sigla3.size'   => 'A sigla de 3 letras deve ter exatamente 3 caracteres.',
            'codigo.required' => 'O código do país é obrigatório.',
            'codigo.numeric'  => 'O código deve ser numérico.',
            'codigo.unique'   => 'Já existe um país com esse código.',
        ];
    }
}
