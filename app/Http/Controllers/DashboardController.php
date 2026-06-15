<?php

namespace App\Http\Controllers;

use App\Http\Requests\Attendance\AttendanceIndexRequest;
use App\Http\Requests\Attendance\StoreAttendanceRequest;
use App\Http\Requests\Attendance\UpdateAttendanceRequest;
use App\Models\Attendance;
use App\Models\User;
use App\Services\Attendance\AttendanceCardsViewDataService;
use App\Services\Attendance\AttendanceMutationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __construct(
        private AttendanceCardsViewDataService $attendanceCardsViewDataService,
        private AttendanceMutationService $attendanceMutationService
    ) {}

    public function index(AttendanceIndexRequest $request): View
    {
        $authenticatedUser = Auth::user();
        $attendanceCardsData = $this->attendanceCardsViewDataService->build(
            $authenticatedUser instanceof User ? $authenticatedUser : null,
            Auth::id(),
            $request->validated('client_ip'),
            $request->ip()
        );

        return view('dashboard', $attendanceCardsData);
    }

    public function store(StoreAttendanceRequest $request): JsonResponse
    {
        try {
            $storeResult = $this->attendanceMutationService->store(
                $request->validated(),
                $request->ip(),
                $request->userAgent(),
                Auth::user(),
                Auth::id(),
            );

            return response()->json($storeResult['payload'], $storeResult['status']);
        } catch (\Throwable $throwable) {
            report($throwable);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memproses absen masuk.',
            ], 500);
        }
    }

    public function update(UpdateAttendanceRequest $request, Attendance $attendance): JsonResponse
    {
        try {
            $updateResult = $this->attendanceMutationService->update(
                $request->validated(),
                $request->ip(),
                $request->userAgent(),
                $attendance,
                Auth::user(),
                Auth::id(),
            );

            return response()->json($updateResult['payload'], $updateResult['status']);
        } catch (\Throwable $throwable) {
            report($throwable);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memproses absen pulang.',
            ], 500);
        }
    }
}
