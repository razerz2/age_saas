<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NetworkAdmin\NetworkAuthController;
use App\Http\Controllers\NetworkAdmin\NetworkDashboardController;
use App\Http\Controllers\NetworkAdmin\NetworkClinicController;
use App\Http\Controllers\NetworkAdmin\NetworkDoctorController;
use App\Http\Controllers\NetworkAdmin\NetworkAppointmentController;
use App\Http\Controllers\NetworkAdmin\NetworkFinanceController;
use App\Http\Controllers\NetworkAdmin\NetworkSettingsController;

/**
 * =====================================================================
 * ROTAS DA ÁREA ADMINISTRATIVA DA REDE DE CLÍNICAS
 * =====================================================================
 * 
 * Acessadas via subdomínio da rede (ex: admin.rede.allsync.com.br)
 * Detectadas pelo middleware DetectClinicNetworkFromSubdomain
 * 
 * IMPORTANTE: Área MAJORITARIAMENTE READ-ONLY
 * Apenas configurações da rede podem ser editadas.
 */

// Rotas públicas (login)
Route::middleware(['ensure.network.context'])->group(function () {
    Route::get('/login', [NetworkAuthController::class, 'showLoginForm'])->name('network.login');
    Route::post('/login', [NetworkAuthController::class, 'login'])->name('network.login.submit');
    Route::post('/logout', [NetworkAuthController::class, 'logout'])->name('network.logout');
});

// Rotas protegidas (área administrativa)
Route::middleware([
    'ensure.network.context',
    'network.auth'
])->group(function () {

    // Dashboard
    Route::get('/dashboard', [NetworkDashboardController::class, 'index'])->name('network.dashboard');

    // Clínicas (somente leitura)
    Route::get('/clinicas', [NetworkClinicController::class, 'index'])->name('network.clinics.index');

    // Médicos (somente leitura)
    Route::get('/medicos', [NetworkDoctorController::class, 'index'])->name('network.doctors.index');

    // Agendamentos (somente leitura - métricas)
    Route::get('/agendamentos', [NetworkAppointmentController::class, 'index'])->name('network.appointments.index');

    // Financeiro (somente leitura - se habilitado)
    Route::get('/financeiro', [NetworkFinanceController::class, 'index'])->name('network.finance.index');

    // Configurações (edição permitida)
    Route::get('/configuracoes', [NetworkSettingsController::class, 'edit'])->name('network.settings.edit');
    Route::post('/configuracoes', [NetworkSettingsController::class, 'update'])->name('network.settings.update');
});

