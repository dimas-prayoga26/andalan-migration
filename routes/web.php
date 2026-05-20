<?php

use App\Http\Controllers\ActivityScheduleController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceOvertimeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BusinessTripController;
use App\Http\Controllers\LeaveRequestController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function (): void {
    Route::get('/', function () {
        return view('dashboard');
    })->name('dashboard');

    // Activity Schedule
    Route::get('/activity-schadule', [ActivityScheduleController::class, 'index'])->name('activity-schadule');
    Route::get('/activity-schadule/events', [ActivityScheduleController::class, 'events'])->name('activity-schadule.events');

    // Project Management
    Route::get('/project-management', function () {
        return view('project_management.index');
    })->name('project_management');
    Route::get('/project-management/detail', function () {
        return view('project_management.detail');
    })->name('project_management.detail');

    // Applicant
    Route::get('/applicant', function () {
        return view('applicant_data.index');
    })->name('applicant');
    Route::get('/applicant/job-vacancies', function () {
        return view('applicant_data.job_vancancies');
    })->name('applicant.job_vacancies');

    // Employee
    Route::get('/employee-data', function () {
        return view('employee_data.index');
    })->name('employee_data');
    Route::get('/employee-data/authorize', function () {
        return view('employee_data.authorization');
    })->name('employee_data.authorization');

    // Agenda
    Route::get('/agenda', function () {
        return view('agenda');
    })->name('agenda');

    // Attendance main resource (index, check-in, check-out)
    Route::resource('absensi', AttendanceController::class)
        ->only(['index', 'store', 'update'])
        ->names(['index' => 'absensi']);

    // Attendance support routes
    Route::get('/absensi/datatable', [AttendanceController::class, 'datatable'])->name('absensi.datatable');
    Route::get('/absensi/current-ip', [AttendanceController::class, 'currentIp'])->name('absensi.current-ip');
    Route::get('/absensi/reports', function () {
        return view('absensi.reports');
    })->name('absensi.reports');

    // Attendance permission routes
    Route::get('/absensi/izin/datatable', [LeaveRequestController::class, 'datatable'])->name('absensi.izin.datatable');
    Route::get('/absensi/izin', [LeaveRequestController::class, 'index'])->name('absensi.izin');
    Route::post('/absensi/izin/upload-image', [LeaveRequestController::class, 'uploadImage'])->name('absensi.izin.upload-image');
    Route::post('/absensi/izin/delete-uploaded-image', [LeaveRequestController::class, 'deleteUploadedImage'])->name('absensi.izin.delete-uploaded-image');
    Route::post('/absensi/izin', [LeaveRequestController::class, 'store'])->name('absensi.izin.store');
    Route::get('/absensi/izin/{leaveRequest}', [LeaveRequestController::class, 'show'])->name('absensi.izin.show');
    Route::get('/absensi/izin/{leaveRequest}/attachment', [LeaveRequestController::class, 'showAttachment'])->name('absensi.izin.attachment');
    Route::put('/absensi/izin/{leaveRequest}/status', [LeaveRequestController::class, 'updateStatus'])->name('absensi.izin.update-status');

    // Attendance permission routes
    Route::delete('/absensi/izin/{leaveRequest}', [LeaveRequestController::class, 'destroy'])->name('absensi.izin.destroy');
    Route::get('/absensi/lembur/datatable', [AttendanceOvertimeController::class, 'datatable'])->name('absensi.lembur.datatable');
    Route::get('/absensi/lembur', [AttendanceOvertimeController::class, 'index'])->name('absensi.lembur');
    Route::post('/absensi/lembur', [AttendanceOvertimeController::class, 'store'])->name('absensi.lembur.store');
    Route::get('/absensi/lembur/{attendanceOvertime}', [AttendanceOvertimeController::class, 'show'])->name('absensi.lembur.show');
    Route::put('/absensi/lembur/{attendanceOvertime}', [AttendanceOvertimeController::class, 'update'])->name('absensi.lembur.update');
    Route::delete('/absensi/lembur/{attendanceOvertime}', [AttendanceOvertimeController::class, 'destroy'])->name('absensi.lembur.destroy');
    Route::get('/absensi/cuti', function () {
        return view('absensi.cuti');
    })->name('absensi.cuti');

    // Attendance business trip routes
    Route::get('/absensi/dinas/datatable', [BusinessTripController::class, 'datatable'])->name('absensi.dinas.datatable');
    Route::resource('absensi/dinas', BusinessTripController::class)
        ->only(['index'])
        ->names(['index' => 'absensi.dinas']);

    // Error page
    Route::get('/error-503', function () {
        return view('error_code.maintance');
    })->name('error_code');

    // Logout
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');
});
