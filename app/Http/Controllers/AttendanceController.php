<?php

namespace App\Http\Controllers;

use App\Http\Requests\Attendance\AttendanceIndexRequest;
use App\Http\Requests\Attendance\CurrentAttendanceIpRequest;
use App\Http\Requests\Attendance\StoreAttendanceExceptionRequest;
use App\Http\Requests\Attendance\StoreAttendanceRequest;
use App\Http\Requests\Attendance\UpdateAttendanceRequest;
use App\Models\Attendance;
use App\Models\User;
use App\Services\Attendance\AttendanceCalendarEventService;
use App\Services\Attendance\AttendanceCardsViewDataService;
use App\Services\Attendance\AttendanceIpVerificationService;
use App\Services\Attendance\AttendanceMutationService;
use App\Services\Attendance\TelegramVerificationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    public function __construct(
        private AttendanceCardsViewDataService $attendanceCardsViewDataService,
        private AttendanceMutationService $attendanceMutationService,
        private AttendanceCalendarEventService $attendanceCalendarEventService,
        private AttendanceIpVerificationService $attendanceIpVerificationService,
        private TelegramVerificationService $telegramVerificationService
    ) {}

    public function index(AttendanceIndexRequest $request): View
    {
        $authenticatedUser = Auth::user();
        if ($authenticatedUser instanceof User) {
            $authenticatedUser->loadMissing('employee.deployment');
        }

        $attendanceCardsData = $this->attendanceCardsViewDataService->build(
            $authenticatedUser instanceof User ? $authenticatedUser : null,
            Auth::id(),
            $request->validated('client_ip'),
            $request->ip()
        );
        $employeeId = $attendanceCardsData['employeeId'];
        $employeeEvents = [
            'attendanceHistoryEvents' => [],
            'calendarLabelEvents' => [],
        ];

        if (is_string($employeeId) && trim($employeeId) !== '') {
            $officeLocation = $attendanceCardsData['officeLocation'] ?? null;
            $employeeEvents = $this->attendanceCalendarEventService->buildEmployeeEvents(
                $employeeId,
                is_array($officeLocation) ? $officeLocation : null
            );
        }

        return view('staff_attendance.attendance.index', array_merge(
            $attendanceCardsData,
            $employeeEvents,
            [
                'holidayEvents' => $this->attendanceCalendarEventService->buildHolidayEvents(),
            ]
        ));
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

    public function currentIp(CurrentAttendanceIpRequest $request): JsonResponse
    {
        return response()->json(
            $this->attendanceIpVerificationService->payload(
                $request->validated('client_ip'),
                $request->ip(),
                Auth::id()
            )
        );
    }

    public function verifyTelegramUsername(): JsonResponse
    {
        $verificationResult = $this->telegramVerificationService->verify(Auth::user());

        return response()->json($verificationResult['payload'], $verificationResult['status']);
    }

    public function storeException(StoreAttendanceExceptionRequest $request): JsonResponse
    {
        $authenticatedUser = Auth::user();
        if ($authenticatedUser instanceof User) {
            $authenticatedUser->loadMissing('employee');
        }

        $employeeId = $authenticatedUser?->employee?->id;
        if (! is_string($employeeId) || trim($employeeId) === '') {
            return response()->json([
                'success' => false,
                'message' => 'Data employee belum tersedia untuk user ini.',
            ], 422);
        }

        try {
            return $this->attendanceMutationService->storeException(
                $request->validated(),
                $employeeId,
                Auth::id(),
            );
        } catch (\Throwable $throwable) {
            report($throwable);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memproses attendance exception.',
            ], 500);
        }
    }
}
