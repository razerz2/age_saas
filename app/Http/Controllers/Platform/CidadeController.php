<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Platform\Cidade;
use App\Models\Platform\Estado;
use App\Models\Platform\Pais;
use Illuminate\Http\Request;

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

    public function store(Request $request)
    {
        $data = $request->validate([
            'nome_cidade' => 'required|string|max:255',
            'uf'          => 'nullable|string|max:2',
            'estado_id'   => 'required|integer|exists:estados,id_estado',
        ]);

        Cidade::create($data);

        return redirect()->route('Platform.cidades.index')->with('success', 'Cidade criada com sucesso.');
    }

    public function update(Request $request, $id)
    {
        $cidade = Cidade::findOrFail($id);

        $data = $request->validate([
            'nome_cidade' => 'required|string|max:255',
            'uf'          => 'nullable|string|max:2',
            'estado_id'   => 'required|integer|exists:estados,id_estado',
        ]);

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
