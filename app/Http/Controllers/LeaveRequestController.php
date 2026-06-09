<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceHoliday;
use App\Models\EmployeeDeployment;
use App\Models\EmployeeProfile;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\LeaveRequestHistory;
use App\Models\LeaveType;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
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
        $leaveTypes = LeaveType::query()
            ->select(['id', 'code', 'name'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $specialLeaveType = $leaveTypes->first(static function (LeaveType $leaveType): bool {
            $normalizedCode = is_string($leaveType->code) ? strtolower(trim($leaveType->code)) : '';
            $normalizedName = is_string($leaveType->name) ? strtolower(trim($leaveType->name)) : '';

            return $normalizedCode === 'special' || $normalizedName === 'cuti khusus' || $normalizedName === 'special leave';
        });
        $sickLeaveType = $leaveTypes->first(static function (LeaveType $leaveType): bool {
            $normalizedCode = is_string($leaveType->code) ? strtolower(trim($leaveType->code)) : '';
            $normalizedName = is_string($leaveType->name) ? strtolower(trim($leaveType->name)) : '';

            return $normalizedCode === 'sick' || $normalizedName === 'sakit' || $normalizedName === 'sick leave';
        });

        $specialLeaveSubTypes = collect();
        if ($specialLeaveType instanceof LeaveType) {
            $specialLeaveSubTypes = DB::table('leave_sub_types')
                ->select(['id', 'name'])
                ->where('leave_type_id', $specialLeaveType->id)
                ->where('is_active', true)
                ->whereNull('deleted_at')
                ->orderBy('name')
                ->get();
        }

        return view('attendance.leave-requests.index', [
            'leaveEligibility' => $this->buildLeaveEligibilityData($authenticatedUser),
            'leaveTracker' => $this->buildLeaveTrackerData($authenticatedUser),
            'leaveTypes' => $leaveTypes,
            'specialLeaveTypeId' => $specialLeaveType?->id,
            'sickLeaveTypeId' => $sickLeaveType?->id,
            'specialLeaveSubTypes' => $specialLeaveSubTypes,
            'leaveHistoryCards' => $this->buildLeaveHistoryCards($authenticatedUser),
        ]);
    }

    public function cards(Request $request): JsonResponse
    {
        $authenticatedUser = $this->resolveAuthenticatedUser(['employee']);
        $leaveHistoryCards = $this->buildLeaveHistoryCards(
            $authenticatedUser,
            $this->resolveLeaveHistoryFilters($request)
        );

        return response()->json([
            'success' => true,
            'cards' => $leaveHistoryCards->values(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'permission_type_id' => ['required', 'exists:leave_types,id'],
            'special_leave_sub_type_id' => ['nullable', 'exists:leave_sub_types,id'],
            'reason' => ['required', 'string', 'max:5000'],
            'handover_notes' => ['nullable', 'string', 'max:5000'],
            'attachment_path' => ['nullable', 'string', 'max:255'],
            'attachment_file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:1024'],
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
        $employeeId = (string) ($authenticatedUser?->employee?->id ?? '');
        $authenticatedUserId = is_string($authenticatedUser?->id) || is_int($authenticatedUser?->id)
            ? (string) $authenticatedUser->id
            : '';

        if ($employeeId === '') {
            return response()->json([
                'success' => false,
                'message' => 'Data karyawan tidak ditemukan.',
            ], 422);
        }

        $specialLeaveSubTypeId = is_string($validated['special_leave_sub_type_id'] ?? null)
            ? trim((string) $validated['special_leave_sub_type_id'])
            : '';
        if ($normalizedPermissionType === 'cuti khusus') {
            if ($specialLeaveSubTypeId === '') {
                return response()->json([
                    'success' => false,
                    'message' => 'Pilih Special Leave Type terlebih dahulu.',
                ], 422);
            }

            $isSpecialSubTypeValid = DB::table('leave_sub_types')
                ->where('id', $specialLeaveSubTypeId)
                ->where('leave_type_id', $validated['permission_type_id'])
                ->where('is_active', true)
                ->whereNull('deleted_at')
                ->exists();
            if (! $isSpecialSubTypeValid) {
                return response()->json([
                    'success' => false,
                    'message' => 'Special Leave Type tidak valid.',
                ], 422);
            }
        }

        if ($normalizedPermissionType === 'cuti tahunan') {
            $hasCheckedInToday = Attendance::query()
                ->where('employee_id', $employeeId)
                ->whereDate('date', now('Asia/Jakarta')->toDateString())
                ->whereNotNull('clock_in')
                ->exists();

            if (! $hasCheckedInToday) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cuti Tahunan hanya dapat diajukan setelah Anda absen masuk hari ini.',
                ], 422);
            }

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

        if ($normalizedPermissionType === 'sakit') {
            $hasUploadFile = $request->hasFile('attachment_file') && $request->file('attachment_file')?->isValid() === true;
            if (! $hasUploadFile && $temporaryAttachmentPath === null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lampiran wajib diisi untuk Sick Leave.',
                ], 422);
            }
        }

        DB::transaction(function () use ($request, $validated, $durationDays, $employeeId, $temporaryAttachmentPath, $authenticatedUserId): void {
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

            $this->writeLeaveRequestHistory(
                leaveRequest: $leaveRequest,
                eventType: 'submitted',
                title: 'Request Submitted',
                fromStatus: null,
                toStatus: 'pending',
                notes: null,
                actorUserId: $authenticatedUserId !== '' ? $authenticatedUserId : null,
            );
        });

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan izin berhasil disimpan.',
        ]);
    }

    public function uploadImage(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'attachment_file' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:1024'],
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

    public function destroy(LeaveRequest $leaveRequest): JsonResponse
    {
        $authenticatedUser = Auth::user();
        if (! $this->canDeletePermissionRequest($authenticatedUser, $leaveRequest)) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak memiliki akses untuk menghapus data izin ini.',
            ], 403);
        }

        $authenticatedUserId = is_string($authenticatedUser?->id) || is_int($authenticatedUser?->id)
            ? (string) $authenticatedUser->id
            : '';

        DB::transaction(function () use ($leaveRequest, $authenticatedUserId): void {
            $currentStatus = is_string($leaveRequest->status) ? strtolower(trim($leaveRequest->status)) : null;
            if (! in_array($currentStatus, ['pending', 'approved', 'refused', 'rejected'], true)) {
                $currentStatus = null;
            }

            $this->writeLeaveRequestHistory(
                leaveRequest: $leaveRequest,
                eventType: 'deleted',
                title: 'Request Deleted',
                fromStatus: $currentStatus,
                toStatus: $currentStatus,
                notes: null,
                actorUserId: $authenticatedUserId !== '' ? $authenticatedUserId : null,
            );

            if (is_string($leaveRequest->attachment_path) && trim($leaveRequest->attachment_path) !== '') {
                $this->publicDisk()->delete($leaveRequest->attachment_path);
            }

            $leaveRequest->delete();
        });

        $leaveRequestYear = (int) Carbon::parse($leaveRequest->start_date)->year;
        $leaveRequestMonth = (int) Carbon::parse($leaveRequest->start_date)->month;
        $leaveRequestTypeId = is_string($leaveRequest->leave_type_id) ? trim($leaveRequest->leave_type_id) : '';

        $this->syncAnnualLeaveBalance((string) $leaveRequest->employee_id, $leaveRequestYear, $leaveRequestMonth);
        if ($this->isSpecialLeaveTypeId($leaveRequestTypeId)) {
            $this->syncMonthlySpecialLeaveLimitFlag((string) $leaveRequest->employee_id, $leaveRequestYear, $leaveRequestMonth);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data izin berhasil dihapus.',
        ]);
    }

    private function isBoardOfDirectur(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        $normalizedRoleNames = $user->getRoleNames()
            ->map(static fn (string $roleName): string => strtolower(trim($roleName)));

        return $normalizedRoleNames->contains('board of directur')
            || $normalizedRoleNames->contains('board of directors')
            || $normalizedRoleNames->contains('supervisor');
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

    private function isSuperuser(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $user->getRoleNames()
            ->map(static fn (string $roleName): string => strtolower(trim($roleName)))
            ->contains('superuser');
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

    private function canDeletePermissionRequest(?User $authenticatedUser, LeaveRequest $leaveRequest): bool
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
                $query->whereRaw('LOWER(name) = ?', ['cuti tahunan']);
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
                $query->whereRaw('LOWER(name) = ?', ['cuti tahunan']);
            })
            ->whereRaw('LOWER(status) = ?', ['approved'])
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

    /**
     * @param  array{status:string, leave_type:string, timeframe:string}  $filters
     * @return Collection<int, array{
     *     title:string,
     *     icon_file:string,
     *     modal_title:string,
     *     detail_leave_type:string,
     *     period_label:string,
     *     reason:string,
     *     is_sick_leave:bool,
     *     timeline:array<int, array{date_label:string, title:string, badge_class:string}>,
     *     due_date_label:string,
     *     status_label:string,
     *     status_badge_class:string,
     *     status_text_class:string,
     *     status_date_label:string,
     *     attachment_url:?string
     * }>
     */
    private function buildLeaveHistoryCards(?User $authenticatedUser, array $filters = [
        'status' => 'all',
        'leave_type' => 'all',
        'timeframe' => 'year_to_date',
    ]): Collection
    {
        $employeeId = is_string($authenticatedUser?->employee?->id)
            ? trim((string) $authenticatedUser->employee->id)
            : '';
        if ($employeeId === '') {
            return collect();
        }

        $leaveRequests = LeaveRequest::query()
            ->with([
                'leaveType:id,code,name',
                'histories' => static function ($query): void {
                    $query->select(['id', 'leave_request_id', 'event_type', 'title', 'to_status', 'happened_at'])
                        ->orderBy('happened_at')
                        ->orderBy('created_at');
                },
            ])
            ->where('employee_id', $employeeId)
            ->where('is_active', true)
            ->when(($filters['status'] ?? 'all') !== 'all', function (Builder $query) use ($filters): void {
                $this->applyLeaveHistoryStatusFilter($query, (string) $filters['status']);
            })
            ->when(($filters['leave_type'] ?? 'all') !== 'all', function (Builder $query) use ($filters): void {
                $this->applyLeaveHistoryTypeFilter($query, (string) $filters['leave_type']);
            })
            ->when(($filters['timeframe'] ?? 'all') !== 'all', function (Builder $query) use ($filters): void {
                $this->applyLeaveHistoryTimeframeFilter($query, (string) $filters['timeframe']);
            })
            ->orderByDesc('created_at')
            ->limit(12)
            ->get(['id', 'leave_type_id', 'start_date', 'end_date', 'total_days', 'reason', 'status', 'attachment_path', 'created_at']);

        return $leaveRequests->map(function (LeaveRequest $leaveRequest): array {
            $startDate = $leaveRequest->start_date instanceof Carbon
                ? $leaveRequest->start_date
                : Carbon::parse((string) $leaveRequest->start_date);
            $endDate = $leaveRequest->end_date instanceof Carbon
                ? $leaveRequest->end_date
                : Carbon::parse((string) $leaveRequest->end_date);
            $totalDays = max((int) ($leaveRequest->total_days ?? ($startDate->diffInDays($endDate) + 1)), 1);
            $status = strtolower(trim((string) $leaveRequest->status));

            $timelineRows = $this->buildFixedLeaveTimelineRows($leaveRequest, $status, $startDate);

            $leaveTypeName = is_string($leaveRequest->leaveType?->name)
                ? trim((string) $leaveRequest->leaveType?->name)
                : '';
            $leaveTypeCode = is_string($leaveRequest->leaveType?->code)
                ? strtolower(trim((string) $leaveRequest->leaveType?->code))
                : '';
            if ($leaveTypeName === '') {
                $leaveTypeName = 'Leave Request';
            }
            $normalizedLeaveTypeName = strtolower($leaveTypeName);
            $isSickLeave = in_array($normalizedLeaveTypeName, ['sakit', 'sick leave'], true);
            if ($isSickLeave) {
                $leaveTypeName = 'Cuti Sakit';
            }
            $detailLeaveType = $isSickLeave ? 'Sick Leave' : $leaveTypeName;
            $modalTitle = $isSickLeave ? 'Attendance Sick' : $leaveTypeName;
            $attachmentPath = is_string($leaveRequest->attachment_path) ? trim($leaveRequest->attachment_path) : '';

            return [
                'title' => $leaveTypeName,
                'icon_file' => $this->resolveLeaveHistoryIconFile($leaveTypeCode, $normalizedLeaveTypeName),
                'modal_title' => $modalTitle,
                'detail_leave_type' => $detailLeaveType,
                'period_label' => $startDate->isSameDay($endDate)
                    ? $startDate->format('d M Y').' ('.$totalDays.' '.Str::plural('day', $totalDays).')'
                    : $startDate->format('d M Y').' - '.$endDate->format('d M Y').' ('.$totalDays.' '.Str::plural('day', $totalDays).')',
                'reason' => trim((string) $leaveRequest->reason) !== '' ? trim((string) $leaveRequest->reason) : '-',
                'is_sick_leave' => $isSickLeave,
                'timeline' => $timelineRows,
                'due_date_label' => $startDate->format('d M Y'),
                'status_label' => match ($status) {
                    'approved' => 'Approved',
                    'rejected', 'refused' => 'Rejected',
                    default => 'Pending',
                },
                'status_badge_class' => match ($status) {
                    'approved' => 'badge-success light',
                    'rejected', 'refused' => 'badge-danger light',
                    default => 'badge-primary light',
                },
                'status_text_class' => match ($status) {
                    'approved' => 'text-success',
                    'rejected', 'refused' => 'text-danger',
                    default => 'text-primary',
                },
                'status_date_label' => $this->resolveLeaveStatusDateLabel($leaveRequest, $status, $startDate),
                'attachment_url' => $attachmentPath !== '' ? $this->publicDisk()->url($attachmentPath) : null,
            ];
        });
    }

    private function resolveLeaveHistoryIconFile(string $leaveTypeCode, string $leaveTypeName): string
    {
        return match (true) {
            in_array($leaveTypeCode, ['annual', 'annual_leave'], true)
                || in_array($leaveTypeName, ['cuti tahunan', 'annual leave'], true) => 'annual_leave.svg',
            in_array($leaveTypeCode, ['sick', 'sick_leave'], true)
                || in_array($leaveTypeName, ['sakit', 'sick leave'], true) => 'sick_leave.svg',
            in_array($leaveTypeCode, ['special', 'special_leave'], true)
                || in_array($leaveTypeName, ['cuti khusus', 'special leave'], true) => 'special_leave.svg',
            in_array($leaveTypeCode, ['unpaid', 'unpaid_leave'], true)
                || in_array($leaveTypeName, ['unpaid leave'], true) => 'unpaid_leave.svg',
            default => 'annual_leave.svg',
        };
    }

    /**
     * @return array{status:string, leave_type:string, timeframe:string}
     */
    private function resolveLeaveHistoryFilters(Request $request): array
    {
        $status = strtolower(trim((string) $request->input('status', 'all')));
        $leaveType = strtolower(trim((string) $request->input('leave_type', 'all')));
        $timeframe = strtolower(trim((string) $request->input('timeframe', 'year_to_date')));

        return [
            'status' => in_array($status, ['all', 'approved', 'pending', 'rejected', 'canceled'], true)
                ? $status
                : 'all',
            'leave_type' => in_array($leaveType, ['all', 'annual_leave', 'sick_leave', 'special_leave', 'unpaid_leave'], true)
                ? $leaveType
                : 'all',
            'timeframe' => in_array($timeframe, ['all', 'this_month', 'last_month', 'year_to_date'], true)
                ? $timeframe
                : 'year_to_date',
        ];
    }

    private function applyLeaveHistoryStatusFilter(Builder $query, string $status): void
    {
        $statusMap = [
            'approved' => ['approved'],
            'pending' => ['pending'],
            'rejected' => ['rejected', 'refused'],
            'canceled' => ['canceled', 'cancelled'],
        ];

        $statuses = $statusMap[$status] ?? [];
        if ($statuses === []) {
            return;
        }

        $query->whereIn(DB::raw('LOWER(status)'), $statuses);
    }

    private function applyLeaveHistoryTypeFilter(Builder $query, string $leaveType): void
    {
        $typeMap = [
            'annual_leave' => [
                'codes' => ['annual', 'annual_leave'],
                'names' => ['cuti tahunan', 'annual leave'],
            ],
            'sick_leave' => [
                'codes' => ['sick', 'sick_leave'],
                'names' => ['sakit', 'sick leave'],
            ],
            'special_leave' => [
                'codes' => ['special', 'special_leave'],
                'names' => ['cuti khusus', 'special leave'],
            ],
            'unpaid_leave' => [
                'codes' => ['unpaid', 'unpaid_leave'],
                'names' => ['cuti tidak dibayar', 'unpaid leave'],
            ],
        ];

        $selectedType = $typeMap[$leaveType] ?? null;
        if (! is_array($selectedType)) {
            return;
        }

        $codes = $this->normalizeTextCandidates($selectedType['codes']);
        $names = $this->normalizeTextCandidates($selectedType['names']);

        $query->whereHas('leaveType', function (Builder $leaveTypeQuery) use ($codes, $names): void {
            $leaveTypeQuery->where(function (Builder $nestedQuery) use ($codes, $names): void {
                if ($codes !== []) {
                    $nestedQuery->whereIn(DB::raw('LOWER(code)'), $codes);
                }

                if ($names !== []) {
                    $method = $codes === [] ? 'whereIn' : 'orWhereIn';
                    $nestedQuery->{$method}(DB::raw('LOWER(name)'), $names);
                }
            });
        });
    }

    private function applyLeaveHistoryTimeframeFilter(Builder $query, string $timeframe): void
    {
        $today = now('Asia/Jakarta')->startOfDay();

        match ($timeframe) {
            'this_month' => $query
                ->whereDate('start_date', '>=', $today->copy()->startOfMonth()->toDateString())
                ->whereDate('start_date', '<=', $today->copy()->endOfMonth()->toDateString()),
            'last_month' => $query
                ->whereDate('start_date', '>=', $today->copy()->subMonthNoOverflow()->startOfMonth()->toDateString())
                ->whereDate('start_date', '<=', $today->copy()->subMonthNoOverflow()->endOfMonth()->toDateString()),
            'year_to_date' => $query
                ->whereDate('start_date', '>=', $today->copy()->startOfYear()->toDateString())
                ->whereDate('start_date', '<=', $today->copy()->endOfYear()->toDateString()),
            default => null,
        };
    }

    /**
     * @return array<int, array{date_label:string, title:string, badge_class:string}>
     */
    private function buildFixedLeaveTimelineRows(LeaveRequest $leaveRequest, string $status, Carbon $fallbackDate): array
    {
        $submittedAt = $this->resolveLeaveTimelineDate(
            $leaveRequest->histories,
            ['submitted', 'created'],
            ['request submitted']
        );
        if (! is_string($submittedAt) || $submittedAt === '') {
            $submittedAt = $fallbackDate->copy()->setTimezone('Asia/Jakarta')->format('d M');
        }

        $supervisorReviewedAt = $this->resolveLeaveTimelineDate(
            $leaveRequest->histories,
            ['supervisor_review', 'reviewed'],
            ['supervisor review']
        );
        $hrVerificationAt = $this->resolveLeaveTimelineDate(
            $leaveRequest->histories,
            ['hr_verification', 'hr_review'],
            ['hr verification']
        );

        $finalDecisionAt = $this->resolveLeaveTimelineDate(
            $leaveRequest->histories,
            ['status_updated', 'approved', 'rejected', 'refused'],
            ['approved', 'rejected', 'final decision']
        );
        if ($finalDecisionAt === '' && in_array($status, ['approved', 'rejected', 'refused'], true)) {
            $finalDecisionAt = $fallbackDate->copy()->setTimezone('Asia/Jakarta')->format('d M');
        }

        $isSubmittedReached = $submittedAt !== '';
        $isSupervisorReached = $supervisorReviewedAt !== '';
        $isHrReached = $hrVerificationAt !== '';
        $isFinalReached = $finalDecisionAt !== '' && in_array($status, ['approved', 'rejected', 'refused'], true);

        return [
            [
                'date_label' => $submittedAt,
                'title' => 'Request Submitted',
                'badge_class' => $isSubmittedReached ? 'border-success' : 'border-dark',
            ],
            [
                'date_label' => $isSupervisorReached ? $supervisorReviewedAt : 'Waiting',
                'title' => 'Supervisor Review',
                'badge_class' => $isSupervisorReached ? 'border-success' : 'border-dark',
            ],
            [
                'date_label' => $isHrReached ? $hrVerificationAt : 'Waiting',
                'title' => 'HR Verification (Pending)',
                'badge_class' => $isHrReached ? 'border-success' : 'border-dark',
            ],
            [
                'date_label' => $isFinalReached ? $finalDecisionAt : 'Waiting',
                'title' => 'Final Decision',
                'badge_class' => $isFinalReached
                    ? (in_array($status, ['rejected', 'refused'], true) ? 'border-danger' : 'border-success')
                    : 'border-dark',
            ],
        ];
    }

    private function resolveLeaveTimelineDate(Collection $histories, array $eventTypes, array $titleKeywords, string $format = 'd M'): string
    {
        /** @var LeaveRequestHistory|null $matchedHistory */
        $matchedHistory = $histories->first(static function (LeaveRequestHistory $history) use ($eventTypes, $titleKeywords): bool {
            $normalizedEventType = is_string($history->event_type) ? strtolower(trim($history->event_type)) : '';
            $normalizedTitle = is_string($history->title) ? strtolower(trim($history->title)) : '';

            if (in_array($normalizedEventType, $eventTypes, true)) {
                return true;
            }

            foreach ($titleKeywords as $keyword) {
                if ($keyword !== '' && str_contains($normalizedTitle, strtolower(trim((string) $keyword)))) {
                    return true;
                }
            }

            return false;
        });

        if (! $matchedHistory instanceof LeaveRequestHistory) {
            return '';
        }

        $happenedAt = $matchedHistory->happened_at instanceof Carbon
            ? $matchedHistory->happened_at
            : ($matchedHistory->happened_at ? Carbon::parse((string) $matchedHistory->happened_at) : null);

        return $happenedAt?->copy()->setTimezone('Asia/Jakarta')->format($format) ?? '';
    }

    private function resolveLeaveStatusDateLabel(LeaveRequest $leaveRequest, string $status, Carbon $fallbackDate): string
    {
        if (! in_array($status, ['approved', 'rejected', 'refused'], true)) {
            return '';
        }

        $statusDate = $this->resolveLeaveTimelineDate(
            $leaveRequest->histories,
            ['status_updated', 'approved', 'rejected', 'refused'],
            ['approved', 'rejected', 'final decision'],
            'd M Y'
        );

        if ($statusDate !== '') {
            return $statusDate;
        }

        return $fallbackDate->copy()->setTimezone('Asia/Jakarta')->format('d M Y');
    }

    /**
     * @return array{
     *     full_name:string,
     *     supervisor_name:string,
     *     join_date_label:string,
     *     tenure_label:string,
     *     is_eligible:bool,
     *     available_balance_label:string,
     *     available_balance_note:string,
     *     next_accrual_label:string,
     *     next_accrual_note:string,
     *     joint_holiday_label:string,
     *     joint_holiday_items:list<string>
     * }
     */
    private function buildLeaveEligibilityData(?User $authenticatedUser): array
    {
        $now = now('Asia/Jakarta');
        $currentYear = (int) $now->year;
        $currentMonth = (int) $now->month;
        $jointHolidaySummary = $this->buildJointHolidaySummary($currentYear, $now);

        $defaultData = [
            'full_name' => '-',
            'supervisor_name' => '-',
            'join_date_label' => '-',
            'tenure_label' => '-',
            'is_eligible' => false,
            'available_balance_label' => '0 Days',
            'available_balance_note' => 'No balance available yet.',
            'next_accrual_label' => '+0 Day',
            'next_accrual_note' => 'No automatic accrual configured.',
            'joint_holiday_label' => $jointHolidaySummary['label'],
            'joint_holiday_items' => $jointHolidaySummary['items'],
        ];

        $employeeId = is_string($authenticatedUser?->employee?->id)
            ? trim((string) $authenticatedUser->employee->id)
            : '';
        if ($employeeId === '') {
            return $defaultData;
        }

        $fullName = EmployeeProfile::query()
            ->where('employee_id', $employeeId)
            ->value('name');
        if (! is_string($fullName) || trim($fullName) === '') {
            $fullName = $authenticatedUser?->username ?: '-';
        }

        $supervisorEmployeeId = DB::table('employee_pic_assignments')
            ->where('staff_employee_id', $employeeId)
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->value('supervisor_employee_id');
        $supervisorName = '-';
        if (is_string($supervisorEmployeeId) && trim($supervisorEmployeeId) !== '') {
            $supervisorProfileName = EmployeeProfile::query()
                ->where('employee_id', trim($supervisorEmployeeId))
                ->value('name');

            if (is_string($supervisorProfileName) && trim($supervisorProfileName) !== '') {
                $supervisorName = trim($supervisorProfileName);
            } else {
                $supervisorUsername = DB::table('employees')
                    ->join('users', 'users.id', '=', 'employees.user_id')
                    ->where('employees.id', trim($supervisorEmployeeId))
                    ->value('users.username');
                if (is_string($supervisorUsername) && trim($supervisorUsername) !== '') {
                    $supervisorName = trim($supervisorUsername);
                }
            }
        }

        $joinDateRaw = EmployeeDeployment::query()
            ->where('employee_id', $employeeId)
            ->value('join_date');
        $joinDateLabel = '-';
        $tenureLabel = '-';
        $isEligible = false;
        $joinDate = null;
        if ($joinDateRaw instanceof \DateTimeInterface) {
            $joinDate = Carbon::instance($joinDateRaw)->startOfDay();
        } elseif (is_string($joinDateRaw) && trim($joinDateRaw) !== '') {
            $joinDate = Carbon::parse($joinDateRaw)->startOfDay();
        }

        if ($joinDate instanceof Carbon) {
            $joinDateLabel = $joinDate->format('d F Y');
            $tenureMonths = max((int) floor($joinDate->diffInMonths($now, true)), 0);
            $tenureLabel = $this->formatTenureLabel($tenureMonths);
            $isEligible = $tenureMonths >= 12;
        }

        $leaveSummary = $this->calculateStaffLeaveSummary($employeeId, $currentYear, $currentMonth);
        $remainingAnnualBalance = (int) ($leaveSummary['remaining_annual_balance'] ?? 0);

        $annualLeaveMonthlyAccrual = (float) LeaveType::query()
            ->whereRaw('LOWER(name) = ?', ['cuti tahunan'])
            ->value('monthly_accrual_rate');
        $nextAccrualDays = max((int) round($annualLeaveMonthlyAccrual), 0);
        $nextAccrualLabel = '+'.$nextAccrualDays.' '.Str::plural('Day', $nextAccrualDays);
        $nextAccrualNote = $nextAccrualDays > 0
            ? 'Will be automatically added next month'
            : 'No automatic accrual configured.';

        return [
            'full_name' => trim((string) $fullName) !== '' ? trim((string) $fullName) : '-',
            'supervisor_name' => $supervisorName,
            'join_date_label' => $joinDateLabel,
            'tenure_label' => $tenureLabel,
            'is_eligible' => $isEligible,
            'available_balance_label' => $remainingAnnualBalance.' '.Str::plural('Day', $remainingAnnualBalance),
            'available_balance_note' => $remainingAnnualBalance > 0 ? 'Rolled over from previous months' : 'No available annual leave balance.',
            'next_accrual_label' => $nextAccrualLabel,
            'next_accrual_note' => $nextAccrualNote,
            'joint_holiday_label' => $jointHolidaySummary['label'],
            'joint_holiday_items' => $jointHolidaySummary['items'],
        ];
    }

    /**
     * @return array{
     *     year:int,
     *     month_label:string,
     *     annual_leave_taken_label:string,
     *     annual_leave_taken_breakdown:string,
     *     annual_leave_taken_month_label:string,
     *     annual_leave_taken_month_breakdown:string,
     *     annual_leave_monthly_limit_label:string,
     *     sick_leave_taken_label:string,
     *     sick_leave_taken_breakdown:string,
     *     sick_leave_taken_month_label:string,
     *     sick_leave_taken_month_breakdown:string,
     *     special_leave_taken_label:string,
     *     special_leave_taken_breakdown:string,
     *     special_leave_taken_month_label:string,
     *     special_leave_taken_month_breakdown:string,
     *     unpaid_leave_taken_label:string,
     *     unpaid_leave_taken_breakdown:string,
     *     unpaid_leave_taken_month_label:string,
     *     unpaid_leave_taken_month_breakdown:string,
     *     pending_requests_label:string,
     *     approved_requests_label:string,
     *     rejected_requests_label:string
     * }
     */
    private function buildLeaveTrackerData(?User $authenticatedUser): array
    {
        $now = now('Asia/Jakarta');
        $currentYear = (int) $now->year;
        $currentMonth = (int) $now->month;
        $monthLabel = $now->format('F');

        $defaultUsageSummary = [
            'year_label' => '0 Days',
            'year_breakdown' => 'No leave taken yet.',
            'month_label' => '0 Days',
            'month_breakdown' => 'No leave taken this month.',
        ];

        $defaultData = [
            'year' => $currentYear,
            'month_label' => $monthLabel,
            'annual_leave_taken_label' => $defaultUsageSummary['year_label'],
            'annual_leave_taken_breakdown' => $defaultUsageSummary['year_breakdown'],
            'annual_leave_taken_month_label' => $defaultUsageSummary['month_label'],
            'annual_leave_taken_month_breakdown' => $defaultUsageSummary['month_breakdown'],
            'annual_leave_monthly_limit_label' => 'Maximum limit is 2 days per month',
            'sick_leave_taken_label' => $defaultUsageSummary['year_label'],
            'sick_leave_taken_breakdown' => $defaultUsageSummary['year_breakdown'],
            'sick_leave_taken_month_label' => $defaultUsageSummary['month_label'],
            'sick_leave_taken_month_breakdown' => $defaultUsageSummary['month_breakdown'],
            'special_leave_taken_label' => $defaultUsageSummary['year_label'],
            'special_leave_taken_breakdown' => $defaultUsageSummary['year_breakdown'],
            'special_leave_taken_month_label' => $defaultUsageSummary['month_label'],
            'special_leave_taken_month_breakdown' => $defaultUsageSummary['month_breakdown'],
            'unpaid_leave_taken_label' => $defaultUsageSummary['year_label'],
            'unpaid_leave_taken_breakdown' => $defaultUsageSummary['year_breakdown'],
            'unpaid_leave_taken_month_label' => $defaultUsageSummary['month_label'],
            'unpaid_leave_taken_month_breakdown' => $defaultUsageSummary['month_breakdown'],
            'pending_requests_label' => '0 Requests',
            'approved_requests_label' => '0 Requests',
            'rejected_requests_label' => '0 Requests',
        ];

        $employeeId = is_string($authenticatedUser?->employee?->id)
            ? trim((string) $authenticatedUser->employee->id)
            : '';
        if ($employeeId === '') {
            return $defaultData;
        }

        $annualLeaveUsage = $this->buildLeaveTypeUsageSummary($employeeId, $currentYear, $currentMonth, ['annual', 'annual_leave'], ['cuti tahunan', 'annual leave']);
        $sickLeaveUsage = $this->buildLeaveTypeUsageSummary($employeeId, $currentYear, $currentMonth, ['sick', 'sick_leave'], ['sakit', 'sick leave']);
        $specialLeaveUsage = $this->buildLeaveTypeUsageSummary($employeeId, $currentYear, $currentMonth, ['special', 'special_leave'], ['cuti khusus', 'special leave']);
        $unpaidLeaveUsage = $this->buildLeaveTypeUsageSummary($employeeId, $currentYear, $currentMonth, ['unpaid', 'unpaid_leave'], ['cuti tidak dibayar', 'unpaid leave']);

        $pendingRequests = $this->countStaffLeaveRequestsByStatus($employeeId, $currentYear, ['pending']);
        $approvedRequests = $this->countStaffLeaveRequestsByStatus($employeeId, $currentYear, ['approved']);
        $rejectedRequests = $this->countStaffLeaveRequestsByStatus($employeeId, $currentYear, ['rejected', 'refused']);

        return [
            'year' => $currentYear,
            'month_label' => $monthLabel,
            'annual_leave_taken_label' => $annualLeaveUsage['year_label'],
            'annual_leave_taken_breakdown' => $annualLeaveUsage['year_breakdown'],
            'annual_leave_taken_month_label' => $annualLeaveUsage['month_label'],
            'annual_leave_taken_month_breakdown' => $annualLeaveUsage['month_breakdown'],
            'annual_leave_monthly_limit_label' => 'Maximum limit is 2 days per month',
            'sick_leave_taken_label' => $sickLeaveUsage['year_label'],
            'sick_leave_taken_breakdown' => $sickLeaveUsage['year_breakdown'],
            'sick_leave_taken_month_label' => $sickLeaveUsage['month_label'],
            'sick_leave_taken_month_breakdown' => $sickLeaveUsage['month_breakdown'],
            'special_leave_taken_label' => $specialLeaveUsage['year_label'],
            'special_leave_taken_breakdown' => $specialLeaveUsage['year_breakdown'],
            'special_leave_taken_month_label' => $specialLeaveUsage['month_label'],
            'special_leave_taken_month_breakdown' => $specialLeaveUsage['month_breakdown'],
            'unpaid_leave_taken_label' => $unpaidLeaveUsage['year_label'],
            'unpaid_leave_taken_breakdown' => $unpaidLeaveUsage['year_breakdown'],
            'unpaid_leave_taken_month_label' => $unpaidLeaveUsage['month_label'],
            'unpaid_leave_taken_month_breakdown' => $unpaidLeaveUsage['month_breakdown'],
            'pending_requests_label' => $pendingRequests.' '.Str::plural('Request', $pendingRequests),
            'approved_requests_label' => $approvedRequests.' '.Str::plural('Request', $approvedRequests),
            'rejected_requests_label' => $rejectedRequests.' '.Str::plural('Request', $rejectedRequests),
        ];
    }

    /**
     * @param  list<string>  $codeCandidates
     * @param  list<string>  $nameCandidates
     * @return array{year_label:string, year_breakdown:string, month_label:string, month_breakdown:string}
     */
    private function buildLeaveTypeUsageSummary(string $employeeId, int $year, int $month, array $codeCandidates, array $nameCandidates): array
    {
        $yearRequests = $this->queryApprovedStaffLeaveRequestsByType($employeeId, $year, null, $codeCandidates, $nameCandidates);
        $monthRequests = $this->queryApprovedStaffLeaveRequestsByType($employeeId, $year, $month, $codeCandidates, $nameCandidates);
        $yearTotalDays = (int) round((float) $yearRequests->sum('total_days'));
        $monthTotalDays = (int) round((float) $monthRequests->sum('total_days'));

        return [
            'year_label' => $yearTotalDays.' '.Str::plural('Day', $yearTotalDays),
            'year_breakdown' => $this->formatLeaveRequestDateList($yearRequests, 'No leave taken yet.'),
            'month_label' => $monthTotalDays.' '.Str::plural('Day', $monthTotalDays),
            'month_breakdown' => $this->formatLeaveRequestDateList($monthRequests, 'No leave taken this month.'),
        ];
    }

    /**
     * @param  list<string>  $codeCandidates
     * @param  list<string>  $nameCandidates
     * @return Collection<int, LeaveRequest>
     */
    private function queryApprovedStaffLeaveRequestsByType(string $employeeId, int $year, ?int $month, array $codeCandidates, array $nameCandidates): Collection
    {
        $normalizedCodeCandidates = $this->normalizeTextCandidates($codeCandidates);
        $normalizedNameCandidates = $this->normalizeTextCandidates($nameCandidates);

        return LeaveRequest::query()
            ->where('employee_id', $employeeId)
            ->whereYear('start_date', $year)
            ->when(is_int($month), static fn ($query) => $query->whereMonth('start_date', $month))
            ->whereRaw('LOWER(status) = ?', ['approved'])
            ->whereHas('leaveType', function ($query) use ($normalizedCodeCandidates, $normalizedNameCandidates): void {
                $query->where(function ($nestedQuery) use ($normalizedCodeCandidates, $normalizedNameCandidates): void {
                    if ($normalizedCodeCandidates !== []) {
                        $nestedQuery->whereIn(DB::raw('LOWER(code)'), $normalizedCodeCandidates);
                    }

                    if ($normalizedNameCandidates !== []) {
                        $method = $normalizedCodeCandidates === [] ? 'whereIn' : 'orWhereIn';
                        $nestedQuery->{$method}(DB::raw('LOWER(name)'), $normalizedNameCandidates);
                    }
                });
            })
            ->orderBy('start_date')
            ->get(['id', 'leave_type_id', 'start_date', 'end_date', 'total_days']);
    }

    /**
     * @param  Collection<int, LeaveRequest>  $leaveRequests
     */
    private function formatLeaveRequestDateList(Collection $leaveRequests, string $emptyLabel): string
    {
        if ($leaveRequests->isEmpty()) {
            return $emptyLabel;
        }

        return $leaveRequests
            ->map(function (LeaveRequest $leaveRequest): string {
                $startDate = $leaveRequest->start_date instanceof Carbon
                    ? $leaveRequest->start_date
                    : Carbon::parse((string) $leaveRequest->start_date);
                $endDate = $leaveRequest->end_date instanceof Carbon
                    ? $leaveRequest->end_date
                    : Carbon::parse((string) ($leaveRequest->end_date ?? $leaveRequest->start_date));

                if ($startDate->isSameDay($endDate)) {
                    return $startDate->format('d M');
                }

                if ($startDate->isSameMonth($endDate)) {
                    return $startDate->format('d').'-'.$endDate->format('d M');
                }

                return $startDate->format('d M').'-'.$endDate->format('d M');
            })
            ->implode(', ');
    }

    /**
     * @param  list<string>  $statuses
     */
    private function countStaffLeaveRequestsByStatus(string $employeeId, int $year, array $statuses): int
    {
        $normalizedStatuses = $this->normalizeTextCandidates($statuses);
        if ($normalizedStatuses === []) {
            return 0;
        }

        return (int) LeaveRequest::query()
            ->where('employee_id', $employeeId)
            ->whereYear('start_date', $year)
            ->whereIn(DB::raw('LOWER(status)'), $normalizedStatuses)
            ->count();
    }

    /**
     * @return array{label:string, items:list<string>}
     */
    private function buildJointHolidaySummary(int $year, Carbon $today): array
    {
        $jointHolidayRows = AttendanceHoliday::query()
            ->whereYear('date', $year)
            ->where('type', 2)
            ->orderBy('date')
            ->get(['date', 'name']);

        $totalDays = $jointHolidayRows->count();
        $passedDays = $jointHolidayRows
            ->filter(function (AttendanceHoliday $attendanceHoliday) use ($today): bool {
                $holidayDate = $attendanceHoliday->date instanceof Carbon
                    ? $attendanceHoliday->date
                    : Carbon::parse((string) $attendanceHoliday->date);

                return $holidayDate->isSameDay($today) || $holidayDate->lessThan($today);
            })
            ->count();
        $remainingDays = max($totalDays - $passedDays, 0);

        $items = $jointHolidayRows
            ->groupBy(static fn (AttendanceHoliday $attendanceHoliday): string => trim((string) $attendanceHoliday->name) !== '' ? trim((string) $attendanceHoliday->name) : 'Joint Holiday')
            ->map(function (Collection $items, string $holidayName): string {
                $dateLabels = $items
                    ->map(function (AttendanceHoliday $attendanceHoliday): string {
                        $holidayDate = $attendanceHoliday->date instanceof Carbon
                            ? $attendanceHoliday->date
                            : Carbon::parse((string) $attendanceHoliday->date);

                        return $holidayDate->format('d M');
                    })
                    ->implode(', ');

                return $holidayName.' ('.$dateLabels.')';
            })
            ->values()
            ->all();

        return [
            'label' => $remainingDays.' / '.$totalDays.' '.Str::plural('Day', $totalDays),
            'items' => $items !== [] ? $items : ['No joint holiday scheduled.'],
        ];
    }

    private function formatTenureLabel(int $tenureMonths): string
    {
        $years = intdiv($tenureMonths, 12);
        $months = $tenureMonths % 12;
        $segments = [];

        if ($years > 0) {
            $segments[] = $years.' '.Str::plural('Year', $years);
        }

        if ($months > 0 || $segments === []) {
            $segments[] = $months.' '.Str::plural('Month', $months);
        }

        return implode(', ', $segments);
    }

    /**
     * @param  list<string>  $candidates
     * @return list<string>
     */
    private function normalizeTextCandidates(array $candidates): array
    {
        return collect($candidates)
            ->filter(static fn (mixed $candidate): bool => is_string($candidate) && trim($candidate) !== '')
            ->map(static fn (string $candidate): string => strtolower(trim($candidate)))
            ->unique()
            ->values()
            ->all();
    }

    private function writeLeaveRequestHistory(
        LeaveRequest $leaveRequest,
        string $eventType,
        string $title,
        ?string $fromStatus,
        ?string $toStatus,
        ?string $notes = null,
        ?string $actorUserId = null,
        ?array $metadata = null
    ): void {
        $normalizedFromStatus = is_string($fromStatus) && in_array($fromStatus, ['pending', 'approved', 'refused', 'rejected'], true)
            ? $fromStatus
            : null;
        $normalizedToStatus = is_string($toStatus) && in_array($toStatus, ['pending', 'approved', 'refused', 'rejected'], true)
            ? $toStatus
            : null;

        LeaveRequestHistory::query()->create([
            'leave_request_id' => $leaveRequest->id,
            'actor_user_id' => is_string($actorUserId) && trim($actorUserId) !== '' ? $actorUserId : null,
            'event_type' => trim($eventType),
            'title' => trim($title),
            'from_status' => $normalizedFromStatus,
            'to_status' => $normalizedToStatus,
            'notes' => is_string($notes) && trim($notes) !== '' ? trim($notes) : null,
            'metadata' => $metadata,
            'happened_at' => now('Asia/Jakarta'),
        ]);
    }

    private function publicDisk(): FilesystemAdapter
    {
        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('public');

        return $disk;
    }

    private function syncAnnualLeaveBalance(string $employeeId, int $year, ?int $month = null): void
    {
        if (trim($employeeId) === '' || $year <= 0) {
            return;
        }

        $annualLeaveTypeId = LeaveType::query()
            ->whereRaw('LOWER(name) = ?', ['cuti tahunan'])
            ->value('id');

        if (! is_string($annualLeaveTypeId) || trim($annualLeaveTypeId) === '') {
            return;
        }

        $existingLeaveBalance = LeaveBalance::withTrashed()
            ->where('employee_id', $employeeId)
            ->where('leave_type_id', $annualLeaveTypeId)
            ->where('period_year', $year)
            ->first();

        $earnedQuota = (float) ($existingLeaveBalance?->earned_quota ?? 0);
        $usedQuota = (float) LeaveRequest::query()
            ->where('employee_id', $employeeId)
            ->where('leave_type_id', $annualLeaveTypeId)
            ->whereYear('start_date', $year)
            ->whereRaw('LOWER(status) = ?', ['approved'])
            ->sum('total_days');
        $remainingQuota = max($earnedQuota - $usedQuota, 0);

        LeaveBalance::withTrashed()->updateOrCreate(
            [
                'employee_id' => $employeeId,
                'leave_type_id' => $annualLeaveTypeId,
                'period_year' => $year,
            ],
            [
                'earned_quota' => $earnedQuota,
                'used_quota' => $usedQuota,
                'remaining_quota' => $remainingQuota,
                'deleted_at' => null,
            ]
        );
    }

    private function syncMonthlySpecialLeaveLimitFlag(string $employeeId, int $year, int $month): void
    {
        if (trim($employeeId) === '' || $year <= 0 || $month < 1 || $month > 12) {
            return;
        }

        $specialLeaveTypeId = $this->resolveSpecialLeaveTypeId();
        if ($specialLeaveTypeId === null) {
            return;
        }

        $existingLeaveBalance = LeaveBalance::withTrashed()
            ->where('employee_id', $employeeId)
            ->where('leave_type_id', $specialLeaveTypeId)
            ->where('period_year', $year)
            ->first();

        $earnedQuota = (float) ($existingLeaveBalance?->earned_quota ?? 0);
        $usedQuota = (float) ($existingLeaveBalance?->used_quota ?? 0);
        $remainingQuota = (float) ($existingLeaveBalance?->remaining_quota ?? 0);

        LeaveBalance::withTrashed()->updateOrCreate(
            [
                'employee_id' => $employeeId,
                'leave_type_id' => $specialLeaveTypeId,
                'period_year' => $year,
            ],
            [
                'earned_quota' => $earnedQuota,
                'used_quota' => $usedQuota,
                'remaining_quota' => $remainingQuota,
                'deleted_at' => null,
            ]
        );
    }

    private function resolveSpecialLeaveTypeId(): ?string
    {
        $specialLeaveTypeId = LeaveType::query()
            ->whereRaw('LOWER(name) = ?', ['cuti khusus'])
            ->value('id');

        if (! is_string($specialLeaveTypeId) || trim($specialLeaveTypeId) === '') {
            return null;
        }

        return trim($specialLeaveTypeId);
    }

    private function isSpecialLeaveTypeId(string $leaveTypeId): bool
    {
        if ($leaveTypeId === '') {
            return false;
        }

        $specialLeaveTypeId = $this->resolveSpecialLeaveTypeId();
        if ($specialLeaveTypeId === null) {
            return false;
        }

        return $leaveTypeId === $specialLeaveTypeId;
    }
}
