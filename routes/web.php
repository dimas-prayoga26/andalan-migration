<?php

use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\ActivityScheduleController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function (): void {
    Route::get('/', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/activity-schadule', [ActivityScheduleController::class, 'index'])->name('activity-schadule');
    Route::get('/activity-schadule/events', [ActivityScheduleController::class, 'events'])->name('activity-schadule.events');

    Route::get('/project-management', function () {
        return view('project_management.index');
    })->name('project_management');

    Route::get('/project-management/detail', function () {
        return view('project_management.detail');
    })->name('project_management.detail');

    Route::get('/applicant', function () {
        return view('applicant_data.index');
    })->name('applicant');

    Route::get('/applicant/job-vacancies', function () {
        return view('applicant_data.job_vancancies');
    })->name('applicant.job_vacancies');

    Route::get('/employee-data', function () {
        return view('employee_data.index');
    })->name('employee_data');

    Route::get('/employee-data/authorize', function () {
        return view('employee_data.authorization');
    })->name('employee_data.authorization');

    Route::get('/agenda', function () {
        return view('agenda');
    })->name('agenda');

    Route::get('/project-management', function () {
        return view('project_management.index');
    })->name('project_management');

    Route::get('/absensi', [AbsensiController::class, 'index'])->name('absensi');
    Route::get('/absensi/reports', function () {
        return view('absensi.reports');
    })->name('absensi.reports');
    Route::get('/absensi/izin', function () {
        return view('absensi.izin');
    })->name('absensi.izin');
    Route::get('/absensi/lembur', function () {
        return view('absensi.lembur');
    })->name('absensi.lembur');
    Route::get('/absensi/cuti', function () {
        return view('absensi.cuti');
    })->name('absensi.cuti');

    Route::get('/error-503', function () {
        return view('error_code.maintance');
    })->name('error_code');

    Route::post('/store', [AbsensiController::class, 'storeAbsen'])->name('absensi.store');
    Route::put('/update', [AbsensiController::class, 'updateAbsen'])->name('absensi.update');

    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');
});
