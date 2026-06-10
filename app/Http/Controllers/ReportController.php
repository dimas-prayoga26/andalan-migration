<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceException;
use App\Models\AttendanceHoliday;
use App\Models\AttendanceLog;
use App\Models\EmployeeProfile;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Services\Attendance\AttendanceCardsViewDataService;
use App\Services\Attendance\AttendanceMutationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use ZipArchive;

class ReportController extends Controller
{
    public function __construct(
        private AttendanceCardsViewDataService $attendanceCardsViewDataService,
        private AttendanceMutationService $attendanceMutationService
    ) {}

    public function index(Request $request): View
    {
        $authenticatedUser = Auth::user();
        if ($authenticatedUser instanceof User) {
            $authenticatedUser->loadMissing('employee.deployment');
        }
        $attendanceCardsData = $this->attendanceCardsViewDataService->build(
            $authenticatedUser instanceof User ? $authenticatedUser : null,
            Auth::id(),
            $request
        );
        $employeeId = $attendanceCardsData['employeeId'];
        $isSuperUser = $this->isSuperUser($authenticatedUser);
        $isStaffUser = $this->isStaffUser($authenticatedUser);
        $nowJakarta = now('Asia/Jakarta');
        $showCompanyFilter = $isSuperUser;
        $showStaffPeriodFilter = $isStaffUser;
        $companies = collect();
        $staffMonthOptions = collect(range(1, 12));
        $staffYearOptions = collect();
        $defaultStaffMonth = (int) $request->integer('month', (int) $nowJakarta->month);
        $defaultStaffYear = (int) $request->integer('year', (int) $nowJakarta->year);

        if ($defaultStaffMonth < 1 || $defaultStaffMonth > 12) {
            $defaultStaffMonth = (int) $nowJakarta->month;
        }

        if ($defaultStaffYear < 2000 || $defaultStaffYear > 2100) {
            $defaultStaffYear = (int) $nowJakarta->year;
        }

        if ($isStaffUser) {
            $employmentStartMonth = $this->resolveStaffEmploymentStartMonth(
                is_string($employeeId) ? $employeeId : null,
                $nowJakarta
            );
            $staffYearOptions = $this->buildStaffYearOptions($employmentStartMonth, $nowJakarta);

            if (! $staffYearOptions->contains($defaultStaffYear)) {
                $defaultStaffYear = $staffYearOptions->contains((int) $nowJakarta->year)
                    ? (int) $nowJakarta->year
                    : (int) $staffYearOptions->first();
            }

            $staffMonthOptions = $this->buildStaffMonthOptionsByYear($employmentStartMonth, $nowJakarta, $defaultStaffYear);

            if (! $staffMonthOptions->contains($defaultStaffMonth)) {
                $defaultStaffMonth = (int) $staffMonthOptions->last();
            }
        }

        if ($showCompanyFilter) {
            $companies = User::query()
                ->with([
                    'employee.deployment.company:id,name',
                ])
                ->get()
                ->pluck('employee.deployment.company')
                ->filter()
                ->unique('id')
                ->sortBy('name')
                ->values();
        }

        return view('attendance.reports.index', array_merge(
            $attendanceCardsData,
            compact(
                'companies',
                'showCompanyFilter',
                'showStaffPeriodFilter',
                'staffMonthOptions',
                'staffYearOptions',
                'defaultStaffMonth',
                'defaultStaffYear',
            )
        ));
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $storeResult = $this->attendanceMutationService->store(
                $request,
                Auth::user(),
                Auth::id(),
            );

            return response()->json($storeResult['payload'], $storeResult['status']);
        } catch (\Throwable $throwable) {
            report($throwable);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memproses absen masuk.',
            ], 500);
        }
    }

    public function update(Request $request, Attendance $attendance): JsonResponse
    {
        try {
            $updateResult = $this->attendanceMutationService->update(
                $request,
                $attendance,
                Auth::user(),
                Auth::id(),
            );

            return response()->json($updateResult['payload'], $updateResult['status']);
        } catch (\Throwable $throwable) {
            report($throwable);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memproses absen pulang.',
            ], 500);
        }
    }

    public function datatable(Request $request): JsonResponse
    {
        $authenticatedUser = Auth::user();
        if ($authenticatedUser instanceof User) {
            $authenticatedUser->loadMissing('employee.deployment');
        }

        $isSuperUser = $this->isSuperUser($authenticatedUser);
        $isBoardOfDirectur = $this->isBoardOfDirectur($authenticatedUser);
        $isStaffUser = $this->isStaffUser($authenticatedUser);
        $userCompanyId = $authenticatedUser?->employee?->deployment?->current_company_id;
        $nowJakarta = now('Asia/Jakarta');
        $todayDate = $nowJakarta->toDateString();
        $selectedMonth = (int) $request->integer('month', (int) $nowJakarta->month);
        $selectedYear = (int) $request->integer('year', (int) $nowJakarta->year);
        $selectedCompanyId = trim((string) $request->input('company_id', ''));

        if ($selectedMonth < 1 || $selectedMonth > 12) {
            $selectedMonth = (int) $nowJakarta->month;
        }

        if ($selectedYear < 2000 || $selectedYear > 2100) {
            $selectedYear = (int) $nowJakarta->year;
        }

        if ($isStaffUser) {
            $staffUser = User::query()
                ->with(['employee.deployment.company:id,name'])
                ->find(Auth::id());

            if (! $staffUser) {
                return response()->json(['data' => []]);
            }

            $staffEmployeeId = $staffUser->employee?->id;
            if (! is_string($staffEmployeeId) || trim($staffEmployeeId) === '') {
                return response()->json(['data' => []]);
            }

            $employmentStartMonth = $this->resolveStaffEmploymentStartMonth($staffEmployeeId, $nowJakarta);
            $currentMonthStart = $nowJakarta->copy()->startOfMonth();
            $selectedPeriodStart = Carbon::create($selectedYear, $selectedMonth, 1, 0, 0, 0, 'Asia/Jakarta')->startOfMonth();

            if ($selectedPeriodStart->lt($employmentStartMonth)) {
                $selectedYear = (int) $employmentStartMonth->year;
                $selectedMonth = (int) $employmentStartMonth->month;
                $selectedPeriodStart = $employmentStartMonth->copy();
            }

            if ($selectedPeriodStart->gt($currentMonthStart)) {
                $selectedYear = (int) $currentMonthStart->year;
                $selectedMonth = (int) $currentMonthStart->month;
            }

            $staffAttendances = Attendance::query()
                ->where('employee_id', $staffEmployeeId)
                ->whereMonth('date', $selectedMonth)
                ->whereYear('date', $selectedYear)
                ->get(['id', 'date', 'leave_request_id', 'status', 'clock_in', 'clock_out', 'late_minutes', 'work_hours', 'created_at']);

            $staffProfileName = EmployeeProfile::query()
                ->where('employee_id', $staffEmployeeId)
                ->value('name');
            $staffDisplayName = is_string($staffProfileName) && trim($staffProfileName) !== ''
                ? trim($staffProfileName)
                : ((is_string($staffUser->username) && trim($staffUser->username) !== '')
                    ? trim($staffUser->username)
                    : ((is_string($staffUser->email) && trim($staffUser->email) !== '') ? trim($staffUser->email) : '-'));

            $attendanceLogsByAttendanceId = AttendanceLog::query()
                ->whereIn('attendance_id', $staffAttendances->pluck('id'))
                ->where('type', true)
                ->orderByDesc('created_at')
                ->get([
                    'attendance_id',
                    'location',
                    'latitude',
                    'longitude',
                    'distance',
                    'radius_result',
                    'address_village',
                    'address_district',
                    'address_regency',
                    'address_city',
                    'address_province',
                    'address_postal_code',
                ])
                ->groupBy('attendance_id')
                ->map(fn ($attendanceLogs) => $attendanceLogs->first());
            $attendanceExceptionsByAttendanceId = AttendanceException::query()
                ->whereIn('attendance_id', $staffAttendances->pluck('id'))
                ->orderByDesc('created_at')
                ->get(['attendance_id', 'exception_date', 'type', 'note', 'from_time', 'to_time'])
                ->groupBy('attendance_id')
                ->map(fn ($attendanceExceptions) => $attendanceExceptions->first());
            $leaveRequestsById = LeaveRequest::query()
                ->with(['leaveType:id,name,code'])
                ->whereIn('id', $staffAttendances->pluck('leave_request_id')->filter()->values())
                ->get(['id', 'leave_type_id', 'attachment_path'])
                ->keyBy('id');
            $attendanceByDate = $staffAttendances
                ->filter(fn (Attendance $attendanceItem): bool => $attendanceItem->date !== null)
                ->sortBy('date')
                ->keyBy(fn (Attendance $attendanceItem): string => $attendanceItem->date->format('Y-m-d'));
            $holidayMapByDate = $this->buildHolidayMapByMonth($selectedYear, $selectedMonth);
            $periodStart = Carbon::create($selectedYear, $selectedMonth, 1, 0, 0, 0, 'Asia/Jakarta')->startOfDay();
            $periodEnd = $periodStart->copy()->endOfMonth()->startOfDay();
            $todayJakarta = now('Asia/Jakarta')->startOfDay();
            if ($selectedYear === (int) $todayJakarta->year && $selectedMonth === (int) $todayJakarta->month) {
                $periodEnd = $todayJakarta->copy();
            }
            $tableRows = collect();

            for ($cursorDate = $periodStart->copy(); $cursorDate->lte($periodEnd); $cursorDate->addDay()) {
                $isoDate = $cursorDate->toDateString();
                $attendanceItem = $attendanceByDate->get($isoDate);

                if ($attendanceItem instanceof Attendance) {
                    $attendanceLog = $attendanceLogsByAttendanceId->get($attendanceItem->id);
                    $attendanceException = $attendanceExceptionsByAttendanceId->get($attendanceItem->id);
                    $leaveRequest = is_string($attendanceItem->leave_request_id)
                        ? $leaveRequestsById->get($attendanceItem->leave_request_id)
                        : null;
                    $leaveTypeLabel = $leaveRequest instanceof LeaveRequest
                        ? $this->formatLeaveTypeLabel($leaveRequest)
                        : null;
                    $checkInValue = $attendanceItem->clock_in?->format('H:i') ?? $leaveTypeLabel;
                    $checkOutValue = $attendanceItem->clock_out?->format('H:i');
                    $noteLabel = $this->resolveAttendanceNoteLabel($attendanceItem, $attendanceException, $leaveRequest);
                    $attachmentUrl = $this->resolveLeaveRequestAttachmentUrl($leaveRequest);

                    $tableRows->push([
                        'attendance_id' => $attendanceItem->id,
                        'attendance_date' => $attendanceItem->date?->translatedFormat('d M Y'),
                        'attendance_date_iso' => $isoDate,
                        'staff_name' => $staffDisplayName,
                        'company_name' => $staffUser->employee?->deployment?->company?->name,
                        'check_in' => $checkInValue,
                        'check_out' => $checkOutValue,
                        'work_hours' => $this->formatWorkHoursLabel($checkInValue, $checkOutValue, $attendanceItem->work_hours),
                        'note' => $noteLabel,
                        'attachment' => $attachmentUrl,
                        'status' => $attendanceItem->status,
                        'row_type' => 'attendance',
                        'is_virtual' => false,
                        'check_in_latitude' => isset($attendanceLog?->latitude) ? (float) $attendanceLog->latitude : null,
                        'check_in_longitude' => isset($attendanceLog?->longitude) ? (float) $attendanceLog->longitude : null,
                        'distance_meters' => isset($attendanceLog?->distance) ? (float) $attendanceLog->distance : null,
                        'radius_result' => isset($attendanceLog?->radius_result) ? (string) $attendanceLog->radius_result : null,
                        'formatted_address' => isset($attendanceLog?->location) ? (string) $attendanceLog->location : null,
                        'address_village' => isset($attendanceLog?->address_village) ? (string) $attendanceLog->address_village : null,
                        'address_district' => isset($attendanceLog?->address_district) ? (string) $attendanceLog->address_district : null,
                        'address_regency' => isset($attendanceLog?->address_regency) ? (string) $attendanceLog->address_regency : null,
                        'address_city' => isset($attendanceLog?->address_city) ? (string) $attendanceLog->address_city : null,
                        'address_province' => isset($attendanceLog?->address_province) ? (string) $attendanceLog->address_province : null,
                        'address_postal_code' => isset($attendanceLog?->address_postal_code) ? (string) $attendanceLog->address_postal_code : null,
                    ]);

                    continue;
                }

                $holidayData = $holidayMapByDate[$isoDate] ?? null;
                if (is_array($holidayData)) {
                    $isNationalHoliday = (bool) ($holidayData['is_national_holiday'] ?? false);
                    $tableRows->push([
                        'attendance_id' => null,
                        'attendance_date' => $cursorDate->translatedFormat('d M Y'),
                        'attendance_date_iso' => $isoDate,
                        'staff_name' => $staffDisplayName,
                        'company_name' => $staffUser->employee?->deployment?->company?->name,
                        'check_in' => (string) ($holidayData['name'] ?? '-'),
                        'check_out' => '-',
                        'work_hours' => '0 hours',
                        'note' => $isNationalHoliday ? 'Libur Nasional' : 'Cuti Bersama',
                        'attachment' => null,
                        'status' => null,
                        'row_type' => $isNationalHoliday ? 'national_holiday' : 'joint_leave',
                        'is_virtual' => true,
                        'check_in_latitude' => null,
                        'check_in_longitude' => null,
                        'distance_meters' => null,
                        'radius_result' => null,
                        'formatted_address' => null,
                        'address_village' => null,
                        'address_district' => null,
                        'address_regency' => null,
                        'address_city' => null,
                        'address_province' => null,
                        'address_postal_code' => null,
                    ]);

                    continue;
                }

                if ($cursorDate->isWeekend()) {
                    $tableRows->push([
                        'attendance_id' => null,
                        'attendance_date' => $cursorDate->translatedFormat('d M Y'),
                        'attendance_date_iso' => $isoDate,
                        'staff_name' => $staffDisplayName,
                        'company_name' => $staffUser->employee?->deployment?->company?->name,
                        'check_in' => 'Weekend / Day Off',
                        'check_out' => '-',
                        'work_hours' => '0 hours',
                        'note' => 'Weekend / Day Off',
                        'attachment' => null,
                        'status' => null,
                        'row_type' => 'weekend',
                        'is_virtual' => true,
                        'check_in_latitude' => null,
                        'check_in_longitude' => null,
                        'distance_meters' => null,
                        'radius_result' => null,
                        'formatted_address' => null,
                        'address_village' => null,
                        'address_district' => null,
                        'address_regency' => null,
                        'address_city' => null,
                        'address_province' => null,
                        'address_postal_code' => null,
                    ]);
                }
            }

            return response()->json([
                'data' => $tableRows,
            ]);
        }

        $tableUsersQuery = User::query()
            ->with([
                'employee.deployment.company:id,name',
                'employee:id,user_id',
            ]);

        if ($isBoardOfDirectur) {
            if ($userCompanyId) {
                $tableUsersQuery->whereHas('employee.deployment', function ($query) use ($userCompanyId): void {
                    $query->where('current_company_id', $userCompanyId);
                });
            } else {
                $tableUsersQuery->whereRaw('1 = 0');
            }
        } elseif ($isSuperUser && $selectedCompanyId !== '') {
            $tableUsersQuery->whereHas('employee.deployment', function ($query) use ($selectedCompanyId): void {
                $query->where('current_company_id', $selectedCompanyId);
            });
        }

        $tableUsers = $tableUsersQuery->get();
        $employeeIds = $tableUsers
            ->map(fn (User $user): mixed => $user->employee?->id)
            ->filter()
            ->values();
        $employeeProfileNamesByEmployeeId = EmployeeProfile::query()
            ->whereIn('employee_id', $employeeIds)
            ->orderBy('created_at')
            ->pluck('name', 'employee_id');
        $attendancesTodayByEmployeeId = Attendance::query()
            ->whereIn('employee_id', $employeeIds)
            ->whereDate('date', $todayDate)
            ->orderByDesc('created_at')
            ->get(['id', 'employee_id', 'date', 'leave_request_id', 'clock_in', 'clock_out', 'late_minutes', 'work_hours', 'status'])
            ->groupBy('employee_id')
            ->map(fn ($attendanceItems) => $attendanceItems->first());
        $attendanceIds = $tableUsers
            ->map(fn (User $user): mixed => $attendancesTodayByEmployeeId->get($user->employee?->id)?->id)
            ->filter()
            ->values();
        $attendanceLogsByAttendanceId = AttendanceLog::query()
            ->whereIn('attendance_id', $attendanceIds)
            ->where('type', true)
            ->orderByDesc('created_at')
            ->get([
                'attendance_id',
                'location',
                'latitude',
                'longitude',
                'distance',
                'radius_result',
                'address_village',
                'address_district',
                'address_regency',
                'address_city',
                'address_province',
                'address_postal_code',
            ])
            ->groupBy('attendance_id')
            ->map(fn ($attendanceLogs) => $attendanceLogs->first());
        $attendanceExceptionsByAttendanceId = AttendanceException::query()
            ->whereIn('attendance_id', $attendanceIds)
            ->orderByDesc('created_at')
            ->get(['attendance_id', 'exception_date', 'type', 'note', 'from_time', 'to_time'])
            ->groupBy('attendance_id')
            ->map(fn ($attendanceExceptions) => $attendanceExceptions->first());
        $leaveRequestsById = LeaveRequest::query()
            ->with(['leaveType:id,name,code'])
            ->whereIn('id', $attendancesTodayByEmployeeId->pluck('leave_request_id')->filter()->values())
            ->get(['id', 'leave_type_id', 'attachment_path'])
            ->keyBy('id');

        $tableRows = $tableUsers->map(function (User $user) use ($attendanceLogsByAttendanceId, $attendanceExceptionsByAttendanceId, $attendancesTodayByEmployeeId, $todayDate, $employeeProfileNamesByEmployeeId, $leaveRequestsById): array {
            $attendanceToday = $attendancesTodayByEmployeeId->get($user->employee?->id);
            $attendanceLog = $attendanceToday ? $attendanceLogsByAttendanceId->get($attendanceToday->id) : null;
            $attendanceException = $attendanceToday ? $attendanceExceptionsByAttendanceId->get($attendanceToday->id) : null;
            $leaveRequest = $attendanceToday instanceof Attendance && is_string($attendanceToday->leave_request_id)
                ? $leaveRequestsById->get($attendanceToday->leave_request_id)
                : null;
            $leaveTypeLabel = $leaveRequest instanceof LeaveRequest
                ? $this->formatLeaveTypeLabel($leaveRequest)
                : null;
            $checkInValue = $attendanceToday?->clock_in?->format('H:i') ?? $leaveTypeLabel;
            $checkOutValue = $attendanceToday?->clock_out?->format('H:i');
            $workHours = $this->formatWorkHoursLabel($checkInValue, $checkOutValue, $attendanceToday?->work_hours);
            $employeeId = $user->employee?->id;
            $profileName = is_string($employeeId) ? $employeeProfileNamesByEmployeeId->get($employeeId) : null;
            $staffDisplayName = is_string($profileName) && trim($profileName) !== ''
                ? trim($profileName)
                : ((is_string($user->username) && trim($user->username) !== '')
                    ? trim($user->username)
                    : ((is_string($user->email) && trim($user->email) !== '') ? trim($user->email) : '-'));

            return [
                'attendance_id' => $attendanceToday?->id,
                'attendance_date' => $attendanceToday?->date?->translatedFormat('d M Y') ?? Carbon::parse($todayDate, 'Asia/Jakarta')->translatedFormat('d M Y'),
                'attendance_date_iso' => $attendanceToday?->date?->format('Y-m-d') ?? $todayDate,
                'staff_name' => $staffDisplayName,
                'company_name' => $user->employee?->deployment?->company?->name,
                'check_in' => $checkInValue,
                'check_out' => $checkOutValue,
                'work_hours' => $workHours,
                'note' => $attendanceToday instanceof Attendance
                    ? $this->resolveAttendanceNoteLabel($attendanceToday, $attendanceException, $leaveRequest)
                    : '-',
                'attachment' => $this->resolveLeaveRequestAttachmentUrl($leaveRequest),
                'status' => $attendanceToday?->status,
                'row_type' => 'attendance',
                'is_virtual' => false,
                'check_in_latitude' => isset($attendanceLog?->latitude) ? (float) $attendanceLog->latitude : null,
                'check_in_longitude' => isset($attendanceLog?->longitude) ? (float) $attendanceLog->longitude : null,
                'distance_meters' => isset($attendanceLog?->distance) ? (float) $attendanceLog->distance : null,
                'radius_result' => isset($attendanceLog?->radius_result) ? (string) $attendanceLog->radius_result : null,
                'formatted_address' => isset($attendanceLog?->location) ? (string) $attendanceLog->location : null,
                'address_village' => isset($attendanceLog?->address_village) ? (string) $attendanceLog->address_village : null,
                'address_district' => isset($attendanceLog?->address_district) ? (string) $attendanceLog->address_district : null,
                'address_regency' => isset($attendanceLog?->address_regency) ? (string) $attendanceLog->address_regency : null,
                'address_city' => isset($attendanceLog?->address_city) ? (string) $attendanceLog->address_city : null,
                'address_province' => isset($attendanceLog?->address_province) ? (string) $attendanceLog->address_province : null,
                'address_postal_code' => isset($attendanceLog?->address_postal_code) ? (string) $attendanceLog->address_postal_code : null,
            ];
        })->values();

        return response()->json([
            'data' => $tableRows,
        ]);
    }

    public function exportReport(Request $request): Response
    {
        $datatableResponse = $this->datatable($request);
        $payload = $datatableResponse->getData(true);
        $reportRows = collect($payload['data'] ?? []);
        $nowJakarta = now('Asia/Jakarta');
        $selectedMonth = (int) $request->integer('month', (int) $nowJakarta->month);
        $selectedYear = (int) $request->integer('year', (int) $nowJakarta->year);

        $titleLabel = $this->resolveReportTitleLabel($reportRows);
        $fileNameSlug = Str::slug($titleLabel);
        $fileNamePrefix = $fileNameSlug !== '' ? $fileNameSlug : 'attendance-report';
        $fileName = $fileNamePrefix.'-'.$selectedYear.'-'.str_pad((string) $selectedMonth, 2, '0', STR_PAD_LEFT).'.xlsx';
        $xlsxContent = $this->buildAttendanceReportXlsx($reportRows, $titleLabel);

        return response($xlsxContent, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
            'Cache-Control' => 'max-age=0, no-cache, must-revalidate, proxy-revalidate',
        ]);
    }

    private function resolveReportTitleLabel(Collection $reportRows): string
    {
        $companyNames = $reportRows
            ->pluck('company_name')
            ->filter(fn (mixed $companyName): bool => is_string($companyName) && trim($companyName) !== '')
            ->map(fn (string $companyName): string => trim($companyName))
            ->unique()
            ->values();
        $staffNames = $reportRows
            ->pluck('staff_name')
            ->filter(fn (mixed $staffName): bool => is_string($staffName) && trim($staffName) !== '')
            ->map(fn (string $staffName): string => trim($staffName))
            ->unique()
            ->values();

        $companyLabel = match ($companyNames->count()) {
            0 => 'Company',
            1 => (string) $companyNames->first(),
            default => 'Multiple Companies',
        };
        $staffLabel = match ($staffNames->count()) {
            0 => 'Staff',
            1 => (string) $staffNames->first(),
            default => 'All Staff',
        };

        return $companyLabel.' - '.$staffLabel;
    }

    private function buildAttendanceReportXlsx(Collection $reportRows, string $titleLabel): string
    {
        $temporaryPath = tempnam(storage_path('app'), 'attendance-report-');
        if (! is_string($temporaryPath)) {
            throw new RuntimeException('Gagal membuat file export sementara.');
        }

        $zipArchive = new ZipArchive;
        if ($zipArchive->open($temporaryPath, ZipArchive::OVERWRITE) !== true) {
            @unlink($temporaryPath);

            throw new RuntimeException('Gagal membuka file export sementara.');
        }

        $sheetParts = $this->buildAttendanceReportSheetXml($reportRows, $titleLabel);

        $zipArchive->addFromString('[Content_Types].xml', $this->xlsxContentTypesXml());
        $zipArchive->addFromString('_rels/.rels', $this->xlsxRootRelationshipsXml());
        $zipArchive->addFromString('xl/workbook.xml', $this->xlsxWorkbookXml());
        $zipArchive->addFromString('xl/_rels/workbook.xml.rels', $this->xlsxWorkbookRelationshipsXml());
        $zipArchive->addFromString('xl/styles.xml', $this->xlsxStylesXml());
        $zipArchive->addFromString('xl/worksheets/sheet1.xml', $sheetParts['sheet_xml']);

        if ($sheetParts['has_hyperlinks']) {
            $zipArchive->addFromString('xl/worksheets/_rels/sheet1.xml.rels', $sheetParts['relationships_xml']);
        }

        $zipArchive->close();

        $xlsxContent = file_get_contents($temporaryPath);
        @unlink($temporaryPath);

        if (! is_string($xlsxContent)) {
            throw new RuntimeException('Gagal membaca file export sementara.');
        }

        return $xlsxContent;
    }

    /**
     * @return array{sheet_xml:string, relationships_xml:string, has_hyperlinks:bool}
     */
    private function buildAttendanceReportSheetXml(Collection $reportRows, string $titleLabel): array
    {
        $sheetRows = [];
        $hyperlinkRelationships = [];
        $sheetRows[] = $this->xlsxRowXml(1, [
            $this->xlsxInlineStringCell('A1', $titleLabel, 1),
        ]);
        $sheetRows[] = $this->xlsxRowXml(2, [
            $this->xlsxInlineStringCell('A2', 'Date', 2),
            $this->xlsxInlineStringCell('B2', 'Clock In', 2),
            $this->xlsxInlineStringCell('C2', 'Clock Out', 2),
            $this->xlsxInlineStringCell('D2', 'Note', 2),
            $this->xlsxInlineStringCell('E2', 'Working Hours', 2),
            $this->xlsxInlineStringCell('F2', 'Attachment', 2),
        ]);

        foreach ($reportRows->values() as $index => $row) {
            $rowNumber = $index + 3;
            $attachmentUrl = $this->stringCellValue($row['attachment'] ?? null);
            $attachmentCellValue = $attachmentUrl !== '' ? 'View Attachment' : '-';
            $attachmentCellStyle = $attachmentUrl !== '' ? 3 : 0;

            $sheetRows[] = $this->xlsxRowXml($rowNumber, [
                $this->xlsxInlineStringCell('A'.$rowNumber, $this->stringCellValue($row['attendance_date'] ?? null)),
                $this->xlsxInlineStringCell('B'.$rowNumber, $this->stringCellValue($row['check_in'] ?? null)),
                $this->xlsxInlineStringCell('C'.$rowNumber, $this->stringCellValue($row['check_out'] ?? null)),
                $this->xlsxInlineStringCell('D'.$rowNumber, $this->stringCellValue($row['note'] ?? null)),
                $this->xlsxInlineStringCell('E'.$rowNumber, $this->stringCellValue($row['work_hours'] ?? null)),
                $this->xlsxInlineStringCell('F'.$rowNumber, $attachmentCellValue, $attachmentCellStyle),
            ]);

            if ($attachmentUrl !== '') {
                $relationshipId = 'rId'.(count($hyperlinkRelationships) + 1);
                $hyperlinkRelationships[] = [
                    'cell' => 'F'.$rowNumber,
                    'id' => $relationshipId,
                    'target' => $attachmentUrl,
                ];
            }
        }

        if ($reportRows->isEmpty()) {
            $sheetRows[] = $this->xlsxRowXml(3, [
                $this->xlsxInlineStringCell('A3', 'Tidak ada data.'),
            ]);
        }

        $hyperlinksXml = collect($hyperlinkRelationships)
            ->map(fn (array $relationship): string => '<hyperlink ref="'.$this->xmlAttribute($relationship['cell']).'" r:id="'.$this->xmlAttribute($relationship['id']).'"/>')
            ->implode('');
        $sheetXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            .'<cols><col min="1" max="1" width="18" customWidth="1"/><col min="2" max="3" width="16" customWidth="1"/><col min="4" max="4" width="30" customWidth="1"/><col min="5" max="5" width="18" customWidth="1"/><col min="6" max="6" width="24" customWidth="1"/></cols>'
            .'<sheetData>'.implode('', $sheetRows).'</sheetData>'
            .'<mergeCells count="1"><mergeCell ref="A1:F1"/></mergeCells>'
            .($hyperlinksXml !== '' ? '<hyperlinks>'.$hyperlinksXml.'</hyperlinks>' : '')
            .'</worksheet>';
        $relationshipsXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .collect($hyperlinkRelationships)
                ->map(fn (array $relationship): string => '<Relationship Id="'.$this->xmlAttribute($relationship['id']).'" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/hyperlink" Target="'.$this->xmlAttribute($relationship['target']).'" TargetMode="External"/>')
                ->implode('')
            .'</Relationships>';

        return [
            'sheet_xml' => $sheetXml,
            'relationships_xml' => $relationshipsXml,
            'has_hyperlinks' => $hyperlinkRelationships !== [],
        ];
    }

    /**
     * @param  list<string>  $cells
     */
    private function xlsxRowXml(int $rowNumber, array $cells): string
    {
        return '<row r="'.$rowNumber.'">'.implode('', $cells).'</row>';
    }

    private function xlsxInlineStringCell(string $coordinate, string $value, int $styleId = 0): string
    {
        $styleAttribute = $styleId > 0 ? ' s="'.$styleId.'"' : '';

        return '<c r="'.$this->xmlAttribute($coordinate).'" t="inlineStr"'.$styleAttribute.'><is><t>'.$this->xmlText($value).'</t></is></c>';
    }

    private function stringCellValue(mixed $value): string
    {
        if (is_string($value) && trim($value) !== '') {
            return trim($value);
        }

        if (is_numeric($value)) {
            return (string) $value;
        }

        return '-';
    }

    private function xlsxContentTypesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            .'<Default Extension="xml" ContentType="application/xml"/>'
            .'<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            .'<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            .'<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            .'</Types>';
    }

    private function xlsxRootRelationshipsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            .'</Relationships>';
    }

    private function xlsxWorkbookXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            .'<sheets><sheet name="Attendance Report" sheetId="1" r:id="rId1"/></sheets>'
            .'</workbook>';
    }

    private function xlsxWorkbookRelationshipsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            .'<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            .'</Relationships>';
    }

    private function xlsxStylesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .'<fonts count="4"><font><sz val="11"/><name val="Calibri"/></font><font><b/><color rgb="FFFFFFFF"/><sz val="14"/><name val="Calibri"/></font><font><b/><color rgb="FF1F2937"/><sz val="11"/><name val="Calibri"/></font><font><u/><color rgb="FF2563EB"/><sz val="11"/><name val="Calibri"/></font></fonts>'
            .'<fills count="4"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill><fill><patternFill patternType="solid"><fgColor rgb="FF1F4E78"/><bgColor indexed="64"/></patternFill></fill><fill><patternFill patternType="solid"><fgColor rgb="FFDDEBF7"/><bgColor indexed="64"/></patternFill></fill></fills>'
            .'<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>'
            .'<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            .'<cellXfs count="4"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/><xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1" applyAlignment="1"><alignment horizontal="center"/></xf><xf numFmtId="0" fontId="2" fillId="3" borderId="0" xfId="0" applyFont="1" applyFill="1"/><xf numFmtId="0" fontId="3" fillId="0" borderId="0" xfId="0" applyFont="1"/></cellXfs>'
            .'</styleSheet>';
    }

    private function xmlText(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_COMPAT, 'UTF-8');
    }

    private function xmlAttribute(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    private function isSuperUser(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $user->getRoleNames()
            ->map(fn (string $roleName): string => strtolower(trim($roleName)))
            ->contains('superuser');
    }

    private function isBoardOfDirectur(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        $normalizedRoleNames = $user->getRoleNames()
            ->map(fn (string $roleName): string => strtolower(trim($roleName)));

        return $normalizedRoleNames->contains('board of directur')
            || $normalizedRoleNames->contains('board of directors')
            || $normalizedRoleNames->contains('supervisor');
    }

    private function isStaffUser(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $user->getRoleNames()
            ->map(fn (string $roleName): string => strtolower(trim($roleName)))
            ->contains('staff');
    }

    private function resolveAttendanceNoteLabel(
        Attendance $attendance,
        ?AttendanceException $attendanceException,
        ?LeaveRequest $leaveRequest
    ): string {
        if ($leaveRequest instanceof LeaveRequest) {
            return $this->formatLeaveTypeLabel($leaveRequest);
        }

        if ($attendanceException instanceof AttendanceException) {
            $attendanceExceptionLabel = $this->formatAttendanceExceptionNoteLabel($attendanceException);
            if ($attendanceExceptionLabel !== null) {
                return $attendanceExceptionLabel;
            }

            if (is_string($attendanceException->note) && trim($attendanceException->note) !== '') {
                return trim($attendanceException->note);
            }
        }

        $lateMinutes = (int) ($attendance->late_minutes ?? 0);
        if ($lateMinutes > 0) {
            return 'Late '.$lateMinutes.' Minutes';
        }

        if ($attendance->clock_in !== null) {
            return 'On Time';
        }

        return '-';
    }

    private function formatAttendanceExceptionNoteLabel(AttendanceException $attendanceException): ?string
    {
        $type = is_string($attendanceException->type) ? trim($attendanceException->type) : '';
        $label = match ($type) {
            'late_arrival' => 'Izin Masuk Terlambat',
            'early_departure' => 'Izin Pulang Lebih Awal',
            default => null,
        };

        if ($label === null) {
            return null;
        }

        $durationLabel = $this->formatAttendanceExceptionDurationLabel($attendanceException);

        return trim($label.' '.$durationLabel);
    }

    private function formatAttendanceExceptionDurationLabel(AttendanceException $attendanceException): string
    {
        $fromTime = $this->normalizeAttendanceExceptionTime($attendanceException->getRawOriginal('from_time'))
            ?? $this->normalizeAttendanceExceptionTime($attendanceException->from_time);
        $toTime = $this->normalizeAttendanceExceptionTime($attendanceException->getRawOriginal('to_time'))
            ?? $this->normalizeAttendanceExceptionTime($attendanceException->to_time);

        if ($fromTime === null || $toTime === null) {
            return '';
        }

        $exceptionDate = $attendanceException->exception_date?->format('Y-m-d') ?? now('Asia/Jakarta')->toDateString();
        try {
            $fromDateTime = Carbon::createFromFormat('Y-m-d H:i:s', $exceptionDate.' '.$fromTime, 'Asia/Jakarta');
            $toDateTime = Carbon::createFromFormat('Y-m-d H:i:s', $exceptionDate.' '.$toTime, 'Asia/Jakarta');
        } catch (\Throwable) {
            return '';
        }

        $minutes = abs((int) $fromDateTime->diffInMinutes($toDateTime, false));
        if ($minutes <= 0) {
            return '';
        }

        $hoursPart = intdiv($minutes, 60);
        $minutesPart = $minutes % 60;
        $segments = [];

        if ($hoursPart > 0) {
            $segments[] = $hoursPart.' Jam';
        }

        if ($minutesPart > 0) {
            $segments[] = $minutesPart.' Menit';
        }

        return implode(' ', $segments);
    }

    private function normalizeAttendanceExceptionTime(mixed $time): ?string
    {
        if ($time instanceof \DateTimeInterface) {
            return $time->format('H:i:s');
        }

        if (! is_string($time) || trim($time) === '') {
            return null;
        }

        $normalizedTime = trim($time);
        if (preg_match('/^\d{2}:\d{2}$/', $normalizedTime) === 1) {
            return $normalizedTime.':00';
        }

        if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $normalizedTime) === 1) {
            return $normalizedTime;
        }

        return null;
    }

    private function formatLeaveTypeLabel(LeaveRequest $leaveRequest): string
    {
        $leaveType = $leaveRequest->leaveType;
        $leaveTypeName = is_string($leaveType?->name) ? trim($leaveType->name) : '';
        $leaveTypeCode = is_string($leaveType?->code) ? strtolower(trim($leaveType->code)) : '';
        $normalizedLeaveTypeName = strtolower($leaveTypeName);

        if (in_array($leaveTypeCode, ['annual', 'annual_leave'], true) || in_array($normalizedLeaveTypeName, ['annual leave', 'cuti tahunan'], true)) {
            return 'Cuti Tahunan';
        }

        if (in_array($leaveTypeCode, ['sick', 'sick_leave'], true) || in_array($normalizedLeaveTypeName, ['sick leave', 'sakit'], true)) {
            return 'Sick Leave';
        }

        if (in_array($leaveTypeCode, ['special', 'special_leave'], true) || in_array($normalizedLeaveTypeName, ['special leave', 'cuti khusus'], true)) {
            return 'Special Leave';
        }

        if (in_array($leaveTypeCode, ['unpaid', 'unpaid_leave'], true) || in_array($normalizedLeaveTypeName, ['unpaid leave', 'cuti tidak dibayar'], true)) {
            return 'Unpaid Leave';
        }

        return $leaveTypeName !== '' ? $leaveTypeName : 'Leave';
    }

    private function resolveLeaveRequestAttachmentUrl(?LeaveRequest $leaveRequest): ?string
    {
        if (! $leaveRequest instanceof LeaveRequest) {
            return null;
        }

        $attachmentPath = is_string($leaveRequest->attachment_path) ? trim($leaveRequest->attachment_path) : '';
        if ($attachmentPath === '') {
            return null;
        }

        return Storage::disk('public')->url($attachmentPath);
    }

    /**
     * @return array<string, array{name:string,is_national_holiday:bool}>
     */
    private function buildHolidayMapByMonth(int $year, int $month): array
    {
        $holidayMapByDate = [];

        $holidayItems = AttendanceHoliday::query()
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->orderBy('date')
            ->get(['date', 'name', 'type']);

        foreach ($holidayItems as $holidayItem) {
            $holidayDate = $holidayItem->date?->toDateString();
            $holidayName = is_string($holidayItem->name) ? trim($holidayItem->name) : '';
            if (! is_string($holidayDate) || $holidayDate === '' || $holidayName === '') {
                continue;
            }

            $holidayMapByDate[$holidayDate] = [
                'name' => $holidayName,
                'is_national_holiday' => (int) $holidayItem->type === 1,
            ];
        }

        return $holidayMapByDate;
    }

    private function formatWorkHoursLabel(?string $clockInValue, ?string $clockOutValue, mixed $storedWorkHours): string
    {
        if (is_string($clockInValue) && is_string($clockOutValue)) {
            $clockInTimestamp = strtotime($clockInValue);
            $clockOutTimestamp = strtotime($clockOutValue);

            if ($clockInTimestamp !== false && $clockOutTimestamp !== false) {
                $minutes = max(0, (int) round(abs(($clockOutTimestamp - $clockInTimestamp) / 60)));

                return $this->formatMinutesToHoursLabel($minutes);
            }
        }

        if (is_numeric($storedWorkHours)) {
            $minutes = max(0, (int) round(((float) $storedWorkHours) * 60));

            return $this->formatMinutesToHoursLabel($minutes);
        }

        return '0 hours';
    }

    private function formatMinutesToHoursLabel(int $minutes): string
    {
        if ($minutes <= 0) {
            return '0 hours';
        }

        $hoursPart = intdiv($minutes, 60);
        $minutesPart = $minutes % 60;
        if ($minutesPart === 0) {
            return $hoursPart.' hours';
        }

        return $hoursPart.' hours, '.$minutesPart.' minutes';
    }

    private function resolveStaffEmploymentStartMonth(?string $employeeId, Carbon $nowJakarta): Carbon
    {
        if (! is_string($employeeId) || trim($employeeId) === '') {
            return $nowJakarta->copy()->startOfYear();
        }

        $deploymentJoinDateRaw = DB::table('employee_deployments')
            ->where('employee_id', $employeeId)
            ->whereNull('deleted_at')
            ->whereNotNull('join_date')
            ->orderBy('join_date')
            ->value('join_date');

        if (is_string($deploymentJoinDateRaw) && trim($deploymentJoinDateRaw) !== '') {
            return Carbon::parse($deploymentJoinDateRaw, 'Asia/Jakarta')->startOfMonth();
        }

        return $nowJakarta->copy()->startOfYear();
    }

    /**
     * @return Collection<int, int>
     */
    private function buildStaffYearOptions(Carbon $employmentStartMonth, Carbon $nowJakarta): Collection
    {
        $startYear = (int) $employmentStartMonth->year;
        $endYear = (int) $nowJakarta->year;

        if ($startYear > $endYear) {
            $startYear = $endYear;
        }

        return collect(range($startYear, $endYear))->values();
    }

    /**
     * @return Collection<int, int>
     */
    private function buildStaffMonthOptionsByYear(Carbon $employmentStartMonth, Carbon $nowJakarta, int $selectedYear): Collection
    {
        $startMonth = 1;
        $endMonth = 12;
        $currentYear = (int) $nowJakarta->year;
        $employmentStartYear = (int) $employmentStartMonth->year;

        if ($selectedYear === $employmentStartYear) {
            $startMonth = (int) $employmentStartMonth->month;
        }

        if ($selectedYear === $currentYear) {
            $endMonth = (int) $nowJakarta->month;
        }

        if ($startMonth > $endMonth) {
            $startMonth = $endMonth;
        }

        return collect(range($startMonth, $endMonth))->values();
    }
}
