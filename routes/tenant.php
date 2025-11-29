<?php

use Illuminate\Support\Facades\Route;

// Auth
use App\Http\Controllers\Tenant\Auth\LoginController;

// Public
use App\Http\Controllers\Tenant\PublicPatientController;
use App\Http\Controllers\Tenant\PublicPatientRegisterController;
use App\Http\Controllers\Tenant\PublicAppointmentController;

// Dashboard
use App\Http\Controllers\Tenant\DashboardController;

// Users & Doctors
use App\Http\Controllers\Tenant\UserController;
use App\Http\Controllers\Tenant\DoctorController;
use App\Http\Controllers\Tenant\UserDoctorPermissionController;

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

// Notifications
use App\Http\Controllers\Tenant\NotificationController;

// Settings
use App\Http\Controllers\Tenant\SettingsController;


/**
 * =====================================================================
 * ROTAS PÚBLICAS DO TENANT (/t/{tenant}/agendamento/*)
 * =====================================================================
 */
Route::prefix('t/{tenant}')
    ->as('public.')
    ->middleware(['tenant-web'])
    ->group(function () {

        // Identificação do paciente
        Route::get('/agendamento/identificar', [PublicPatientController::class, 'showIdentify'])->name('patient.identify');
        Route::post('/agendamento/identificar', [PublicPatientController::class, 'identify'])->name('patient.identify.submit');

        // Cadastro de paciente
        Route::get('/agendamento/cadastro', [PublicPatientRegisterController::class, 'showRegister'])->name('patient.register');
        Route::post('/agendamento/cadastro', [PublicPatientRegisterController::class, 'register'])->name('patient.register.submit');

        // Agendamento
        Route::get('/agendamento/criar', [PublicAppointmentController::class, 'create'])->name('appointment.create');
        Route::post('/agendamento/criar', [PublicAppointmentController::class, 'store'])->name('appointment.store');
        Route::get('/agendamento/sucesso/{appointment_id?}', [PublicAppointmentController::class, 'success'])->name('appointment.success');
        Route::get('/agendamento/{appointment_id}', [PublicAppointmentController::class, 'show'])->name('appointment.show');

        // APIs públicas para agendamento
        Route::prefix('agendamento/api')->group(function () {
            Route::get('/doctors/{doctorId}/calendars', [PublicAppointmentController::class, 'getCalendarsByDoctor'])->name('api.calendars');
            Route::get('/doctors/{doctorId}/appointment-types', [PublicAppointmentController::class, 'getAppointmentTypesByDoctor'])->name('api.appointment-types');
            Route::get('/doctors/{doctorId}/specialties', [PublicAppointmentController::class, 'getSpecialtiesByDoctor'])->name('api.specialties');
            Route::get('/doctors/{doctorId}/available-slots', [PublicAppointmentController::class, 'getAvailableSlots'])->name('api.available-slots');
        });
    });

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
        // IMPORTANTE: Resource deve vir ANTES das rotas com {id} para evitar
        // que rotas como "create" sejam capturadas como ID
        // =====================================================================
        Route::resource('users', UserController::class)->except(['show', 'edit', 'update', 'destroy']);
        
        // Rotas específicas com {id} devem vir DEPOIS do resource
        // Usando where para garantir que {id} aceite apenas números
        Route::get('users/{id}', [UserController::class, 'show'])->where('id', '[0-9]+')->name('users.show');
        Route::get('users/{id}/edit', [UserController::class, 'edit'])->where('id', '[0-9]+')->name('users.edit');
        Route::put('users/{id}', [UserController::class, 'update'])->where('id', '[0-9]+')->name('users.update');
        Route::delete('users/{id}', [UserController::class, 'destroy'])->where('id', '[0-9]+')->name('users.destroy');
        // Mostrar o formulário para alterar a senha
        Route::get('users/{id}/change-password', [UserController::class, 'showChangePasswordForm'])->where('id', '[0-9]+')->name('users.change-password');
        // Alterar a senha
        Route::post('users/{id}/change-password', [UserController::class, 'changePassword'])->where('id', '[0-9]+')->name('users.change-password');
        
        // Gerenciar permissões de médicos para usuários
        Route::get('users/{id}/doctor-permissions', [UserDoctorPermissionController::class, 'index'])->where('id', '[0-9]+')->name('users.doctor-permissions');
        Route::put('users/{id}/doctor-permissions', [UserDoctorPermissionController::class, 'update'])->where('id', '[0-9]+')->name('users.doctor-permissions.update');
        Route::get('users/{id}/allowed-doctors', [UserDoctorPermissionController::class, 'getAllowedDoctors'])->where('id', '[0-9]+')->name('users.allowed-doctors');


        // =====================================================================
        // DOCTORS
        // =====================================================================
        Route::resource('doctors', DoctorController::class)->except(['show', 'edit', 'update', 'destroy']);
        
        Route::get('doctors/{id}', [DoctorController::class, 'show'])
            ->where('id', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}')
            ->name('doctors.show');
        Route::get('doctors/{id}/edit', [DoctorController::class, 'edit'])
            ->where('id', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}')
            ->name('doctors.edit');
        Route::put('doctors/{id}', [DoctorController::class, 'update'])
            ->where('id', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}')
            ->name('doctors.update');
        Route::delete('doctors/{id}', [DoctorController::class, 'destroy'])
            ->where('id', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}')
            ->name('doctors.destroy');


        // =====================================================================
        // SPECIALTIES
        // =====================================================================
        Route::resource('specialties', MedicalSpecialtyController::class)->except(['show', 'edit', 'update', 'destroy']);
        
        Route::get('specialties/{id}', [MedicalSpecialtyController::class, 'show'])
            ->where('id', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}')
            ->name('specialties.show');
        Route::get('specialties/{id}/edit', [MedicalSpecialtyController::class, 'edit'])
            ->where('id', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}')
            ->name('specialties.edit');
        Route::put('specialties/{id}', [MedicalSpecialtyController::class, 'update'])
            ->where('id', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}')
            ->name('specialties.update');
        Route::delete('specialties/{id}', [MedicalSpecialtyController::class, 'destroy'])
            ->where('id', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}')
            ->name('specialties.destroy');


        // =====================================================================
        // PATIENTS
        // =====================================================================
        Route::resource('patients', PatientController::class)->except(['show', 'edit', 'update', 'destroy']);
        
        Route::get('patients/{id}', [PatientController::class, 'show'])
            ->where('id', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}')
            ->name('patients.show');
        Route::get('patients/{id}/edit', [PatientController::class, 'edit'])
            ->where('id', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}')
            ->name('patients.edit');
        Route::put('patients/{id}', [PatientController::class, 'update'])
            ->where('id', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}')
            ->name('patients.update');
        Route::delete('patients/{id}', [PatientController::class, 'destroy'])
            ->where('id', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}')
            ->name('patients.destroy');

        // Rotas para gerenciar login do paciente
        Route::get('patients/{id}/login', [PatientController::class, 'showLoginForm'])
            ->where('id', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}')
            ->name('patients.login.form');
        Route::post('patients/{id}/login', [PatientController::class, 'storeLogin'])
            ->where('id', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}')
            ->name('patients.login.store');
        Route::post('patients/{id}/login/toggle', [PatientController::class, 'toggleLoginStatus'])
            ->where('id', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}')
            ->name('patients.login.toggle');
        Route::delete('patients/{id}/login', [PatientController::class, 'destroyLogin'])
            ->where('id', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}')
            ->name('patients.login.destroy');
        Route::get('patients/{id}/login/show', [PatientController::class, 'showLogin'])
            ->where('id', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}')
            ->name('patients.login.show');
        Route::post('patients/{id}/login/send-email', [PatientController::class, 'sendLoginByEmail'])
            ->where('id', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}')
            ->name('patients.login.send-email');
        Route::post('patients/{id}/login/send-whatsapp', [PatientController::class, 'sendLoginByWhatsApp'])
            ->where('id', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}')
            ->name('patients.login.send-whatsapp');


        // =====================================================================
        // CALENDARS
        // =====================================================================
        Route::resource('calendars', CalendarController::class)->except(['show', 'edit', 'update', 'destroy']);

        Route::get('calendars/{id}', [CalendarController::class, 'show'])
            ->where('id', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}')
            ->name('calendars.show');
        Route::get('calendars/{id}/edit', [CalendarController::class, 'edit'])
            ->where('id', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}')
            ->name('calendars.edit');
        Route::put('calendars/{id}', [CalendarController::class, 'update'])
            ->where('id', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}')
            ->name('calendars.update');
        Route::delete('calendars/{id}', [CalendarController::class, 'destroy'])
            ->where('id', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}')
            ->name('calendars.destroy');

        Route::get('calendars/events', [CalendarController::class, 'eventsRedirect'])
            ->name('calendars.events.redirect');

        Route::get('calendars/{id}/events', [AppointmentController::class, 'events'])
            ->where('id', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}')
            ->name('calendars.events');


        // =====================================================================
        // BUSINESS HOURS
        // =====================================================================
        Route::resource('business-hours', BusinessHourController::class)->except(['show', 'edit', 'update', 'destroy']);
        
        Route::get('business-hours/{id}', [BusinessHourController::class, 'show'])
            ->where('id', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}')
            ->name('business-hours.show');
        Route::get('business-hours/{id}/edit', [BusinessHourController::class, 'edit'])
            ->where('id', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}')
            ->name('business-hours.edit');
        Route::put('business-hours/{id}', [BusinessHourController::class, 'update'])
            ->where('id', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}')
            ->name('business-hours.update');
        Route::delete('business-hours/{id}', [BusinessHourController::class, 'destroy'])
            ->where('id', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}')
            ->name('business-hours.destroy');


        // =====================================================================
        // APPOINTMENT TYPES
        // =====================================================================
        Route::resource('appointment-types', AppointmentTypeController::class)->except(['show', 'edit', 'update', 'destroy']);
        
        Route::get('appointment-types/{id}', [AppointmentTypeController::class, 'show'])
            ->where('id', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}')
            ->name('appointment-types.show');
        Route::get('appointment-types/{id}/edit', [AppointmentTypeController::class, 'edit'])
            ->where('id', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}')
            ->name('appointment-types.edit');
        Route::put('appointment-types/{id}', [AppointmentTypeController::class, 'update'])
            ->where('id', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}')
            ->name('appointment-types.update');
        Route::delete('appointment-types/{id}', [AppointmentTypeController::class, 'destroy'])
            ->where('id', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}')
            ->name('appointment-types.destroy');


        // =====================================================================
        // APPOINTMENTS
        // =====================================================================
        Route::resource('appointments', AppointmentController::class)->except(['show', 'edit', 'update', 'destroy']);
        
        Route::get('appointments/{id}', [AppointmentController::class, 'show'])
            ->where('id', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}')
            ->name('appointments.show');
        Route::get('appointments/{id}/edit', [AppointmentController::class, 'edit'])
            ->where('id', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}')
            ->name('appointments.edit');
        Route::put('appointments/{id}', [AppointmentController::class, 'update'])
            ->where('id', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}')
            ->name('appointments.update');
        Route::delete('appointments/{id}', [AppointmentController::class, 'destroy'])
            ->where('id', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}')
            ->name('appointments.destroy');

        // API endpoints para agendamentos
        Route::get('api/doctors/{doctorId}/calendars', [AppointmentController::class, 'getCalendarsByDoctor'])
            ->where('doctorId', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}')
            ->name('appointments.api.calendars');
        Route::get('api/doctors/{doctorId}/appointment-types', [AppointmentController::class, 'getAppointmentTypesByDoctor'])
            ->where('doctorId', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}')
            ->name('appointments.api.appointment-types');
        Route::get('api/doctors/{doctorId}/specialties', [AppointmentController::class, 'getSpecialtiesByDoctor'])
            ->where('doctorId', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}')
            ->name('appointments.api.specialties');
        Route::get('api/doctors/{doctorId}/available-slots', [AppointmentController::class, 'getAvailableSlots'])
            ->where('doctorId', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}')
            ->name('appointments.api.available-slots');


        // =====================================================================
        // FORMS (muitos binds automáticos)
        // =====================================================================
        Route::resource('forms', FormController::class);

        // Rota para construir o formulário (seções, perguntas, opções)
        Route::get('forms/{id}/builder', [FormController::class, 'builder'])
            ->where('id', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}')
            ->name('forms.builder');

        // Rota para visualizar o formulário construído
        Route::get('forms/{id}/preview', [FormController::class, 'preview'])
            ->where('id', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}')
            ->name('forms.preview');

        // Rota para limpar apenas o conteúdo do formulário (manter formulário)
        Route::delete('forms/{id}/clear-content', [FormController::class, 'clearContent'])
            ->where('id', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}')
            ->name('forms.clear-content');

        // Rotas de forms com {id} - usando UUID, então não precisa where numérico
        // Mas forms usa UUID, então vamos usar uma regex para UUID
        Route::post('forms/{id}/sections', [FormController::class, 'addSection'])
            ->where('id', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}')
            ->name('forms.sections.store');
        Route::put('sections/{id}', [FormController::class, 'updateSection'])
            ->where('id', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}')
            ->name('sections.update');
        Route::delete('sections/{id}', [FormController::class, 'deleteSection'])
            ->where('id', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}')
            ->name('sections.destroy');

        Route::post('forms/{id}/questions', [FormController::class, 'addQuestion'])
            ->where('id', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}')
            ->name('forms.questions.store');
        Route::put('questions/{id}', [FormController::class, 'updateQuestion'])
            ->where('id', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}')
            ->name('questions.update');
        Route::delete('questions/{id}', [FormController::class, 'deleteQuestion'])
            ->where('id', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}')
            ->name('questions.destroy');

        Route::post('questions/{id}/options', [FormController::class, 'addOption'])
            ->where('id', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}')
            ->name('questions.options.store');
        Route::put('options/{id}', [FormController::class, 'updateOption'])
            ->where('id', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}')
            ->name('options.update');
        Route::delete('options/{id}', [FormController::class, 'deleteOption'])
            ->where('id', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}')
            ->name('options.destroy');

        // Rota para buscar especialidades de um médico
        Route::get('doctors/{doctorId}/specialties', [FormController::class, 'getSpecialtiesByDoctor'])
            ->where('doctorId', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}')
            ->name('forms.doctors.specialties');


        // =====================================================================
        // RESPONSES
        // =====================================================================
        // Rotas customizadas para criar resposta a partir de um formulário
        Route::get('forms/{form_id}/responses/create', [FormResponseController::class, 'create'])
            ->where('form_id', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}')
            ->name('responses.create');
        Route::post('forms/{form_id}/responses', [FormResponseController::class, 'store'])
            ->where('form_id', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}')
            ->name('responses.store');

        // Rotas padrão para outras operações
        Route::get('responses', [FormResponseController::class, 'index'])->name('responses.index');
        Route::get('responses/{id}', [FormResponseController::class, 'show'])
            ->where('id', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}')
            ->name('responses.show');
        Route::get('responses/{id}/edit', [FormResponseController::class, 'edit'])
            ->where('id', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}')
            ->name('responses.edit');
        Route::put('responses/{id}', [FormResponseController::class, 'update'])
            ->where('id', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}')
            ->name('responses.update');
        Route::delete('responses/{id}', [FormResponseController::class, 'destroy'])
            ->where('id', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}')
            ->name('responses.destroy');

        // Responses também usa UUID
        Route::post('responses/{id}/answer', [FormResponseController::class, 'storeAnswer'])
            ->where('id', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}')
            ->name('responses.answer.store');
        Route::put('answers/{id}', [FormResponseController::class, 'updateAnswer'])
            ->where('id', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}')
            ->name('responses.answer.update');


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

        // =====================================================================
        // NOTIFICATIONS
        // =====================================================================
        Route::get('notifications/json', [NotificationController::class, 'json'])->name('notifications.json');
        Route::post('notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
        Route::post('notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
        Route::resource('notifications', NotificationController::class)->only(['index', 'show']);

        // =====================================================================
        // SETTINGS
        // =====================================================================
        Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::post('settings/general', [SettingsController::class, 'updateGeneral'])->name('settings.update.general');
        Route::post('settings/appointments', [SettingsController::class, 'updateAppointments'])->name('settings.update.appointments');
        Route::post('settings/calendar', [SettingsController::class, 'updateCalendar'])->name('settings.update.calendar');
        Route::post('settings/notifications', [SettingsController::class, 'updateNotifications'])->name('settings.update.notifications');
        Route::post('settings/integrations', [SettingsController::class, 'updateIntegrations'])->name('settings.update.integrations');
    });
