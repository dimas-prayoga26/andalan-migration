<?php

namespace App\Http\Controllers;

use App\Models\BusinessTrip;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class BusinessTripController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('absensi.dinas');
    }

    public function datatable(): JsonResponse
    {
        $authenticatedUser = Auth::user();
        if ($authenticatedUser instanceof User) {
            $authenticatedUser->loadMissing('employee.deployment');
        }

        $isBoardOfDirectur = $this->isBoardOfDirectur($authenticatedUser);
        $isAdminUser = $this->isAdminUser($authenticatedUser);
        $userCompanyId = $authenticatedUser?->employee?->deployment?->current_company_id;
        $authenticatedEmployeeId = $authenticatedUser?->employee?->id;

        $businessTripQuery = BusinessTrip::query()
            ->with([
                'employee.user:id,username',
                'employee.profile:id,employee_id,name',
                'approvedBy:id,username',
                'approvedBy.employee:id,user_id',
                'approvedBy.employee.profile:id,employee_id,name',
            ])
            ->latest('start_date')
            ->latest('id')
            ->select([
                'id',
                'employee_id',
                'start_date',
                'end_date',
                'total_days',
                'destination_zone',
                'city_destination',
                'purpose',
                'daily_rate',
                'total_allowance',
                'approval_status',
                'approved_by',
                'payment_status',
                'payment_reference',
            ]);

        if ($isBoardOfDirectur && $userCompanyId) {
            $businessTripQuery->whereHas('employee.deployment', function ($query) use ($userCompanyId): void {
                $query->where('current_company_id', $userCompanyId);
            });
        } elseif (! $isAdminUser) {
            if (! is_string($authenticatedEmployeeId) || trim($authenticatedEmployeeId) === '') {
                return response()->json(['data' => []]);
            }

            $businessTripQuery->where('employee_id', $authenticatedEmployeeId);
        }

        $businessTrips = $businessTripQuery->get();

        $tableRows = $businessTrips->map(function (BusinessTrip $businessTrip): array {
            $startDate = $this->normalizeDateLabel($businessTrip->start_date);
            $endDate = $this->normalizeDateLabel($businessTrip->end_date);

            return [
                'id' => $businessTrip->id,
                'staff_name' => $this->resolveEmployeeDisplayName($businessTrip->employee),
                'pic_name' => $this->resolveUserDisplayName($businessTrip->approvedBy),
                'trip_date' => $startDate.' - '.$endDate,
                'total_days' => (int) ($businessTrip->total_days ?? 0),
                'destination' => trim((string) $businessTrip->city_destination) !== ''
                    ? trim((string) $businessTrip->city_destination)
                    : trim((string) $businessTrip->destination_zone),
                'purpose' => trim((string) $businessTrip->purpose) !== '' ? (string) $businessTrip->purpose : '-',
                'approval_status' => strtolower(trim((string) ($businessTrip->approval_status ?? 'pending'))),
                'payment_status' => strtolower(trim((string) ($businessTrip->payment_status ?? 'pending'))),
                'payment_reference' => trim((string) ($businessTrip->payment_reference ?? '')) !== ''
                    ? (string) $businessTrip->payment_reference
                    : '-',
            ];
        })->values();

        return response()->json([
            'data' => $tableRows,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Belum diimplementasikan.',
        ], 404);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Belum diimplementasikan.',
        ], 404);
    }

    /**
     * Display the specified resource.
     */
    public function show(BusinessTrip $businessTrip): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Belum diimplementasikan.',
        ], 404);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(BusinessTrip $businessTrip): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Belum diimplementasikan.',
        ], 404);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, BusinessTrip $businessTrip): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Belum diimplementasikan.',
        ], 404);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BusinessTrip $businessTrip): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Belum diimplementasikan.',
        ], 404);
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

    private function isAdminUser(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        $normalizedRoleNames = $user->getRoleNames()
            ->map(static fn (string $roleName): string => strtolower(trim($roleName)));

        return $normalizedRoleNames->contains('admin')
            || $normalizedRoleNames->contains('superuser');
    }

    private function resolveEmployeeDisplayName(?Employee $employee): string
    {
        if (! $employee) {
            return '-';
        }

        $profileName = is_string($employee->profile?->name) ? trim($employee->profile->name) : '';
        if ($profileName !== '') {
            return $profileName;
        }

        $username = is_string($employee->user?->username) ? trim($employee->user->username) : '';
        if ($username !== '') {
            return $username;
        }

        return '-';
    }

    private function resolveUserDisplayName(?User $user): string
    {
        if (! $user) {
            return '-';
        }

        $user->loadMissing('employee.profile');

        $profileName = is_string($user->employee?->profile?->name) ? trim($user->employee->profile->name) : '';
        if ($profileName !== '') {
            return $profileName;
        }

        $username = is_string($user->username) ? trim($user->username) : '';
        if ($username !== '') {
            return $username;
        }

        return '-';
    }

    private function normalizeDateLabel(mixed $dateValue): string
    {
        if ($dateValue instanceof Carbon) {
            return $dateValue->format('d M Y');
        }

        if (! is_string($dateValue) || trim($dateValue) === '') {
            return '-';
        }

        try {
            return Carbon::parse($dateValue)->format('d M Y');
        } catch (\Throwable) {
            return '-';
        }
    }
}
