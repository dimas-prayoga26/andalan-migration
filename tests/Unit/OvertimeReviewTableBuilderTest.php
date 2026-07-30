<?php

namespace Tests\Unit;

use App\Models\AttendanceOvertime;
use App\Models\Employee;
use App\Models\EmployeeProfile;
use App\Models\OvertimeLifecycleLog;
use App\Models\User;
use App\Support\Attendance\OvertimeReviewTableBuilder;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionClass;
use Tests\TestCase;

class OvertimeReviewTableBuilderTest extends TestCase
{
    #[DataProvider('contextStatusRules')]
    public function test_it_uses_expected_pending_and_approved_lifecycle_rules(string $context, array $expectedRule): void
    {
        $reflection = new ReflectionClass(OvertimeReviewTableBuilder::class);
        $method = $reflection->getMethod('statusRuleFor');
        $method->setAccessible(true);

        $this->assertSame($expectedRule, $method->invoke(new OvertimeReviewTableBuilder, $context));
    }

    public function test_overtime_review_views_use_dynamic_rows_and_filter_inputs(): void
    {
        $builder = (string) file_get_contents(app_path('Support/Attendance/OvertimeReviewTableBuilder.php'));

        $this->assertStringContainsString("\$context === 'pic' && (! is_string(\$companyId) || trim(\$companyId) === '')", $builder);
        $this->assertStringContainsString('private function baseQuery(?string $companyId, int $month, int $year, bool $includeCancelled = false): Builder', $builder);
        $this->assertStringContainsString("if (is_string(\$companyId) && trim(\$companyId) !== '')", $builder);
        $this->assertStringContainsString('if (! $includeCancelled) {', $builder);

        foreach ([
            resource_path('views/admin_attendance/overtime/index.blade.php'),
            resource_path('views/pic_attendance/overtime/index.blade.php'),
        ] as $viewPath) {
            $view = (string) file_get_contents($viewPath);

            $this->assertStringNotContainsString('name="month"', $view);
            $this->assertStringNotContainsString('name="year"', $view);
            $this->assertStringContainsString('$monthOptions', $view);
            $this->assertStringContainsString('$yearOptions', $view);
            $this->assertStringContainsString('$pendingRows', $view);
            $this->assertStringContainsString('$approvedRows', $view);
            $this->assertStringNotContainsString('<td>Dimas</td>', $view);
            $this->assertStringNotContainsString('<td>Rexy</td>', $view);
        }

        $picView = (string) file_get_contents(resource_path('views/pic_attendance/overtime/index.blade.php'));

        $this->assertStringContainsString('name="card_month"', $picView);
        $this->assertStringContainsString('name="card_year"', $picView);
        $this->assertStringContainsString('name="pending_month"', $picView);
        $this->assertStringContainsString('name="pending_year"', $picView);
        $this->assertStringContainsString('name="approved_month"', $picView);
        $this->assertStringContainsString('name="approved_year"', $picView);
        $this->assertStringContainsString('$selectedCardMonth', $picView);
        $this->assertStringContainsString('$selectedPendingMonth', $picView);
        $this->assertStringContainsString('$selectedApprovedMonth', $picView);
        $this->assertStringContainsString('pic-overtime-card-filter', $picView);
        $this->assertStringContainsString('overtime-summary-metrics mb-3', $picView);
        $this->assertStringContainsString('@foreach (($overtimeMetricCards ?? []) as $summaryCard)', $picView);
        $this->assertStringContainsString('overtime-summary-card {{ $summaryCard[\'background_class\'] ?? \'bg-light-subtle\' }}', $picView);
        $this->assertStringContainsString('min-height: 82px;', $picView);
        $this->assertStringContainsString('grid-template-columns: repeat(9, minmax(0, 1fr));', $picView);
        $this->assertStringNotContainsString('$overtimeSummary[\'pending_label\']', $picView);
        $this->assertLessThan(
            strpos($picView, '@forelse (($overtimeCards ?? collect()) as $overtimeCard)'),
            strpos($picView, 'pic-overtime-card-filter'),
        );
        $this->assertStringContainsString('<th class="mw-80">Status</th>', $picView);
        $this->assertStringContainsString("{{ \$row['status_class'] ?? 'text-muted' }}", $picView);
        $this->assertStringContainsString("{{ \$row['status'] ?? '-' }}", $picView);
        $this->assertStringContainsString("{{ \$row['status_info'] ?? '-' }}", $picView);
        $this->assertStringContainsString('fa fa-info-circle ms-1', $picView);
        $this->assertStringContainsString('title="Status: {{ $row[\'status_info\'] ?? \'-\' }}"', $picView);
        $this->assertStringContainsString('$picOvertimeTablePageSize = 5;', $picView);
        $this->assertStringContainsString('max(1, (int) ceil($pendingTableRows->count() / $picOvertimeTablePageSize))', $picView);
        $this->assertStringContainsString('max(1, (int) ceil($approvedTableRows->count() / $picOvertimeTablePageSize))', $picView);
        $this->assertStringContainsString('data-pic-overtime-table data-pic-overtime-page-size="{{ $picOvertimeTablePageSize }}"', $picView);
        $this->assertStringContainsString('js-pic-overtime-table-row', $picView);
        $this->assertStringContainsString('table-responsive pic-overtime-review-table', $picView);
        $this->assertStringContainsString('.pic-overtime-review-table', $picView);
        $this->assertStringContainsString('height: 286px;', $picView);
        $this->assertStringContainsString('overflow-x: auto;', $picView);
        $this->assertStringContainsString('overflow-y: hidden;', $picView);
        $this->assertStringContainsString('-webkit-overflow-scrolling: touch;', $picView);
        $this->assertStringContainsString('scrollbar-width: thin;', $picView);
        $this->assertStringContainsString('scrollbar-color: #c6ccd6 #f1f3f7;', $picView);
        $this->assertStringContainsString('.pic-overtime-review-table::-webkit-scrollbar', $picView);
        $this->assertStringContainsString('height: 7px;', $picView);
        $this->assertStringContainsString('min-width: 900px;', $picView);
        $this->assertStringNotContainsString('table-layout: fixed;', $picView);
        $this->assertStringNotContainsString('text-overflow: ellipsis;', $picView);
        $this->assertStringNotContainsString('.overtime-review-table tbody tr', $picView);
        $this->assertStringNotContainsString('height: 100%;', $picView);
        $this->assertStringContainsString('overtime-pending-table', $picView);
        $this->assertStringContainsString('overtime-approved-table', $picView);
        $this->assertStringContainsString('td class="overtime-status-cell"', $picView);
        $this->assertStringNotContainsString('.pic-overtime-review-table.is-empty', $picView);
        $this->assertStringNotContainsString("'is-empty' => \$pendingTableRows->isEmpty()", $picView);
        $this->assertStringNotContainsString("'is-empty' => \$approvedTableRows->isEmpty()", $picView);
        $this->assertStringContainsString('pic-overtime-table-footer dataTables_wrapper no-footer', $picView);
        $this->assertStringContainsString('.pic-overtime-table-footer.dataTables_wrapper', $picView);
        $this->assertStringContainsString('padding: 8px 30px;', $picView);
        $this->assertStringContainsString('margin-bottom: 0;', $picView);
        $this->assertStringContainsString('js-pic-overtime-pagination', $picView);
        $this->assertStringContainsString('Showing {{ $pendingTableRows->isEmpty() ? 0 : 1 }}', $picView);
        $this->assertStringContainsString('Showing {{ $approvedTableRows->isEmpty() ? 0 : 1 }}', $picView);
        $this->assertStringContainsString('function showPicOvertimeTablePage', $picView);
        $this->assertStringContainsString('function initPicOvertimeTablePagination', $picView);

        $directorView = (string) file_get_contents(resource_path('views/director_attendance/overtime/index.blade.php'));

        $this->assertStringContainsString('$monthOptions', $directorView);
        $this->assertStringContainsString('$yearOptions', $directorView);
        $this->assertStringContainsString('$pendingRows', $directorView);
        $this->assertStringContainsString('$approvedRows', $directorView);
        $this->assertStringNotContainsString('name="month"', $directorView);
        $this->assertStringNotContainsString('name="year"', $directorView);
        $this->assertStringContainsString('name="card_month"', $directorView);
        $this->assertStringContainsString('name="card_year"', $directorView);
        $this->assertStringContainsString('name="pending_month"', $directorView);
        $this->assertStringContainsString('name="pending_year"', $directorView);
        $this->assertStringContainsString('name="approved_month"', $directorView);
        $this->assertStringContainsString('name="approved_year"', $directorView);
        $this->assertStringContainsString('$selectedCardMonth', $directorView);
        $this->assertStringContainsString('$selectedPendingMonth', $directorView);
        $this->assertStringContainsString('$selectedApprovedMonth', $directorView);
        $this->assertStringContainsString('director-overtime-card-filter', $directorView);
        $this->assertStringContainsString('overtime-summary-metrics mb-3', $directorView);
        $this->assertStringContainsString('@foreach (($overtimeMetricCards ?? []) as $summaryCard)', $directorView);
        $this->assertStringContainsString('overtime-summary-card {{ $summaryCard[\'background_class\'] ?? \'bg-light-subtle\' }}', $directorView);
        $this->assertStringContainsString('min-height: 82px;', $directorView);
        $this->assertStringContainsString('grid-template-columns: repeat(9, minmax(0, 1fr));', $directorView);
        $this->assertStringNotContainsString('$overtimeSummary[\'pending_label\']', $directorView);
        $this->assertLessThan(
            strpos($directorView, '@forelse (($overtimeCards ?? collect()) as $overtimeCard)'),
            strpos($directorView, 'director-overtime-card-filter'),
        );
        $this->assertStringContainsString('<th class="mw-80">Status</th>', $directorView);
        $this->assertStringContainsString("{{ \$row['status_class'] ?? 'text-muted' }}", $directorView);
        $this->assertStringContainsString("{{ \$row['status'] ?? '-' }}", $directorView);
        $this->assertStringContainsString("{{ \$row['status_info'] ?? '-' }}", $directorView);
        $this->assertStringContainsString('fa fa-info-circle ms-1', $directorView);
        $this->assertStringContainsString('title="Status: {{ $row[\'status_info\'] ?? \'-\' }}"', $directorView);
        $this->assertStringContainsString('$directorOvertimeTablePageSize = 5;', $directorView);
        $this->assertStringContainsString('max(1, (int) ceil($pendingTableRows->count() / $directorOvertimeTablePageSize))', $directorView);
        $this->assertStringContainsString('max(1, (int) ceil($approvedTableRows->count() / $directorOvertimeTablePageSize))', $directorView);
        $this->assertStringContainsString('data-director-overtime-table data-director-overtime-page-size="{{ $directorOvertimeTablePageSize }}"', $directorView);
        $this->assertStringContainsString('js-director-overtime-table-row', $directorView);
        $this->assertStringContainsString('table-responsive director-overtime-review-table', $directorView);
        $this->assertStringContainsString('.director-overtime-review-table', $directorView);
        $this->assertStringContainsString('height: 286px;', $directorView);
        $this->assertStringContainsString('overflow-x: auto;', $directorView);
        $this->assertStringContainsString('overflow-y: hidden;', $directorView);
        $this->assertStringContainsString('-webkit-overflow-scrolling: touch;', $directorView);
        $this->assertStringContainsString('scrollbar-width: thin;', $directorView);
        $this->assertStringContainsString('scrollbar-color: #c6ccd6 #f1f3f7;', $directorView);
        $this->assertStringContainsString('.director-overtime-review-table::-webkit-scrollbar', $directorView);
        $this->assertStringContainsString('height: 7px;', $directorView);
        $this->assertStringContainsString('min-width: 900px;', $directorView);
        $this->assertStringNotContainsString('table-layout: fixed;', $directorView);
        $this->assertStringNotContainsString('text-overflow: ellipsis;', $directorView);
        $this->assertStringNotContainsString('.overtime-review-table tbody tr', $directorView);
        $this->assertStringNotContainsString('height: 100%;', $directorView);
        $this->assertStringContainsString('overtime-pending-table', $directorView);
        $this->assertStringContainsString('overtime-approved-table', $directorView);
        $this->assertStringContainsString('td class="overtime-status-cell"', $directorView);
        $this->assertStringNotContainsString('.director-overtime-review-table.is-empty', $directorView);
        $this->assertStringNotContainsString("'is-empty' => \$pendingTableRows->isEmpty()", $directorView);
        $this->assertStringNotContainsString("'is-empty' => \$approvedTableRows->isEmpty()", $directorView);
        $this->assertStringContainsString('director-overtime-table-footer dataTables_wrapper no-footer', $directorView);
        $this->assertStringContainsString('js-director-overtime-pagination', $directorView);
        $this->assertStringContainsString('Showing {{ $pendingTableRows->isEmpty() ? 0 : 1 }}', $directorView);
        $this->assertStringContainsString('Showing {{ $approvedTableRows->isEmpty() ? 0 : 1 }}', $directorView);
        $this->assertStringContainsString('function showDirectorOvertimeTablePage', $directorView);
        $this->assertStringContainsString('function initDirectorOvertimeTablePagination', $directorView);
    }

    public function test_admin_view_uses_complete_table_and_dynamic_lifecycle_cards(): void
    {
        $view = (string) file_get_contents(resource_path('views/admin_attendance/overtime/index.blade.php'));
        $detailView = (string) file_get_contents(resource_path('views/admin_attendance/overtime/detail.blade.php'));

        $this->assertStringContainsString('Status : <span class="text-success">Complete</span>', $view);
        $this->assertStringNotContainsString('name="month"', $view);
        $this->assertStringNotContainsString('name="year"', $view);
        $this->assertStringContainsString('name="card_month"', $view);
        $this->assertStringContainsString('name="card_year"', $view);
        $this->assertStringContainsString('name="pending_month"', $view);
        $this->assertStringContainsString('name="pending_year"', $view);
        $this->assertStringContainsString('name="complete_month"', $view);
        $this->assertStringContainsString('name="complete_year"', $view);
        $this->assertStringContainsString('$selectedCardMonth', $view);
        $this->assertStringContainsString('$selectedPendingMonth', $view);
        $this->assertStringContainsString('$selectedCompleteMonth', $view);
        $this->assertStringContainsString('admin-overtime-card-filter', $view);
        $this->assertStringContainsString('overtime-summary-metrics mb-3', $view);
        $this->assertStringContainsString('@foreach (($overtimeMetricCards ?? []) as $summaryCard)', $view);
        $this->assertStringContainsString('overtime-summary-card {{ $summaryCard[\'background_class\'] ?? \'bg-light-subtle\' }}', $view);
        $this->assertStringContainsString('min-height: 82px;', $view);
        $this->assertStringContainsString('grid-template-columns: repeat(9, minmax(0, 1fr));', $view);
        $this->assertStringNotContainsString('$overtimeSummary[\'pending_label\']', $view);
        $this->assertLessThan(
            strpos($view, '@forelse (($overtimeCards ?? collect()) as $overtimeCard)'),
            strpos($view, 'admin-overtime-card-filter'),
        );
        $this->assertStringContainsString('<th class="mw-80">Status</th>', $view);
        $this->assertStringContainsString("{{ \$row['status_class'] ?? 'text-muted' }}", $view);
        $this->assertStringContainsString("{{ \$row['status'] ?? '-' }}", $view);
        $this->assertStringContainsString("{{ \$row['status_info'] ?? '-' }}", $view);
        $this->assertStringContainsString('fa fa-info-circle ms-1', $view);
        $this->assertStringContainsString('title="Status: {{ $row[\'status_info\'] ?? \'-\' }}"', $view);
        $this->assertStringContainsString('$adminOvertimeTablePageSize = 5;', $view);
        $this->assertStringContainsString('max(1, (int) ceil($pendingTableRows->count() / $adminOvertimeTablePageSize))', $view);
        $this->assertStringContainsString('max(1, (int) ceil($completeTableRows->count() / $adminOvertimeTablePageSize))', $view);
        $this->assertStringContainsString('data-admin-overtime-table data-admin-overtime-page-size="{{ $adminOvertimeTablePageSize }}"', $view);
        $this->assertStringContainsString('js-admin-overtime-table-row', $view);
        $this->assertStringContainsString('table-responsive admin-overtime-review-table', $view);
        $this->assertStringContainsString('.admin-overtime-review-table', $view);
        $this->assertStringContainsString('height: 286px;', $view);
        $this->assertStringContainsString('overflow-x: auto;', $view);
        $this->assertStringContainsString('overflow-y: hidden;', $view);
        $this->assertStringContainsString('-webkit-overflow-scrolling: touch;', $view);
        $this->assertStringContainsString('scrollbar-width: thin;', $view);
        $this->assertStringContainsString('scrollbar-color: #c6ccd6 #f1f3f7;', $view);
        $this->assertStringContainsString('.admin-overtime-review-table::-webkit-scrollbar', $view);
        $this->assertStringContainsString('height: 7px;', $view);
        $this->assertStringContainsString('min-width: 900px;', $view);
        $this->assertStringNotContainsString('table-layout: fixed;', $view);
        $this->assertStringNotContainsString('text-overflow: ellipsis;', $view);
        $this->assertStringNotContainsString('.overtime-review-table tbody tr', $view);
        $this->assertStringNotContainsString('height: 100%;', $view);
        $this->assertStringContainsString('overtime-pending-table', $view);
        $this->assertStringContainsString('overtime-approved-table', $view);
        $this->assertStringContainsString('td class="overtime-status-cell"', $view);
        $this->assertStringNotContainsString('.admin-overtime-review-table.is-empty', $view);
        $this->assertStringNotContainsString("'is-empty' => \$pendingTableRows->isEmpty()", $view);
        $this->assertStringNotContainsString("'is-empty' => \$completeTableRows->isEmpty()", $view);
        $this->assertStringContainsString('admin-overtime-table-footer dataTables_wrapper no-footer', $view);
        $this->assertStringContainsString('js-admin-overtime-pagination', $view);
        $this->assertStringContainsString('Showing {{ $pendingTableRows->isEmpty() ? 0 : 1 }}', $view);
        $this->assertStringContainsString('Showing {{ $completeTableRows->isEmpty() ? 0 : 1 }}', $view);
        $this->assertStringContainsString('function showAdminOvertimeTablePage', $view);
        $this->assertStringContainsString('function initAdminOvertimeTablePagination', $view);
        $this->assertStringContainsString('No complete overtime data available for this period.', $view);
        $this->assertStringContainsString('$overtimeCards', $view);
        $this->assertStringContainsString('$overtimeCard[\'current_log\'][\'title\']', $view);
        $this->assertStringNotContainsString('SPV : Approved', $view);
        $this->assertStringNotContainsString('#OVT-2605-0101', $view);
        $this->assertStringContainsString('.admin-overtime-task-list', $detailView);
        $this->assertStringContainsString('min-height: 500px;', $detailView);
        $this->assertStringContainsString('height400 admin-overtime-task-list', $detailView);
        $this->assertStringContainsString('class="btn light btn-success btn-lg w-100"', $detailView);
    }

    public function test_admin_pending_lifecycle_range_starts_at_task_hours_verification_and_excludes_payment_complete(): void
    {
        $reflection = new ReflectionClass(OvertimeReviewTableBuilder::class);
        $method = $reflection->getMethod('isAdminPendingLifecycleRange');
        $method->setAccessible(true);

        $builder = new OvertimeReviewTableBuilder;

        $this->assertFalse($method->invoke($builder, $this->overtimeWithLifecycle([
            'task_hours_verification' => 'waiting',
        ])));

        $this->assertTrue($method->invoke($builder, $this->overtimeWithLifecycle([
            'task_hours_verification' => 'pending',
            'payment_disbursement' => 'waiting',
        ])));

        $this->assertTrue($method->invoke($builder, $this->overtimeWithLifecycle([
            'task_hours_verification' => 'verified',
            'payroll_processing' => 'calculated_locked',
            'director_approval' => 'approved',
            'payment_disbursement' => 'upcoming',
        ])));

        $this->assertFalse($method->invoke($builder, $this->overtimeWithLifecycle([
            'task_hours_verification' => 'verified',
            'payroll_processing' => 'calculated_locked',
            'director_approval' => 'approved',
            'payment_disbursement' => 'complete',
        ])));
    }

    public function test_datetime_label_prefers_approved_times_when_available(): void
    {
        $reflection = new ReflectionClass(OvertimeReviewTableBuilder::class);
        $method = $reflection->getMethod('datetimeLabel');
        $method->setAccessible(true);

        $overtime = new AttendanceOvertime([
            'overtime_date' => '2026-07-30',
            'actual_start_time' => '09:46:00',
            'actual_end_time' => '09:47:00',
            'approved_start_time' => '10:00:00',
            'approved_end_time' => '11:30:00',
        ]);

        $this->assertSame(
            '30 Jul 2026, 10:00 - 11:30 (1h 30m)',
            $method->invoke(new OvertimeReviewTableBuilder, $overtime)
        );
    }

    public function test_datetime_label_falls_back_to_actual_times_when_approved_times_are_empty(): void
    {
        $reflection = new ReflectionClass(OvertimeReviewTableBuilder::class);
        $method = $reflection->getMethod('datetimeLabel');
        $method->setAccessible(true);

        $overtime = new AttendanceOvertime([
            'overtime_date' => '2026-07-30',
            'actual_start_time' => '09:46:00',
            'actual_end_time' => '09:47:00',
            'approved_start_time' => null,
            'approved_end_time' => null,
        ]);

        $this->assertSame(
            '30 Jul 2026, 09:46 - 09:47 (0h 1m)',
            $method->invoke(new OvertimeReviewTableBuilder, $overtime)
        );
    }

    public function test_employee_name_uses_user_username_instead_of_profile_name(): void
    {
        $reflection = new ReflectionClass(OvertimeReviewTableBuilder::class);
        $method = $reflection->getMethod('employeeName');
        $method->setAccessible(true);

        $employee = new Employee(['id' => 'employee-a']);
        $employee->setRelation('profile', new EmployeeProfile([
            'employee_id' => 'employee-a',
            'name' => 'Dimas Prayoga',
        ]));
        $employee->setRelation('user', new User([
            'username' => 'dimas',
        ]));

        $overtime = new AttendanceOvertime(['employee_id' => 'employee-a']);
        $overtime->setRelation('employee', $employee);

        $this->assertSame('dimas', $method->invoke(new OvertimeReviewTableBuilder, $overtime));
    }

    public function test_supervisor_name_uses_assigned_user_username_instead_of_profile_name(): void
    {
        $reflection = new ReflectionClass(OvertimeReviewTableBuilder::class);
        $method = $reflection->getMethod('supervisorName');
        $method->setAccessible(true);

        $employee = new Employee(['id' => 'supervisor-employee']);
        $employee->setRelation('profile', new EmployeeProfile([
            'employee_id' => 'supervisor-employee',
            'name' => 'Muhammad Syafiq',
        ]));

        $assignedBy = new User([
            'username' => 'syafiq',
        ]);
        $assignedBy->setRelation('employee', $employee);

        $overtime = new AttendanceOvertime(['assigned_by' => 'supervisor-user']);
        $overtime->setRelation('assignedBy', $assignedBy);

        $this->assertSame('syafiq', $method->invoke(new OvertimeReviewTableBuilder, $overtime));
    }

    public function test_current_lifecycle_title_uses_running_overtime_lifecycle_log_title(): void
    {
        $reflection = new ReflectionClass(OvertimeReviewTableBuilder::class);
        $method = $reflection->getMethod('currentLifecycleTitle');
        $method->setAccessible(true);

        $overtime = new AttendanceOvertime(['id' => 'overtime-test']);
        $overtime->setRelation('lifecycleLogs', collect([
            new OvertimeLifecycleLog([
                'overtime_id' => 'overtime-test',
                'event_key' => 'task_hours_verification',
                'step_order' => 5,
                'title' => 'Task & Hours Verification',
                'status' => 'verified',
            ]),
            new OvertimeLifecycleLog([
                'overtime_id' => 'overtime-test',
                'event_key' => 'payroll_processing',
                'step_order' => 6,
                'title' => 'HR / Payroll Processing',
                'status' => 'pending',
            ]),
            new OvertimeLifecycleLog([
                'overtime_id' => 'overtime-test',
                'event_key' => 'director_approval',
                'step_order' => 7,
                'title' => 'Director Approval',
                'status' => 'waiting',
            ]),
        ]));

        $this->assertSame('HR / Payroll Processing', $method->invoke(new OvertimeReviewTableBuilder, $overtime));
    }

    public function test_current_lifecycle_value_includes_text_class_based_on_running_status(): void
    {
        $reflection = new ReflectionClass(OvertimeReviewTableBuilder::class);
        $method = $reflection->getMethod('currentLifecycleValue');
        $method->setAccessible(true);

        $overtime = new AttendanceOvertime(['id' => 'overtime-test']);
        $overtime->setRelation('lifecycleLogs', collect([
            new OvertimeLifecycleLog([
                'overtime_id' => 'overtime-test',
                'event_key' => 'task_hours_verification',
                'step_order' => 5,
                'title' => 'Task & Hours Verification',
                'status' => 'verified',
            ]),
            new OvertimeLifecycleLog([
                'overtime_id' => 'overtime-test',
                'event_key' => 'payroll_processing',
                'step_order' => 6,
                'title' => 'HR / Payroll Processing',
                'status' => 'pending',
            ]),
        ]));

        $this->assertSame([
            'title' => 'HR / Payroll Processing',
            'status' => 'pending',
            'text_class' => 'text-warning',
        ], $method->invoke(new OvertimeReviewTableBuilder, $overtime));
    }

    public function test_admin_pic_and_director_detail_urls_include_overtime_uid(): void
    {
        $reflection = new ReflectionClass(OvertimeReviewTableBuilder::class);
        $method = $reflection->getMethod('detailUrlFor');
        $method->setAccessible(true);

        $builder = new OvertimeReviewTableBuilder;
        $overtime = new AttendanceOvertime(['id' => 'OVT-DETAIL-UID']);

        $this->assertStringEndsWith(
            '/admin-attendance/overtime/detail/OVT-DETAIL-UID',
            $method->invoke($builder, 'admin', $overtime)
        );
        $this->assertStringEndsWith(
            '/pic-attendance/overtime/detail/OVT-DETAIL-UID',
            $method->invoke($builder, 'pic', $overtime)
        );
        $this->assertStringEndsWith(
            '/director-attendance/overtime/detail/OVT-DETAIL-UID',
            $method->invoke($builder, 'director', $overtime)
        );
    }

    /**
     * @return array<string, array{0:string,1:array{0:string,1:string,2:string,3:string}}>
     */
    public static function contextStatusRules(): array
    {
        return [
            'admin' => ['admin', ['task_hours_verification', 'in_progress', 'payment_disbursement', 'complete']],
            'pic' => ['pic', ['task_hours_verification', 'pending', 'task_hours_verification', 'verified']],
            'director' => ['director', ['director_approval', 'pending', 'director_approval', 'approved']],
        ];
    }

    /**
     * @param  array<string, string>  $statuses
     */
    private function overtimeWithLifecycle(array $statuses): AttendanceOvertime
    {
        $overtime = new AttendanceOvertime(['id' => 'overtime-test']);
        $overtime->setRelation('lifecycleLogs', $this->lifecycleLogs($statuses));

        return $overtime;
    }

    /**
     * @param  array<string, string>  $statuses
     * @return Collection<int, OvertimeLifecycleLog>
     */
    private function lifecycleLogs(array $statuses): Collection
    {
        return collect($statuses)
            ->map(fn (string $status, string $eventKey): OvertimeLifecycleLog => new OvertimeLifecycleLog([
                'overtime_id' => 'overtime-test',
                'event_key' => $eventKey,
                'status' => $status,
            ]))
            ->values();
    }
}
