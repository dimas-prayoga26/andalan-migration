<?php

namespace App\Http\Controllers;

use App\Http\Requests\AttendanceRequest;
use App\Models\Attendance;
use App\Models\Company;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    public function index(): View
    {
        $userId = 1;
        $attendance = Attendance::with('user')->where('user_id', $userId)->get();
        $izin = collect();
        $lembur = collect();
        $absensiHariIni = Attendance::where('date', now()->format('Y-m-d'))->where('user_id', $userId)->first();
        $totalLemburJam = 0;

        $agEvent = $attendance->groupBy(function ($data) {
            return Carbon::parse($data->date)->format('Y-m-d');
        })->map(function ($items) {
            return $items->map(function ($data) {
                return [
                    'check_in' => $data->check_in?->format('H:i'),
                    'check_out' => $data->check_out?->format('H:i'),
                    'status' => $data->status,
                ];
            })->values();
        });

        $officeCompany = Company::query()
            ->where('is_active', true)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->select(['name', 'address', 'latitude', 'longitude'])
            ->first();

        $officeLocation = $officeCompany
            ? [
                'name' => $officeCompany->name,
                'address' => $officeCompany->address,
                'latitude' => (float) $officeCompany->latitude,
                'longitude' => (float) $officeCompany->longitude,
                'radius_meters' => 10,
            ]
            : null;

        return view('absensi.index', compact('attendance', 'izin', 'lembur', 'agEvent', 'totalLemburJam', 'absensiHariIni', 'officeLocation'));
    }

    public function store(AttendanceRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['user_id'] = Auth::id();

        if (Attendance::where('user_id', Auth::id())
            ->whereDate('date', $data['date'])
            ->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Kamu sudah absen hari ini',
            ], 422);
        }

        $attendance = Attendance::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Absen masuk berhasil',
            'attendance_id' => $attendance->id,
            'update' => route('absensi.update', ['absensi' => $attendance]),
        ]);
    }

    public function update(AttendanceRequest $request, Attendance $absensi): JsonResponse
    {
        $data = $request->validated();

        if ($absensi->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak memiliki akses ke data absen ini',
            ], 403);
        }

        if ($absensi->date?->format('Y-m-d') !== $data['date']) {
            return response()->json([
                'success' => false,
                'message' => 'Data absen tidak sesuai tanggal yang dipilih',
            ], 422);
        }

        $absensi->update([
            'check_out' => $data['check_out'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Absen berhasil diupdate',
        ]);
    }
}
