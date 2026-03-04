<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Tenant\Reports\ReportController;
use App\Http\Controllers\Tenant\Reports\AppointmentReportController;
use App\Http\Controllers\Tenant\Reports\PatientReportController;
use App\Http\Controllers\Tenant\Reports\DoctorReportController;
use App\Http\Controllers\Tenant\Reports\RecurringReportController;
use App\Http\Controllers\Tenant\Reports\FormReportController;
use App\Http\Controllers\Tenant\Reports\PortalReportController;
use App\Http\Controllers\Tenant\Reports\NotificationReportController;

Route::prefix('reports')->middleware(['module.access:reports'])->group(function () {
    Route::get('/', [ReportController::class, 'index'])->name('reports.index');

    // Agendamentos
    Route::get('/appointments', [AppointmentReportController::class, 'index'])->name('reports.appointments');
    Route::get('/appointments/grid-data', [AppointmentReportController::class, 'gridData'])->name('reports.appointments.grid-data');
    Route::get('/appointments/export.xlsx', [AppointmentReportController::class, 'exportExcel'])->name('reports.appointments.export.xlsx');
    Route::get('/appointments/export.pdf', [AppointmentReportController::class, 'exportPdf'])->name('reports.appointments.export.pdf');

    // Pacientes
    Route::get('/patients', [PatientReportController::class, 'index'])->name('reports.patients');
    Route::get('/patients/grid-data', [PatientReportController::class, 'gridData'])->name('reports.patients.grid-data');
    Route::get('/patients/export.xlsx', [PatientReportController::class, 'exportExcel'])->name('reports.patients.export.xlsx');
    Route::get('/patients/export.pdf', [PatientReportController::class, 'exportPdf'])->name('reports.patients.export.pdf');

    // Medicos
    Route::get('/doctors', [DoctorReportController::class, 'index'])->name('reports.doctors');
    Route::get('/doctors/grid-data', [DoctorReportController::class, 'gridData'])->name('reports.doctors.grid-data');
    Route::get('/doctors/export.xlsx', [DoctorReportController::class, 'exportExcel'])->name('reports.doctors.export.xlsx');
    Route::get('/doctors/export.pdf', [DoctorReportController::class, 'exportPdf'])->name('reports.doctors.export.pdf');

    // Recorrencias
    Route::get('/recurring', [RecurringReportController::class, 'index'])->name('reports.recurring');
    Route::get('/recurring/grid-data', [RecurringReportController::class, 'gridData'])->name('reports.recurring.grid-data');
    Route::get('/recurring/export.xlsx', [RecurringReportController::class, 'exportExcel'])->name('reports.recurring.export.xlsx');
    Route::get('/recurring/export.pdf', [RecurringReportController::class, 'exportPdf'])->name('reports.recurring.export.pdf');

    // Formularios
    Route::get('/forms', [FormReportController::class, 'index'])->name('reports.forms');
    Route::get('/forms/grid-data', [FormReportController::class, 'gridData'])->name('reports.forms.grid-data');
    Route::get('/forms/export.xlsx', [FormReportController::class, 'exportExcel'])->name('reports.forms.export.xlsx');
    Route::get('/forms/export.pdf', [FormReportController::class, 'exportPdf'])->name('reports.forms.export.pdf');

    // Portal do paciente
    Route::get('/portal', [PortalReportController::class, 'index'])->name('reports.portal');
    Route::get('/portal/grid-data', [PortalReportController::class, 'gridData'])->name('reports.portal.grid-data');
    Route::get('/portal/export.xlsx', [PortalReportController::class, 'exportExcel'])->name('reports.portal.export.xlsx');
    Route::get('/portal/export.pdf', [PortalReportController::class, 'exportPdf'])->name('reports.portal.export.pdf');

    // Notificacoes
    Route::get('/notifications', [NotificationReportController::class, 'index'])->name('reports.notifications');
    Route::get('/notifications/grid-data', [NotificationReportController::class, 'gridData'])->name('reports.notifications.grid-data');
    Route::get('/notifications/export.xlsx', [NotificationReportController::class, 'exportExcel'])->name('reports.notifications.export.xlsx');
    Route::get('/notifications/export.pdf', [NotificationReportController::class, 'exportPdf'])->name('reports.notifications.export.pdf');
});
