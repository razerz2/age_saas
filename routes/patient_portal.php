<?php

use Illuminate\Support\Facades\Route;

// Controllers do Portal do Paciente
use App\Http\Controllers\Tenant\PatientPortal\AuthController;
use App\Http\Controllers\Tenant\PatientPortal\DashboardController;
use App\Http\Controllers\Tenant\PatientPortal\AppointmentController;
use App\Http\Controllers\Tenant\PatientPortal\NotificationController;
use App\Http\Controllers\Tenant\PatientPortal\ProfileController;

/**
 * =====================================================================
 * PORTAL DO PACIENTE
 * =====================================================================
 * Login: /customer/{slug}/paciente/login (com slug na URL)
 * Autenticadas: /workspace/{slug}/paciente/* (com slug na URL)
 */

/**
 * Rotas Públicas (Autenticação com slug na URL)
 */
Route::prefix('customer/{slug}/paciente')
    ->as('patient.')
    ->middleware(['tenant-web', 'ensure.guard'])
    ->group(function () {

        // Login
        Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [AuthController::class, 'login'])->name('login.submit');

        // Recuperação de senha
        Route::get('/esqueci-senha', [AuthController::class, 'showForgotPasswordForm'])->name('forgot-password');
        Route::get('/resetar-senha/{token}', [AuthController::class, 'showResetPasswordForm'])->name('reset-password');
    });

/**
 * Rotas Autenticadas (com slug na URL)
 */
Route::prefix('workspace/{slug}/paciente')
    ->as('patient.')
    ->middleware([
        'web',
        'persist.tenant',
        'tenant.from.guard',
        'ensure.guard',
        'patient.auth'
    ])
    ->group(function () {

        // Logout
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('/logout', [AuthController::class, 'logout'])->name('logout.get');

        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Agendamentos
        Route::get('/agendamentos', [AppointmentController::class, 'index'])->name('appointments.index');
        Route::get('/agendamentos/criar', [AppointmentController::class, 'create'])->name('appointments.create');
        Route::post('/agendamentos', [AppointmentController::class, 'store'])->name('appointments.store');
        Route::get('/agendamentos/{id}/editar', [AppointmentController::class, 'edit'])->name('appointments.edit');
        Route::put('/agendamentos/{id}', [AppointmentController::class, 'update'])->name('appointments.update');
        Route::post('/agendamentos/{id}/cancelar', [AppointmentController::class, 'cancel'])->name('appointments.cancel');

        // Notificações
        Route::get('/notificacoes', [NotificationController::class, 'index'])->name('notifications.index');

        // Perfil
        Route::get('/perfil', [ProfileController::class, 'index'])->name('profile.index');
        Route::post('/perfil', [ProfileController::class, 'update'])->name('profile.update');
    });

