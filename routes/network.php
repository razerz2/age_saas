<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Public\NetworkPublicController;

/**
 * =====================================================================
 * ROTAS PÚBLICAS DA REDE DE CLÍNICAS
 * =====================================================================
 * 
 * Acessadas via subdomínio da rede (ex: rede.allsync.com.br)
 * Detectadas pelo middleware DetectClinicNetworkFromSubdomain
 * 
 * IMPORTANTE: Estas rotas NÃO ativam tenant automaticamente.
 * A rede apenas orquestra e direciona para agendamento no tenant correto.
 */
Route::middleware(['require.network'])
    ->group(function () {
        Route::get('/', [NetworkPublicController::class, 'home'])->name('network.home');
        Route::get('/medicos', [NetworkPublicController::class, 'doctors'])->name('network.doctors');
        Route::get('/unidades', [NetworkPublicController::class, 'units'])->name('network.units');
    });

