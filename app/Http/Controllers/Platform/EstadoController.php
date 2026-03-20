<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Platform\Estado;
use App\Http\Requests\Platform\EstadoRequest;

class EstadoController extends Controller
{
    private const BRAZIL_COUNTRY_ID = 31;

    public function index()
    {
        $estados = Estado::where('pais_id', self::BRAZIL_COUNTRY_ID)->orderBy('nome_estado')->get();

        return view('platform.estados.index', compact('estados'));
    }

    public function store(EstadoRequest $request)
    {
        $data = $request->validated();
        $data['pais_id'] = self::BRAZIL_COUNTRY_ID;
        Estado::create($data);

        return redirect()->back()->with('success', 'Estado cadastrado com sucesso!');
    }

    public function update(EstadoRequest $request, $id)
    {
        $data = $request->validated();
        $data['pais_id'] = self::BRAZIL_COUNTRY_ID;
        $estado = Estado::findOrFail($id);
        $estado->update($data);
        
        return redirect()->back()->with('success', 'Estado atualizado com sucesso!');
    }

    public function destroy($id)
    {
        Estado::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Estado removido com sucesso!');
    }

    public function show($id)
    {
        $estado = Estado::findOrFail($id);

        return view('platform.estados.show', compact('estado'));
    }
}

