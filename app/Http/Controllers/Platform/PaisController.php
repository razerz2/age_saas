<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Platform\Pais;
use App\Http\Requests\PaisRequest;
use Illuminate\Support\Facades\Log;

class PaisController extends Controller
{
    public function index()
    {
        $paises = Pais::orderBy('nome')->get();
        return view('platform.paises.index', compact('paises'));
    }

    public function store(PaisRequest $request)
    {
        $request->validate();
        Pais::create($request->all());

        return redirect()->back()->with('success', 'País cadastrado com sucesso!');
    }

    public function update(PaisRequest $request, $id)
    {
        $request->validate();
        $pais = Pais::findOrFail($id);
        $pais->update($request->all());
        
        return redirect()->back()->with('success', 'País atualizado com sucesso!');
    }

    public function destroy($id)
    {
        Pais::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'País removido com sucesso!');
    }

    public function show($id)
    {
        $pais = Pais::findOrFail($id);
        return view('platform.paises.show', compact('pais'));
    }
}
