<?php

use App\Http\Controllers\ActivityScheduleController;
use App\Http\Controllers\AttendanceBusinessTripCashAdvanceController;
use App\Http\Controllers\AttendanceBusinessTripController;
use App\Http\Controllers\AttendanceBusinessTripReimbursementController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceLeaveRequestController;
use App\Http\Controllers\AttendanceOvertimeController;
use App\Http\Controllers\AttendanceReportController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function (): void {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

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

    // Attendance overview and daily attendance
    Route::view('/attendance/overview', 'staff_attendance.overview.index')->name('attendance');
    Route::get('/attendance/today', [AttendanceController::class, 'index'])->name('attendance.today');

    // Attendance check-in and check-out
    Route::resource('attendance', AttendanceController::class)
        ->only(['store', 'update']);

    // Attendance support routes
    Route::get('/attendance/current-ip', [AttendanceController::class, 'currentIp'])->name('attendance.current-ip');
    Route::post('/attendance/verify-telegram-username', [AttendanceController::class, 'verifyTelegramUsername'])->name('attendance.verify-telegram-username');
    Route::post('/attendance/exceptions', [AttendanceController::class, 'storeException'])->name('attendance.exceptions.store');
    Route::get('/attendance/reports', [AttendanceReportController::class, 'index'])->name('attendance.reports');
    Route::get('/attendance/reports/datatable', [AttendanceReportController::class, 'datatable'])->name('attendance.reports.datatable');
    Route::get('/attendance/reports/export', [AttendanceReportController::class, 'exportReport'])->name('attendance.reports.export');

    // Attendance leave request routes
    Route::get('/attendance/leave-requests', [AttendanceLeaveRequestController::class, 'index'])->name('attendance.leave-requests');
    Route::get('/attendance/leave-requests/cards', [AttendanceLeaveRequestController::class, 'cards'])->name('attendance.leave-requests.cards');
    Route::post('/attendance/leave-requests/upload-image', [AttendanceLeaveRequestController::class, 'uploadImage'])->name('attendance.leave-requests.upload-image');
    Route::post('/attendance/leave-requests/delete-uploaded-image', [AttendanceLeaveRequestController::class, 'deleteUploadedImage'])->name('attendance.leave-requests.delete-uploaded-image');
    Route::post('/attendance/leave-requests', [AttendanceLeaveRequestController::class, 'store'])->name('attendance.leave-requests.store');
    Route::put('/attendance/leave-requests/{leaveRequest}', [AttendanceLeaveRequestController::class, 'update'])->name('attendance.leave-requests.update');
    Route::delete('/attendance/leave-requests/{leaveRequest}', [AttendanceLeaveRequestController::class, 'destroy'])->name('attendance.leave-requests.destroy');

    // Attendance overtime routes
    Route::get('/attendance/overtimes/datatable', [AttendanceOvertimeController::class, 'datatable'])->name('attendance.overtimes.datatable');
    Route::get('/attendance/overtimes', [AttendanceOvertimeController::class, 'index'])->name('attendance.overtimes');
    Route::post('/attendance/overtimes', [AttendanceOvertimeController::class, 'store'])->name('attendance.overtimes.store');
    Route::get('/attendance/overtimes/{attendanceOvertime}/data', [AttendanceOvertimeController::class, 'show'])->name('attendance.overtimes.show');
    Route::post('/attendance/overtimes/{attendanceOvertime}/tasks', [AttendanceOvertimeController::class, 'storeTask'])->name('attendance.overtimes.tasks.store');
    Route::put('/attendance/overtimes/{attendanceOvertime}/tasks/{projectTask}', [AttendanceOvertimeController::class, 'updateTask'])->name('attendance.overtimes.tasks.update');
    Route::delete('/attendance/overtimes/{attendanceOvertime}/tasks/{projectTask}', [AttendanceOvertimeController::class, 'destroyTask'])->name('attendance.overtimes.tasks.destroy');
    Route::get('/attendance/overtimes/{attendanceOvertime}', [AttendanceOvertimeController::class, 'detail'])->name('attendance.overtimes.detail');
    Route::put('/attendance/overtimes/{attendanceOvertime}', [AttendanceOvertimeController::class, 'update'])->name('attendance.overtimes.update');
    Route::delete('/attendance/overtimes/{attendanceOvertime}', [AttendanceOvertimeController::class, 'destroy'])->name('attendance.overtimes.destroy');

    // Attendance business trip routes
    Route::get('/attendance/business-trips/provinces', [AttendanceBusinessTripController::class, 'provinces'])->name('attendance.business-trips.provinces');
    Route::get('/attendance/business-trips/regencies/{provinceCode}', [AttendanceBusinessTripController::class, 'regencies'])->name('attendance.business-trips.regencies');
    Route::get('/attendance/business-trips/{businessTrip}/cash-advances/create', [AttendanceBusinessTripCashAdvanceController::class, 'create'])->name('attendance.business-trips.cash-advances.create');
    Route::post('/attendance/business-trips/{businessTrip}/cash-advances', [AttendanceBusinessTripCashAdvanceController::class, 'store'])->name('attendance.business-trips.cash-advances.store');
    Route::get('/attendance/business-trips/{businessTrip}/reimbursements/create', [AttendanceBusinessTripReimbursementController::class, 'create'])->name('attendance.business-trips.reimbursements.create');
    Route::post('/attendance/business-trips/{businessTrip}/reimbursements', [AttendanceBusinessTripReimbursementController::class, 'store'])->name('attendance.business-trips.reimbursements.store');
    Route::resource('attendance/business-trips', AttendanceBusinessTripController::class)
        ->only(['index', 'create', 'store', 'show'])
        ->names([
            'index' => 'attendance.business-trips',
            'create' => 'attendance.business-trips.create',
            'store' => 'attendance.business-trips.store',
            'show' => 'attendance.business-trips.show',
        ]);

    // Error page
    Route::get('/error-503', function () {
        return view('error_code.maintance');
    })->name('error_code');

    // Logout
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');
});
