<?php

use App\Models\Absensi\Absen;
use App\Models\Absensi\Izin;
use App\Models\Absensi\Lembur;

use Carbon\Carbon;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('dashboard');
});

Route::get('/absensi', function(){
    $userId = 1;
    $tahun = now()->year;

    $absen = Absen::with('user')->where('id_user', $userId)->get();
    $izin = Izin::with('user')->where('id_user', $userId)->get();
    $lembur = Lembur::with('user')->where('id_user', $userId)->get();

    // ── LEMBUR (filter tahun aktif) ─────
    $lembur = Lembur::with('user')
        ->where('id_user', $userId)
        ->where('status_persetujuan', 'Disetujui')
        ->whereYear('tanggal_lembur', $tahun)
        ->get();

    // ── TOTAL JAM LEMBUR ───────────────
    $totalLemburMenit = 0;

    foreach ($lembur as $l) {
        $mulai = Carbon::parse($l->jam_mulai);
        $selesai = Carbon::parse($l->jam_selesai);

        $batas = Carbon::parse($l->tanggal_lembur . ' 17:00:00');

        if ($mulai->lt($batas)) {
            $mulai = $batas;
        }

        if ($selesai->gt($mulai)) {
            $totalLemburMenit += $mulai->diffInMinutes($selesai);
        }
    }

    $totalLemburJam = round($totalLemburMenit / 60, 1);

    $agEvent = $absen->groupBy(function ($data) {
        return Carbon::parse($data->tanggal)->format('Y-m-d');
    })->map(function ($items) {
        return $items->map(function ($data) {
            return [
                'jam_masuk' => $data->jam_masuk?->format('H:i'),
                'jam_keluar' => $data->jam_keluar?->format('H:i'),
                'status' => $data->status,
            ];
        })->values();
    });

    return view('absensi.index', compact('absen', 'izin', 'lembur', 'agEvent', 'totalLemburJam'));
});
