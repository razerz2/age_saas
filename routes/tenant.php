<?php

use Illuminate\Support\Facades\Route;

// Auth
use App\Http\Controllers\Tenant\Auth\LoginController;

// Public
use App\Http\Controllers\Tenant\PublicPatientController;
use App\Http\Controllers\Tenant\PublicPatientRegisterController;
use App\Http\Controllers\Tenant\PublicAppointmentController;
use App\Http\Controllers\Tenant\PublicFormController;

// Dashboard
use App\Http\Controllers\Tenant\DashboardController;

// Users & Doctors
use App\Http\Controllers\Tenant\UserController;
use App\Http\Controllers\Tenant\DoctorController;
use App\Http\Controllers\Tenant\UserDoctorPermissionController;
use App\Http\Controllers\Tenant\ProfileController;

// Medical
use App\Http\Controllers\Tenant\MedicalSpecialtyController;
use App\Http\Controllers\Tenant\PatientController;

// Calendar / Agenda
use App\Http\Controllers\Tenant\CalendarController;
use App\Http\Controllers\Tenant\BusinessHourController;
use App\Http\Controllers\Tenant\AppointmentTypeController;
use App\Http\Controllers\Tenant\AppointmentController;
use App\Http\Controllers\Tenant\DoctorSettingsController;

// Forms
use App\Http\Controllers\Tenant\FormController;

// Responses
use App\Http\Controllers\Tenant\FormResponseController;

// Integrations
use App\Http\Controllers\Tenant\IntegrationController;
use App\Http\Controllers\Tenant\OAuthAccountController;
use App\Http\Controllers\Tenant\Integrations\GoogleCalendarController;
use App\Http\Controllers\Tenant\Integrations\AppleCalendarController;

// Calendar Sync
use App\Http\Controllers\Tenant\CalendarSyncStateController;

// Notifications
use App\Http\Controllers\Tenant\NotificationController;

// Settings
use App\Http\Controllers\Tenant\SettingsController;

// Recurring Appointments
use App\Http\Controllers\Tenant\RecurringAppointmentController;

// Online Appointments
use App\Http\Controllers\Tenant\OnlineAppointmentController;

// Medical Appointments
use App\Http\Controllers\Tenant\MedicalAppointmentController;


/**
 * =====================================================================
 * ROTAS PÚBLICAS DO TENANT (/customer/{slug}/agendamento/*)
 * =====================================================================
 */
Route::prefix('customer/{slug}')
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
            Route::get('/doctors/{doctorId}/business-hours', [PublicAppointmentController::class, 'getBusinessHoursByDoctor'])->name('api.business-hours');
        });

        // Formulários públicos
        Route::get('/formulario/{form}/responder', [PublicFormController::class, 'create'])->name('form.response.create');
        Route::post('/formulario/{form}/responder', [PublicFormController::class, 'store'])->name('form.response.store');
        Route::get('/formulario/{form}/resposta/{response}/sucesso', [PublicFormController::class, 'success'])->name('form.response.success');
    });

/**
 * =====================================================================
 * LOGIN DO TENANT (/customer/{slug}/login)
 * =====================================================================
 */
Route::prefix('customer/{slug}')
    ->as('tenant.')
    ->middleware(['tenant-web'])
    ->group(function () {

        Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
        Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
        
        Route::get('/two-factor-challenge', [\App\Http\Controllers\Tenant\Auth\TwoFactorChallengeController::class, 'create'])->name('two-factor.challenge');
        Route::post('/two-factor-challenge', [\App\Http\Controllers\Tenant\Auth\TwoFactorChallengeController::class, 'store']);
        Route::post('/two-factor-challenge/resend', [\App\Http\Controllers\Tenant\Auth\TwoFactorChallengeController::class, 'resend'])->name('two-factor.challenge.resend');
    });

/**
 * =====================================================================
 * ROTAS AUTENTICADAS DO TENANT (/workspace/{slug}/*)
 * =====================================================================
 */
Route::prefix('workspace/{slug}')
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

        // Profile
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

        // Autenticação de dois fatores (2FA)
        Route::prefix('two-factor')->name('two-factor.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Tenant\TwoFactorController::class, 'index'])->name('index');
            Route::post('/generate-secret', [\App\Http\Controllers\Tenant\TwoFactorController::class, 'generateSecret'])->name('generate-secret');
            Route::post('/confirm', [\App\Http\Controllers\Tenant\TwoFactorController::class, 'confirm'])->name('confirm');
            Route::post('/set-method', [\App\Http\Controllers\Tenant\TwoFactorController::class, 'setMethod'])->name('set-method');
            Route::post('/activate-with-code', [\App\Http\Controllers\Tenant\TwoFactorController::class, 'activateWithCode'])->name('activate-with-code');
            Route::post('/confirm-with-code', [\App\Http\Controllers\Tenant\TwoFactorController::class, 'confirmWithCode'])->name('confirm-with-code');
            Route::post('/disable', [\App\Http\Controllers\Tenant\TwoFactorController::class, 'disable'])->name('disable');
            Route::post('/regenerate-recovery-codes', [\App\Http\Controllers\Tenant\TwoFactorController::class, 'regenerateRecoveryCodes'])->name('regenerate-recovery-codes');
        });


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
        Route::post('users/{id}/change-password', [UserController::class, 'changePassword'])->where('id', '[0-9]+')->name('users.change-password.store');
        
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
        // DOCTOR SETTINGS (Página única para médico ou usuário com 1 médico)
        // =====================================================================
        Route::get('doctor-settings', [DoctorSettingsController::class, 'index'])
            ->name('doctor-settings.index');
        Route::put('doctor-settings/calendar', [DoctorSettingsController::class, 'updateCalendar'])
            ->name('doctor-settings.update-calendar');
        Route::post('doctor-settings/business-hour', [DoctorSettingsController::class, 'storeBusinessHour'])
            ->name('doctor-settings.store-business-hour');
        Route::put('doctor-settings/business-hour/{id}', [DoctorSettingsController::class, 'updateBusinessHour'])
            ->where('id', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}')
            ->name('doctor-settings.update-business-hour');
        Route::delete('doctor-settings/business-hour/{id}', [DoctorSettingsController::class, 'destroyBusinessHour'])
            ->where('id', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}')
            ->name('doctor-settings.destroy-business-hour');
        Route::post('doctor-settings/appointment-type', [DoctorSettingsController::class, 'storeAppointmentType'])
            ->name('doctor-settings.store-appointment-type');
        Route::put('doctor-settings/appointment-type/{id}', [DoctorSettingsController::class, 'updateAppointmentType'])
            ->where('id', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}')
            ->name('doctor-settings.update-appointment-type');
        Route::delete('doctor-settings/appointment-type/{id}', [DoctorSettingsController::class, 'destroyAppointmentType'])
            ->where('id', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}')
            ->name('doctor-settings.destroy-appointment-type');

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

        // =====================================================================
        // ONLINE APPOINTMENTS
        // =====================================================================
        Route::prefix('appointments/online')
            ->name('online-appointments.')
            ->middleware(['module.access:online_appointments'])
            ->group(function () {
                Route::get('/', [\App\Http\Controllers\Tenant\OnlineAppointmentController::class, 'index'])
                    ->name('index');
                
                Route::get('/{appointment}', [\App\Http\Controllers\Tenant\OnlineAppointmentController::class, 'show'])
                    ->where('appointment', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}')
                    ->name('show');
                
                Route::post('/{appointment}/save', [\App\Http\Controllers\Tenant\OnlineAppointmentController::class, 'save'])
                    ->where('appointment', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}')
                    ->name('save');
                
                Route::post('/{appointment}/send-email', [\App\Http\Controllers\Tenant\OnlineAppointmentController::class, 'sendEmail'])
                    ->where('appointment', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}')
                    ->name('send-email');
                
                Route::post('/{appointment}/send-whatsapp', [\App\Http\Controllers\Tenant\OnlineAppointmentController::class, 'sendWhatsapp'])
                    ->where('appointment', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}')
                    ->name('send-whatsapp');
            });

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
        Route::get('api/doctors/{doctorId}/business-hours', [AppointmentController::class, 'getBusinessHoursByDoctor'])
            ->where('doctorId', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}')
            ->name('appointments.api.business-hours');


        // =====================================================================
        // RECURRING APPOINTMENTS
        // =====================================================================
        Route::get('agendamentos/recorrentes', [RecurringAppointmentController::class, 'index'])
            ->name('recurring-appointments.index');
        Route::get('agendamentos/recorrentes/criar', [RecurringAppointmentController::class, 'create'])
            ->name('recurring-appointments.create');
        Route::post('agendamentos/recorrentes', [RecurringAppointmentController::class, 'store'])
            ->name('recurring-appointments.store');
        Route::get('agendamentos/recorrentes/{id}', [RecurringAppointmentController::class, 'show'])
            ->where('id', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}')
            ->name('recurring-appointments.show');
        Route::get('agendamentos/recorrentes/{id}/editar', [RecurringAppointmentController::class, 'edit'])
            ->where('id', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}')
            ->name('recurring-appointments.edit');
        Route::put('agendamentos/recorrentes/{id}', [RecurringAppointmentController::class, 'update'])
            ->where('id', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}')
            ->name('recurring-appointments.update');
        Route::get('agendamentos/recorrentes/{id}/cancelar', [RecurringAppointmentController::class, 'cancel'])
            ->where('id', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}')
            ->name('recurring-appointments.cancel');
        Route::delete('agendamentos/recorrentes/{id}', [RecurringAppointmentController::class, 'destroy'])
            ->where('id', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}')
            ->name('recurring-appointments.destroy');

        // API endpoints para agendamentos recorrentes
        Route::get('api/doctors/{doctorId}/business-hours', [RecurringAppointmentController::class, 'getBusinessHoursByDoctor'])
            ->where('doctorId', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}')
            ->name('recurring-appointments.api.business-hours');
        Route::get('api/doctors/{doctorId}/available-slots-recurring', [RecurringAppointmentController::class, 'getAvailableSlotsForRecurring'])
            ->where('doctorId', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}')
            ->name('recurring-appointments.api.available-slots');


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
        // IMPORTANTE: Rotas específicas (Google Calendar) devem vir ANTES do resource
        // para evitar que "google" seja interpretado como um ID UUID
        
        // =====================================================================
        // GOOGLE CALENDAR INTEGRATION
        // =====================================================================
        Route::prefix('integrations/google')
            ->name('integrations.google.')
            ->middleware(['module.access:integrations'])
            ->group(function () {
                // Página principal
                Route::get('/', [GoogleCalendarController::class, 'index'])->name('index');
                
                // Conectar conta Google do médico
                Route::get('/{doctor}/connect', [GoogleCalendarController::class, 'connect'])
                    ->where('doctor', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}')
                    ->name('connect');
                
                // Desconectar
                Route::delete('/{doctor}/disconnect', [GoogleCalendarController::class, 'disconnect'])
                    ->where('doctor', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}')
                    ->name('disconnect');
                
                // Status JSON
                Route::get('/{doctor}/status', [GoogleCalendarController::class, 'status'])
                    ->where('doctor', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}')
                    ->name('status');
                
                // API: Eventos do Google Calendar para FullCalendar
                Route::get('/api/{doctor}/events', [GoogleCalendarController::class, 'getEvents'])
                    ->where('doctor', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}')
                    ->name('api.events');
            });

        // =====================================================================
        // APPLE CALENDAR INTEGRATION
        // =====================================================================
        Route::prefix('integrations/apple')
            ->name('integrations.apple.')
            ->middleware(['module.access:integrations'])
            ->group(function () {
                // Página principal
                Route::get('/', [AppleCalendarController::class, 'index'])->name('index');
                
                // Mostrar formulário de conexão
                Route::get('/{doctor}/connect', [AppleCalendarController::class, 'showConnectForm'])
                    ->where('doctor', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}')
                    ->name('connect.form');
                
                // Conectar conta Apple do médico
                Route::post('/{doctor}/connect', [AppleCalendarController::class, 'connect'])
                    ->where('doctor', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}')
                    ->name('connect');
                
                // Desconectar
                Route::delete('/{doctor}/disconnect', [AppleCalendarController::class, 'disconnect'])
                    ->where('doctor', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}')
                    ->name('disconnect');
                
                // Status JSON
                Route::get('/{doctor}/status', [AppleCalendarController::class, 'status'])
                    ->where('doctor', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}')
                    ->name('status');
                
                // API: Eventos do Apple Calendar para FullCalendar
                Route::get('/api/{doctor}/events', [AppleCalendarController::class, 'getEvents'])
                    ->where('doctor', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}')
                    ->name('api.events');
            });

        // Resource de integrations (deve vir DEPOIS das rotas específicas)
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
        // MEDICAL APPOINTMENTS (Atendimento Médico)
        // =====================================================================
        Route::middleware(['module.access:medical_appointments'])->group(function () {
            // Tela inicial: escolher dia e iniciar atendimento
            Route::get('/atendimento', [MedicalAppointmentController::class, 'index'])
                ->name('medical-appointments.index');

            Route::post('/atendimento/iniciar', [MedicalAppointmentController::class, 'start'])
                ->name('medical-appointments.start');

            // Tela de atendimento em si
            Route::get('/atendimento/dia/{date}', [MedicalAppointmentController::class, 'session'])
                ->name('medical-appointments.session');

            // Detalhes do agendamento (AJAX)
            Route::get('/atendimento/{appointment}/detalhes', [MedicalAppointmentController::class, 'details'])
                ->where('appointment', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}')
                ->name('medical-appointments.details');

            // Alterar status do atendimento
            Route::post('/atendimento/{appointment}/status', [MedicalAppointmentController::class, 'updateStatus'])
                ->where('appointment', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}')
                ->name('medical-appointments.update-status');

            // Concluir e ir para o próximo
            Route::post('/atendimento/{appointment}/concluir', [MedicalAppointmentController::class, 'complete'])
                ->where('appointment', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}')
                ->name('medical-appointments.complete');

            // Buscar resposta do formulário
            Route::get('/atendimento/{appointment}/formulario-resposta', [MedicalAppointmentController::class, 'getFormResponse'])
                ->where('appointment', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}')
                ->name('medical-appointments.form-response');
        });

        // =====================================================================
        // PUBLIC BOOKING LINK (acessível a todos os usuários autenticados)
        // =====================================================================
        Route::get('agendamento-publico', [SettingsController::class, 'publicBookingLink'])->name('public-booking-link.index');

        // =====================================================================
        // SETTINGS
        // =====================================================================
        Route::middleware(['module.access:settings'])->group(function () {
            Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
            Route::post('settings/general', [SettingsController::class, 'updateGeneral'])->name('settings.update.general');
            Route::post('settings/appointments', [SettingsController::class, 'updateAppointments'])->name('settings.update.appointments');
            Route::post('settings/calendar', [SettingsController::class, 'updateCalendar'])->name('settings.update.calendar');
            Route::post('settings/notifications', [SettingsController::class, 'updateNotifications'])->name('settings.update.notifications');
            Route::post('settings/integrations', [SettingsController::class, 'updateIntegrations'])->name('settings.update.integrations');
            Route::post('settings/user-defaults', [SettingsController::class, 'updateUserDefaults'])->name('settings.update.user-defaults');
            Route::post('settings/professionals', [SettingsController::class, 'updateProfessionals'])->name('settings.update.professionals');
            Route::post('settings/appearance', [SettingsController::class, 'updateAppearance'])->name('settings.update.appearance');
        });

        // =====================================================================
        // REPORTS
        // =====================================================================
        require __DIR__ . '/tenant/reports.php';
    });
