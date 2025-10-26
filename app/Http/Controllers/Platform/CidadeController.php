<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Platform\Cidade;
use App\Models\Platform\Estado;
use App\Models\Platform\Pais;
use App\Http\Requests\CidadeRequest;

class CidadeController extends Controller
{
    public function index()
    {
        // 🔹 Agora a tela usa os filtros de País e Estado
        $paises  = Pais::orderBy('nome')->get(['id_pais', 'nome']);
        $estados = Estado::orderBy('nome_estado')->get(['id_estado', 'nome_estado', 'uf']); // usado no modal de criar

        return view('platform.cidades.index', compact('paises', 'estados'));
    }

    public function show($id)
    {
        $cidade  = Cidade::with('estado.pais')->findOrFail($id);
        $estados = Estado::orderBy('nome_estado')->get(['id_estado', 'nome_estado', 'uf']);

        return view('platform.cidades.show', compact('cidade', 'estados'));
    }

    public function store(CidadeRequest $request)
    {
        $data = $request->validate();
        Cidade::create($data);

        return redirect()->route('Platform.cidades.index')->with('success', 'Cidade criada com sucesso.');
    }

    public function update(CidadeRequest $request, $id)
    {
        $cidade = Cidade::findOrFail($id);
        $data = $request->validate();
        $cidade->update($data);

        return redirect()->route('Platformcidades.index')->with('success', 'Cidade atualizada com sucesso.');
    }


    public function destroy($id)
    {
        $cidade = Cidade::findOrFail($id);
        $cidade->delete();

        return redirect()->route('Platform.cidades.index')->with('success', 'Cidade excluída com sucesso.');
    }
}
