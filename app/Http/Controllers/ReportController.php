<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceException;
use App\Models\AttendanceLog;
use App\Models\EmployeeProfile;
use App\Models\User;
use App\Services\Attendance\AttendanceCardsViewDataService;
use App\Services\Attendance\AttendanceMutationService;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Spatie\LaravelPdf\Enums\Format;
use Spatie\LaravelPdf\Facades\Pdf;

class ReportController extends Controller
{
    public function __construct(
        private AttendanceCardsViewDataService $attendanceCardsViewDataService,
        private AttendanceMutationService $attendanceMutationService
    ) {}

    public function index(Request $request): View
    {
        $authenticatedUser = Auth::user();
        if ($authenticatedUser instanceof User) {
            $authenticatedUser->loadMissing('employee.deployment');
        }
        $attendanceCardsData = $this->attendanceCardsViewDataService->build(
            $authenticatedUser instanceof User ? $authenticatedUser : null,
            Auth::id(),
            $request
        );
        $employeeId = $attendanceCardsData['employeeId'];
        $isSuperUser = $this->isSuperUser($authenticatedUser);
        $isStaffUser = $this->isStaffUser($authenticatedUser);
        $nowJakarta = now('Asia/Jakarta');
        $attendance = collect();
        if (is_string($employeeId) && trim($employeeId) !== '') {
            $attendance = Attendance::query()
                ->where('employee_id', $employeeId)
                ->get();
        }
        $showCompanyFilter = $isSuperUser;
        $showStaffPeriodFilter = $isStaffUser;
        $companies = collect();
        $staffMonthOptions = collect(range(1, 12));
        $staffYearOptions = collect();
        $defaultStaffMonth = (int) $request->integer('month', (int) $nowJakarta->month);
        $defaultStaffYear = (int) $request->integer('year', (int) $nowJakarta->year);

        if ($defaultStaffMonth < 1 || $defaultStaffMonth > 12) {
            $defaultStaffMonth = (int) $nowJakarta->month;
        }

        if ($defaultStaffYear < 2000 || $defaultStaffYear > 2100) {
            $defaultStaffYear = (int) $nowJakarta->year;
        }

        if ($isStaffUser) {
            $employmentStartMonth = $this->resolveStaffEmploymentStartMonth(
                is_string($employeeId) ? $employeeId : null,
                $nowJakarta
            );
            $staffYearOptions = $this->buildStaffYearOptions($employmentStartMonth, $nowJakarta);

            if (! $staffYearOptions->contains($defaultStaffYear)) {
                $defaultStaffYear = $staffYearOptions->contains((int) $nowJakarta->year)
                    ? (int) $nowJakarta->year
                    : (int) $staffYearOptions->first();
            }

            $staffMonthOptions = $this->buildStaffMonthOptionsByYear($employmentStartMonth, $nowJakarta, $defaultStaffYear);

            if (! $staffMonthOptions->contains($defaultStaffMonth)) {
                $defaultStaffMonth = (int) $staffMonthOptions->last();
            }
        }

        if ($showCompanyFilter) {
            $companies = User::query()
                ->with([
                    'employee.deployment.company:id,name',
                ])
                ->get()
                ->pluck('employee.deployment.company')
                ->filter()
                ->unique('id')
                ->sortBy('name')
                ->values();
        }
        $izin = collect();
        $lembur = collect();
        $totalLemburJam = 0;

        $agEvent = $attendance->groupBy(function ($data) {
            return Carbon::parse($data->date)->format('Y-m-d');
        })->map(function ($items) {
            return $items->map(function ($data) {
                return [
                    'check_in' => $data->clock_in?->format('H:i'),
                    'check_out' => $data->clock_out?->format('H:i'),
                    'status' => $data->status,
                ];
            })->values();
        });

        return view('absensi.report', array_merge(
            $attendanceCardsData,
            compact(
                'attendance',
                'izin',
                'lembur',
                'agEvent',
                'totalLemburJam',
                'companies',
                'showCompanyFilter',
                'showStaffPeriodFilter',
                'staffMonthOptions',
                'staffYearOptions',
                'defaultStaffMonth',
                'defaultStaffYear',
            )
        ));
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $storeResult = $this->attendanceMutationService->store(
                $request,
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

    public function update(Request $request, Attendance $absensi): JsonResponse
    {
        try {
            $updateResult = $this->attendanceMutationService->update(
                $request,
                $absensi,
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

    public function datatable(Request $request): JsonResponse
    {
        $authenticatedUser = Auth::user();
        if ($authenticatedUser instanceof User) {
            $authenticatedUser->loadMissing('employee.deployment');
        }

        $isSuperUser = $this->isSuperUser($authenticatedUser);
        $isBoardOfDirectur = $this->isBoardOfDirectur($authenticatedUser);
        $isStaffUser = $this->isStaffUser($authenticatedUser);
        $userCompanyId = $authenticatedUser?->employee?->deployment?->current_company_id;
        $nowJakarta = now('Asia/Jakarta');
        $todayDate = $nowJakarta->toDateString();
        $selectedMonth = (int) $request->integer('month', (int) $nowJakarta->month);
        $selectedYear = (int) $request->integer('year', (int) $nowJakarta->year);
        $selectedCompanyId = trim((string) $request->input('company_id', ''));

        if ($selectedMonth < 1 || $selectedMonth > 12) {
            $selectedMonth = (int) $nowJakarta->month;
        }

        if ($selectedYear < 2000 || $selectedYear > 2100) {
            $selectedYear = (int) $nowJakarta->year;
        }

        if ($isStaffUser) {
            $staffUser = User::query()
                ->with(['employee.deployment.company:id,name'])
                ->find(Auth::id());

            if (! $staffUser) {
                return response()->json(['data' => []]);
            }

            $staffEmployeeId = $staffUser->employee?->id;
            if (! is_string($staffEmployeeId) || trim($staffEmployeeId) === '') {
                return response()->json(['data' => []]);
            }

            $employmentStartMonth = $this->resolveStaffEmploymentStartMonth($staffEmployeeId, $nowJakarta);
            $currentMonthStart = $nowJakarta->copy()->startOfMonth();
            $selectedPeriodStart = Carbon::create($selectedYear, $selectedMonth, 1, 0, 0, 0, 'Asia/Jakarta')->startOfMonth();

            if ($selectedPeriodStart->lt($employmentStartMonth)) {
                $selectedYear = (int) $employmentStartMonth->year;
                $selectedMonth = (int) $employmentStartMonth->month;
                $selectedPeriodStart = $employmentStartMonth->copy();
            }

            if ($selectedPeriodStart->gt($currentMonthStart)) {
                $selectedYear = (int) $currentMonthStart->year;
                $selectedMonth = (int) $currentMonthStart->month;
            }

            $staffAttendances = Attendance::query()
                ->where('employee_id', $staffEmployeeId)
                ->whereMonth('date', $selectedMonth)
                ->whereYear('date', $selectedYear)
                ->get(['id', 'date', 'status', 'clock_in', 'clock_out', 'work_hours', 'created_at']);

            $staffProfileName = EmployeeProfile::query()
                ->where('employee_id', $staffEmployeeId)
                ->value('name');
            $staffDisplayName = is_string($staffProfileName) && trim($staffProfileName) !== ''
                ? trim($staffProfileName)
                : ((is_string($staffUser->username) && trim($staffUser->username) !== '')
                    ? trim($staffUser->username)
                    : ((is_string($staffUser->email) && trim($staffUser->email) !== '') ? trim($staffUser->email) : '-'));

            $attendanceLogsByAttendanceId = AttendanceLog::query()
                ->whereIn('attendance_id', $staffAttendances->pluck('id'))
                ->where('type', true)
                ->orderByDesc('created_at')
                ->get([
                    'attendance_id',
                    'location',
                    'latitude',
                    'longitude',
                    'distance',
                    'radius_result',
                    'address_village',
                    'address_district',
                    'address_regency',
                    'address_city',
                    'address_province',
                    'address_postal_code',
                ])
                ->groupBy('attendance_id')
                ->map(fn ($attendanceLogs) => $attendanceLogs->first());
            $attendanceExceptionsByAttendanceId = AttendanceException::query()
                ->whereIn('attendance_id', $staffAttendances->pluck('id'))
                ->orderByDesc('created_at')
                ->get(['attendance_id', 'note', 'from_time', 'to_time'])
                ->groupBy('attendance_id')
                ->map(fn ($attendanceExceptions) => $attendanceExceptions->first());
            $attendanceByDate = $staffAttendances
                ->filter(fn (Attendance $attendanceItem): bool => $attendanceItem->date !== null)
                ->sortBy('date')
                ->keyBy(fn (Attendance $attendanceItem): string => $attendanceItem->date->format('Y-m-d'));
            $holidayMapByDate = $this->buildHolidayMapByMonth($selectedYear, $selectedMonth);
            $periodStart = Carbon::create($selectedYear, $selectedMonth, 1, 0, 0, 0, 'Asia/Jakarta')->startOfDay();
            $periodEnd = $periodStart->copy()->endOfMonth()->startOfDay();
            $todayJakarta = now('Asia/Jakarta')->startOfDay();
            if ($selectedYear === (int) $todayJakarta->year && $selectedMonth === (int) $todayJakarta->month) {
                $periodEnd = $todayJakarta->copy();
            }
            $tableRows = collect();

            for ($cursorDate = $periodStart->copy(); $cursorDate->lte($periodEnd); $cursorDate->addDay()) {
                $isoDate = $cursorDate->toDateString();
                $attendanceItem = $attendanceByDate->get($isoDate);

                if ($attendanceItem instanceof Attendance) {
                    $attendanceLog = $attendanceLogsByAttendanceId->get($attendanceItem->id);
                    $attendanceException = $attendanceExceptionsByAttendanceId->get($attendanceItem->id);
                    $variance = $this->formatVariance($attendanceException?->from_time, $attendanceException?->to_time);
                    $checkInValue = $attendanceItem->clock_in?->format('H:i');
                    $checkOutValue = $attendanceItem->clock_out?->format('H:i');

                    $tableRows->push([
                        'attendance_id' => $attendanceItem->id,
                        'attendance_date' => $attendanceItem->date?->translatedFormat('d M Y'),
                        'attendance_date_iso' => $isoDate,
                        'staff_name' => $staffDisplayName,
                        'company_name' => $staffUser->employee?->deployment?->company?->name,
                        'check_in' => $checkInValue,
                        'check_out' => $checkOutValue,
                        'variance' => $variance,
                        'work_hours' => $this->formatWorkHoursLabel($checkInValue, $checkOutValue, $attendanceItem->work_hours),
                        'notes' => is_string($attendanceException?->note) && trim($attendanceException->note) !== '' ? trim($attendanceException->note) : null,
                        'status' => $attendanceItem->status,
                        'row_type' => 'attendance',
                        'is_virtual' => false,
                        'check_in_latitude' => isset($attendanceLog?->latitude) ? (float) $attendanceLog->latitude : null,
                        'check_in_longitude' => isset($attendanceLog?->longitude) ? (float) $attendanceLog->longitude : null,
                        'distance_meters' => isset($attendanceLog?->distance) ? (float) $attendanceLog->distance : null,
                        'radius_result' => isset($attendanceLog?->radius_result) ? (string) $attendanceLog->radius_result : null,
                        'formatted_address' => isset($attendanceLog?->location) ? (string) $attendanceLog->location : null,
                        'address_village' => isset($attendanceLog?->address_village) ? (string) $attendanceLog->address_village : null,
                        'address_district' => isset($attendanceLog?->address_district) ? (string) $attendanceLog->address_district : null,
                        'address_regency' => isset($attendanceLog?->address_regency) ? (string) $attendanceLog->address_regency : null,
                        'address_city' => isset($attendanceLog?->address_city) ? (string) $attendanceLog->address_city : null,
                        'address_province' => isset($attendanceLog?->address_province) ? (string) $attendanceLog->address_province : null,
                        'address_postal_code' => isset($attendanceLog?->address_postal_code) ? (string) $attendanceLog->address_postal_code : null,
                    ]);

                    continue;
                }

                $holidayData = $holidayMapByDate[$isoDate] ?? null;
                if (is_array($holidayData)) {
                    $isNationalHoliday = (bool) ($holidayData['is_national_holiday'] ?? false);
                    $tableRows->push([
                        'attendance_id' => null,
                        'attendance_date' => $cursorDate->translatedFormat('d M Y'),
                        'attendance_date_iso' => $isoDate,
                        'staff_name' => $staffDisplayName,
                        'company_name' => $staffUser->employee?->deployment?->company?->name,
                        'check_in' => (string) ($holidayData['name'] ?? '-'),
                        'check_out' => '-',
                        'variance' => $isNationalHoliday ? 'Libur Nasional' : 'Cuti Bersama',
                        'work_hours' => '0 hours',
                        'notes' => null,
                        'status' => null,
                        'row_type' => $isNationalHoliday ? 'national_holiday' : 'joint_leave',
                        'is_virtual' => true,
                        'check_in_latitude' => null,
                        'check_in_longitude' => null,
                        'distance_meters' => null,
                        'radius_result' => null,
                        'formatted_address' => null,
                        'address_village' => null,
                        'address_district' => null,
                        'address_regency' => null,
                        'address_city' => null,
                        'address_province' => null,
                        'address_postal_code' => null,
                    ]);

                    continue;
                }

                if ($cursorDate->isWeekend()) {
                    $tableRows->push([
                        'attendance_id' => null,
                        'attendance_date' => $cursorDate->translatedFormat('d M Y'),
                        'attendance_date_iso' => $isoDate,
                        'staff_name' => $staffDisplayName,
                        'company_name' => $staffUser->employee?->deployment?->company?->name,
                        'check_in' => 'Weekend / Day Off',
                        'check_out' => '-',
                        'variance' => 'Weekend / Day Off',
                        'work_hours' => '0 hours',
                        'notes' => null,
                        'status' => null,
                        'row_type' => 'weekend',
                        'is_virtual' => true,
                        'check_in_latitude' => null,
                        'check_in_longitude' => null,
                        'distance_meters' => null,
                        'radius_result' => null,
                        'formatted_address' => null,
                        'address_village' => null,
                        'address_district' => null,
                        'address_regency' => null,
                        'address_city' => null,
                        'address_province' => null,
                        'address_postal_code' => null,
                    ]);
                }
            }

            return response()->json([
                'data' => $tableRows,
            ]);
        }

        $tableUsersQuery = User::query()
            ->with([
                'employee.deployment.company:id,name',
                'employee:id,user_id',
            ]);

        if ($isBoardOfDirectur) {
            if ($userCompanyId) {
                $tableUsersQuery->whereHas('employee.deployment', function ($query) use ($userCompanyId): void {
                    $query->where('current_company_id', $userCompanyId);
                });
            } else {
                $tableUsersQuery->whereRaw('1 = 0');
            }
        } elseif ($isSuperUser && $selectedCompanyId !== '') {
            $tableUsersQuery->whereHas('employee.deployment', function ($query) use ($selectedCompanyId): void {
                $query->where('current_company_id', $selectedCompanyId);
            });
        }

        $tableUsers = $tableUsersQuery->get();
        $employeeIds = $tableUsers
            ->map(fn (User $user): mixed => $user->employee?->id)
            ->filter()
            ->values();
        $employeeProfileNamesByEmployeeId = EmployeeProfile::query()
            ->whereIn('employee_id', $employeeIds)
            ->orderBy('created_at')
            ->pluck('name', 'employee_id');
        $attendancesTodayByEmployeeId = Attendance::query()
            ->whereIn('employee_id', $employeeIds)
            ->whereDate('date', $todayDate)
            ->orderByDesc('created_at')
            ->get(['id', 'employee_id', 'date', 'clock_in', 'clock_out', 'work_hours', 'status'])
            ->groupBy('employee_id')
            ->map(fn ($attendanceItems) => $attendanceItems->first());
        $attendanceIds = $tableUsers
            ->map(fn (User $user): mixed => $attendancesTodayByEmployeeId->get($user->employee?->id)?->id)
            ->filter()
            ->values();
        $attendanceLogsByAttendanceId = AttendanceLog::query()
            ->whereIn('attendance_id', $attendanceIds)
            ->where('type', true)
            ->orderByDesc('created_at')
            ->get([
                'attendance_id',
                'location',
                'latitude',
                'longitude',
                'distance',
                'radius_result',
                'address_village',
                'address_district',
                'address_regency',
                'address_city',
                'address_province',
                'address_postal_code',
            ])
            ->groupBy('attendance_id')
            ->map(fn ($attendanceLogs) => $attendanceLogs->first());
        $attendanceExceptionsByAttendanceId = AttendanceException::query()
            ->whereIn('attendance_id', $attendanceIds)
            ->orderByDesc('created_at')
            ->get(['attendance_id', 'note', 'from_time', 'to_time'])
            ->groupBy('attendance_id')
            ->map(fn ($attendanceExceptions) => $attendanceExceptions->first());

        $tableRows = $tableUsers->map(function (User $user) use ($attendanceLogsByAttendanceId, $attendanceExceptionsByAttendanceId, $attendancesTodayByEmployeeId, $todayDate, $employeeProfileNamesByEmployeeId): array {
            $attendanceToday = $attendancesTodayByEmployeeId->get($user->employee?->id);
            $attendanceLog = $attendanceToday ? $attendanceLogsByAttendanceId->get($attendanceToday->id) : null;
            $attendanceException = $attendanceToday ? $attendanceExceptionsByAttendanceId->get($attendanceToday->id) : null;
            $variance = $this->formatVariance($attendanceException?->from_time, $attendanceException?->to_time);
            $checkInValue = $attendanceToday?->clock_in?->format('H:i');
            $checkOutValue = $attendanceToday?->clock_out?->format('H:i');
            $workHours = $this->formatWorkHoursLabel($checkInValue, $checkOutValue, $attendanceToday?->work_hours);
            $employeeId = $user->employee?->id;
            $profileName = is_string($employeeId) ? $employeeProfileNamesByEmployeeId->get($employeeId) : null;
            $staffDisplayName = is_string($profileName) && trim($profileName) !== ''
                ? trim($profileName)
                : ((is_string($user->username) && trim($user->username) !== '')
                    ? trim($user->username)
                    : ((is_string($user->email) && trim($user->email) !== '') ? trim($user->email) : '-'));

            return [
                'attendance_id' => $attendanceToday?->id,
                'attendance_date' => $attendanceToday?->date?->translatedFormat('d M Y') ?? Carbon::parse($todayDate, 'Asia/Jakarta')->translatedFormat('d M Y'),
                'attendance_date_iso' => $attendanceToday?->date?->format('Y-m-d') ?? $todayDate,
                'staff_name' => $staffDisplayName,
                'company_name' => $user->employee?->deployment?->company?->name,
                'check_in' => $checkInValue,
                'check_out' => $checkOutValue,
                'variance' => $variance,
                'work_hours' => $workHours,
                'notes' => is_string($attendanceException?->note) && trim($attendanceException->note) !== '' ? trim($attendanceException->note) : null,
                'status' => $attendanceToday?->status,
                'row_type' => 'attendance',
                'is_virtual' => false,
                'check_in_latitude' => isset($attendanceLog?->latitude) ? (float) $attendanceLog->latitude : null,
                'check_in_longitude' => isset($attendanceLog?->longitude) ? (float) $attendanceLog->longitude : null,
                'distance_meters' => isset($attendanceLog?->distance) ? (float) $attendanceLog->distance : null,
                'radius_result' => isset($attendanceLog?->radius_result) ? (string) $attendanceLog->radius_result : null,
                'formatted_address' => isset($attendanceLog?->location) ? (string) $attendanceLog->location : null,
                'address_village' => isset($attendanceLog?->address_village) ? (string) $attendanceLog->address_village : null,
                'address_district' => isset($attendanceLog?->address_district) ? (string) $attendanceLog->address_district : null,
                'address_regency' => isset($attendanceLog?->address_regency) ? (string) $attendanceLog->address_regency : null,
                'address_city' => isset($attendanceLog?->address_city) ? (string) $attendanceLog->address_city : null,
                'address_province' => isset($attendanceLog?->address_province) ? (string) $attendanceLog->address_province : null,
                'address_postal_code' => isset($attendanceLog?->address_postal_code) ? (string) $attendanceLog->address_postal_code : null,
            ];
        })->values();

        return response()->json([
            'data' => $tableRows,
        ]);
    }

    public function exportReport(Request $request): Responsable
    {
        $datatableResponse = $this->datatable($request);
        $payload = $datatableResponse->getData(true);
        $reportRows = collect($payload['data'] ?? []);
        $nowJakarta = now('Asia/Jakarta');
        $selectedMonth = (int) $request->integer('month', (int) $nowJakarta->month);
        $selectedYear = (int) $request->integer('year', (int) $nowJakarta->year);

        $periodLabel = Carbon::create($selectedYear, max(1, min(12, $selectedMonth)), 1, 0, 0, 0, 'Asia/Jakarta')
            ->translatedFormat('F Y');

        return Pdf::view('absensi.report-pdf', [
            'rows' => $reportRows,
            'periodLabel' => $periodLabel,
            'generatedAt' => $nowJakarta->translatedFormat('d M Y H:i'),
            'userLabel' => Auth::user()?->username ?? Auth::user()?->email ?? '-',
        ])
            ->driver('dompdf')
            ->format(Format::A4)
            ->name('attendance-report-'.$selectedYear.'-'.str_pad((string) $selectedMonth, 2, '0', STR_PAD_LEFT).'.pdf')
            ->download();
    }

    private function isSuperUser(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $user->getRoleNames()
            ->map(fn (string $roleName): string => strtolower(trim($roleName)))
            ->contains('superuser');
    }

    private function isBoardOfDirectur(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        $normalizedRoleNames = $user->getRoleNames()
            ->map(fn (string $roleName): string => strtolower(trim($roleName)));

        return $normalizedRoleNames->contains('board of directur')
            || $normalizedRoleNames->contains('board of directors')
            || $normalizedRoleNames->contains('supervisor');
    }

    private function isStaffUser(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $user->getRoleNames()
            ->map(fn (string $roleName): string => strtolower(trim($roleName)))
            ->contains('staff');
    }

    private function formatVariance(mixed $fromTime, mixed $toTime): ?string
    {
        if (! $fromTime || ! $toTime) {
            return null;
        }

        $fromDateTime = Carbon::parse($fromTime);
        $toDateTime = Carbon::parse($toTime);
        $minutes = abs($fromDateTime->diffInMinutes($toDateTime, false));

        return number_format($minutes / 60, 2, '.', '');
    }

    /**
     * @return array<string, array{name:string,is_national_holiday:bool}>
     */
    private function buildHolidayMapByMonth(int $year, int $month): array
    {
        $holidayItems = $this->fetchPublicHolidaysByYear($year);
        $holidayMapByDate = [];

        foreach ($holidayItems as $holidayItem) {
            if (! is_array($holidayItem)) {
                continue;
            }

            $dateValue = isset($holidayItem['date']) ? trim((string) $holidayItem['date']) : '';
            $nameValue = isset($holidayItem['name']) ? trim((string) $holidayItem['name']) : '';
            if ($dateValue === '' || $nameValue === '') {
                continue;
            }

            $parsedDate = Carbon::parse($dateValue, 'Asia/Jakarta');
            if ((int) $parsedDate->month !== $month) {
                continue;
            }

            $holidayMapByDate[$parsedDate->toDateString()] = [
                'name' => $nameValue,
                'is_national_holiday' => (bool) ($holidayItem['is_national_holiday'] ?? false),
            ];
        }

        return $holidayMapByDate;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function fetchPublicHolidaysByYear(int $year): array
    {
        $cacheKey = 'public-holidays-deno-year-'.$year;

        return Cache::remember($cacheKey, now('Asia/Jakarta')->addHours(12), function () use ($year): array {
            try {
                $response = Http::timeout(8)
                    ->retry(2, 300)
                    ->acceptJson()
                    ->get('https://libur.deno.dev/api', ['year' => $year]);

                if (! $response->successful()) {
                    return [];
                }

                $payload = $response->json();

                return is_array($payload) ? $payload : [];
            } catch (\Throwable) {
                return [];
            }
        });
    }

    private function formatWorkHoursLabel(?string $clockInValue, ?string $clockOutValue, mixed $storedWorkHours): string
    {
        if (is_string($clockInValue) && is_string($clockOutValue)) {
            $clockInTimestamp = strtotime($clockInValue);
            $clockOutTimestamp = strtotime($clockOutValue);

            if ($clockInTimestamp !== false && $clockOutTimestamp !== false) {
                $minutes = max(0, (int) round(abs(($clockOutTimestamp - $clockInTimestamp) / 60)));

                return $this->formatMinutesToHoursLabel($minutes);
            }
        }

        if (is_numeric($storedWorkHours)) {
            $minutes = max(0, (int) round(((float) $storedWorkHours) * 60));

            return $this->formatMinutesToHoursLabel($minutes);
        }

        return '0 hours';
    }

    private function formatMinutesToHoursLabel(int $minutes): string
    {
        if ($minutes <= 0) {
            return '0 hours';
        }

        $hoursPart = intdiv($minutes, 60);
        $minutesPart = $minutes % 60;
        if ($minutesPart === 0) {
            return $hoursPart.' hours';
        }

        return $hoursPart.' hours, '.$minutesPart.' minutes';
    }

    private function resolveStaffEmploymentStartMonth(?string $employeeId, Carbon $nowJakarta): Carbon
    {
        if (! is_string($employeeId) || trim($employeeId) === '') {
            return $nowJakarta->copy()->startOfYear();
        }

        $deploymentJoinDateRaw = DB::table('employee_deployments')
            ->where('employee_id', $employeeId)
            ->whereNull('deleted_at')
            ->whereNotNull('join_date')
            ->orderBy('join_date')
            ->value('join_date');

        if (is_string($deploymentJoinDateRaw) && trim($deploymentJoinDateRaw) !== '') {
            return Carbon::parse($deploymentJoinDateRaw, 'Asia/Jakarta')->startOfMonth();
        }

        return $nowJakarta->copy()->startOfYear();
    }

    /**
     * @return Collection<int, int>
     */
    private function buildStaffYearOptions(Carbon $employmentStartMonth, Carbon $nowJakarta): Collection
    {
        $startYear = (int) $employmentStartMonth->year;
        $endYear = (int) $nowJakarta->year;

        if ($startYear > $endYear) {
            $startYear = $endYear;
        }

        return collect(range($startYear, $endYear))->values();
    }

    /**
     * @return Collection<int, int>
     */
    private function buildStaffMonthOptionsByYear(Carbon $employmentStartMonth, Carbon $nowJakarta, int $selectedYear): Collection
    {
        $startMonth = 1;
        $endMonth = 12;
        $currentYear = (int) $nowJakarta->year;
        $employmentStartYear = (int) $employmentStartMonth->year;

        if ($selectedYear === $employmentStartYear) {
            $startMonth = (int) $employmentStartMonth->month;
        }

        if ($selectedYear === $currentYear) {
            $endMonth = (int) $nowJakarta->month;
        }

        if ($startMonth > $endMonth) {
            $startMonth = $endMonth;
        }

        return collect(range($startMonth, $endMonth))->values();
    }
}
