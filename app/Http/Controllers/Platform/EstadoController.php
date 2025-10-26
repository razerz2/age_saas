<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Platform\Estado;
use App\Models\Platform\Pais;
use App\Http\Requests\EstadoRequest;

class EstadoController extends Controller
{
    public function index()
    {
        $paises = Pais::orderBy('nome')->get();
        $estados = Estado::with('pais')->orderBy('nome_estado')->get();

        return view('platform.estados.index', compact('estados', 'paises'));
    }

    public function store(EstadoRequest $request)
    {
        $request->validate();
        Estado::create($request->all());

        return redirect()->back()->with('success', 'Estado cadastrado com sucesso!');
    }

    public function update(EstadoRequest $request, $id)
    {
        $request->validate();
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
