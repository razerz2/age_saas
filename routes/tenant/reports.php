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
    Route::post('/appointments/data', [AppointmentReportController::class, 'data'])->name('reports.appointments.data');
    Route::get('/appointments/export/excel', [AppointmentReportController::class, 'exportExcel'])->name('reports.appointments.export.excel');
    Route::get('/appointments/export/pdf', [AppointmentReportController::class, 'exportPdf'])->name('reports.appointments.export.pdf');
    Route::get('/appointments/export/csv', [AppointmentReportController::class, 'exportCsv'])->name('reports.appointments.export.csv');

    // Pacientes
    Route::get('/patients', [PatientReportController::class, 'index'])->name('reports.patients');
    Route::post('/patients/data', [PatientReportController::class, 'data'])->name('reports.patients.data');
    Route::get('/patients/export/excel', [PatientReportController::class, 'exportExcel'])->name('reports.patients.export.excel');
    Route::get('/patients/export/pdf', [PatientReportController::class, 'exportPdf'])->name('reports.patients.export.pdf');
    Route::get('/patients/export/csv', [PatientReportController::class, 'exportCsv'])->name('reports.patients.export.csv');

    // Médicos
    Route::get('/doctors', [DoctorReportController::class, 'index'])->name('reports.doctors');
    Route::post('/doctors/data', [DoctorReportController::class, 'data'])->name('reports.doctors.data');
    Route::get('/doctors/export/excel', [DoctorReportController::class, 'exportExcel'])->name('reports.doctors.export.excel');
    Route::get('/doctors/export/pdf', [DoctorReportController::class, 'exportPdf'])->name('reports.doctors.export.pdf');
    Route::get('/doctors/export/csv', [DoctorReportController::class, 'exportCsv'])->name('reports.doctors.export.csv');

    // Recorrências
    Route::get('/recurring', [RecurringReportController::class, 'index'])->name('reports.recurring');
    Route::post('/recurring/data', [RecurringReportController::class, 'data'])->name('reports.recurring.data');
    Route::get('/recurring/export/excel', [RecurringReportController::class, 'exportExcel'])->name('reports.recurring.export.excel');
    Route::get('/recurring/export/pdf', [RecurringReportController::class, 'exportPdf'])->name('reports.recurring.export.pdf');
    Route::get('/recurring/export/csv', [RecurringReportController::class, 'exportCsv'])->name('reports.recurring.export.csv');

    // Formulários
    Route::get('/forms', [FormReportController::class, 'index'])->name('reports.forms');
    Route::post('/forms/data', [FormReportController::class, 'data'])->name('reports.forms.data');
    Route::get('/forms/export/excel', [FormReportController::class, 'exportExcel'])->name('reports.forms.export.excel');
    Route::get('/forms/export/pdf', [FormReportController::class, 'exportPdf'])->name('reports.forms.export.pdf');
    Route::get('/forms/export/csv', [FormReportController::class, 'exportCsv'])->name('reports.forms.export.csv');

    // Portal do Paciente
    Route::get('/portal', [PortalReportController::class, 'index'])->name('reports.portal');
    Route::post('/portal/data', [PortalReportController::class, 'data'])->name('reports.portal.data');
    Route::get('/portal/export/excel', [PortalReportController::class, 'exportExcel'])->name('reports.portal.export.excel');
    Route::get('/portal/export/pdf', [PortalReportController::class, 'exportPdf'])->name('reports.portal.export.pdf');
    Route::get('/portal/export/csv', [PortalReportController::class, 'exportCsv'])->name('reports.portal.export.csv');

    // Notificações
    Route::get('/notifications', [NotificationReportController::class, 'index'])->name('reports.notifications');
    Route::post('/notifications/data', [NotificationReportController::class, 'data'])->name('reports.notifications.data');
    Route::get('/notifications/export/excel', [NotificationReportController::class, 'exportExcel'])->name('reports.notifications.export.excel');
    Route::get('/notifications/export/pdf', [NotificationReportController::class, 'exportPdf'])->name('reports.notifications.export.pdf');
    Route::get('/notifications/export/csv', [NotificationReportController::class, 'exportCsv'])->name('reports.notifications.export.csv');
});

