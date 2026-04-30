<?php

namespace App\Http\Controllers;

use App\Http\Requests\AbsenRequest;
use App\Models\Absensi\Absen;
use App\Models\Absensi\Izin;
use App\Models\Absensi\Lembur;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class AbsensiController extends Controller
{
    // Absensi
    public function index(){
        $userId = 1;
        $tahun = now()->year;

        $absen = Absen::with('user')->where('id_user', $userId)->get();
        $izin = Izin::with('user')->where('id_user', $userId)->get();
        $lembur = Lembur::with('user')->where('id_user', $userId)->get();

        $absensiHariIni = Absen::where('tanggal', now()->format('Y-m-d'))->where('id_user', $userId)->first();

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

        return view('absensi.index', compact('absen', 'izin', 'lembur', 'agEvent', 'totalLemburJam', 'absensiHariIni'));
    }

    public function storeAbsen(AbsenRequest $request)
    {
        $data = $request->validated();

        if (Absen::where('id_user', Auth::id())
            ->whereDate('tanggal', $data['tanggal'])
            ->exists()) {

            return response()->json([
                'success' => false,
                'message' => 'Kamu sudah absen hari ini',
            ], 422);
        }

        Absen::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Absen masuk berhasil',
            'update' => route('absensi.update')
        ]);
    }

    public function updateAbsen(AbsenRequest $request){
        $data = $request->all();
        $id = Auth::id();

        $absen = Absen::where('id_user', $id);
        $absen->id_user = $id;
        $absen->jam_keluar = $data['jam_keluar'];
        $absen->update();

        return response()->json([
            'success' => true,
            'message' => 'Absen berhasil diupdate',
        ]);
    }
}
