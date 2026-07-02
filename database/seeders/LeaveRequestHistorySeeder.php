<?php

namespace Database\Seeders;

use App\Models\AttendanceHoliday;
use App\Models\Company;
use App\Models\LeaveRequest;
use App\Models\LeaveRequestHistory;
use App\Models\LeaveType;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Throwable;

class LeaveRequestHistorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        try {
            if (
                ! Schema::hasTable('leave_requests')
                || ! Schema::hasTable('leave_request_histories')
                || ! Schema::hasTable('companies')
                || ! Schema::hasTable('leave_types')
                || ! Schema::hasTable('leave_sub_types')
            ) {
                return;
            }

            $rnbCompanyId = Company::query()
                ->whereRaw('LOWER(name) = ?', ['rnb'])
                ->value('id');
            if (! is_string($rnbCompanyId) || trim($rnbCompanyId) === '') {
                throw new RuntimeException('Company RNB tidak ditemukan.');
            }

            $staffUser = User::query()
                ->where('username', 'staff31')
                ->whereHas('roles', function ($query): void {
                    $query->whereRaw('LOWER(name) = ?', ['staff']);
                })
                ->whereHas('employee.deployment', function ($query) use ($rnbCompanyId): void {
                    $query->where('current_company_id', $rnbCompanyId);
                })
                ->with(['employee:id,user_id'])
                ->orderBy('username')
                ->first();

            $staffEmployeeId = is_string($staffUser?->employee?->id) ? trim($staffUser->employee->id) : '';
            $staffActorUserId = is_string($staffUser?->id) ? trim($staffUser->id) : '';
            if ($staffEmployeeId === '' || $staffActorUserId === '') {
                throw new RuntimeException('User Staff pada company RNB tidak ditemukan.');
            }

            $boardUsers = User::query()
                ->whereHas('roles', function ($query): void {
                    $query->whereRaw('LOWER(name) = ?', ['board of directors']);
                })
                ->whereHas('employee.deployment', function ($query) use ($rnbCompanyId): void {
                    $query->where('current_company_id', $rnbCompanyId);
                })
                ->orderBy('username')
                ->get(['id', 'username']);

            if ($boardUsers->isEmpty()) {
                throw new RuntimeException('User role Board of Directors pada company RNB tidak ditemukan.');
            }

            $supervisorActorUserId = (string) $boardUsers->first()->id;

            $leaveTypeIdsByCode = LeaveType::query()
                ->whereIn(DB::raw('LOWER(code)'), ['annual', 'sick', 'special', 'unpaid'])
                ->get(['id', 'code'])
                ->mapWithKeys(static function (LeaveType $leaveType): array {
                    return [strtolower(trim((string) $leaveType->code)) => trim((string) $leaveType->id)];
                });

            foreach (['annual', 'sick', 'special', 'unpaid'] as $requiredLeaveTypeCode) {
                if (! is_string($leaveTypeIdsByCode->get($requiredLeaveTypeCode)) || trim((string) $leaveTypeIdsByCode->get($requiredLeaveTypeCode)) === '') {
                    throw new RuntimeException('Leave type '.strtoupper($requiredLeaveTypeCode).' tidak ditemukan.');
                }
            }

            $specialLeaveSubType = DB::table('leave_sub_types')
                ->where('leave_type_id', $leaveTypeIdsByCode->get('special'))
                ->where('is_active', true)
                ->orderBy('name')
                ->first(['id', 'code', 'name']);

            $specialLeaveSubTypeId = is_string($specialLeaveSubType?->id ?? null) ? trim((string) $specialLeaveSubType->id) : '';
            $specialLeaveSubTypeName = is_string($specialLeaveSubType?->name ?? null) ? trim((string) $specialLeaveSubType->name) : '';
            if ($specialLeaveSubTypeId === '' || $specialLeaveSubTypeName === '') {
                throw new RuntimeException('Leave subtype aktif untuk SPECIAL tidak ditemukan.');
            }

            $baseDay = now('Asia/Jakarta')->startOfDay()->subDays(2);
            $seedWorkingDates = $this->nextSeedWorkingDates($baseDay, 4, $this->seedBlockedDateValues());
            [$specialLeaveDate, $annualLeaveDate, $sickLeaveDate, $unpaidLeaveDate] = $seedWorkingDates;

            $seedPlans = [
                [
                    'leave_type_id' => $leaveTypeIdsByCode->get('special'),
                    'start_date' => $specialLeaveDate->copy()->toDateString(),
                    'end_date' => $specialLeaveDate->copy()->toDateString(),
                    'total_days' => 1,
                    'reason' => '[Seeder] RNB special leave - '.$specialLeaveSubTypeName,
                    'handover_notes' => 'Seed handover notes for special leave.',
                    'status' => 'pending',
                    'approved_by' => null,
                    'approved_at' => null,
                    'metadata' => [
                        'special_leave_sub_type_id' => $specialLeaveSubTypeId,
                        'special_leave_sub_type_name' => $specialLeaveSubTypeName,
                    ],
                    'histories' => [
                        [
                            'event_type' => 'submitted',
                            'title' => 'Request Submitted',
                            'from_status' => null,
                            'to_status' => 'pending',
                            'actor_user_id' => $staffActorUserId,
                            'happened_at' => $specialLeaveDate->copy()->setTime(8, 10, 0),
                        ],
                    ],
                ],
                [
                    'leave_type_id' => $leaveTypeIdsByCode->get('annual'),
                    'start_date' => $annualLeaveDate->copy()->toDateString(),
                    'end_date' => $annualLeaveDate->copy()->toDateString(),
                    'total_days' => 1,
                    'reason' => '[Seeder] RNB annual leave pending',
                    'handover_notes' => 'Seed handover notes for annual leave.',
                    'status' => 'pending',
                    'approved_by' => null,
                    'approved_at' => null,
                    'metadata' => null,
                    'histories' => [
                        [
                            'event_type' => 'submitted',
                            'title' => 'Request Submitted',
                            'from_status' => null,
                            'to_status' => 'pending',
                            'actor_user_id' => $staffActorUserId,
                            'happened_at' => $annualLeaveDate->copy()->setTime(8, 20, 0),
                        ],
                        [
                            'event_type' => 'supervisor_review',
                            'title' => 'Supervisor Review',
                            'from_status' => 'pending',
                            'to_status' => 'pending',
                            'actor_user_id' => $supervisorActorUserId,
                            'happened_at' => $annualLeaveDate->copy()->setTime(9, 30, 0),
                        ],
                    ],
                ],
                [
                    'leave_type_id' => $leaveTypeIdsByCode->get('sick'),
                    'start_date' => $sickLeaveDate->copy()->toDateString(),
                    'end_date' => $sickLeaveDate->copy()->toDateString(),
                    'total_days' => 1,
                    'reason' => '[Seeder] RNB sick leave pending',
                    'handover_notes' => 'Seed handover notes for sick leave.',
                    'status' => 'pending',
                    'approved_by' => null,
                    'approved_at' => null,
                    'metadata' => null,
                    'histories' => [
                        [
                            'event_type' => 'submitted',
                            'title' => 'Request Submitted',
                            'from_status' => null,
                            'to_status' => 'pending',
                            'actor_user_id' => $staffActorUserId,
                            'happened_at' => $sickLeaveDate->copy()->setTime(8, 5, 0),
                        ],
                        [
                            'event_type' => 'supervisor_review',
                            'title' => 'Supervisor Review',
                            'from_status' => 'pending',
                            'to_status' => 'approved',
                            'actor_user_id' => $supervisorActorUserId,
                            'happened_at' => $sickLeaveDate->copy()->setTime(9, 10, 0),
                        ],
                    ],
                ],
                [
                    'leave_type_id' => $leaveTypeIdsByCode->get('unpaid'),
                    'start_date' => $unpaidLeaveDate->copy()->toDateString(),
                    'end_date' => $unpaidLeaveDate->copy()->toDateString(),
                    'total_days' => 1,
                    'reason' => '[Seeder] RNB unpaid leave pending',
                    'handover_notes' => 'Seed handover notes for unpaid leave.',
                    'status' => 'pending',
                    'approved_by' => null,
                    'approved_at' => null,
                    'metadata' => null,
                    'histories' => [
                        [
                            'event_type' => 'submitted',
                            'title' => 'Request Submitted',
                            'from_status' => null,
                            'to_status' => 'pending',
                            'actor_user_id' => $staffActorUserId,
                            'happened_at' => $unpaidLeaveDate->copy()->setTime(8, 15, 0),
                        ],
                        [
                            'event_type' => 'supervisor_review',
                            'title' => 'Supervisor Review',
                            'from_status' => 'pending',
                            'to_status' => 'approved',
                            'actor_user_id' => $supervisorActorUserId,
                            'happened_at' => $unpaidLeaveDate->copy()->setTime(9, 20, 0),
                        ],
                    ],
                ],
            ];

            DB::transaction(function () use ($seedPlans, $staffEmployeeId): void {
                $seedReasons = collect($seedPlans)
                    ->pluck('reason')
                    ->all();
                $legacySeededLeaveRequestIds = LeaveRequest::query()
                    ->where(function ($query) use ($seedReasons): void {
                        $query
                            ->whereIn('reason', $seedReasons)
                            ->orWhere('reason', 'like', '[Seeder] RNB dummy leave request%')
                            ->orWhere('reason', 'like', '[Seeder] RNB special leave - %')
                            ->orWhere('reason', 'like', '[Seeder] RNB % leave approved');
                    })
                    ->pluck('id')
                    ->filter()
                    ->all();

                if ($legacySeededLeaveRequestIds !== []) {
                    LeaveRequestHistory::query()
                        ->whereIn('leave_request_id', $legacySeededLeaveRequestIds)
                        ->delete();
                    LeaveRequest::query()
                        ->whereIn('id', $legacySeededLeaveRequestIds)
                        ->delete();
                }

                foreach ($seedPlans as $seedPlan) {
                    $leaveRequestPayload = [
                        'employee_id' => $staffEmployeeId,
                        'leave_type_id' => $seedPlan['leave_type_id'],
                        'start_date' => $seedPlan['start_date'],
                        'end_date' => $seedPlan['end_date'],
                        'total_days' => $seedPlan['total_days'],
                        'reason' => $seedPlan['reason'],
                        'status' => $seedPlan['status'],
                        'is_active' => true,
                        'approved_by' => $seedPlan['approved_by'],
                        'approved_at' => $seedPlan['approved_at'],
                        'attachment_path' => null,
                        'deleted_at' => null,
                    ];

                    if (Schema::hasColumn('leave_requests', 'handover_notes')) {
                        $leaveRequestPayload['handover_notes'] = $seedPlan['handover_notes'];
                    }

                    $leaveRequest = LeaveRequest::query()->create($leaveRequestPayload);

                    foreach ($seedPlan['histories'] as $historyPlan) {
                        LeaveRequestHistory::query()->create([
                            'leave_request_id' => $leaveRequest->id,
                            'actor_user_id' => $historyPlan['actor_user_id'],
                            'event_type' => $historyPlan['event_type'],
                            'title' => $historyPlan['title'],
                            'from_status' => $historyPlan['from_status'],
                            'to_status' => $historyPlan['to_status'],
                            'notes' => null,
                            'metadata' => $seedPlan['metadata'],
                            'happened_at' => $historyPlan['happened_at'] instanceof Carbon
                                ? $historyPlan['happened_at']
                                : Carbon::parse((string) $historyPlan['happened_at']),
                        ]);
                    }
                }
            });
        } catch (Throwable $throwable) {
            throw new RuntimeException('LeaveRequestHistorySeeder gagal dijalankan.', 0, $throwable);
        }
    }

    /**
     * @param  list<string>  $blockedDateValues
     * @return list<Carbon>
     */
    private function nextSeedWorkingDates(Carbon $startDate, int $requiredDates, array $blockedDateValues): array
    {
        $blockedDateSet = collect($blockedDateValues)
            ->map(static function (string $blockedDateValue): string {
                return Carbon::parse($blockedDateValue, 'Asia/Jakarta')->toDateString();
            })
            ->flip()
            ->all();
        $workingDates = [];
        $cursorDate = $startDate->copy()->startOfDay();

        while (count($workingDates) < $requiredDates) {
            if (! $cursorDate->isWeekend() && ! array_key_exists($cursorDate->toDateString(), $blockedDateSet)) {
                $workingDates[] = $cursorDate->copy();
            }

            $cursorDate->addDay();
        }

        return $workingDates;
    }

    /**
     * @return list<string>
     */
    private function seedBlockedDateValues(): array
    {
        if (! Schema::hasTable('attendances_holidays')) {
            return [];
        }

        return AttendanceHoliday::query()
            ->pluck('date')
            ->map(static function (mixed $dateValue): ?string {
                if ($dateValue instanceof Carbon) {
                    return $dateValue->toDateString();
                }

                $dateValue = trim((string) $dateValue);
                if ($dateValue === '') {
                    return null;
                }

                return Carbon::parse($dateValue, 'Asia/Jakarta')->toDateString();
            })
            ->filter(static fn (?string $dateValue): bool => $dateValue !== null)
            ->values()
            ->all();
    }
}
