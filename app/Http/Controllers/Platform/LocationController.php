<?php

namespace App\Http\Controllers\Platform;

use App\Models\Platform\Estado;
use App\Models\Platform\Cidade;
use App\Http\Controllers\Controller;

class LocationController extends Controller
{
    private const BRAZIL_COUNTRY_ID = 31;

    public function getEstados()
    {
        $estados = Estado::where('pais_id', self::BRAZIL_COUNTRY_ID)
            ->select(
                'id_estado',
                'nome_estado',
                'uf',
                'ibge_id'
            )
            ->orderBy('nome_estado')
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
                'cidades.ibge_id',
                'estados.id_estado as estado_id',
                'estados.nome_estado',
                'estados.ibge_id as estado_ibge_id'
            )
            ->orderBy('cidades.nome_cidade')
            ->get();

        return response()->json($cidades);
    }
}
