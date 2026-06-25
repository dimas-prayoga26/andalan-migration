<?php

namespace App\Http\Controllers\AdminAttendance;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Attendance\OvertimeReviewTableBuilder;
use App\Support\Attendance\OvertimeSummaryMetricBuilder;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class AttendanceOvertimeController extends Controller
{
    public function index(Request $request, OvertimeSummaryMetricBuilder $metricBuilder, OvertimeReviewTableBuilder $tableBuilder): View
    {
        $authenticatedUser = $request->user();
        $companyId = $authenticatedUser instanceof User ? $this->currentCompanyIdFor($authenticatedUser) : null;

        return view('admin_attendance.overtime.index', [
            'overtimeSummary' => $metricBuilder->summarizeForCompany($companyId),
            ...$tableBuilder->buildForContext('admin', $companyId, null, $request->query('month'), $request->query('year')),
        ]);
    }

    public function detail(): View
    {
        return view('admin_attendance.overtime.detail');
    }

    private function currentCompanyIdFor(User $user): ?string
    {
        $user->loadMissing('employee.deployment:id,employee_id,current_company_id');
        $companyId = $user->employee?->deployment?->current_company_id;

        return is_string($companyId) && trim($companyId) !== '' ? trim($companyId) : null;
    }
}
