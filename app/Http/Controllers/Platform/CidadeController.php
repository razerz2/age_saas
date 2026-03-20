<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Http\Requests\Platform\CidadeRequest;
use App\Models\Platform\Cidade;
use App\Models\Platform\Estado;

class CidadeController extends Controller
{
    private const BRAZIL_COUNTRY_ID = 31;

    public function index()
    {
        $estados = Estado::where('pais_id', self::BRAZIL_COUNTRY_ID)
            ->orderBy('nome_estado')
            ->get(['id_estado', 'nome_estado', 'uf']);

        return view('platform.cidades.index', compact('estados'));
    }

    public function show($id)
    {
        $cidade = Cidade::with('estado')->findOrFail($id);
        $estados = Estado::where('pais_id', self::BRAZIL_COUNTRY_ID)
            ->orderBy('nome_estado')
            ->get(['id_estado', 'nome_estado', 'uf']);

        return view('platform.cidades.show', compact('cidade', 'estados'));
    }

    public function store(CidadeRequest $request)
    {
        $data = $request->validated();
        Cidade::create($data);

        return redirect()->route('Platform.cidades.index')->with('success', 'Cidade criada com sucesso.');
    }

    public function update(CidadeRequest $request, $id)
    {
        $cidade = Cidade::findOrFail($id);
        $data = $request->validated();
        $cidade->update($data);

        return redirect()->route('Platform.cidades.index')->with('success', 'Cidade atualizada com sucesso.');
    }

    public function destroy($id)
    {
        $cidade = Cidade::findOrFail($id);
        $cidade->delete();

        return redirect()->route('Platform.cidades.index')->with('success', 'Cidade excluida com sucesso.');
    }
}
