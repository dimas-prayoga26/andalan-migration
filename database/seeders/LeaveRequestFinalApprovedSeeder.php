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

class LeaveRequestFinalApprovedSeeder extends Seeder
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
                ->get(['id']);

            if ($boardUsers->isEmpty()) {
                throw new RuntimeException('User role Board of Directors pada company RNB tidak ditemukan.');
            }

            $supervisorActorUserId = (string) $boardUsers->first()->id;
            $hrActorUserId = (string) ($boardUsers->skip(1)->first()->id ?? $supervisorActorUserId);
            $finalDecisionActorUserId = (string) ($boardUsers->skip(2)->first()->id ?? $hrActorUserId);

            $annualLeaveTypeId = LeaveType::query()
                ->whereRaw('LOWER(code) = ?', ['annual'])
                ->value('id');
            if (! is_string($annualLeaveTypeId) || trim($annualLeaveTypeId) === '') {
                throw new RuntimeException('Leave type ANNUAL tidak ditemukan.');
            }

            $leaveDate = $this->nextSeedWorkingDate(
                now('Asia/Jakarta')->startOfDay()->addDays(2),
                $this->seedBlockedDateValues()
            );
            $seedReason = '[Seeder] RNB annual leave final approved';
            $finalDecisionAt = $leaveDate->copy()->setTime(11, 0, 0);

            DB::transaction(function () use (
                $annualLeaveTypeId,
                $finalDecisionActorUserId,
                $finalDecisionAt,
                $hrActorUserId,
                $leaveDate,
                $seedReason,
                $staffActorUserId,
                $staffEmployeeId,
                $supervisorActorUserId
            ): void {
                $seededLeaveRequestIds = LeaveRequest::query()
                    ->where('reason', $seedReason)
                    ->pluck('id')
                    ->all();

                if ($seededLeaveRequestIds !== []) {
                    LeaveRequestHistory::query()
                        ->whereIn('leave_request_id', $seededLeaveRequestIds)
                        ->delete();
                    LeaveRequest::query()
                        ->whereIn('id', $seededLeaveRequestIds)
                        ->delete();
                }

                $leaveRequestPayload = [
                    'employee_id' => $staffEmployeeId,
                    'leave_type_id' => $annualLeaveTypeId,
                    'start_date' => $leaveDate->toDateString(),
                    'end_date' => $leaveDate->toDateString(),
                    'total_days' => 1,
                    'reason' => $seedReason,
                    'status' => 'approved',
                    'is_active' => true,
                    'approved_by' => $finalDecisionActorUserId,
                    'approved_at' => $finalDecisionAt,
                    'attachment_path' => null,
                    'deleted_at' => null,
                ];

                if (Schema::hasColumn('leave_requests', 'handover_notes')) {
                    $leaveRequestPayload['handover_notes'] = 'Seed annual leave approved through the final decision.';
                }

                $leaveRequest = LeaveRequest::query()->create($leaveRequestPayload);

                $historyPlans = [
                    [
                        'actor_user_id' => $staffActorUserId,
                        'event_type' => 'submitted',
                        'title' => 'Request Submitted',
                        'from_status' => null,
                        'to_status' => 'pending',
                        'happened_at' => $leaveDate->copy()->setTime(8, 0, 0),
                    ],
                    [
                        'actor_user_id' => $supervisorActorUserId,
                        'event_type' => 'supervisor_review',
                        'title' => 'Supervisor Review',
                        'from_status' => 'pending',
                        'to_status' => 'approved',
                        'happened_at' => $leaveDate->copy()->setTime(9, 0, 0),
                    ],
                    [
                        'actor_user_id' => $finalDecisionActorUserId,
                        'event_type' => 'status_updated',
                        'title' => 'Final Decision',
                        'from_status' => 'pending',
                        'to_status' => 'approved',
                        'happened_at' => $finalDecisionAt,
                    ],
                ];

                foreach ($historyPlans as $historyPlan) {
                    LeaveRequestHistory::query()->create([
                        'leave_request_id' => $leaveRequest->id,
                        'actor_user_id' => $historyPlan['actor_user_id'],
                        'event_type' => $historyPlan['event_type'],
                        'title' => $historyPlan['title'],
                        'from_status' => $historyPlan['from_status'],
                        'to_status' => $historyPlan['to_status'],
                        'notes' => null,
                        'metadata' => null,
                        'happened_at' => $historyPlan['happened_at'],
                    ]);
                }

                $hrVerificationHistory = LeaveRequestHistory::query()
                    ->where('leave_request_id', $leaveRequest->id)
                    ->where('event_type', 'hr_verification')
                    ->firstOrFail();
                $hrVerificationHistory->update([
                    'actor_user_id' => $hrActorUserId,
                    'to_status' => 'approved',
                    'happened_at' => $leaveDate->copy()->setTime(10, 0, 0),
                ]);
            });
        } catch (Throwable $throwable) {
            throw new RuntimeException('LeaveRequestFinalApprovedSeeder gagal dijalankan.', 0, $throwable);
        }
    }

    /**
     * @param  list<string>  $blockedDateValues
     */
    private function nextSeedWorkingDate(Carbon $startDate, array $blockedDateValues): Carbon
    {
        $blockedDateSet = collect($blockedDateValues)
            ->map(static fn (string $dateValue): string => Carbon::parse($dateValue, 'Asia/Jakarta')->toDateString())
            ->flip()
            ->all();
        $cursorDate = $startDate->copy()->startOfDay();

        while ($cursorDate->isWeekend() || array_key_exists($cursorDate->toDateString(), $blockedDateSet)) {
            $cursorDate->addDay();
        }

        return $cursorDate;
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

                return $dateValue === ''
                    ? null
                    : Carbon::parse($dateValue, 'Asia/Jakarta')->toDateString();
            })
            ->filter(static fn (?string $dateValue): bool => $dateValue !== null)
            ->values()
            ->all();
    }
}
