<?php

namespace App\View\Composers;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class AbsensiProfileComposer
{
    public function compose(View $view): void
    {
        $nowJakarta = now('Asia/Jakarta');
        $profileData = [
            'profilePicturePath' => null,
            'profilePositionName' => '-',
            'profileAddressSummary' => '-',
            'profileBusinessEmail' => '-',
            'profileDisplayName' => '-',
            'profileAttendanceDaysCount' => 0,
            'profileWorkingDaysCount' => 0,
            'profileWorkingMonthLabel' => $nowJakarta->format('F'),
            'profileStatsMode' => 'staff',
            'managementTotalEmployeesCount' => 0,
            'managementPresentTodayCount' => 0,
            'managementLateTodayCount' => 0,
            'managementLeaveTodayCount' => 0,
        ];

        $authenticatedUserId = Auth::id();
        $authenticatedUser = (is_string($authenticatedUserId) || is_int($authenticatedUserId))
            ? User::query()
                ->with([
                    'userProfile:id,user_id,profile_picture',
                    'employee:id,user_id',
                    'employee.profile:id,employee_id,name',
                    'employee.deployment:id,employee_id,current_company_id,current_position_id',
                    'employee.deployment.position:id,name',
                    'employee.latestAddress' => static function ($query): void {
                        $query->select([
                            'employee_addresses.id',
                            'employee_addresses.employee_id',
                            'employee_addresses.village',
                            'employee_addresses.subdistrict',
                            'employee_addresses.created_at',
                        ]);
                    },
                ])
                ->find($authenticatedUserId)
            : null;
        if (! $authenticatedUser instanceof User) {
            $view->with($profileData);

            return;
        }
        $employeeId = is_string($authenticatedUser->employee?->id) ? trim($authenticatedUser->employee->id) : '';
        $isStaffUser = $this->isStaffUser($authenticatedUser);
        $isBoardOfDirectur = $this->isBoardOfDirectur($authenticatedUser);

        $profileData['profileStatsMode'] = $isStaffUser ? 'staff' : 'management';

        if (is_string($authenticatedUser->userProfile?->profile_picture) && trim($authenticatedUser->userProfile->profile_picture) !== '') {
            $profileData['profilePicturePath'] = trim($authenticatedUser->userProfile->profile_picture);
        }

        if (is_string($authenticatedUser->business_email) && trim($authenticatedUser->business_email) !== '') {
            $profileData['profileBusinessEmail'] = trim($authenticatedUser->business_email);
        } elseif (is_string($authenticatedUser->email) && trim($authenticatedUser->email) !== '') {
            $profileData['profileBusinessEmail'] = trim($authenticatedUser->email);
        }

        if (is_string($authenticatedUser->username) && trim($authenticatedUser->username) !== '') {
            $profileData['profileDisplayName'] = trim($authenticatedUser->username);
        } elseif (is_string($authenticatedUser->email) && trim($authenticatedUser->email) !== '') {
            $profileData['profileDisplayName'] = (string) explode('@', trim($authenticatedUser->email))[0];
        }

        if ($employeeId !== '') {
            $employeeProfileName = $authenticatedUser->employee?->profile?->name;
            if (is_string($employeeProfileName) && trim($employeeProfileName) !== '') {
                $profileData['profileDisplayName'] = trim($employeeProfileName);
            }

            $positionName = $authenticatedUser->employee?->deployment?->position?->name;
            if (is_string($positionName) && trim($positionName) !== '') {
                $profileData['profilePositionName'] = trim($positionName);
            }

            $latestAddress = $authenticatedUser->employee?->latestAddress;
            $villageName = is_string($latestAddress?->village) ? trim($latestAddress->village) : '';
            $subdistrictName = is_string($latestAddress?->subdistrict) ? trim($latestAddress->subdistrict) : '';
            if ($villageName !== '' && $subdistrictName !== '') {
                $profileData['profileAddressSummary'] = $villageName.', '.$subdistrictName;
            } elseif ($villageName !== '') {
                $profileData['profileAddressSummary'] = $villageName;
            } elseif ($subdistrictName !== '') {
                $profileData['profileAddressSummary'] = $subdistrictName;
            }

            $profileData['profileAttendanceDaysCount'] = Attendance::query()
                ->where('employee_id', $employeeId)
                ->whereYear('date', (int) $nowJakarta->year)
                ->whereMonth('date', (int) $nowJakarta->month)
                ->whereNotNull('clock_in')
                ->count();
        }

        $profileData['profileWorkingDaysCount'] = $this->calculateWorkingDaysInMonth($nowJakarta);

        if (! $isStaffUser) {
            $employeeScopeQuery = Employee::query()->select('id');

            if ($isBoardOfDirectur) {
                $currentCompanyId = $authenticatedUser->employee?->deployment?->current_company_id;
                if (! is_string($currentCompanyId) || trim($currentCompanyId) === '') {
                    $employeeScopeQuery->whereRaw('1 = 0');
                } else {
                    $employeeScopeQuery->whereHas('deployment', function ($query) use ($currentCompanyId): void {
                        $query->where('current_company_id', $currentCompanyId);
                    });
                }
            }

            $scopedEmployeeIds = $employeeScopeQuery
                ->pluck('id')
                ->filter(static fn (mixed $value): bool => is_string($value) && trim($value) !== '')
                ->values();

            $profileData['managementTotalEmployeesCount'] = $scopedEmployeeIds->count();

            if ($profileData['managementTotalEmployeesCount'] > 0) {
                $todayDate = $nowJakarta->toDateString();
                $attendanceTodayQuery = Attendance::query()
                    ->whereIn('employee_id', $scopedEmployeeIds)
                    ->whereDate('date', $todayDate);

                $profileData['managementPresentTodayCount'] = (clone $attendanceTodayQuery)
                    ->whereNotNull('clock_in')
                    ->count();

                $profileData['managementLateTodayCount'] = (clone $attendanceTodayQuery)
                    ->whereRaw('LOWER(COALESCE(status, "")) = ?', ['terlambat'])
                    ->count();

                $profileData['managementLeaveTodayCount'] = LeaveRequest::query()
                    ->whereIn('employee_id', $scopedEmployeeIds)
                    ->whereDate('start_date', '<=', $todayDate)
                    ->whereDate('end_date', '>=', $todayDate)
                    ->where('status', 'approved')
                    ->where('is_active', true)
                    ->whereNull('deleted_at')
                    ->count();
            }
        }

        $view->with($profileData);
    }

    private function calculateWorkingDaysInMonth(Carbon $referenceDate): int
    {
        $monthStart = $referenceDate->copy()->startOfMonth();
        $monthEnd = $referenceDate->copy()->endOfMonth();
        $holidayDates = $this->fetchIndonesiaHolidayDates((int) $referenceDate->year);
        $holidayMap = array_fill_keys($holidayDates, true);
        $workingDays = 0;

        for ($day = $monthStart->copy(); $day->lte($monthEnd); $day->addDay()) {
            if ($day->isWeekend()) {
                continue;
            }

            if (isset($holidayMap[$day->format('Y-m-d')])) {
                continue;
            }

            $workingDays++;
        }

        return $workingDays;
    }

    /**
     * @return array<int, string>
     */
    private function fetchIndonesiaHolidayDates(int $year): array
    {
        $cacheKey = "libur-deno:indonesia:{$year}";
        $cacheExpiry = now('Asia/Jakarta')->endOfDay();

        return Cache::remember($cacheKey, $cacheExpiry, function () use ($year): array {
            try {
                $response = Http::timeout(10)
                    ->acceptJson()
                    ->get('https://libur.deno.dev/api', [
                        'year' => $year,
                    ]);

                if (! $response->successful()) {
                    return [];
                }

                $payload = $response->json();
                if (! is_array($payload)) {
                    return [];
                }

                $items = $payload['value'] ?? $payload;
                if (! is_array($items)) {
                    return [];
                }

                $holidayDates = [];
                foreach ($items as $item) {
                    if (! is_array($item)) {
                        continue;
                    }

                    $dateValue = $item['date'] ?? null;
                    if (! is_string($dateValue) || trim($dateValue) === '') {
                        continue;
                    }

                    $holidayDates[] = trim($dateValue);
                }

                return array_values(array_unique($holidayDates));
            } catch (\Throwable) {
                return [];
            }
        });
    }

    private function isBoardOfDirectur(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        $normalizedRoleNames = $user->getRoleNames()
            ->map(static fn (string $roleName): string => strtolower(trim($roleName)));

        return $normalizedRoleNames->contains('board of directur')
            || $normalizedRoleNames->contains('board of directors');
    }

    private function isStaffUser(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $user->getRoleNames()
            ->map(static fn (string $roleName): string => strtolower(trim($roleName)))
            ->contains('staff');
    }
}
