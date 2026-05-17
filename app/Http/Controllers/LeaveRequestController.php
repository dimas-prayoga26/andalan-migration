<?php

namespace App\Http\Controllers;

use App\Models\EmployeeDeployment;
use App\Models\EmployeeProfile;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\MetaDataLeaveCompany;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LeaveRequestController extends Controller
{
    public function index(): View
    {
        $authenticatedUser = $this->resolveAuthenticatedUser(['employee.deployment']);

        $isBoardOfDirectur = $this->isBoardOfDirectur($authenticatedUser);
        $isAdminUser = $this->isAdminUser($authenticatedUser);
        $isStaffUser = $this->isStaffUser($authenticatedUser);
        $canFilterEmployees = $isBoardOfDirectur || $isAdminUser;
        $canUpdatePermissionStatus = $isBoardOfDirectur || $isAdminUser;
        $userCompanyId = $authenticatedUser?->employee?->deployment?->current_company_id;
        $approvalSummary = [
            'pending' => 0,
            'approved' => 0,
            'refused' => 0,
            'total' => 0,
        ];
        $staffUsersQuery = User::query()
            ->select(['id', 'username', 'email'])
            ->with(['employee:id,user_id'])
            ->whereHas('employee.deployment')
            ->orderBy('username');

        if ($isBoardOfDirectur && $userCompanyId) {
            $staffUsersQuery->whereHas('employee.deployment', function ($query) use ($userCompanyId): void {
                $query->where('current_company_id', $userCompanyId);
            });
        } elseif (! $isAdminUser) {
            $staffUsersQuery->whereKey(Auth::id());
        }

        $staffUsersQuery->whereKeyNot(Auth::id());

        $staffUsers = $staffUsersQuery->get();
        $staffUsersProfileNamesByEmployeeId = $this->fetchEmployeeProfileNamesByUsers($staffUsers);
        $staffUsers = $staffUsers->map(function (User $staffUser) use ($staffUsersProfileNamesByEmployeeId): object {
            return (object) [
                'id' => $staffUser->id,
                'name' => $this->resolveUserDisplayName($staffUser, $staffUsersProfileNamesByEmployeeId),
            ];
        });

        $permissionTypes = LeaveType::query()
            ->select(['id', 'name'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $summaryQuery = LeaveRequest::query();

        if ($isBoardOfDirectur && $userCompanyId) {
            $summaryQuery->whereHas('employee.deployment', function ($query) use ($userCompanyId): void {
                $query->where('current_company_id', $userCompanyId);
            });
        } elseif (! $isAdminUser) {
            $currentEmployeeId = $authenticatedUser?->employee?->id;
            $summaryQuery->where('employee_id', $currentEmployeeId);
        }

        $approvalCounts = $summaryQuery
            ->selectRaw('LOWER(status) as status, COUNT(*) as total')
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
            $userId = (string) $authenticatedUser->id;
            $employeeId = (string) ($authenticatedUser->employee?->id ?? '');

            $monthlyLeaveLimit = 0;
            if ($userCompanyId) {
                $monthlyLeaveLimit = (int) MetaDataLeaveCompany::query()
                    ->where('company_id', $userCompanyId)
                    ->value('montly_leave_limit');
            }

            $leaveSummary = $this->calculateStaffLeaveSummary($employeeId, $currentYear, $currentMonth);
            $remainingAnnualLeave = $leaveSummary['remaining_annual_balance'];
            $annualLeaveUsage = $leaveSummary['used_annual_balance'];

            $isMonthlySpecialLeaveUsed = LeaveRequest::query()
                ->where('employee_id', $employeeId)
                ->whereHas('leaveType', function ($query): void {
                    $query->whereRaw('LOWER(name) = ?', ['cuti khusus']);
                })
                ->whereYear('start_date', $currentYear)
                ->whereMonth('start_date', $currentMonth)
                ->whereRaw('LOWER(status) NOT IN (?, ?)', ['rejected', 'refused'])
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
            'defaultStaffUserId' => '',
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
            'permission_type_id' => ['required', 'exists:leave_types,id'],
            'reason' => ['required', 'string', 'max:5000'],
            'attachment_path' => ['nullable', 'string', 'max:255'],
            'attachment_file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        $permissionTypeName = LeaveType::query()
            ->where('id', $validated['permission_type_id'])
            ->value('name');

        $permissionTypeValue = is_string($permissionTypeName) ? trim($permissionTypeName) : '';
        if ($permissionTypeValue === '') {
            return response()->json([
                'success' => false,
                'message' => 'Tipe izin tidak ditemukan.',
            ], 422);
        }
        $normalizedPermissionType = strtolower($permissionTypeValue);

        $startDate = Carbon::parse($validated['start_date']);
        $endDate = Carbon::parse($validated['end_date']);
        $durationDays = $startDate->diffInDays($endDate) + 1;
        $currentYear = (int) $startDate->year;
        $currentMonth = (int) $startDate->month;
        $authenticatedUser = $this->resolveAuthenticatedUser(['employee.deployment']);
        $userId = (string) ($authenticatedUser?->id ?? '');
        $employeeId = (string) ($authenticatedUser?->employee?->id ?? '');
        $userCompanyId = (string) ($authenticatedUser?->employee?->deployment?->current_company_id ?? '');

        if ($employeeId === '') {
            return response()->json([
                'success' => false,
                'message' => 'Data karyawan tidak ditemukan.',
            ], 422);
        }

        if ($normalizedPermissionType === 'cuti khusus') {
            $monthlyLeaveLimit = 0;
            if ($userCompanyId !== '') {
                $monthlyLeaveLimit = (int) MetaDataLeaveCompany::query()
                    ->where('company_id', $userCompanyId)
                    ->value('montly_leave_limit');
            }

            if ($monthlyLeaveLimit <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Limit cuti khusus bulanan belum tersedia untuk perusahaan Anda.',
                ], 422);
            }

            $isMonthlySpecialLeaveUsed = LeaveRequest::query()
                ->where('employee_id', $employeeId)
                ->whereHas('leaveType', function ($query): void {
                    $query->whereRaw('LOWER(name) = ?', ['cuti khusus']);
                })
                ->whereYear('start_date', $currentYear)
                ->whereMonth('start_date', $currentMonth)
                ->whereRaw('LOWER(status) NOT IN (?, ?)', ['rejected', 'refused'])
                ->exists();

            if ($isMonthlySpecialLeaveUsed) {
                return response()->json([
                    'success' => false,
                    'message' => 'Limit cuti khusus bulan ini sudah digunakan.',
                ], 422);
            }
        }

        if (in_array($normalizedPermissionType, ['cuti khusus', 'cuti tahunan'], true)) {
            $leaveSummary = $this->calculateStaffLeaveSummary($employeeId, $currentYear, $currentMonth);
            $remainingAnnualBalance = (int) $leaveSummary['remaining_annual_balance'];

            if ($remainingAnnualBalance < $durationDays) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sisa cuti tahunan tidak mencukupi untuk pengajuan ini.',
                ], 422);
            }
        }

        $temporaryAttachmentPath = $this->normalizeUploadedAttachmentPath($validated['attachment_path'] ?? null);
        if ($temporaryAttachmentPath !== null && ! $this->publicDisk()->exists($temporaryAttachmentPath)) {
            return response()->json([
                'success' => false,
                'message' => 'Lampiran sementara tidak ditemukan. Silakan upload ulang lampiran.',
            ], 422);
        }

        DB::transaction(function () use ($request, $validated, $durationDays, $employeeId, $temporaryAttachmentPath): void {
            $storedAttachmentPath = null;
            $attachmentDirectory = 'leave-request-attachments';
            if (! $this->publicDisk()->directoryExists($attachmentDirectory)) {
                $this->publicDisk()->makeDirectory($attachmentDirectory);
            }

            if ($temporaryAttachmentPath !== null) {
                $temporaryExtension = strtolower((string) pathinfo($temporaryAttachmentPath, PATHINFO_EXTENSION));
                $finalExtension = in_array($temporaryExtension, ['jpg', 'jpeg', 'png', 'pdf'], true)
                    ? $temporaryExtension
                    : 'bin';
                $finalFileName = now()->format('YmdHis').'_'.Str::random(8).'_'.Str::slug(pathinfo($temporaryAttachmentPath, PATHINFO_FILENAME)).'.'.$finalExtension;
                $finalStoredPath = $attachmentDirectory.'/'.$finalFileName;

                $isMoved = $this->publicDisk()->move($temporaryAttachmentPath, $finalStoredPath);
                if ($isMoved) {
                    $storedAttachmentPath = $finalStoredPath;
                }
            } else {
                $attachmentFile = $request->file('attachment_file');
                if ($attachmentFile && $attachmentFile->isValid()) {
                    $originalName = $attachmentFile->getClientOriginalName();
                    $sanitizedName = Str::slug(pathinfo($originalName, PATHINFO_FILENAME));
                    $extension = strtolower((string) $attachmentFile->getClientOriginalExtension());
                    $storedFileName = now()->format('YmdHis').'_'.Str::random(8).'_'.$sanitizedName.'.'.$extension;
                    $storedPath = $attachmentFile->storeAs($attachmentDirectory, $storedFileName, 'public');

                    if ($storedPath !== false) {
                        $storedAttachmentPath = $storedPath;
                    }
                }
            }

            $leaveRequest = LeaveRequest::create([
                'employee_id' => $employeeId,
                'leave_type_id' => $validated['permission_type_id'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'total_days' => $durationDays,
                'reason' => $validated['reason'],
                'is_active' => true,
                'attachment_path' => $storedAttachmentPath,
                'status' => 'pending',
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan izin berhasil disimpan.',
        ]);
    }

    public function uploadImage(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'attachment_file' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        $attachmentFile = $request->file('attachment_file');
        if (! $attachmentFile || ! $attachmentFile->isValid()) {
            return response()->json([
                'success' => false,
                'message' => 'File lampiran tidak valid.',
            ], 422);
        }

        $temporaryDirectory = 'leave-request-temp';
        if (! $this->publicDisk()->directoryExists($temporaryDirectory)) {
            $this->publicDisk()->makeDirectory($temporaryDirectory);
        }

        $originalName = $attachmentFile->getClientOriginalName();
        $sanitizedName = Str::slug(pathinfo($originalName, PATHINFO_FILENAME));
        $extension = strtolower((string) $attachmentFile->getClientOriginalExtension());
        $storedFileName = now()->format('YmdHis').'_'.Str::random(8).'_'.$sanitizedName.'.'.$extension;
        $storedPath = $attachmentFile->storeAs($temporaryDirectory, $storedFileName, 'public');

        if ($storedPath === false) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan lampiran sementara.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Lampiran berhasil diupload.',
            'attachment_path' => $storedPath,
            'attachment_url' => $this->publicDisk()->url($storedPath),
        ]);
    }

    public function deleteUploadedImage(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'attachment_path' => ['required', 'string', 'max:255'],
        ]);

        $attachmentPath = $this->normalizeUploadedAttachmentPath($validated['attachment_path'] ?? null);
        if ($attachmentPath === null) {
            return response()->json([
                'success' => false,
                'message' => 'Lampiran sementara tidak valid.',
            ], 422);
        }

        if ($this->publicDisk()->exists($attachmentPath)) {
            $this->publicDisk()->delete($attachmentPath);
        }

        return response()->json([
            'success' => true,
            'message' => 'Lampiran sementara berhasil dihapus.',
        ]);
    }

    public function datatable(Request $request): JsonResponse
    {
        $authenticatedUser = $this->resolveAuthenticatedUser(['employee.deployment']);

        $isBoardOfDirectur = $this->isBoardOfDirectur($authenticatedUser);
        $isAdminUser = $this->isAdminUser($authenticatedUser);
        $userCompanyId = $authenticatedUser?->employee?->deployment?->current_company_id;
        $selectedStaffUserId = trim((string) $request->input('staff_user_id', ''));
        $currentEmployeeId = (string) ($authenticatedUser?->employee?->id ?? '');

        $permissionsQuery = LeaveRequest::query()
            ->with(['employee:id,user_id', 'employee.user:id,username,email', 'leaveType:id,name'])
            ->latest('id')
            ->select([
                'id',
                'employee_id',
                'leave_type_id',
                'start_date',
                'end_date',
                'status',
                'reason',
            ]);

        if ($isBoardOfDirectur && $userCompanyId) {
            $permissionsQuery->whereHas('employee.deployment', function ($query) use ($userCompanyId): void {
                $query->where('current_company_id', $userCompanyId);
            });
        } elseif (! $isAdminUser) {
            $permissionsQuery->where('employee_id', $currentEmployeeId);
        }

        if ($selectedStaffUserId !== '') {
            $selectedStaffUser = User::query()
                ->select(['id'])
                ->with('employee:id,user_id')
                ->whereKey($selectedStaffUserId)
                ->first();
            $selectedStaffEmployeeId = (string) ($selectedStaffUser?->employee?->id ?? '');

            if ($selectedStaffEmployeeId !== '') {
                $permissionsQuery->where('employee_id', $selectedStaffEmployeeId);
            } else {
                $permissionsQuery->whereRaw('1 = 0');
            }
        }

        $permissions = $permissionsQuery->get();
        $permissionEmployeeIds = $permissions
            ->pluck('employee_id')
            ->filter(static fn (mixed $employeeId): bool => is_string($employeeId) && trim($employeeId) !== '');
        $permissionUserProfileNamesByEmployeeId = $this->fetchEmployeeProfileNamesByEmployeeIds($permissionEmployeeIds);

        $tableRows = $permissions->map(function (LeaveRequest $permission) use ($permissionUserProfileNamesByEmployeeId): array {
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
                'staff_name' => $this->resolveUserDisplayName($permission->employee?->user, $permissionUserProfileNamesByEmployeeId),
                'permission_type' => $permission->leaveType?->name ?? '-',
                'status' => $permission->status,
                'reason' => $permission->reason ?: '-',
            ];
        })->values();

        return response()->json([
            'data' => $tableRows,
        ]);
    }

    public function show(LeaveRequest $leaveRequest): JsonResponse
    {
        $currentEmployeeId = (string) (Auth::user()?->employee?->id ?? '');
        if ((string) $leaveRequest->employee_id !== $currentEmployeeId) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak memiliki akses ke data izin ini.',
            ], 403);
        }

        $leaveRequest = LeaveRequest::query()
            ->with(['employee:id,user_id', 'employee.user:id,username,email', 'leaveType:id,name'])
            ->find($leaveRequest->id);
        if (! $leaveRequest instanceof LeaveRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Data izin tidak ditemukan.',
            ], 404);
        }

        $leaveRequestUserProfileNamesByEmployeeId = $this->fetchEmployeeProfileNamesByEmployeeIds(
            collect([$leaveRequest->employee_id])->filter(static fn (mixed $employeeId): bool => is_string($employeeId) && trim($employeeId) !== '')
        );

        $startDate = $leaveRequest->start_date instanceof Carbon
            ? $leaveRequest->start_date
            : Carbon::parse($leaveRequest->start_date);
        $endDate = $leaveRequest->end_date instanceof Carbon
            ? $leaveRequest->end_date
            : Carbon::parse($leaveRequest->end_date);
        $durationDays = $startDate->diffInDays($endDate) + 1;

        $attachmentPath = is_string($leaveRequest->attachment_path) ? trim($leaveRequest->attachment_path) : '';
        $attachments = collect();
        if ($attachmentPath !== '') {
            $attachments->push([
                'file_name' => basename($attachmentPath),
                'file_url' => $this->publicDisk()->url($attachmentPath),
                'attachment_type' => '',
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $leaveRequest->id,
                'staff_name' => $this->resolveUserDisplayName($leaveRequest->employee?->user, $leaveRequestUserProfileNamesByEmployeeId),
                'date_range' => $startDate->format('d M Y').' - '.$endDate->format('d M Y'),
                'duration' => $durationDays.' Hari',
                'permission_type' => (string) ($leaveRequest->leaveType?->name ?? '-'),
                'status' => (string) $leaveRequest->status,
                'reason' => $leaveRequest->reason ?: '-',
                'attachments' => $attachments->values(),
            ],
        ]);
    }

    public function destroy(LeaveRequest $leaveRequest): JsonResponse
    {
        $currentEmployeeId = (string) (Auth::user()?->employee?->id ?? '');
        if ((string) $leaveRequest->employee_id !== $currentEmployeeId) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak memiliki akses untuk menghapus data izin ini.',
            ], 403);
        }

        DB::transaction(function () use ($leaveRequest): void {
            if (is_string($leaveRequest->attachment_path) && trim($leaveRequest->attachment_path) !== '') {
                $this->publicDisk()->delete($leaveRequest->attachment_path);
            }

            $leaveRequest->delete();
        });

        return response()->json([
            'success' => true,
            'message' => 'Data izin berhasil dihapus.',
        ]);
    }

    public function updateStatus(Request $request, LeaveRequest $leaveRequest): JsonResponse
    {
        $authenticatedUser = Auth::user();
        if (! $this->canManagePermissionStatus($authenticatedUser, $leaveRequest)) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak memiliki akses untuk mengubah status izin ini.',
            ], 403);
        }

        $validated = $request->validate([
            'status' => ['required', 'in:pending,approved,refused,rejected'],
        ]);

        $currentStatus = strtolower((string) ($leaveRequest->status ?? 'pending'));
        if ($currentStatus !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Status izin yang sudah diproses tidak dapat diubah.',
            ], 422);
        }

        $normalizedStatus = strtolower(trim((string) $validated['status']));
        if ($normalizedStatus === 'rejected') {
            $normalizedStatus = 'refused';
        }

        $leaveRequest->update([
            'status' => $normalizedStatus,
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
                    && is_string($permissionType->id)
                    && trim($permissionType->id) !== ''
                    && is_string($permissionType->name)
                    && trim($permissionType->name) !== '';
            })
            ->values();
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

    private function normalizeUploadedAttachmentPath(mixed $attachmentPath): ?string
    {
        if (! is_string($attachmentPath)) {
            return null;
        }

        $normalizedAttachmentPath = trim(str_replace('\\', '/', $attachmentPath));
        $normalizedAttachmentPath = ltrim($normalizedAttachmentPath, '/');
        if ($normalizedAttachmentPath === '') {
            return null;
        }

        if (! Str::startsWith($normalizedAttachmentPath, 'leave-request-temp/')) {
            return null;
        }

        return $normalizedAttachmentPath;
    }

    private function canManagePermissionStatus(?User $authenticatedUser, LeaveRequest $leaveRequest): bool
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

        $authenticatedUser = $this->resolveAuthenticatedUser(['employee.deployment']);
        if (! $authenticatedUser instanceof User) {
            return false;
        }

        $userCompanyId = $authenticatedUser->employee?->deployment?->current_company_id;
        if (! is_string($userCompanyId) || trim($userCompanyId) === '') {
            return false;
        }

        return $leaveRequest->employee()
            ->whereHas('deployment', function ($query) use ($userCompanyId): void {
                $query->where('current_company_id', $userCompanyId);
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
    private function calculateStaffLeaveSummary(string $employeeId, int $year, int $currentMonth): array
    {
        $baseAnnualBalance = (int) round((float) LeaveBalance::query()
            ->where('employee_id', $employeeId)
            ->where('period_year', $year)
            ->whereHas('leaveType', function ($query): void {
                $query->whereRaw('LOWER(name) IN (?, ?)', ['cuti tahunan', 'cuti khusus']);
            })
            ->sum('earned_quota'));
        $employmentStartDateRaw = EmployeeDeployment::query()
            ->where('employee_id', $employeeId)
            ->value('join_date');

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

        $usedAnnualBalance = (int) LeaveRequest::query()
            ->where('employee_id', $employeeId)
            ->whereYear('start_date', $year)
            ->whereHas('leaveType', function ($query): void {
                $query->whereRaw('LOWER(name) IN (?, ?)', ['cuti tahunan', 'cuti khusus']);
            })
            ->whereRaw('LOWER(status) NOT IN (?, ?)', ['rejected', 'refused'])
            ->sum('total_days');

        $monthlyBonus = 0;
        $remainingAnnualBalance = max($baseAnnualBalance - $usedAnnualBalance, 0);

        return [
            'base_annual_balance' => $baseAnnualBalance,
            'monthly_bonus' => $monthlyBonus,
            'used_annual_balance' => $usedAnnualBalance,
            'remaining_annual_balance' => $remainingAnnualBalance,
        ];
    }

    /**
     * @param  Collection<int, string>  $employeeIds
     * @return Collection<string, string>
     */
    private function fetchEmployeeProfileNamesByEmployeeIds(Collection $employeeIds): Collection
    {
        $filteredEmployeeIds = $employeeIds
            ->filter(static fn (mixed $employeeId): bool => is_string($employeeId) && trim($employeeId) !== '')
            ->values();

        if ($filteredEmployeeIds->isEmpty()) {
            return collect();
        }

        return EmployeeProfile::query()
            ->whereIn('employee_id', $filteredEmployeeIds)
            ->pluck('name', 'employee_id')
            ->map(static fn (mixed $name): string => is_string($name) ? trim($name) : '')
            ->filter(static fn (string $name): bool => $name !== '');
    }

    /**
     * @param  Collection<int, User>  $users
     * @return Collection<string, string>
     */
    private function fetchEmployeeProfileNamesByUsers(Collection $users): Collection
    {
        $employeeIds = $users
            ->map(static fn (User $user): mixed => $user->employee?->id)
            ->filter(static fn (mixed $employeeId): bool => is_string($employeeId) && trim($employeeId) !== '')
            ->values();

        if ($employeeIds->isEmpty()) {
            return collect();
        }

        return EmployeeProfile::query()
            ->whereIn('employee_id', $employeeIds)
            ->pluck('name', 'employee_id')
            ->map(static fn (mixed $name): string => is_string($name) ? trim($name) : '')
            ->filter(static fn (string $name): bool => $name !== '');
    }

    /**
     * @param  Collection<string, string>  $employeeProfileNamesByEmployeeId
     */
    private function resolveUserDisplayName(?User $user, Collection $employeeProfileNamesByEmployeeId): string
    {
        if (! $user instanceof User) {
            return '-';
        }

        $employeeId = $user->employee?->id;
        if (is_string($employeeId) && $employeeProfileNamesByEmployeeId->has($employeeId)) {
            $profileName = $employeeProfileNamesByEmployeeId->get($employeeId);
            if (is_string($profileName) && trim($profileName) !== '') {
                return trim($profileName);
            }
        }

        if (is_string($user->username) && trim($user->username) !== '') {
            return trim($user->username);
        }

        if (is_string($user->email) && trim($user->email) !== '') {
            return (string) explode('@', trim($user->email))[0];
        }

        return '-';
    }

    /**
     * @param  array<int, string>  $relations
     */
    private function resolveAuthenticatedUser(array $relations = []): ?User
    {
        $authenticatedUserId = Auth::id();
        if (! is_string($authenticatedUserId) && ! is_int($authenticatedUserId)) {
            return null;
        }

        $authenticatedUserQuery = User::query();
        if ($relations !== []) {
            $authenticatedUserQuery->with($relations);
        }

        return $authenticatedUserQuery->find($authenticatedUserId);
    }

    private function publicDisk(): FilesystemAdapter
    {
        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('public');

        return $disk;
    }
}
