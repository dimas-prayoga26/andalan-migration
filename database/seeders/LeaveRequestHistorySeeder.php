<?php

namespace Database\Seeders;

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
            $hrActorUserId = (string) ($boardUsers->skip(1)->first()->id ?? $supervisorActorUserId);

            $sickLeaveTypeId = LeaveType::query()
                ->whereRaw('LOWER(code) = ?', ['sick'])
                ->value('id');
            $annualLeaveTypeId = LeaveType::query()
                ->whereRaw('LOWER(code) = ?', ['annual'])
                ->value('id');

            if (! is_string($sickLeaveTypeId) || trim($sickLeaveTypeId) === '') {
                throw new RuntimeException('Leave type SICK tidak ditemukan.');
            }
            if (! is_string($annualLeaveTypeId) || trim($annualLeaveTypeId) === '') {
                throw new RuntimeException('Leave type ANNUAL tidak ditemukan.');
            }

            $baseDay = now('Asia/Jakarta')->startOfDay()->subDays(2);
            $seedPlans = [
                [
                    'leave_type_id' => trim($sickLeaveTypeId),
                    'start_date' => $baseDay->copy()->toDateString(),
                    'end_date' => $baseDay->copy()->addDay()->toDateString(),
                    'total_days' => 2,
                    'reason' => '[Seeder] RNB dummy leave request approved',
                    'status' => 'approved',
                    'approved_by' => $hrActorUserId,
                    'approved_at' => $baseDay->copy()->setTime(16, 30, 0),
                    'histories' => [
                        [
                            'event_type' => 'submitted',
                            'title' => 'Request Submitted',
                            'from_status' => null,
                            'to_status' => 'pending',
                            'actor_user_id' => $staffActorUserId,
                            'happened_at' => $baseDay->copy()->setTime(8, 10, 0),
                        ],
                        [
                            'event_type' => 'supervisor_review',
                            'title' => 'Supervisor Review',
                            'from_status' => 'pending',
                            'to_status' => 'pending',
                            'actor_user_id' => $supervisorActorUserId,
                            'happened_at' => $baseDay->copy()->setTime(9, 0, 0),
                        ],
                        [
                            'event_type' => 'hr_verification',
                            'title' => 'HR Verification (Pending)',
                            'from_status' => 'pending',
                            'to_status' => 'pending',
                            'actor_user_id' => $hrActorUserId,
                            'happened_at' => $baseDay->copy()->setTime(10, 30, 0),
                        ],
                        [
                            'event_type' => 'status_updated',
                            'title' => 'Approved',
                            'from_status' => 'pending',
                            'to_status' => 'approved',
                            'actor_user_id' => $hrActorUserId,
                            'happened_at' => $baseDay->copy()->setTime(16, 30, 0),
                        ],
                    ],
                ],
                [
                    'leave_type_id' => trim($annualLeaveTypeId),
                    'start_date' => $baseDay->copy()->addDays(2)->toDateString(),
                    'end_date' => $baseDay->copy()->addDays(3)->toDateString(),
                    'total_days' => 2,
                    'reason' => '[Seeder] RNB dummy leave request rejected',
                    'status' => 'rejected',
                    'approved_by' => $hrActorUserId,
                    'approved_at' => $baseDay->copy()->addDays(1)->setTime(15, 45, 0),
                    'histories' => [
                        [
                            'event_type' => 'submitted',
                            'title' => 'Request Submitted',
                            'from_status' => null,
                            'to_status' => 'pending',
                            'actor_user_id' => $staffActorUserId,
                            'happened_at' => $baseDay->copy()->addDay()->setTime(8, 20, 0),
                        ],
                        [
                            'event_type' => 'supervisor_review',
                            'title' => 'Supervisor Review',
                            'from_status' => 'pending',
                            'to_status' => 'pending',
                            'actor_user_id' => $supervisorActorUserId,
                            'happened_at' => $baseDay->copy()->addDay()->setTime(9, 30, 0),
                        ],
                        [
                            'event_type' => 'hr_verification',
                            'title' => 'HR Verification (Pending)',
                            'from_status' => 'pending',
                            'to_status' => 'pending',
                            'actor_user_id' => $hrActorUserId,
                            'happened_at' => $baseDay->copy()->addDay()->setTime(11, 0, 0),
                        ],
                        [
                            'event_type' => 'status_updated',
                            'title' => 'Rejected',
                            'from_status' => 'pending',
                            'to_status' => 'rejected',
                            'actor_user_id' => $hrActorUserId,
                            'happened_at' => $baseDay->copy()->addDay()->setTime(15, 45, 0),
                        ],
                    ],
                ],
                [
                    'leave_type_id' => trim($annualLeaveTypeId),
                    'start_date' => $baseDay->copy()->addDays(5)->toDateString(),
                    'end_date' => $baseDay->copy()->addDays(5)->toDateString(),
                    'total_days' => 1,
                    'reason' => '[Seeder] RNB dummy leave request supervisor review only',
                    'status' => 'pending',
                    'approved_by' => null,
                    'approved_at' => null,
                    'histories' => [
                        [
                            'event_type' => 'submitted',
                            'title' => 'Request Submitted',
                            'from_status' => null,
                            'to_status' => 'pending',
                            'actor_user_id' => $staffActorUserId,
                            'happened_at' => $baseDay->copy()->addDays(4)->setTime(8, 5, 0),
                        ],
                        [
                            'event_type' => 'supervisor_review',
                            'title' => 'Supervisor Review',
                            'from_status' => 'pending',
                            'to_status' => 'pending',
                            'actor_user_id' => $supervisorActorUserId,
                            'happened_at' => $baseDay->copy()->addDays(4)->setTime(9, 10, 0),
                        ],
                    ],
                ],
            ];

            DB::transaction(function () use ($seedPlans, $staffEmployeeId): void {
                foreach ($seedPlans as $seedPlan) {
                    $leaveRequest = LeaveRequest::query()->updateOrCreate(
                        [
                            'employee_id' => $staffEmployeeId,
                            'start_date' => $seedPlan['start_date'],
                            'end_date' => $seedPlan['end_date'],
                            'reason' => $seedPlan['reason'],
                        ],
                        [
                            'leave_type_id' => $seedPlan['leave_type_id'],
                            'total_days' => $seedPlan['total_days'],
                            'status' => $seedPlan['status'],
                            'is_active' => true,
                            'approved_by' => $seedPlan['approved_by'],
                            'approved_at' => $seedPlan['approved_at'],
                            'attachment_path' => null,
                            'deleted_at' => null,
                        ]
                    );

                    LeaveRequestHistory::query()
                        ->where('leave_request_id', $leaveRequest->id)
                        ->delete();

                    foreach ($seedPlan['histories'] as $historyPlan) {
                        LeaveRequestHistory::query()->create([
                            'leave_request_id' => $leaveRequest->id,
                            'actor_user_id' => $historyPlan['actor_user_id'],
                            'event_type' => $historyPlan['event_type'],
                            'title' => $historyPlan['title'],
                            'from_status' => $historyPlan['from_status'],
                            'to_status' => $historyPlan['to_status'],
                            'notes' => null,
                            'metadata' => null,
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
}
