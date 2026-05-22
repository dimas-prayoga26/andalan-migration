<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceLog;
use App\Models\EmployeeProfile;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $authenticatedUser = Auth::user();
        if ($authenticatedUser instanceof User) {
            $authenticatedUser->loadMissing('employee.deployment');
        }
        $userId = Auth::id();
        $employeeId = $authenticatedUser?->employee?->id;
        $isSuperUser = $this->isSuperUser($authenticatedUser);
        $isStaffUser = $this->isStaffUser($authenticatedUser);
        $nowJakarta = now('Asia/Jakarta');
        $publicIp = '-';
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
            $staffYearOptions = Attendance::query()
                ->where('employee_id', $employeeId)
                ->selectRaw('DISTINCT YEAR(`date`) as year_value')
                ->orderByDesc('year_value')
                ->pluck('year_value')
                ->filter(static fn (mixed $yearValue): bool => is_numeric($yearValue))
                ->map(static fn (mixed $yearValue): int => (int) $yearValue)
                ->values();

            if ($staffYearOptions->isEmpty()) {
                $staffYearOptions = collect([(int) $nowJakarta->year]);
            }

            if (! $staffYearOptions->contains($defaultStaffYear)) {
                $staffYearOptions = $staffYearOptions
                    ->push($defaultStaffYear)
                    ->unique()
                    ->sortDesc()
                    ->values();
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
        $absensiHariIni = null;
        if (is_string($employeeId) && trim($employeeId) !== '') {
            $absensiHariIni = Attendance::query()
                ->where('date', now()->format('Y-m-d'))
                ->where('employee_id', $employeeId)
                ->first();
        }
        $todayAttendanceId = $absensiHariIni?->id;
        $hasCheckedInToday = ! empty($absensiHariIni?->clock_in);
        $hasCheckedOutToday = ! empty($absensiHariIni?->clock_out);
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

        $officeLocation = $this->resolveOfficeContext($userId);

        $clientIpAddress = $this->resolveClientIpAddress($request);
        $ipdataData = $this->fetchIpdata($clientIpAddress);
        if (! empty($ipdataData['ip'])) {
            $publicIp = (string) $ipdataData['ip'];
        }

        $allowedIpRange = is_array($officeLocation) ? ($officeLocation['ip_range'] ?? null) : null;
        $publicIpPrefix = $this->extractIpTwoOctets($publicIp);
        $allowedIpPrefix = $this->extractIpTwoOctets($allowedIpRange);
        $isIpPrefixMatch = $publicIpPrefix !== null
            && $allowedIpPrefix !== null
            && $publicIpPrefix === $allowedIpPrefix;

        return view('absensi.report', compact(
            'attendance',
            'izin',
            'lembur',
            'agEvent',
            'totalLemburJam',
            'absensiHariIni',
            'officeLocation',
            'publicIp',
            'publicIpPrefix',
            'allowedIpPrefix',
            'isIpPrefixMatch',
            'companies',
            'showCompanyFilter',
            'showStaffPeriodFilter',
            'staffMonthOptions',
            'staffYearOptions',
            'defaultStaffMonth',
            'defaultStaffYear',
            'todayAttendanceId',
            'hasCheckedInToday',
            'hasCheckedOutToday',
        ));
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

            $staffAttendances = Attendance::query()
                ->where('employee_id', $staffEmployeeId)
                ->whereMonth('date', $selectedMonth)
                ->whereYear('date', $selectedYear)
                ->orderByDesc('date')
                ->orderByDesc('created_at')
                ->get(['id', 'date', 'status', 'clock_in', 'clock_out', 'created_at']);

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

            $tableRows = $staffAttendances->map(function (Attendance $attendanceItem) use ($attendanceLogsByAttendanceId, $staffDisplayName, $staffUser): array {
                $attendanceLog = $attendanceLogsByAttendanceId->get($attendanceItem->id);

                return [
                    'attendance_id' => $attendanceItem->id,
                    'attendance_date' => $attendanceItem->date?->format('Y-m-d'),
                    'attendance_created_at' => $attendanceItem->date?->format('Y-m-d'),
                    'staff_name' => $staffDisplayName,
                    'company_name' => $staffUser->employee?->deployment?->company?->name,
                    'check_in' => $attendanceItem->clock_in?->format('H:i'),
                    'check_out' => $attendanceItem->clock_out?->format('H:i'),
                    'status' => $attendanceItem->status,
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
            ->get(['id', 'employee_id', 'date', 'clock_in', 'clock_out', 'status'])
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

        $tableRows = $tableUsers->map(function (User $user) use ($attendanceLogsByAttendanceId, $attendancesTodayByEmployeeId, $todayDate, $employeeProfileNamesByEmployeeId): array {
            $attendanceToday = $attendancesTodayByEmployeeId->get($user->employee?->id);
            $attendanceLog = $attendanceToday ? $attendanceLogsByAttendanceId->get($attendanceToday->id) : null;
            $employeeId = $user->employee?->id;
            $profileName = is_string($employeeId) ? $employeeProfileNamesByEmployeeId->get($employeeId) : null;
            $staffDisplayName = is_string($profileName) && trim($profileName) !== ''
                ? trim($profileName)
                : ((is_string($user->username) && trim($user->username) !== '')
                    ? trim($user->username)
                    : ((is_string($user->email) && trim($user->email) !== '') ? trim($user->email) : '-'));

            return [
                'attendance_id' => $attendanceToday?->id,
                'attendance_date' => $attendanceToday?->date?->format('Y-m-d') ?? $todayDate,
                'staff_name' => $staffDisplayName,
                'company_name' => $user->employee?->deployment?->company?->name,
                'check_in' => $attendanceToday?->clock_in?->format('H:i'),
                'check_out' => $attendanceToday?->clock_out?->format('H:i'),
                'status' => $attendanceToday?->status,
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

    /**
     * @return array<string, mixed>
     */
    private function fetchIpdata(?string $ipAddress = null): array
    {
        $ipdataApiKey = config('services.ipdata.api_key');

        if (empty($ipdataApiKey)) {
            return [];
        }

        try {
            $endpoint = 'https://api.ipdata.co';
            if ($ipAddress) {
                $endpoint .= '/'.rawurlencode($ipAddress);
            }

            $ipdataResponse = Http::timeout(7)
                ->acceptJson()
                ->get($endpoint, [
                    'api-key' => $ipdataApiKey,
                ]);

            if (! $ipdataResponse->successful()) {
                return [];
            }

            $ipdataData = $ipdataResponse->json();

            return is_array($ipdataData) ? $ipdataData : [];
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * @return array{
     *     name:string|null,
     *     address:string|null,
     *     latitude:float,
     *     longitude:float,
     *     radius_meters:int,
     *     ip_range:string|null,
     *     office_start_time:string,
     *     office_end_time:string
     * }|null
     */
    private function resolveOfficeContext(int|string|null $userId): ?array
    {
        if (! is_string($userId) && ! is_int($userId)) {
            return null;
        }

        $currentUser = User::query()
            ->with([
                'employee.deployment.company:id,name,address,latitude,longitude',
                'employee.deployment.company.activeAttendanceRule' => static function ($query): void {
                    $query->select([
                        'rules_of_attendaces.id',
                        'rules_of_attendaces.companies_id',
                        'rules_of_attendaces.radius',
                        'rules_of_attendaces.ip_range',
                        'rules_of_attendaces.office_start_time',
                        'rules_of_attendaces.office_end_time',
                    ]);
                },
            ])
            ->find($userId);

        $officeCompany = $currentUser?->employee?->deployment?->company;

        if (! $officeCompany || $officeCompany->latitude === null || $officeCompany->longitude === null) {
            return null;
        }

        $attendanceRule = $officeCompany->activeAttendanceRule;

        return [
            'name' => $officeCompany->name,
            'address' => $officeCompany->address,
            'latitude' => (float) $officeCompany->latitude,
            'longitude' => (float) $officeCompany->longitude,
            'radius_meters' => (int) ($attendanceRule->radius ?? 10),
            'ip_range' => isset($attendanceRule?->ip_range) ? (string) $attendanceRule->ip_range : null,
            'office_start_time' => isset($attendanceRule?->office_start_time) && is_string($attendanceRule->office_start_time)
                ? $attendanceRule->office_start_time
                : '08:00:00',
            'office_end_time' => isset($attendanceRule?->office_end_time) && is_string($attendanceRule->office_end_time)
                ? $attendanceRule->office_end_time
                : '17:00:00',
        ];
    }

    private function extractIpTwoOctets(?string $ipValue): ?string
    {
        if ($ipValue === null) {
            return null;
        }

        $matches = [];
        if (preg_match('/(\d{1,3})\.(\d{1,3})/', $ipValue, $matches) !== 1) {
            return null;
        }

        $firstOctet = (int) $matches[1];
        $secondOctet = (int) $matches[2];

        if ($firstOctet > 255 || $secondOctet > 255) {
            return null;
        }

        return $firstOctet.'.'.$secondOctet;
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
            || $normalizedRoleNames->contains('board of directors');
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

    private function resolveClientIpAddress(Request $request, mixed $preferredIpAddress = null): ?string
    {
        if (is_string($preferredIpAddress) && filter_var($preferredIpAddress, FILTER_VALIDATE_IP)) {
            return $preferredIpAddress;
        }

        $requestIpAddress = $request->ip();
        if (is_string($requestIpAddress) && filter_var($requestIpAddress, FILTER_VALIDATE_IP)) {
            return $requestIpAddress;
        }

        return null;
    }
}
