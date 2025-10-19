<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Platform\Estado;
use App\Models\Platform\Pais;
use Illuminate\Http\Request;

class EstadoController extends Controller
{
    public function index()
    {
        $paises = Pais::orderBy('nome')->get();
        $estados = Estado::with('pais')->orderBy('nome_estado')->get();

        return view('platform.estados.index', compact('estados', 'paises'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nome_estado' => 'required|string|max:100',
            'uf'          => 'required|string|max:2',
            'pais_id'     => 'required|integer|exists:paises,id_pais',
        ]);

        Estado::create($request->all());
        return redirect()->back()->with('success', 'Estado cadastrado com sucesso!');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nome_estado' => 'required|string|max:100',
            'uf'          => 'required|string|max:2',
            'pais_id'     => 'required|integer|exists:paises,id_pais',
        ]);

        $estado = Estado::findOrFail($id);
        $estado->update($request->all());
        return redirect()->back()->with('success', 'Estado atualizado com sucesso!');
    }

    public function destroy($id)
    {
        Estado::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Estado removido com sucesso!');
    }

    public function show($id)
    {
        $estado  = Estado::with('pais')->findOrFail($id);
        $paises  = Pais::orderBy('nome')->get(['id_pais', 'nome']); // <- adicionar

        return view('platform.estados.show', compact('estado', 'paises')); // <- passar
    }
}
