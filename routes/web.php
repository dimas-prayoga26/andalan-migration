<?php

use App\Http\Controllers\ActivityScheduleController;
use App\Http\Controllers\AdminAttendance\AttendanceOverviewController as AdminAttendanceOverviewController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AuthorizationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StaffAttendance\AttendanceBusinessTripCashAdvanceController;
use App\Http\Controllers\StaffAttendance\AttendanceBusinessTripController;
use App\Http\Controllers\StaffAttendance\AttendanceBusinessTripReimbursementController;
use App\Http\Controllers\StaffAttendance\AttendanceController;
use App\Http\Controllers\StaffAttendance\AttendanceLeaveRequestController;
use App\Http\Controllers\StaffAttendance\AttendanceOvertimeController;
use App\Http\Controllers\StaffAttendance\AttendanceReportController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function (): void {
    Route::get('/', [DashboardController::class, 'index'])
        ->middleware('position.permission:view-dashboard')
        ->name('dashboard');

    // Activity Schedule
    Route::middleware('position.permission:view-calendar')->group(function (): void {
        Route::get('/activity-schadule', [ActivityScheduleController::class, 'index'])->name('activity-schadule');
        Route::get('/activity-schadule/events', [ActivityScheduleController::class, 'events'])->name('activity-schadule.events');
    });

    // Project Management
    Route::middleware('position.permission:view-timesheet-reporting')->group(function (): void {
        Route::get('/project-management', function () {
            return view('project_management.index');
        })->name('project_management');
        Route::get('/project-management/detail', function () {
            return view('project_management.detail');
        })->name('project_management.detail');
    });

    // Applicant
    Route::middleware('position.permission:view-talent-acquisition')->group(function (): void {
        Route::get('/applicant', function () {
            return view('applicant_data.index');
        })->name('applicant');
        Route::get('/applicant/job-vacancies', function () {
            return view('applicant_data.job_vancancies');
        })->name('applicant.job_vacancies');
    });

    // Employee
    Route::middleware('position.permission:view-organization,view-employee-database')->group(function (): void {
        Route::get('/employee-data', function () {
            return view('employee_data.index');
        })->name('employee_data');
        Route::get('/employee-data/authorize', function () {
            return view('employee_data.authorization');
        })->name('employee_data.authorization');
    });

    // Authorization
    Route::middleware('position.permission:view-authorization')->group(function (): void {
        Route::get('/authorization', [AuthorizationController::class, 'index'])->name('authorization');
        Route::get('/authorization/access-menus', [AuthorizationController::class, 'accessMenus'])->name('authorization.access-menus');
        Route::post('/authorization/position-permissions', [AuthorizationController::class, 'updatePositionPermissions'])->name('authorization.position-permissions.update');
    });

    // Agenda
    Route::get('/agenda', function () {
        return view('agenda');
    })->middleware('position.permission:view-meeting')->name('agenda');

    // Attendance overview and daily attendance
    Route::view('/attendance/overview', 'staff_attendance.overview.index')
        ->middleware('position.permission:view-attendance')
        ->name('attendance');
    Route::get('/attendance/today', [AttendanceController::class, 'index'])
        ->middleware('position.permission:view-attendance')
        ->name('attendance.today');

    // Admin attendance routes
    Route::get('/admin-attendance/overview', [AdminAttendanceOverviewController::class, 'index'])
        ->middleware('position.permission:view-admin-attendance')
        ->name('admin-attendance.overview');

    // Attendance check-in and check-out
    Route::resource('attendance', AttendanceController::class)
        ->only(['store', 'update'])
        ->middleware('position.permission:view-attendance');

    // Attendance support routes
    Route::middleware('position.permission:view-attendance')->group(function (): void {
        Route::get('/attendance/current-ip', [AttendanceController::class, 'currentIp'])->name('attendance.current-ip');
        Route::post('/attendance/verify-telegram-username', [AttendanceController::class, 'verifyTelegramUsername'])->name('attendance.verify-telegram-username');
        Route::post('/attendance/exceptions', [AttendanceController::class, 'storeException'])->name('attendance.exceptions.store');
    });
    Route::middleware('position.permission:view-timesheet-reporting')->group(function (): void {
        Route::get('/attendance/reports', [AttendanceReportController::class, 'index'])->name('attendance.reports');
        Route::get('/attendance/reports/datatable', [AttendanceReportController::class, 'datatable'])->name('attendance.reports.datatable');
        Route::get('/attendance/reports/export', [AttendanceReportController::class, 'exportReport'])->name('attendance.reports.export');
    });

    // Attendance leave request routes
    Route::middleware('position.permission:view-attendance')->group(function (): void {
        Route::get('/attendance/leave-requests', [AttendanceLeaveRequestController::class, 'index'])->name('attendance.leave-requests');
        Route::get('/attendance/leave-requests/cards', [AttendanceLeaveRequestController::class, 'cards'])->name('attendance.leave-requests.cards');
        Route::post('/attendance/leave-requests/upload-image', [AttendanceLeaveRequestController::class, 'uploadImage'])->name('attendance.leave-requests.upload-image');
        Route::post('/attendance/leave-requests/delete-uploaded-image', [AttendanceLeaveRequestController::class, 'deleteUploadedImage'])->name('attendance.leave-requests.delete-uploaded-image');
        Route::post('/attendance/leave-requests', [AttendanceLeaveRequestController::class, 'store'])->name('attendance.leave-requests.store');
        Route::put('/attendance/leave-requests/{leaveRequest}', [AttendanceLeaveRequestController::class, 'update'])->name('attendance.leave-requests.update');
        Route::delete('/attendance/leave-requests/{leaveRequest}', [AttendanceLeaveRequestController::class, 'destroy'])->name('attendance.leave-requests.destroy');
    });

    // Attendance overtime routes
    Route::middleware('position.permission:view-attendance')->group(function (): void {
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
    });

    // Attendance business trip routes
    Route::middleware('position.permission:view-attendance')->group(function (): void {
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
    });

    // Error page
    Route::get('/error-503', function () {
        return view('error_code.maintance');
    })->name('error_code');

    // Logout
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');
});
