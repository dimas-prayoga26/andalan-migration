<?php

namespace Database\Seeders;

use App\Models\BusinessTrip;
use App\Models\BusinessTripCashAdvance;
use App\Models\BusinessTripLifecycleLog;
use App\Models\BusinessTripReimbursement;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class BusinessTripSeeder extends Seeder
{
    private const LIFECYCLE_STEPS = [
        [
            'phase' => 'trip_approval',
            'event_key' => 'submitted',
            'step_order' => 1,
            'title' => 'Trip Request Submitted',
        ],
        [
            'phase' => 'trip_approval',
            'event_key' => 'supervisor_review',
            'step_order' => 2,
            'title' => 'Supervisor Review',
        ],
        [
            'phase' => 'pre_trip_preparation',
            'event_key' => 'cash_advance_submitted',
            'step_order' => 3,
            'title' => 'Cash Advance Submitted',
        ],
        [
            'phase' => 'pre_trip_preparation',
            'event_key' => 'finance_disbursement',
            'step_order' => 4,
            'title' => 'Finance Disbursement',
        ],
        [
            'phase' => 'trip_execution',
            'event_key' => 'trip_execution',
            'step_order' => 5,
            'title' => 'Business Trip in Progress',
        ],
        [
            'phase' => 'post_trip_settlement',
            'event_key' => 'trip_report',
            'step_order' => 6,
            'title' => 'Trip Report & Task Submitted',
        ],
        [
            'phase' => 'post_trip_settlement',
            'event_key' => 'reimbursement_submitted',
            'step_order' => 7,
            'title' => 'Reimbursement Submitted',
        ],
        [
            'phase' => 'post_trip_settlement',
            'event_key' => 'finance_verification',
            'step_order' => 8,
            'title' => 'Final Finance Verification',
        ],
        [
            'phase' => 'post_trip_settlement',
            'event_key' => 'payment_distribution',
            'step_order' => 9,
            'title' => 'Reimbursement & Incentive Distributed',
        ],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        try {
            $startDate = Carbon::create(2026, 6, 10, 0, 0, 0, 'Asia/Jakarta');
            $endDate = Carbon::create(2026, 6, 15, 0, 0, 0, 'Asia/Jakarta');
            $staffScenarios = $this->staffScenarios();
            $rnbCompanyId = DB::table('companies')->where('name', 'RNB')->value('id');

            if (! is_string($rnbCompanyId) || trim($rnbCompanyId) === '') {
                throw new RuntimeException('Company RNB tidak ditemukan. Jalankan CompanySeeder terlebih dahulu.');
            }

            $staffUsers = User::query()
                ->whereIn('username', array_keys($staffScenarios))
                ->whereHas('roles', function ($query): void {
                    $query->whereRaw('LOWER(name) = ?', ['staff']);
                })
                ->whereHas('employee.deployment', function ($query) use ($rnbCompanyId): void {
                    $query->where('current_company_id', $rnbCompanyId);
                })
                ->with(['employee.deployment'])
                ->get()
                ->keyBy('username');

            $missingUsernames = collect(array_keys($staffScenarios))
                ->reject(fn (string $username): bool => $staffUsers->has($username))
                ->values();

            if ($missingUsernames->isNotEmpty()) {
                throw new RuntimeException('Akun staff31 sampai staff34 pada company RNB belum lengkap: '.$missingUsernames->implode(', ').'. Jalankan UserSeeder terlebih dahulu.');
            }

            DB::transaction(function () use ($endDate, $staffScenarios, $staffUsers, $startDate): void {
                foreach ($staffScenarios as $username => $scenario) {
                    /** @var User $staffUser */
                    $staffUser = $staffUsers->get($username);
                    $this->seedBusinessTrip($staffUser, $scenario, $startDate, $endDate);
                }
            });
        } catch (Throwable $throwable) {
            throw new RuntimeException('BusinessTripSeeder gagal dijalankan.', 0, $throwable);
        }
    }

    /**
     * @param  array{
     *     request_number: string,
     *     purpose: string,
     *     approval_status: string,
     *     submitted_at: string,
     *     lifecycle: array<string, array{status: string, actor: string|null, happened_at: string|null}>,
     *     cash_advances?: array<int, array{category: string, amount_requested: int, amount_realized?: int|null, status?: string, notes: string, attachment?: bool}>,
     *     reimbursements?: array<int, array{category: string, amount: int, notes: string, receipt?: bool}>
     * } $scenario
     */
    private function seedBusinessTrip(User $staffUser, array $scenario, Carbon $startDate, Carbon $endDate): void
    {
        $employeeId = trim((string) ($staffUser->employee?->id ?? ''));
        if ($employeeId === '') {
            throw new RuntimeException("Data employee untuk {$staffUser->username} tidak ditemukan.");
        }

        $supervisorEmployeeId = $this->resolveSupervisorEmployeeId($employeeId);
        $supervisorUserId = $this->resolveSupervisorUserId($supervisorEmployeeId);
        $submittedAt = Carbon::parse($scenario['submitted_at'], 'Asia/Jakarta');
        $businessTrip = BusinessTrip::withTrashed()
            ->where('request_number', $scenario['request_number'])
            ->first() ?? new BusinessTrip(['request_number' => $scenario['request_number']]);

        if ($businessTrip->exists && $businessTrip->trashed()) {
            $businessTrip->restore();
        }

        $businessTrip->fill([
            'employee_id' => $employeeId,
            'supervisor_employee_id' => $supervisorEmployeeId,
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'total_days' => (int) $startDate->diffInDays($endDate) + 1,
            'destination_zone' => 'Intercity (Luar Kota)',
            'province_destination' => 'DI Yogyakarta',
            'city_destination' => 'Sleman',
            'purpose' => $scenario['purpose'],
            'trip_type' => 'intercity',
            'transportation_arrangement' => 'self_managed',
            'accommodation_arrangement' => 'self_managed',
            'transportation_mode' => 'Train',
            'departure_date' => $startDate->toDateString(),
            'departure_time_window' => 'Morning',
            'check_in_date' => $startDate->toDateString(),
            'check_out_date' => $endDate->toDateString(),
            'submitted_at' => $submittedAt,
            'approval_status' => $scenario['approval_status'],
            'approved_by' => $scenario['approval_status'] === 'approved' ? $supervisorUserId : null,
            'approved_at' => $scenario['approval_status'] === 'approved' ? $submittedAt->copy()->addHour() : null,
            'rejected_at' => null,
            'payment_status' => 'pending',
            'payment_reference' => null,
            'daily_rate' => 0,
            'total_allowance' => 0,
        ]);
        $businessTrip->save();

        $this->resetBusinessTripDetails($businessTrip);
        $this->seedCashAdvances($businessTrip, $staffUser, $supervisorUserId, $scenario['cash_advances'] ?? [], $startDate, $endDate);
        $this->seedReimbursements($businessTrip, $staffUser, $scenario['reimbursements'] ?? [], $startDate);
        $this->seedLifecycleLogs($businessTrip, $staffUser, $supervisorUserId, $scenario['lifecycle']);
    }

    private function resolveSupervisorEmployeeId(string $employeeId): ?string
    {
        $supervisorEmployeeId = DB::table('employee_pic_assignments')
            ->where('staff_employee_id', $employeeId)
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->value('supervisor_employee_id');

        return is_string($supervisorEmployeeId) && trim($supervisorEmployeeId) !== ''
            ? trim($supervisorEmployeeId)
            : null;
    }

    private function resolveSupervisorUserId(?string $supervisorEmployeeId): ?string
    {
        if (! is_string($supervisorEmployeeId) || trim($supervisorEmployeeId) === '') {
            return null;
        }

        $supervisorUserId = DB::table('employees')
            ->where('id', $supervisorEmployeeId)
            ->value('user_id');

        return is_string($supervisorUserId) && trim($supervisorUserId) !== ''
            ? trim($supervisorUserId)
            : null;
    }

    private function resetBusinessTripDetails(BusinessTrip $businessTrip): void
    {
        BusinessTripCashAdvance::withTrashed()
            ->where('business_trip_id', $businessTrip->id)
            ->get()
            ->each(function (BusinessTripCashAdvance $cashAdvance): void {
                if (is_string($cashAdvance->attachment_path) && trim($cashAdvance->attachment_path) !== '') {
                    Storage::disk('public')->delete(trim($cashAdvance->attachment_path));
                }

                $cashAdvance->forceDelete();
            });

        BusinessTripReimbursement::withTrashed()
            ->where('business_trip_id', $businessTrip->id)
            ->get()
            ->each(function (BusinessTripReimbursement $reimbursement): void {
                if (is_string($reimbursement->receipt_path) && trim($reimbursement->receipt_path) !== '') {
                    Storage::disk('public')->delete(trim($reimbursement->receipt_path));
                }

                $reimbursement->forceDelete();
            });

        BusinessTripLifecycleLog::query()
            ->where('business_trip_id', $businessTrip->id)
            ->delete();
    }

    /**
     * @param  array<int, array{category: string, amount_requested: int, amount_realized?: int|null, status?: string, notes: string, attachment?: bool}>  $cashAdvances
     */
    private function seedCashAdvances(BusinessTrip $businessTrip, User $staffUser, ?string $supervisorUserId, array $cashAdvances, Carbon $startDate, Carbon $endDate): void
    {
        $hasDateNeededUntilColumn = Schema::hasColumn('business_trip_cash_advances', 'date_needed_until');

        foreach ($cashAdvances as $index => $cashAdvanceData) {
            $status = $cashAdvanceData['status'] ?? 'pending';
            $attachmentPath = (bool) ($cashAdvanceData['attachment'] ?? false)
                ? $this->seedFilePath(
                    'business-trip-cash-advance-attachments',
                    $businessTrip,
                    $cashAdvanceData['category'],
                    'cash-advance'
                )
                : null;

            $payload = [
                'business_trip_id' => $businessTrip->id,
                'requested_by' => $staffUser->employee?->id,
                'date_needed' => $startDate->copy()->addDays($index)->toDateString(),
                'category' => $cashAdvanceData['category'],
                'amount_requested' => $cashAdvanceData['amount_requested'],
                'amount_realized' => $cashAdvanceData['amount_realized'] ?? null,
                'notes' => $cashAdvanceData['notes'],
                'attachment_path' => $attachmentPath,
                'status' => $status,
                'amount_approved' => $status === 'approved' ? $cashAdvanceData['amount_requested'] : null,
                'approved_by' => $status === 'approved' ? $supervisorUserId : null,
                'approved_at' => $status === 'approved' ? $startDate->copy()->subDay()->setTime(15, 0) : null,
                'finance_notes' => $status === 'approved' ? 'Seed finance approval.' : null,
            ];

            if ($hasDateNeededUntilColumn) {
                $payload['date_needed_until'] = $endDate->toDateString();
            }

            BusinessTripCashAdvance::query()->create($payload);
        }
    }

    /**
     * @param  array<int, array{category: string, amount: int, notes: string, receipt?: bool}>  $reimbursements
     */
    private function seedReimbursements(BusinessTrip $businessTrip, User $staffUser, array $reimbursements, Carbon $startDate): void
    {
        foreach ($reimbursements as $index => $reimbursementData) {
            $receiptPath = (bool) ($reimbursementData['receipt'] ?? false)
                ? $this->seedFilePath(
                    'business-trip-reimbursement-receipts',
                    $businessTrip,
                    $reimbursementData['category'],
                    'reimbursement'
                )
                : null;

            BusinessTripReimbursement::query()->create([
                'business_trip_id' => $businessTrip->id,
                'requested_by' => $staffUser->employee?->id,
                'expense_date' => $startDate->copy()->addDays($index + 1)->toDateString(),
                'category' => $reimbursementData['category'],
                'amount' => $reimbursementData['amount'],
                'notes' => $reimbursementData['notes'],
                'receipt_path' => $receiptPath,
                'status' => 'pending',
                'amount_approved' => null,
                'approved_by' => null,
                'approved_at' => null,
                'finance_notes' => null,
            ]);
        }
    }

    /**
     * @param  array<string, array{status: string, actor: string|null, happened_at: string|null}>  $lifecycleValues
     */
    private function seedLifecycleLogs(BusinessTrip $businessTrip, User $staffUser, ?string $supervisorUserId, array $lifecycleValues): void
    {
        foreach (self::LIFECYCLE_STEPS as $lifecycleStep) {
            $eventKey = $lifecycleStep['event_key'];
            $lifecycleValue = $lifecycleValues[$eventKey] ?? [
                'status' => 'waiting',
                'actor' => null,
                'happened_at' => null,
            ];

            BusinessTripLifecycleLog::query()->create([
                'business_trip_id' => $businessTrip->id,
                'phase' => $lifecycleStep['phase'],
                'event_key' => $eventKey,
                'step_order' => $lifecycleStep['step_order'],
                'title' => $lifecycleStep['title'],
                'status' => $lifecycleValue['status'],
                'actor_id' => $this->lifecycleActorId($lifecycleValue['actor'], $staffUser, $supervisorUserId),
                'happened_at' => $lifecycleValue['happened_at'] !== null
                    ? Carbon::parse($lifecycleValue['happened_at'], 'Asia/Jakarta')
                    : null,
                'metadata' => [
                    'request_number' => $businessTrip->request_number,
                    'seeded_for' => $staffUser->username,
                ],
            ]);
        }
    }

    private function lifecycleActorId(?string $actor, User $staffUser, ?string $supervisorUserId): ?string
    {
        return match ($actor) {
            'staff' => $staffUser->id,
            'supervisor', 'finance' => $supervisorUserId,
            default => null,
        };
    }

    private function seedFilePath(string $folder, BusinessTrip $businessTrip, string $category, string $prefix): string
    {
        $fileName = $prefix.'-'.$category.'.pdf';
        $path = $folder.'/'.$businessTrip->id.'/'.$fileName;

        Storage::disk('public')->put(
            $path,
            "%PDF-1.4\n% Business trip seed placeholder for {$businessTrip->request_number} {$category}\n"
        );

        return $path;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function staffScenarios(): array
    {
        return [
            'staff31' => [
                'request_number' => 'TRP-RNB-STAFF31',
                'purpose' => 'Survey lokasi project RNB tahap awal.',
                'approval_status' => 'pending',
                'submitted_at' => '2026-06-05 09:00:00',
                'lifecycle' => [
                    'submitted' => [
                        'status' => 'complete',
                        'actor' => 'staff',
                        'happened_at' => '2026-06-05 09:00:00',
                    ],
                    'supervisor_review' => [
                        'status' => 'pending',
                        'actor' => null,
                        'happened_at' => null,
                    ],
                ],
            ],
            'staff32' => [
                'request_number' => 'TRP-RNB-STAFF32',
                'purpose' => 'Koordinasi persiapan kebutuhan operasional RNB.',
                'approval_status' => 'approved',
                'submitted_at' => '2026-06-05 09:15:00',
                'lifecycle' => [
                    'submitted' => [
                        'status' => 'complete',
                        'actor' => 'staff',
                        'happened_at' => '2026-06-05 09:15:00',
                    ],
                    'supervisor_review' => [
                        'status' => 'complete',
                        'actor' => 'supervisor',
                        'happened_at' => '2026-06-05 10:00:00',
                    ],
                    'cash_advance_submitted' => [
                        'status' => 'pending',
                        'actor' => 'staff',
                        'happened_at' => '2026-06-05 11:00:00',
                    ],
                ],
                'cash_advances' => [
                    [
                        'category' => 'local_transport',
                        'amount_requested' => 500000,
                        'notes' => 'Local transport preparation request.',
                    ],
                ],
            ],
            'staff33' => [
                'request_number' => 'TRP-RNB-STAFF33',
                'purpose' => 'Pelaksanaan perjalanan dinas RNB sampai submit trip report.',
                'approval_status' => 'approved',
                'submitted_at' => '2026-06-05 09:30:00',
                'lifecycle' => [
                    'submitted' => [
                        'status' => 'complete',
                        'actor' => 'staff',
                        'happened_at' => '2026-06-05 09:30:00',
                    ],
                    'supervisor_review' => [
                        'status' => 'complete',
                        'actor' => 'supervisor',
                        'happened_at' => '2026-06-05 10:15:00',
                    ],
                    'cash_advance_submitted' => [
                        'status' => 'complete',
                        'actor' => 'staff',
                        'happened_at' => '2026-06-05 11:15:00',
                    ],
                    'finance_disbursement' => [
                        'status' => 'complete',
                        'actor' => 'finance',
                        'happened_at' => '2026-06-06 15:00:00',
                    ],
                    'trip_execution' => [
                        'status' => 'complete',
                        'actor' => 'staff',
                        'happened_at' => '2026-06-15 17:00:00',
                    ],
                    'trip_report' => [
                        'status' => 'complete',
                        'actor' => 'staff',
                        'happened_at' => '2026-06-16 09:00:00',
                    ],
                ],
                'cash_advances' => [
                    [
                        'category' => 'transportation',
                        'amount_requested' => 1200000,
                        'amount_realized' => 1150000,
                        'status' => 'approved',
                        'notes' => 'Transportation cash advance.',
                        'attachment' => true,
                    ],
                    [
                        'category' => 'accommodation',
                        'amount_requested' => 1800000,
                        'amount_realized' => 1750000,
                        'status' => 'approved',
                        'notes' => 'Accommodation cash advance.',
                        'attachment' => true,
                    ],
                    [
                        'category' => 'meals_entertainment',
                        'amount_requested' => 900000,
                        'amount_realized' => 850000,
                        'status' => 'approved',
                        'notes' => 'Meals & Entertaintment cash advance.',
                        'attachment' => true,
                    ],
                    [
                        'category' => 'local_transport',
                        'amount_requested' => 650000,
                        'amount_realized' => 600000,
                        'status' => 'approved',
                        'notes' => 'Local Transport cash advance.',
                        'attachment' => true,
                    ],
                ],
            ],
            'staff34' => [
                'request_number' => 'TRP-RNB-STAFF34',
                'purpose' => 'Perjalanan dinas RNB sampai reimbursement submitted.',
                'approval_status' => 'approved',
                'submitted_at' => '2026-06-05 09:45:00',
                'lifecycle' => [
                    'submitted' => [
                        'status' => 'complete',
                        'actor' => 'staff',
                        'happened_at' => '2026-06-05 09:45:00',
                    ],
                    'supervisor_review' => [
                        'status' => 'complete',
                        'actor' => 'supervisor',
                        'happened_at' => '2026-06-05 10:30:00',
                    ],
                    'cash_advance_submitted' => [
                        'status' => 'complete',
                        'actor' => 'staff',
                        'happened_at' => '2026-06-05 11:30:00',
                    ],
                    'finance_disbursement' => [
                        'status' => 'complete',
                        'actor' => 'finance',
                        'happened_at' => '2026-06-06 15:30:00',
                    ],
                    'trip_execution' => [
                        'status' => 'complete',
                        'actor' => 'staff',
                        'happened_at' => '2026-06-15 17:30:00',
                    ],
                    'trip_report' => [
                        'status' => 'complete',
                        'actor' => 'staff',
                        'happened_at' => '2026-06-16 09:30:00',
                    ],
                    'reimbursement_submitted' => [
                        'status' => 'complete',
                        'actor' => 'staff',
                        'happened_at' => '2026-06-16 11:00:00',
                    ],
                ],
                'cash_advances' => [
                    [
                        'category' => 'local_transport',
                        'amount_requested' => 600000,
                        'amount_realized' => 575000,
                        'status' => 'approved',
                        'notes' => 'Local Transport cash advance.',
                        'attachment' => true,
                    ],
                    [
                        'category' => 'meals_entertainment',
                        'amount_requested' => 850000,
                        'amount_realized' => 800000,
                        'status' => 'approved',
                        'notes' => 'Meals & Entertaintment cash advance.',
                        'attachment' => true,
                    ],
                ],
                'reimbursements' => [
                    [
                        'category' => 'transportation',
                        'amount' => 450000,
                        'notes' => 'Transportation reimbursement.',
                        'receipt' => true,
                    ],
                    [
                        'category' => 'accommodation',
                        'amount' => 700000,
                        'notes' => 'Accommodation reimbursement.',
                        'receipt' => true,
                    ],
                ],
            ],
        ];
    }
}
