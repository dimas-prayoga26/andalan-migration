<?php

<<<<<<< HEAD
=======
use App\Http\Controllers\AbsensiController;
>>>>>>> 8a4f1a0790dd8b5a5f6450921a45bab2660f51fc
use App\Models\Absensi\Absen;
use App\Models\Absensi\Izin;
use App\Models\Absensi\Lembur;

use Carbon\Carbon;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function (): void {
    Route::get('/', function () {
        $authenticatedUser = auth()->user();

        if ($authenticatedUser?->hasAnyRole(['admin', 'superuser'])) {
            return redirect()->route('dashboard', ['tenant' => 'admin']);
        }

        $companyName = $authenticatedUser?->company?->name;

        abort_if($companyName === null, 403);

        return redirect()->route('dashboard', ['tenant' => $companyName]);
    })->name('home');

    Route::prefix('/{tenant}/page')
        ->middleware('company.context')
        ->group(function (): void {
            Route::get('/dashboard', function () {
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
