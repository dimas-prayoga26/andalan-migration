<?php

namespace App\Http\Controllers;

use App\Models\BusinessTrip;
use App\Models\BusinessTripLifecycleLog;
use App\Models\Employee;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rule;
use Throwable;

class BusinessTripController extends Controller
{
    private const WILAYAH_API_BASE_URL = 'https://wilayah.id/api';

    private const BUSINESS_TRIP_LIFECYCLE_PHASES = [
        'trip_approval' => [
            'title' => 'Phase 1: Trip Approval',
            'sort_order' => 1,
        ],
        'pre_trip_preparation' => [
            'title' => 'Phase 2: Pre-Trip Preparation',
            'sort_order' => 2,
        ],
        'trip_execution' => [
            'title' => 'Phase 3: Trip Execution',
            'sort_order' => 3,
        ],
        'post_trip_settlement' => [
            'title' => 'Phase 4: Post-Trip Reporting & Settlement',
            'sort_order' => 4,
        ],
    ];

    private const BUSINESS_TRIP_LIFECYCLE_STEPS = [
        [
            'phase' => 'trip_approval',
            'event_key' => 'submitted',
            'step_order' => 1,
            'title' => 'Trip Request Submitted',
            'status' => 'complete',
        ],
        [
            'phase' => 'trip_approval',
            'event_key' => 'supervisor_review',
            'step_order' => 2,
            'title' => 'Supervisor Review',
            'status' => 'pending',
        ],
        [
            'phase' => 'pre_trip_preparation',
            'event_key' => 'cash_advance_submitted',
            'step_order' => 3,
            'title' => 'Cash Advance Submitted',
            'status' => 'waiting',
        ],
        [
            'phase' => 'pre_trip_preparation',
            'event_key' => 'finance_disbursement',
            'step_order' => 4,
            'title' => 'Finance Disbursement',
            'status' => 'waiting',
        ],
        [
            'phase' => 'trip_execution',
            'event_key' => 'trip_execution',
            'step_order' => 5,
            'title' => 'Business Trip in Progress',
            'status' => 'waiting',
        ],
        [
            'phase' => 'post_trip_settlement',
            'event_key' => 'trip_report',
            'step_order' => 6,
            'title' => 'Trip Report & Task Submitted',
            'status' => 'waiting',
        ],
        [
            'phase' => 'post_trip_settlement',
            'event_key' => 'reimbursement_submitted',
            'step_order' => 7,
            'title' => 'Reimbursement Submitted',
            'status' => 'waiting',
        ],
        [
            'phase' => 'post_trip_settlement',
            'event_key' => 'finance_verification',
            'step_order' => 8,
            'title' => 'Final Finance Verification',
            'status' => 'waiting',
        ],
        [
            'phase' => 'post_trip_settlement',
            'event_key' => 'payment_distribution',
            'step_order' => 9,
            'title' => 'Reimbursement & Incentive Distributed',
            'status' => 'waiting',
        ],
    ];

    public function index(): View
    {
        $businessTrips = BusinessTrip::query()
            ->with(['cashAdvances', 'reimbursements'])
            ->latest('created_at')
            ->get();

        return view('attendance.business-trips.index', [
            'businessTripSummary' => $this->buildBusinessTripSummary($businessTrips),
            'businessTripCards' => $businessTrips->map(fn (BusinessTrip $businessTrip): array => $this->buildBusinessTripCard($businessTrip)),
        ]);
    }

    public function create(): View
    {
        return view('attendance.business-trips.create');
    }

    public function show(BusinessTrip $businessTrip): View
    {
        $businessTrip->loadMissing([
            'employee.profile',
            'employee.user',
            'supervisor.profile',
            'supervisor.user',
            'cashAdvances',
            'reimbursements',
            'lifecycleLogs.actor',
            'lifecycleLogs.actor.employee.profile',
            'lifecycleLogs.actor.userProfile',
        ]);

        $businessTripRequestDetails = $this->buildBusinessTripRequestDetails($businessTrip);

        return view('attendance.business-trips.detail', [
            'businessTrip' => $businessTrip,
            'businessTripRequestDetails' => $businessTripRequestDetails,
            'businessTripDetailPermissions' => $this->buildBusinessTripDetailPermissions($businessTrip),
            'businessTripLifecycleTracker' => $this->buildBusinessTripLifecycleTracker($businessTrip),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'purpose' => ['required', 'string', 'max:5000'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'trip_type' => ['required', Rule::in(['local', 'intercity'])],
            'province_destination' => ['required', 'string', 'max:255'],
            'province_code' => ['nullable', 'string', 'max:20'],
            'city_destination' => ['required', 'string', 'max:255'],
            'city_regency_code' => ['nullable', 'string', 'max:20'],
            'transportation_arrangement' => ['required', Rule::in(['self_managed', 'booked_by_ga'])],
            'accommodation_arrangement' => ['required', Rule::in(['self_managed', 'booked_by_ga', 'not_needed'])],
            'transportation_mode' => ['required', Rule::in(['flight', 'bus', 'train', 'car'])],
            'departure_date' => [
                Rule::requiredIf($request->input('transportation_arrangement') === 'self_managed'),
                'nullable',
                'date',
            ],
            'departure_time_window' => [
                Rule::requiredIf($request->input('transportation_arrangement') === 'self_managed'),
                'nullable',
                Rule::in(['morning', 'afternoon', 'evening', 'early_morning']),
            ],
            'check_in_date' => [
                Rule::requiredIf($request->input('accommodation_arrangement') === 'self_managed'),
                'nullable',
                'date',
            ],
            'check_out_date' => [
                Rule::requiredIf($request->input('accommodation_arrangement') === 'self_managed'),
                'nullable',
                'date',
                'after_or_equal:check_in_date',
            ],
        ]);

        $authenticatedUser = Auth::user();
        if ($authenticatedUser instanceof User) {
            $authenticatedUser->loadMissing(['employee.profile', 'userProfile']);
        }

        $actorUser = $authenticatedUser instanceof User ? $authenticatedUser : null;
        $employeeId = trim((string) ($authenticatedUser?->employee?->id ?? ''));
        if ($employeeId === '') {
            return back()
                ->withInput()
                ->withErrors(['employee' => 'Data karyawan tidak ditemukan.']);
        }

        $startDate = Carbon::parse($validated['start_date']);
        $endDate = Carbon::parse($validated['end_date']);
        $totalDays = (int) $startDate->diffInDays($endDate) + 1;

        DB::transaction(function () use ($actorUser, $employeeId, $endDate, $startDate, $totalDays, $validated): void {
            $submittedAt = now();

            $businessTrip = BusinessTrip::query()->create([
                'employee_id' => $employeeId,
                'request_number' => $this->generateRequestNumber(),
                'supervisor_employee_id' => $this->resolveSupervisorEmployeeId($employeeId),
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
                'total_days' => $totalDays,
                'destination_zone' => $this->destinationZoneLabel((string) $validated['trip_type']),
                'province_destination' => trim((string) $validated['province_destination']),
                'city_destination' => trim((string) $validated['city_destination']),
                'purpose' => trim((string) $validated['purpose']),
                'trip_type' => $validated['trip_type'],
                'transportation_arrangement' => $validated['transportation_arrangement'],
                'accommodation_arrangement' => $validated['accommodation_arrangement'],
                'transportation_mode' => $validated['transportation_mode'],
                'departure_date' => $validated['departure_date'] ?? null,
                'departure_time_window' => $validated['departure_time_window'] ?? null,
                'check_in_date' => $validated['check_in_date'] ?? null,
                'check_out_date' => $validated['check_out_date'] ?? null,
                'submitted_at' => $submittedAt,
                'approval_status' => 'pending',
                'payment_status' => 'pending',
                'daily_rate' => 0,
                'total_allowance' => 0,
            ]);

            $this->createInitialLifecycleLogs($businessTrip, $actorUser, $submittedAt);
        });

        return redirect()
            ->route('attendance.business-trips')
            ->with('success', 'Business trip request berhasil disimpan.');
    }

    public function provinces(): JsonResponse
    {
        return $this->wilayahResponse('/provinces.json');
    }

    public function regencies(string $provinceCode): JsonResponse
    {
        if (! preg_match('/^\d{2}$/', $provinceCode)) {
            return response()->json([
                'data' => [],
                'message' => 'Invalid province code.',
            ], 422);
        }

        return $this->wilayahResponse('/regencies/'.$provinceCode.'.json');
    }

    private function wilayahResponse(string $path): JsonResponse
    {
        try {
            $response = Http::timeout(10)
                ->acceptJson()
                ->get(self::WILAYAH_API_BASE_URL.$path);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'data' => [],
                'message' => 'Failed to load location data.',
            ], 502);
        }

        if ($response->failed()) {
            return response()->json([
                'data' => [],
                'message' => 'Failed to load location data.',
            ], 502);
        }

        return response()->json($response->json());
    }

    private function destinationZoneLabel(string $tripType): string
    {
        return match ($tripType) {
            'local' => 'Local (Dalam Kota)',
            'intercity' => 'Intercity (Luar Kota)',
            default => $tripType,
        };
    }

    private function buildBusinessTripSummary(Collection $businessTrips): array
    {
        $today = now('Asia/Jakarta')->startOfDay();
        $cashAdvanceTotal = $businessTrips
            ->flatMap(fn (BusinessTrip $businessTrip) => $businessTrip->cashAdvances)
            ->filter(fn ($cashAdvance): bool => in_array(strtolower((string) $cashAdvance->status), ['pending', 'approved'], true))
            ->sum(fn ($cashAdvance): float => (float) ($cashAdvance->amount_approved ?? $cashAdvance->amount_requested ?? 0));
        $reimbursementTotal = $businessTrips
            ->flatMap(fn (BusinessTrip $businessTrip) => $businessTrip->reimbursements)
            ->filter(fn ($reimbursement): bool => strtolower((string) $reimbursement->status) === 'pending')
            ->sum(fn ($reimbursement): float => (float) ($reimbursement->amount_approved ?? $reimbursement->amount ?? 0));

        return [
            'total_trips' => $businessTrips->count(),
            'total_days_away' => (int) $businessTrips->sum('total_days'),
            'pending_approvals' => $businessTrips
                ->filter(fn (BusinessTrip $businessTrip): bool => strtolower((string) $businessTrip->approval_status) === 'pending')
                ->count(),
            'upcoming_scheduled' => $businessTrips
                ->filter(fn (BusinessTrip $businessTrip): bool => $businessTrip->start_date !== null && $businessTrip->start_date->greaterThan($today))
                ->count(),
            'active_cash_advance' => $this->formatRupiah($cashAdvanceTotal),
            'pending_reimbursement' => $this->formatRupiah($reimbursementTotal),
            'overdue_reports' => $businessTrips
                ->filter(fn (BusinessTrip $businessTrip): bool => $businessTrip->end_date !== null
                    && $businessTrip->end_date->lt($today)
                    && strtolower((string) $businessTrip->payment_status) !== 'paid')
                ->count(),
            'successfully_settled' => $businessTrips
                ->filter(fn (BusinessTrip $businessTrip): bool => strtolower((string) $businessTrip->payment_status) === 'paid')
                ->count(),
        ];
    }

    private function buildBusinessTripCard(BusinessTrip $businessTrip): array
    {
        $startDate = $businessTrip->start_date;
        $endDate = $businessTrip->end_date;
        $status = strtolower((string) ($businessTrip->approval_status ?: 'pending'));

        return [
            'request_number' => '#'.($businessTrip->request_number ?: $businessTrip->id),
            'detail_url' => route('attendance.business-trips.show', $businessTrip),
            'location' => collect([$businessTrip->city_destination, $businessTrip->province_destination])
                ->filter(fn ($value): bool => is_string($value) && trim($value) !== '')
                ->implode(', '),
            'purpose' => (string) $businessTrip->purpose,
            'date_label' => $startDate !== null && $endDate !== null
                ? $startDate->format('d M Y').' - '.$endDate->format('d M Y').' ('.(int) $businessTrip->total_days.' Days)'
                : '-',
            'due_label' => $endDate?->format('d M Y') ?? '-',
            'progress_percentage' => 0,
            'status_label' => $this->businessTripStatusLabel($status),
            'status_badge_class' => $this->businessTripStatusBadgeClass($status),
        ];
    }

    private function buildBusinessTripRequestDetails(BusinessTrip $businessTrip): array
    {
        return [
            'full_name' => $this->employeeDisplayName($businessTrip->employee),
            'supervisor_name' => $this->employeeDisplayName($businessTrip->supervisor),
            'purpose' => trim((string) $businessTrip->purpose) !== '' ? trim((string) $businessTrip->purpose) : '-',
            'trip_type' => $this->businessTripTypeLabel($businessTrip),
            'destination' => collect([$businessTrip->city_destination, $businessTrip->province_destination])
                ->filter(fn ($value): bool => is_string($value) && trim($value) !== '')
                ->map(fn (string $value): string => trim($value))
                ->implode(', ') ?: '-',
            'date_range' => $this->formatBusinessTripDateRange($businessTrip),
            'duration' => $this->formatBusinessTripDuration((int) $businessTrip->total_days),
        ];
    }

    private function buildBusinessTripDetailPermissions(BusinessTrip $businessTrip): array
    {
        $supervisorReviewApproved = $this->isApprovedLifecycleStatus((string) $businessTrip->approval_status)
            || $this->businessTripLifecycleEventIsApproved($businessTrip, 'supervisor_review');
        $cashAdvanceApproved = $businessTrip->cashAdvances
            ->contains(fn ($cashAdvance): bool => $this->isApprovedLifecycleStatus((string) $cashAdvance->status))
            || $this->businessTripLifecycleEventIsApproved($businessTrip, 'cash_advance_submitted')
            || $this->businessTripLifecycleEventIsApproved($businessTrip, 'finance_disbursement');
        $cashAdvanceDetailsReady = $businessTrip->cashAdvances->isNotEmpty()
            && ($supervisorReviewApproved || $this->businessTripLifecycleEventHasStarted($businessTrip, 'cash_advance_submitted'));
        $reimbursementDetailsReady = $this->businessTripHasCompletedReimbursementReport($businessTrip)
            || $this->businessTripLifecycleEventIsApproved($businessTrip, 'trip_report');

        return [
            'can_view_trip_expense_values' => $cashAdvanceApproved,
            'can_view_cash_advance_values' => $cashAdvanceDetailsReady,
            'can_view_reimbursement_values' => $reimbursementDetailsReady,
            'can_use_action_buttons' => $supervisorReviewApproved,
        ];
    }

    private function businessTripHasCompletedReimbursementReport(BusinessTrip $businessTrip): bool
    {
        return $businessTrip->reimbursements->isNotEmpty()
            && $businessTrip->reimbursements->every(fn ($reimbursement): bool => trim((string) $reimbursement->receipt_path) !== '');
    }

    private function businessTripLifecycleEventIsApproved(BusinessTrip $businessTrip, string $eventKey): bool
    {
        $lifecycleLog = $businessTrip->lifecycleLogs
            ->first(fn (BusinessTripLifecycleLog $lifecycleLog): bool => $lifecycleLog->event_key === $eventKey);

        return $lifecycleLog instanceof BusinessTripLifecycleLog
            && $this->isApprovedLifecycleStatus((string) $lifecycleLog->status);
    }

    private function businessTripLifecycleEventHasStarted(BusinessTrip $businessTrip, string $eventKey): bool
    {
        $lifecycleLog = $businessTrip->lifecycleLogs
            ->first(fn (BusinessTripLifecycleLog $lifecycleLog): bool => $lifecycleLog->event_key === $eventKey);

        return $lifecycleLog instanceof BusinessTripLifecycleLog
            && ! in_array(strtolower(trim((string) $lifecycleLog->status)), ['', 'waiting'], true);
    }

    private function isApprovedLifecycleStatus(string $status): bool
    {
        return in_array(strtolower(trim($status)), ['approved', 'complete', 'completed', 'paid', 'done', 'success'], true);
    }

    private function createInitialLifecycleLogs(BusinessTrip $businessTrip, ?User $actor, Carbon $submittedAt): void
    {
        foreach (self::BUSINESS_TRIP_LIFECYCLE_STEPS as $lifecycleStep) {
            $isSubmittedStep = $lifecycleStep['event_key'] === 'submitted';

            BusinessTripLifecycleLog::query()->create([
                'business_trip_id' => $businessTrip->id,
                'phase' => $lifecycleStep['phase'],
                'event_key' => $lifecycleStep['event_key'],
                'step_order' => $lifecycleStep['step_order'],
                'title' => $lifecycleStep['title'],
                'status' => $lifecycleStep['status'],
                'actor_id' => $isSubmittedStep ? $actor?->id : null,
                'happened_at' => $isSubmittedStep ? $submittedAt : null,
                'metadata' => [
                    'request_number' => $businessTrip->request_number,
                    'approval_status' => $businessTrip->approval_status,
                    'payment_status' => $businessTrip->payment_status,
                ],
            ]);
        }
    }

    private function buildBusinessTripLifecycleTracker(BusinessTrip $businessTrip): Collection
    {
        return $businessTrip->lifecycleLogs
            ->sortBy('step_order')
            ->groupBy('phase')
            ->map(function (Collection $lifecycleLogs, string $phase): array {
                $items = $lifecycleLogs
                    ->map(fn (BusinessTripLifecycleLog $lifecycleLog): array => $this->lifecycleValueFromLog($lifecycleLog))
                    ->values();
                $phaseValue = $this->lifecyclePhaseValue($items->all());
                $phaseConfig = self::BUSINESS_TRIP_LIFECYCLE_PHASES[$phase] ?? null;

                return [
                    'phase' => $phase,
                    'title' => $phaseConfig['title'] ?? ucwords(str_replace('_', ' ', $phase)),
                    'sort_order' => $phaseConfig['sort_order'] ?? 999,
                    'date_label' => $phaseValue['date_label'],
                    'marker_class' => $phaseValue['marker_class'],
                    'items' => $items,
                ];
            })
            ->sortBy('sort_order')
            ->values();
    }

    private function lifecyclePhaseValue(array $items): array
    {
        $itemCollection = collect($items);
        $state = $itemCollection->contains(fn (array $item): bool => $item['state'] === 'rejected')
            ? 'rejected'
            : ($itemCollection->contains(fn (array $item): bool => $item['state'] === 'pending')
                ? 'pending'
                : ($itemCollection->every(fn (array $item): bool => $item['state'] === 'completed') ? 'completed' : 'waiting'));
        $latestDatedItem = $itemCollection
            ->filter(fn (array $item): bool => in_array($item['state'], ['completed', 'pending', 'rejected'], true) && ! in_array($item['date_label'], ['-', 'Now', 'Next'], true))
            ->last();

        return [
            'date_label' => $latestDatedItem['date_label'] ?? ($state === 'pending' ? 'Now' : 'Next'),
            'marker_class' => $this->lifecycleMarkerClass($state),
        ];
    }

    private function lifecycleValueFromLog(BusinessTripLifecycleLog $lifecycleLog): array
    {
        $status = trim(strtolower((string) ($lifecycleLog->status ?? ''))) ?: 'waiting';
        $state = $this->normalizeLifecycleState($status);

        return [
            'event_key' => (string) $lifecycleLog->event_key,
            'step_order' => (int) $lifecycleLog->step_order,
            'title' => (string) $lifecycleLog->title,
            ...$this->buildLifecycleValue(
                $state,
                $this->businessTripStatusLabel($status),
                $lifecycleLog->happened_at,
                $this->actorDisplayName($lifecycleLog->actor)
            ),
        ];
    }

    private function buildLifecycleValue(string $state, string $statusLabel, mixed $date, string $actorLabel): array
    {
        return [
            'state' => $state,
            'date_label' => $this->formatLifecycleDateLabel($date, $state),
            'datetime_label' => $this->formatLifecycleDateTimeLabel($date, $state),
            'actor_label' => trim($actorLabel) !== '' ? trim($actorLabel) : '-',
            'status_label' => $statusLabel,
            'marker_class' => $this->lifecycleMarkerClass($state),
            'badge_class' => $this->lifecycleBadgeClass($state),
        ];
    }

    private function normalizeLifecycleState(string $state): string
    {
        return match (strtolower(trim($state))) {
            'approved', 'complete', 'completed', 'paid', 'done', 'success' => 'completed',
            'rejected', 'cancelled', 'canceled', 'failed' => 'rejected',
            'pending', 'in_progress', 'progress', 'review' => 'pending',
            default => 'waiting',
        };
    }

    private function formatLifecycleDateLabel(mixed $date, string $state): string
    {
        if ($date === null) {
            return $state === 'pending' ? 'Now' : 'Next';
        }

        return Carbon::parse($date)->timezone('Asia/Jakarta')->format('d M');
    }

    private function formatLifecycleDateTimeLabel(mixed $date, string $state): string
    {
        if ($date === null) {
            return $state === 'pending' ? 'Pending' : 'Waiting';
        }

        return Carbon::parse($date)->timezone('Asia/Jakarta')->format('d F Y, H:i');
    }

    private function lifecycleMarkerClass(string $state): string
    {
        return match ($state) {
            'completed' => 'border-success',
            'pending' => 'border-warning',
            'rejected' => 'border-danger',
            default => 'border-secondary',
        };
    }

    private function lifecycleBadgeClass(string $state): string
    {
        return match ($state) {
            'completed' => 'badge-success light',
            'pending' => 'badge-warning light',
            'rejected' => 'badge-danger light',
            default => 'badge-secondary light',
        };
    }

    private function actorDisplayName(?User $user): string
    {
        if ($user === null) {
            return '-';
        }

        $user->loadMissing(['employee.profile', 'userProfile']);

        $employeeName = trim((string) ($user->employee?->profile?->name ?? ''));
        if ($employeeName !== '') {
            return $employeeName;
        }

        $nickname = trim((string) ($user->userProfile?->nickname ?? ''));
        if ($nickname !== '') {
            return $nickname;
        }

        $username = trim((string) $user->username);
        if ($username !== '') {
            return $username;
        }

        return trim((string) $user->email) !== '' ? trim((string) $user->email) : '-';
    }

    private function employeeDisplayName(?Employee $employee): string
    {
        $profileName = trim((string) ($employee?->profile?->name ?? ''));
        if ($profileName !== '') {
            return $profileName;
        }

        $userName = trim((string) ($employee?->user?->name ?? ''));
        if ($userName !== '') {
            return $userName;
        }

        return '-';
    }

    private function businessTripTypeLabel(BusinessTrip $businessTrip): string
    {
        $destinationZone = trim((string) $businessTrip->destination_zone);
        if ($destinationZone !== '') {
            return $destinationZone;
        }

        $tripType = trim((string) $businessTrip->trip_type);
        if ($tripType === '') {
            return '-';
        }

        return $this->destinationZoneLabel($tripType);
    }

    private function formatBusinessTripDateRange(BusinessTrip $businessTrip): string
    {
        if ($businessTrip->start_date === null || $businessTrip->end_date === null) {
            return '-';
        }

        return $businessTrip->start_date->format('d F Y').' - '.$businessTrip->end_date->format('d F Y');
    }

    private function formatBusinessTripDuration(int $totalDays): string
    {
        $normalizedTotalDays = max($totalDays, 0);

        if ($normalizedTotalDays === 0) {
            return '-';
        }

        return $normalizedTotalDays.' '.($normalizedTotalDays === 1 ? 'Day' : 'Days');
    }

    private function businessTripStatusLabel(string $status): string
    {
        return ucwords(str_replace('_', ' ', $status ?: 'pending'));
    }

    private function businessTripStatusBadgeClass(string $status): string
    {
        return match ($status) {
            'approved', 'complete', 'completed' => 'badge-success light',
            'rejected', 'cancelled', 'canceled' => 'badge-danger light',
            default => 'badge-primary light',
        };
    }

    private function formatRupiah(float $amount): string
    {
        return 'Rp '.number_format($amount, 0, ',', '.');
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

    private function generateRequestNumber(): string
    {
        $prefix = 'TRP-'.now('Asia/Jakarta')->format('ym').'-';

        for ($attempt = 0; $attempt < 10; $attempt++) {
            $requestNumber = $prefix.str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);

            if (! BusinessTrip::query()->where('request_number', $requestNumber)->exists()) {
                return $requestNumber;
            }
        }

        return $prefix.now('Asia/Jakarta')->format('His');
    }
}
