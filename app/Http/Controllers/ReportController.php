<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Contracts\View\View;
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
