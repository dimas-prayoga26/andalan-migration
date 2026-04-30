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
        return view('dashboard');
    })->name('dashboard');

    // Route::get('/agenda', function () {
    //     return view('agenda');
    // })->name('agenda');

    Route::prefix('absensi')->group(function (): void {
        Route::get('/', [AbsensiController::class, 'index'])->name('absensi');
        Route::post('store', [AbsensiController::class, 'storeAbsen'])->name('absensi.store');
        Route::put('update', [AbsensiController::class, 'updateAbsen'])->name('absensi.update');
    });

    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');
});
