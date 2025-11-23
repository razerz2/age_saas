<?php

use Illuminate\Support\Facades\Route;

// Auth
use App\Http\Controllers\Tenant\Auth\LoginController;

// Dashboard
use App\Http\Controllers\Tenant\DashboardController;

// Users & Doctors
use App\Http\Controllers\Tenant\UserController;
use App\Http\Controllers\Tenant\DoctorController;

// Medical
use App\Http\Controllers\Tenant\MedicalSpecialtyController;
use App\Http\Controllers\Tenant\PatientController;

// Calendar / Agenda
use App\Http\Controllers\Tenant\CalendarController;
use App\Http\Controllers\Tenant\BusinessHourController;
use App\Http\Controllers\Tenant\AppointmentTypeController;
use App\Http\Controllers\Tenant\AppointmentController;

// Forms
use App\Http\Controllers\Tenant\FormController;

// Responses
use App\Http\Controllers\Tenant\FormResponseController;

// Integrations
use App\Http\Controllers\Tenant\IntegrationController;
use App\Http\Controllers\Tenant\OAuthAccountController;

// Calendar Sync
use App\Http\Controllers\Tenant\CalendarSyncStateController;


/**
 * =====================================================================
 * LOGIN DO TENANT (/t/{tenant}/login)
 * =====================================================================
 */
Route::prefix('t/{tenant}')
    ->as('tenant.')
    ->middleware(['tenant-web'])
    ->group(function () {

        Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
        Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    });

/**
 * =====================================================================
 * ROTAS AUTENTICADAS DO TENANT (/tenant/*)
 * =====================================================================
 */
Route::prefix('tenant')
    ->as('tenant.')
    ->middleware([
        'web',
        'persist.tenant',
        'tenant.from.guard',
        'ensure.guard',
        'tenant.auth',
    ])
    ->group(function () {

        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->name('dashboard');


        // =====================================================================
        // USERS — sobrescrevendo model binding (show precisa de id)
        // =====================================================================
        Route::get('users/{id}', [UserController::class, 'show'])->name('users.show');
        Route::get('users/{id}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('users/{id}', [UserController::class, 'update'])->name('users.update');
        Route::delete('users/{id}', [UserController::class, 'destroy'])->name('users.destroy');
        // Mostrar o formulário para alterar a senha
        Route::get('users/{id}/change-password', [UserController::class, 'showChangePasswordForm'])->name('users.change-password');
        // Alterar a senha
        Route::post('users/{id}/change-password', [UserController::class, 'changePassword'])->name('users.change-password');

        Route::resource('users', UserController::class)->except(['show', 'edit', 'update', 'destroy']);


        // =====================================================================
        // DOCTORS
        // =====================================================================
        Route::get('doctors/{id}', [DoctorController::class, 'show'])->name('doctors.show');
        Route::get('doctors/{id}/edit', [DoctorController::class, 'edit'])->name('doctors.edit');
        Route::put('doctors/{id}', [DoctorController::class, 'update'])->name('doctors.update');
        Route::delete('doctors/{id}', [DoctorController::class, 'destroy'])->name('doctors.destroy');

        Route::resource('doctors', DoctorController::class)->except(['show', 'edit', 'update', 'destroy']);


        // =====================================================================
        // SPECIALTIES
        // =====================================================================
        Route::get('specialties/{id}', [MedicalSpecialtyController::class, 'show'])->name('specialties.show');
        Route::get('specialties/{id}/edit', [MedicalSpecialtyController::class, 'edit'])->name('specialties.edit');
        Route::put('specialties/{id}', [MedicalSpecialtyController::class, 'update'])->name('specialties.update');
        Route::delete('specialties/{id}', [MedicalSpecialtyController::class, 'destroy'])->name('specialties.destroy');

        Route::resource('specialties', MedicalSpecialtyController::class)->except(['show', 'edit', 'update', 'destroy']);


        // =====================================================================
        // PATIENTS
        // =====================================================================
        Route::get('patients/{id}', [PatientController::class, 'show'])->name('patients.show');
        Route::get('patients/{id}/edit', [PatientController::class, 'edit'])->name('patients.edit');
        Route::put('patients/{id}', [PatientController::class, 'update'])->name('patients.update');
        Route::delete('patients/{id}', [PatientController::class, 'destroy'])->name('patients.destroy');

        Route::resource('patients', PatientController::class)->except(['show', 'edit', 'update', 'destroy']);


        // =====================================================================
        // CALENDARS
        // =====================================================================
        Route::get('calendars/{id}', [CalendarController::class, 'show'])->name('calendars.show');
        Route::get('calendars/{id}/edit', [CalendarController::class, 'edit'])->name('calendars.edit');
        Route::put('calendars/{id}', [CalendarController::class, 'update'])->name('calendars.update');
        Route::delete('calendars/{id}', [CalendarController::class, 'destroy'])->name('calendars.destroy');

        Route::resource('calendars', CalendarController::class)->except(['show', 'edit', 'update', 'destroy']);

        Route::get('calendars/{id}/events', [AppointmentController::class, 'events'])
            ->name('calendars.events');


        // =====================================================================
        // BUSINESS HOURS
        // =====================================================================
        Route::resource('business-hours', BusinessHourController::class);


        // =====================================================================
        // APPOINTMENT TYPES
        // =====================================================================
        Route::resource('appointment-types', AppointmentTypeController::class);


        // =====================================================================
        // APPOINTMENTS
        // =====================================================================
        Route::resource('appointments', AppointmentController::class);


        // =====================================================================
        // FORMS (muitos binds automáticos)
        // =====================================================================
        Route::resource('forms', FormController::class);

        Route::post('forms/{id}/sections', [FormController::class, 'addSection'])->name('forms.sections.store');
        Route::put('sections/{id}', [FormController::class, 'updateSection'])->name('sections.update');
        Route::delete('sections/{id}', [FormController::class, 'deleteSection'])->name('sections.destroy');

        Route::post('forms/{id}/questions', [FormController::class, 'addQuestion'])->name('forms.questions.store');
        Route::put('questions/{id}', [FormController::class, 'updateQuestion'])->name('questions.update');
        Route::delete('questions/{id}', [FormController::class, 'deleteQuestion'])->name('questions.destroy');

        Route::post('questions/{id}/options', [FormController::class, 'addOption'])->name('questions.options.store');
        Route::put('options/{id}', [FormController::class, 'updateOption'])->name('options.update');
        Route::delete('options/{id}', [FormController::class, 'deleteOption'])->name('options.destroy');


        // =====================================================================
        // RESPONSES
        // =====================================================================
        Route::resource('responses', FormResponseController::class);

        Route::post('responses/{id}/answer', [FormResponseController::class, 'storeAnswer'])->name('responses.answer.store');
        Route::put('answers/{id}', [FormResponseController::class, 'updateAnswer'])->name('responses.answer.update');


        // =====================================================================
        // INTEGRATIONS
        // =====================================================================
        Route::resource('integrations', IntegrationController::class);


        // =====================================================================
        // OAUTH ACCOUNTS
        // =====================================================================
        Route::resource('oauth-accounts', OAuthAccountController::class);


        // =====================================================================
        // CALENDAR SYNC
        // =====================================================================
        Route::resource('calendar-sync', CalendarSyncStateController::class);
    });
