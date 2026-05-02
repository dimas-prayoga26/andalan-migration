<?php

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

    Route::get('/project-management', function () {
        return view('project_management.index');
    })->name('project_management');

    Route::get('/project-management/detail', function () {
        return view('project_management.detail');
    })->name('project_management.detail');


    Route::get('/applicant', function () {
        return view('applicant_data.index');
    })->name('applicant');

    Route::get('/agenda', function () {
        return view('agenda');
    })->name('agenda');

    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');
});
