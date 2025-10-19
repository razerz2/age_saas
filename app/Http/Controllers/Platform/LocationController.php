<?php

namespace App\Http\Controllers\Platform;

use App\Models\Platform\Estado;
use App\Models\Platform\Cidade;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function getEstados($paisId)
    {
        $estados = Estado::where('pais_id', $paisId)
            ->join('paises', 'estados.pais_id', '=', 'paises.id_pais')
            ->select(
                'estados.id_estado',
                'estados.nome_estado',
                'estados.uf',
                'paises.id_pais as pais_id',
                'paises.nome as pais_nome'
            )
            ->orderBy('estados.nome_estado')
            ->get();

        return response()->json($estados);
    }

    public function getCidades($estadoId)
    {
        $cidades = Cidade::where('estado_id', $estadoId)
            ->join('estados', 'cidades.estado_id', '=', 'estados.id_estado')
            ->select(
                'cidades.id_cidade',
                'cidades.nome_cidade',
                'cidades.uf',
                'estados.nome_estado'
            )
            ->orderBy('cidades.nome_cidade')
            ->get();

        return response()->json($cidades);
    }
}
