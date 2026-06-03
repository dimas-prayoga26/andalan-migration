<?php

namespace App\Http\Controllers;

use App\Models\BusinessTrip;
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
        return view('attendance.business-trips.detail', [
            'businessTrip' => $businessTrip,
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
            $authenticatedUser->loadMissing('employee');
        }

        $employeeId = trim((string) ($authenticatedUser?->employee?->id ?? ''));
        if ($employeeId === '') {
            return back()
                ->withInput()
                ->withErrors(['employee' => 'Data karyawan tidak ditemukan.']);
        }

        $startDate = Carbon::parse($validated['start_date']);
        $endDate = Carbon::parse($validated['end_date']);
        $totalDays = (int) $startDate->diffInDays($endDate) + 1;

        BusinessTrip::query()->create([
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
            'submitted_at' => now('Asia/Jakarta'),
            'approval_status' => 'pending',
            'payment_status' => 'pending',
            'daily_rate' => 0,
            'total_allowance' => 0,
        ]);

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
