<?php

namespace App\Http\Controllers;

use App\Models\AttendancePermission;
use App\Models\AttendancePermissionAttachment;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttendancePermissionController extends Controller
{
    public function index(): View
    {
        $authenticatedUser = Auth::user();
        if ($authenticatedUser instanceof User) {
            $authenticatedUser->loadMissing('userEmployee');
        }

        $isBoardOfDirectur = $this->isBoardOfDirectur($authenticatedUser);
        $isAdminUser = $this->isAdminUser($authenticatedUser);
        $isStaffUser = $this->isStaffUser($authenticatedUser);
        $canFilterEmployees = $isBoardOfDirectur || $isAdminUser;
        $canUpdatePermissionStatus = $isBoardOfDirectur || $isAdminUser;
        $userCompanyId = $authenticatedUser?->userEmployee?->company_id;
        $permissionTypes = collect();
        $approvalSummary = [
            'pending' => 0,
            'approved' => 0,
            'refused' => 0,
            'total' => 0,
        ];
        $staffUsersQuery = User::query()
            ->select(['id', 'name'])
            ->whereHas('userEmployee')
            ->orderBy('name');

        if ($isBoardOfDirectur && $userCompanyId) {
            $staffUsersQuery->whereHas('userEmployee', function ($query) use ($userCompanyId): void {
                $query->where('company_id', $userCompanyId);
            });
        } elseif (! $isAdminUser) {
            $staffUsersQuery->whereKey(Auth::id());
        }

        $staffUsersQuery->whereKeyNot(Auth::id());

        $staffUsers = $staffUsersQuery->get();

        if (Schema::hasTable('meta_data_permission_types')) {
            $permissionTypes = DB::table('meta_data_permission_types')
                ->select(['id', 'name'])
                ->whereNotIn('name', $this->attendanceStatusPermissionTypeNames())
                ->orderBy('name')
                ->get();
        }

        $summaryQuery = AttendancePermission::query();

        if ($isBoardOfDirectur && $userCompanyId) {
            $summaryQuery->whereHas('user.userEmployee', function ($query) use ($userCompanyId): void {
                $query->where('company_id', $userCompanyId);
            });
        } elseif (! $isAdminUser) {
            $summaryQuery->where('user_id', Auth::id());
        }

        $approvalCounts = $summaryQuery
            ->selectRaw('LOWER(approval_status) as status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $approvalSummary['pending'] = (int) ($approvalCounts['pending'] ?? 0);
        $approvalSummary['approved'] = (int) ($approvalCounts['approved'] ?? 0);
        $approvalSummary['refused'] = (int) ($approvalCounts['refused'] ?? 0);
        $approvalSummary['total'] = $approvalSummary['pending'] + $approvalSummary['approved'] + $approvalSummary['refused'];

        $summaryCards = [
            ['label' => 'Pending', 'value' => $approvalSummary['pending']],
            ['label' => 'Approved', 'value' => $approvalSummary['approved']],
            ['label' => 'Refused', 'value' => $approvalSummary['refused']],
            ['label' => 'Total', 'value' => $approvalSummary['total']],
        ];

        if ($isStaffUser && $authenticatedUser instanceof User) {
            $currentYear = (int) now()->year;
            $currentMonth = (int) now()->month;
            $currentMonthYearLabel = now()->format('F Y');
            $userId = (int) $authenticatedUser->id;

            $monthlyLeaveLimit = 0;
            if ($userCompanyId) {
                $monthlyLeaveLimit = (int) DB::table('meta_data_leave_companies')
                    ->where('company_id', $userCompanyId)
                    ->value('montly_leave_limit');
            }

            $leaveSummary = $this->calculateStaffLeaveSummary($userId, $currentYear, $currentMonth);
            $remainingAnnualLeave = $leaveSummary['remaining_annual_balance'];
            $annualLeaveUsage = $leaveSummary['used_annual_balance'];

            $isMonthlySpecialLeaveUsed = AttendancePermission::query()
                ->where('user_id', $userId)
                ->whereRaw('LOWER(permission_types) = ?', ['cuti khusus'])
                ->whereYear('start_date', $currentYear)
                ->whereMonth('start_date', $currentMonth)
                ->whereRaw('LOWER(approval_status) NOT IN (?, ?)', ['rejected', 'refused'])
                ->exists();

            $remainingMonthlyLeave = $isMonthlySpecialLeaveUsed ? 0 : $monthlyLeaveLimit;

            $summaryCards = [
                ['label' => 'Sisa Cuti '.$currentYear, 'value' => $remainingAnnualLeave],
                ['label' => 'Cuti '.$currentYear, 'value' => $annualLeaveUsage],
                ['label' => 'Cuti Bersama '.$currentYear, 'value' => $monthlyLeaveLimit],
                ['label' => 'Cuti '.$currentMonthYearLabel, 'value' => $remainingMonthlyLeave],
            ];
        }

        return view('absensi.izin', [
            'permissionTypes' => $this->normalizePermissionTypes($permissionTypes),
            'approvalSummary' => $approvalSummary,
            'summaryCards' => $summaryCards,
            'staffUsers' => $staffUsers,
            'defaultStaffUserId' => 0,
            'canSubmitPermission' => $isStaffUser,
            'canFilterEmployees' => $canFilterEmployees,
            'canUpdatePermissionStatus' => $canUpdatePermissionStatus,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'permission_type_id' => ['required'],
            'reason' => ['required', 'string', 'max:5000'],
            'attachment_files' => ['sometimes', 'array'],
            'attachment_files.*' => ['file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        $permissionTypeName = DB::table('meta_data_permission_types')
            ->where('id', $validated['permission_type_id'])
            ->value('name');

        $permissionTypeValue = is_string($permissionTypeName) && trim($permissionTypeName) !== ''
            ? $permissionTypeName
            : (string) $validated['permission_type_id'];
        $normalizedPermissionType = strtolower(trim($permissionTypeValue));
        $normalizedAttendanceStatusTypes = collect($this->attendanceStatusPermissionTypeNames())
            ->map(static fn (string $statusType): string => strtolower(trim($statusType)));

        if ($normalizedAttendanceStatusTypes->contains($normalizedPermissionType)) {
            return response()->json([
                'success' => false,
                'message' => 'Tipe izin tidak valid untuk pengajuan izin.',
            ], 422);
        }

        $startDate = Carbon::parse($validated['start_date']);
        $endDate = Carbon::parse($validated['end_date']);
        $durationDays = $startDate->diffInDays($endDate) + 1;
        $currentYear = (int) $startDate->year;
        $currentMonth = (int) $startDate->month;
        $userId = (int) Auth::id();
        $authenticatedUser = Auth::user();
        $authenticatedUser?->loadMissing('userEmployee');
        $userCompanyId = (int) ($authenticatedUser?->userEmployee?->company_id ?? 0);

        if ($normalizedPermissionType === 'cuti khusus') {
            $monthlyLeaveLimit = 0;
            if ($userCompanyId > 0) {
                $monthlyLeaveLimit = (int) DB::table('meta_data_leave_companies')
                    ->where('company_id', $userCompanyId)
                    ->value('montly_leave_limit');
            }

            if ($monthlyLeaveLimit <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Limit cuti khusus bulanan belum tersedia untuk perusahaan Anda.',
                ], 422);
            }

            $isMonthlySpecialLeaveUsed = AttendancePermission::query()
                ->where('user_id', $userId)
                ->whereRaw('LOWER(permission_types) = ?', ['cuti khusus'])
                ->whereYear('start_date', $currentYear)
                ->whereMonth('start_date', $currentMonth)
                ->whereRaw('LOWER(approval_status) NOT IN (?, ?)', ['rejected', 'refused'])
                ->exists();

            if ($isMonthlySpecialLeaveUsed) {
                return response()->json([
                    'success' => false,
                    'message' => 'Limit cuti khusus bulan ini sudah digunakan.',
                ], 422);
            }
        }

        if (in_array($normalizedPermissionType, ['cuti khusus', 'cuti tahunan'], true)) {
            $leaveSummary = $this->calculateStaffLeaveSummary($userId, $currentYear, $currentMonth);
            $remainingAnnualBalance = (int) $leaveSummary['remaining_annual_balance'];

            if ($remainingAnnualBalance < $durationDays) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sisa cuti tahunan tidak mencukupi untuk pengajuan ini.',
                ], 422);
            }
        }

        DB::transaction(function () use ($request, $validated, $permissionTypeValue): void {
            $attendancePermission = AttendancePermission::create([
                'user_id' => Auth::id(),
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'reason' => $validated['reason'],
                'permission_types' => $permissionTypeValue,
                'approval_status' => 'pending',
            ]);

            /** @var array<int, UploadedFile> $attachments */
            $attachments = array_values(array_filter(
                (array) $request->file('attachment_files', []),
                static fn (mixed $attachment): bool => $attachment instanceof UploadedFile
            ));
            $attachmentDirectory = 'attendance-permission-attachments';

            foreach ($attachments as $attachment) {
                if (! $attachment->isValid()) {
                    continue;
                }

                if (! Storage::disk('public')->directoryExists($attachmentDirectory)) {
                    Storage::disk('public')->makeDirectory($attachmentDirectory);
                }

                $originalName = $attachment->getClientOriginalName();
                $sanitizedName = Str::slug(pathinfo($originalName, PATHINFO_FILENAME));
                $extension = strtolower((string) $attachment->getClientOriginalExtension());
                $storedFileName = now()->format('YmdHis').'_'.Str::random(8).'_'.$sanitizedName.'.'.$extension;
                $storedPath = $attachment->storeAs($attachmentDirectory, $storedFileName, 'public');
                if ($storedPath === false) {
                    continue;
                }

                $attendancePermission->attachments()->create([
                    'file_path' => $storedPath,
                    'file_name' => $originalName,
                    'attachment_type' => $attachment->getClientMimeType() ?: $extension,
                ]);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan izin berhasil disimpan.',
        ]);
    }

    public function datatable(Request $request): JsonResponse
    {
        $authenticatedUser = Auth::user();
        if ($authenticatedUser instanceof User) {
            $authenticatedUser->loadMissing('userEmployee');
        }

        $isBoardOfDirectur = $this->isBoardOfDirectur($authenticatedUser);
        $isAdminUser = $this->isAdminUser($authenticatedUser);
        $userCompanyId = $authenticatedUser?->userEmployee?->company_id;
        $selectedStaffUserId = $request->integer('staff_user_id', 0);

        $permissionsQuery = AttendancePermission::query()
            ->with(['user:id,name'])
            ->latest('id')
            ->select([
                'id',
                'user_id',
                'start_date',
                'end_date',
                'permission_types',
                'approval_status',
                'reason',
            ]);

        if ($isBoardOfDirectur && $userCompanyId) {
            $permissionsQuery->whereHas('user.userEmployee', function ($query) use ($userCompanyId): void {
                $query->where('company_id', $userCompanyId);
            });
        } elseif (! $isAdminUser) {
            $permissionsQuery->where('user_id', Auth::id());
        }

        if ($selectedStaffUserId > 0) {
            $permissionsQuery->where('user_id', $selectedStaffUserId);
        }

        $permissions = $permissionsQuery->get();

        $tableRows = $permissions->map(function (AttendancePermission $permission): array {
            $startDate = $permission->start_date instanceof Carbon
                ? $permission->start_date
                : Carbon::parse($permission->start_date);
            $endDate = $permission->end_date instanceof Carbon
                ? $permission->end_date
                : Carbon::parse($permission->end_date);

            $durationDays = $startDate->diffInDays($endDate) + 1;

            return [
                'id' => $permission->id,
                'date_range' => $startDate->format('d M Y').' - '.$endDate->format('d M Y'),
                'duration' => $durationDays.' Hari',
                'staff_name' => $permission->user?->name ?? '-',
                'permission_type' => $permission->permission_types,
                'status' => $permission->approval_status,
                'reason' => $permission->reason ?: '-',
            ];
        })->values();

        return response()->json([
            'data' => $tableRows,
        ]);
    }

    public function show(AttendancePermission $attendancePermission): JsonResponse
    {
        if ((int) $attendancePermission->user_id !== (int) Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak memiliki akses ke data izin ini.',
            ], 403);
        }

        $attendancePermission->loadMissing('user:id,name');
        $attendancePermission->loadMissing('attachments:id,attendance_permission_id,file_name,file_path,attachment_type');

        $startDate = $attendancePermission->start_date instanceof Carbon
            ? $attendancePermission->start_date
            : Carbon::parse($attendancePermission->start_date);
        $endDate = $attendancePermission->end_date instanceof Carbon
            ? $attendancePermission->end_date
            : Carbon::parse($attendancePermission->end_date);
        $durationDays = $startDate->diffInDays($endDate) + 1;

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $attendancePermission->id,
                'staff_name' => $attendancePermission->user?->name ?? '-',
                'date_range' => $startDate->format('d M Y').' - '.$endDate->format('d M Y'),
                'duration' => $durationDays.' Hari',
                'permission_type' => (string) $attendancePermission->permission_types,
                'status' => (string) $attendancePermission->approval_status,
                'reason' => $attendancePermission->reason ?: '-',
                'attachments' => $attendancePermission->attachments->map(function ($attachment): array {
                    $filePath = is_string($attachment->file_path) ? $attachment->file_path : '';

                    return [
                        'file_name' => is_string($attachment->file_name) && trim($attachment->file_name) !== ''
                            ? $attachment->file_name
                            : basename($filePath),
                        'file_url' => $filePath !== ''
                            ? route('absensi.izin.attachment', ['attachment' => $attachment->id])
                            : '',
                        'attachment_type' => is_string($attachment->attachment_type) ? $attachment->attachment_type : '',
                    ];
                })->values(),
            ],
        ]);
    }

    public function attachment(AttendancePermissionAttachment $attachment): StreamedResponse
    {
        $attachment->loadMissing('attendancePermission:id,user_id');

        if ((int) ($attachment->attendancePermission?->user_id ?? 0) !== (int) Auth::id()) {
            abort(403);
        }

        $filePath = is_string($attachment->file_path) ? $attachment->file_path : '';
        if ($filePath === '' || ! Storage::disk('public')->exists($filePath)) {
            abort(404);
        }

        return Storage::disk('public')->response($filePath);
    }

    public function destroy(AttendancePermission $attendancePermission): JsonResponse
    {
        if ((int) $attendancePermission->user_id !== (int) Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak memiliki akses untuk menghapus data izin ini.',
            ], 403);
        }

        DB::transaction(function () use ($attendancePermission): void {
            $attendancePermission->loadMissing('attachments:id,attendance_permission_id,file_path');

            foreach ($attendancePermission->attachments as $attachment) {
                if (is_string($attachment->file_path) && trim($attachment->file_path) !== '') {
                    Storage::disk('public')->delete($attachment->file_path);
                }
            }

            $attendancePermission->attachments()->delete();
            $attendancePermission->delete();
        });

        return response()->json([
            'success' => true,
            'message' => 'Data izin berhasil dihapus.',
        ]);
    }

    public function updateStatus(Request $request, AttendancePermission $attendancePermission): JsonResponse
    {
        $authenticatedUser = Auth::user();
        if (! $this->canManagePermissionStatus($authenticatedUser, $attendancePermission)) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak memiliki akses untuk mengubah status izin ini.',
            ], 403);
        }

        $validated = $request->validate([
            'approval_status' => ['required', 'in:pending,approved,refused,rejected'],
        ]);

        $currentStatus = strtolower((string) ($attendancePermission->approval_status ?? 'pending'));
        if ($currentStatus !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Status izin yang sudah diproses tidak dapat diubah.',
            ], 422);
        }

        $normalizedStatus = strtolower(trim((string) $validated['approval_status']));
        if ($normalizedStatus === 'rejected') {
            $normalizedStatus = 'refused';
        }

        $attendancePermission->update([
            'approval_status' => $normalizedStatus,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Status izin berhasil diperbarui.',
        ]);
    }

    /**
     * @param  Collection<int, object>  $permissionTypes
     * @return Collection<int, object>
     */
    private function normalizePermissionTypes(Collection $permissionTypes): Collection
    {
        return $permissionTypes
            ->filter(static function (object $permissionType): bool {
                return isset($permissionType->id, $permissionType->name)
                    && is_numeric($permissionType->id)
                    && is_string($permissionType->name)
                    && trim($permissionType->name) !== '';
            })
            ->values();
    }

    /**
     * @return array<int, string>
     */
    private function attendanceStatusPermissionTypeNames(): array
    {
        return ['Masuk', 'Terlambat', 'Pulang'];
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

    private function isStaffUser(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        $normalizedRoleNames = $user->getRoleNames()
            ->map(static fn (string $roleName): string => strtolower(trim($roleName)));

        return $normalizedRoleNames->contains('staff');
    }

    private function canManagePermissionStatus(?User $authenticatedUser, AttendancePermission $attendancePermission): bool
    {
        if (! $authenticatedUser instanceof User) {
            return false;
        }

        if ($this->isAdminUser($authenticatedUser)) {
            return true;
        }

        if (! $this->isBoardOfDirectur($authenticatedUser)) {
            return false;
        }

        $authenticatedUser->loadMissing('userEmployee');
        $userCompanyId = (int) ($authenticatedUser->userEmployee?->company_id ?? 0);
        if ($userCompanyId <= 0) {
            return false;
        }

        return $attendancePermission->user()
            ->whereHas('userEmployee', function ($query) use ($userCompanyId): void {
                $query->where('company_id', $userCompanyId);
            })
            ->exists();
    }

    /**
     * @return array{
     *     base_annual_balance:int,
     *     monthly_bonus:int,
     *     used_annual_balance:int,
     *     remaining_annual_balance:int
     * }
     */
    private function calculateStaffLeaveSummary(int $userId, int $year, int $currentMonth): array
    {
        $leaveBalance = DB::table('leave_balances')
            ->select(['annual_balance', 'created_at'])
            ->where('user_id', $userId)
            ->where('year', $year)
            ->first();

        $baseAnnualBalance = max((int) ($leaveBalance->annual_balance ?? 0), 0);
        $employmentStartDateRaw = DB::table('user_employments')
            ->where('user_id', $userId)
            ->value('start_date');

        $calculationStartDate = Carbon::create($year, 1, 1)->startOfMonth();
        if (is_string($employmentStartDateRaw) && trim($employmentStartDateRaw) !== '') {
            $employmentStartDate = Carbon::parse($employmentStartDateRaw)->startOfMonth();
            if ($employmentStartDate->greaterThan($calculationStartDate)) {
                $calculationStartDate = $employmentStartDate;
            }
        }

        $currentMonthStartDate = Carbon::create($year, max($currentMonth, 1), 1)->startOfMonth();
        $accruedMonthLimit = 0;
        if (! $calculationStartDate->greaterThan($currentMonthStartDate)) {
            $accruedMonthLimit = $calculationStartDate->diffInMonths($currentMonthStartDate) + 1;
        }

        $baseAnnualBalance = min($baseAnnualBalance, $accruedMonthLimit);

        $usedAnnualBalance = (int) DB::table('attendance_permissions')
            ->where('user_id', $userId)
            ->whereYear('start_date', $year)
            ->whereRaw('LOWER(permission_types) IN (?, ?)', ['cuti tahunan', 'cuti khusus'])
            ->whereRaw('LOWER(approval_status) NOT IN (?, ?)', ['rejected', 'refused'])
            ->selectRaw('COALESCE(SUM(DATEDIFF(end_date, start_date) + 1), 0) as used_days')
            ->value('used_days');

        $monthlyBonus = 0;
        $remainingAnnualBalance = max($baseAnnualBalance - $usedAnnualBalance, 0);

        return [
            'base_annual_balance' => $baseAnnualBalance,
            'monthly_bonus' => $monthlyBonus,
            'used_annual_balance' => $usedAnnualBalance,
            'remaining_annual_balance' => $remainingAnnualBalance,
        ];
    }
}
