<?php

namespace Tests\Feature;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StaffAttendance\AttendanceController;
use App\Http\Controllers\StaffAttendance\AttendanceReportController;
use App\Http\Requests\Attendance\AttendanceIndexRequest;
use App\Http\Requests\Attendance\CurrentAttendanceIpRequest;
use App\Http\Requests\Attendance\StoreAttendanceExceptionRequest;
use App\Http\Requests\Attendance\StoreAttendanceRequest;
use App\Http\Requests\Attendance\UpdateAttendanceRequest;
use App\Services\Attendance\AttendanceCalendarEventService;
use App\Services\Attendance\AttendanceMutationService;
use App\Support\Attendance\AttendanceCalendarPresenter;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class AttendanceCalendarModalRoutingTest extends TestCase
{
    public function test_attendance_refactor_dependencies_can_be_resolved(): void
    {
        $this->assertInstanceOf(AttendanceController::class, app(AttendanceController::class));
        $this->assertInstanceOf(DashboardController::class, app(DashboardController::class));
        $this->assertInstanceOf(AttendanceReportController::class, app(AttendanceReportController::class));
        $this->assertSame(8.0, app(AttendanceMutationService::class)->calculateWorkHours(
            Carbon::parse('2026-07-07 08:00:00', 'Asia/Jakarta'),
            Carbon::parse('2026-07-07 17:00:00', 'Asia/Jakarta')
        ));

        $request = new StoreAttendanceExceptionRequest;
        $this->assertSame(
            ['required', 'in:late_arrival,early_departure'],
            $request->rules()['type']
        );

        $this->assertSame(['nullable', 'ip'], (new AttendanceIndexRequest)->rules()['client_ip']);
        $this->assertSame(['nullable', 'ip'], (new CurrentAttendanceIpRequest)->rules()['client_ip']);
        $this->assertSame(
            ['nullable', 'required_with:longitude', 'numeric', 'between:-90,90'],
            (new StoreAttendanceRequest)->rules()['latitude']
        );
        $this->assertSame(
            ['nullable', 'required_with:latitude', 'numeric', 'between:-180,180'],
            (new UpdateAttendanceRequest)->rules()['longitude']
        );

        $invalidTrackingInput = Validator::make(
            ['latitude' => -91, 'longitude' => 107],
            (new StoreAttendanceRequest)->rules()
        );
        $this->assertTrue($invalidTrackingInput->fails());

        $validTrackingInput = Validator::make(
            ['client_ip' => '203.0.113.10', 'latitude' => -6.2, 'longitude' => 106.8],
            (new StoreAttendanceRequest)->rules()
        );
        $this->assertFalse($validTrackingInput->fails());
    }

    public function test_attendance_calendar_responsibilities_are_separated_from_controller(): void
    {
        $attendanceController = File::get(app_path('Http/Controllers/StaffAttendance/AttendanceController.php'));
        $calendarEventService = File::get(app_path('Services/Attendance/AttendanceCalendarEventService.php'));
        $calendarPresenter = File::get(app_path('Support/Attendance/AttendanceCalendarPresenter.php'));
        $exceptionPresenter = File::get(app_path('Support/Attendance/AttendanceExceptionPresenter.php'));
        $locationFormatter = File::get(app_path('Support/Attendance/AttendanceLocationFormatter.php'));
        $attendanceTrackingRequest = File::get(app_path('Http/Requests/Attendance/AttendanceTrackingRequest.php'));
        $exceptionRequest = File::get(app_path('Http/Requests/Attendance/StoreAttendanceExceptionRequest.php'));
        $attendanceMutationService = File::get(app_path('Services/Attendance/AttendanceMutationService.php'));
        $attendanceCardsService = File::get(app_path('Services/Attendance/AttendanceCardsViewDataService.php'));
        $attendanceIpService = File::get(app_path('Services/Attendance/AttendanceIpVerificationService.php'));

        $this->assertStringContainsString('private AttendanceCalendarEventService $attendanceCalendarEventService', $attendanceController);
        $this->assertStringContainsString('$this->attendanceCalendarEventService->buildEmployeeEvents(', $attendanceController);
        $this->assertStringContainsString('$this->attendanceCalendarEventService->buildHolidayEvents()', $attendanceController);
        $this->assertStringContainsString('StoreAttendanceExceptionRequest $request', $attendanceController);
        $this->assertStringContainsString('StoreAttendanceRequest $request', $attendanceController);
        $this->assertStringContainsString('UpdateAttendanceRequest $request', $attendanceController);
        $this->assertStringContainsString('CurrentAttendanceIpRequest $request', $attendanceController);
        $this->assertStringContainsString('$request->validated()', $attendanceController);
        $this->assertStringNotContainsString('private function buildLeaveCalendarEvents', $attendanceController);
        $this->assertStringNotContainsString('private function formatAttendanceLocationName', $attendanceController);

        $this->assertStringContainsString('class AttendanceCalendarEventService', $calendarEventService);
        $this->assertStringContainsString('public function buildHolidayEvents(): array', $calendarEventService);
        $this->assertStringContainsString('public function buildEmployeeEvents(string $employeeId, ?array $officeLocation): array', $calendarEventService);
        $this->assertStringContainsString('private function buildAttendanceHistoryEvents', $calendarEventService);
        $this->assertStringContainsString('private function buildLeaveCalendarEvents', $calendarEventService);
        $this->assertStringContainsString('private function buildBusinessTripCalendarEvents', $calendarEventService);
        $this->assertStringContainsString("->whereIn('status', ['pending', 'approved'])", $calendarEventService);
        $this->assertStringContainsString("->where('approval_status', 'approved')", $calendarEventService);
        $this->assertStringContainsString("'calendarModalId' => 'trip'", $calendarEventService);
        $this->assertStringContainsString("'attendanceModalId' => \$attendanceModalId", $calendarEventService);
        $this->assertStringContainsString("'clockOut' => \$clockOutLabel", $calendarEventService);
        $this->assertStringContainsString("'tripSubmitTaskUrl' => route('attendance.business-trips.cash-advances.create', \$businessTrip)", $calendarEventService);
        $this->assertStringContainsString("'tripReimbursementUrl' => route('attendance.business-trips.reimbursements.create', \$businessTrip)", $calendarEventService);

        $this->assertStringContainsString('class AttendanceCalendarPresenter', $calendarPresenter);
        $this->assertStringContainsString('public function leaveTypeLabel', $calendarPresenter);
        $this->assertStringContainsString('public function attendanceStatusLabel', $calendarPresenter);
        $this->assertStringContainsString("return asset('storage/'.ltrim(\$attachmentPath, '/'));", $calendarPresenter);
        $this->assertStringNotContainsString('AttendanceException', $calendarPresenter);
        $this->assertStringNotContainsString('AttendanceLog', $calendarPresenter);

        $this->assertStringContainsString('class AttendanceExceptionPresenter', $exceptionPresenter);
        $this->assertStringContainsString('public function timeVarianceLabel', $exceptionPresenter);
        $this->assertStringContainsString("'late_arrival' => 'Permitted Late Arrival'", $exceptionPresenter);
        $this->assertStringContainsString("'early_departure' => 'Early Departure'", $exceptionPresenter);

        $this->assertStringContainsString('class AttendanceLocationFormatter', $locationFormatter);
        $this->assertStringContainsString('public function name', $locationFormatter);
        $this->assertStringContainsString("return 'Koordinat Absensi';", $locationFormatter);
        $this->assertStringContainsString("str_starts_with(strtolower(\$location), 'https://www.google.com/maps')", $locationFormatter);

        $this->assertStringContainsString('abstract class AttendanceTrackingRequest extends FormRequest', $attendanceTrackingRequest);
        $this->assertStringContainsString('namespace App\Http\Requests\Attendance;', $exceptionRequest);
        $this->assertStringContainsString("'type' => ['required', 'in:late_arrival,early_departure']", $exceptionRequest);
        $this->assertStringContainsString("'exception_date' => ['nullable', 'date']", $exceptionRequest);
        $this->assertStringContainsString('has_attendance_exception_today', $attendanceMutationService);
        $this->assertStringContainsString('clock_in_label', $attendanceMutationService);
        $this->assertStringContainsString('clock_out_label', $attendanceMutationService);
        $this->assertStringContainsString('calendar_event', $attendanceMutationService);
        $this->assertStringContainsString('private function buildAttendanceExceptionCalendarEvent(', $attendanceMutationService);
        $this->assertStringContainsString('$attendanceStatus = $lateMinutes > 0 ? \'Terlambat\' : \'Masuk\';', $attendanceMutationService);
        $this->assertStringContainsString('hasAttendanceExceptionToday', $attendanceCardsService);
        $attendanceCardsView = File::get(resource_path('views/staff_attendance/components/attendance-cards.blade.php'));
        $this->assertStringContainsString('id="attendanceExceptionCardButton"', $attendanceCardsView);
        $this->assertStringContainsString('id="attendanceClockInValue"', $attendanceCardsView);
        $this->assertStringContainsString('id="attendanceClockOutValue"', $attendanceCardsView);
        $this->assertStringContainsString('Date &amp; Time', $attendanceCardsView);
        $this->assertStringContainsString("format('d M Y | H:i:s')", $attendanceCardsView);
        $this->assertStringContainsString("month: 'short'", $attendanceCardsView);
        $this->assertStringContainsString("var formattedCardMonth = String(cardDateMap.month || '').replace('.', '');", $attendanceCardsView);
        $this->assertStringContainsString("var formattedDateTime = cardDateMap.day + ' ' + formattedCardMonth + ' ' + cardDateMap.year + ' | ' + formattedTime;", $attendanceCardsView);
        $this->assertStringContainsString("var modalDateTime = modalDate + ' - ' + formattedTime;", $attendanceCardsView);
        $this->assertStringContainsString('clockInCurrentDateElement.textContent = modalDateTime;', $attendanceCardsView);
        $this->assertStringContainsString("clockInCurrentDateElement.classList.add(totalMinutes <= lateThresholdTotalMinutes ? 'text-success' : 'text-danger');", $attendanceCardsView);
        $this->assertStringContainsString('clockOutCurrentDateElement.textContent = modalDateTime;', $attendanceCardsView);
        $this->assertStringContainsString("clockOutCurrentDateElement.classList.add(isWithinWorkRange ? 'text-warning' : 'text-black');", $attendanceCardsView);
        $this->assertStringNotContainsString('<p class="fs-14 mb-2">Date</p>', $attendanceCardsView);
        $this->assertStringContainsString('id="clockInStatusText">Please wait</p>', $attendanceCardsView);
        $this->assertStringContainsString('id="clockOutStatusText">Please wait</p>', $attendanceCardsView);
        $this->assertStringContainsString('id="clockInSubmitBtn" disabled>Clock In</button>', $attendanceCardsView);
        $this->assertStringContainsString('id="clockOutSubmitBtn" disabled>Clock Out</button>', $attendanceCardsView);
        $this->assertStringContainsString("setVerificationMessage(context, 'Verification successful', 'success');", $attendanceCardsView);
        $this->assertStringContainsString('checkOnsiteLocation(context);', $attendanceCardsView);
        $this->assertStringNotContainsString('Mulai Verifikasi', $attendanceCardsView);
        $this->assertStringContainsString('fa-solid fa-calendar-xmark fs-24 text-secondary', $attendanceCardsView);
        $this->assertStringNotContainsString('viewBox="0 0 51 51"', $attendanceCardsView);
        $this->assertStringContainsString('window.upsertAttendanceHistoryEvent(response.calendar_event);', $attendanceCardsView);

        $profileIndexView = File::get(resource_path('views/staff_attendance/layouts/profile-index.blade.php'));
        $this->assertStringContainsString('assets/vendor/apexcharts/dist/apexcharts.min.js', $profileIndexView);
        $this->assertStringContainsString('function renderProfileProgressChart(chartElement)', $profileIndexView);
        $this->assertStringContainsString('#chartProfileProgressDesktop, #chartProfileProgress', $profileIndexView);

        $dashboardCss = File::get(public_path('assets/css/dashboard.css'));
        $this->assertStringContainsString('.attendance-rate-mobile-slider .card:has(.effect):hover .avatar-secondary i', $dashboardCss);
        $this->assertStringContainsString('.attendance-rate-mobile-slider .card:has(.effect):hover .avatar-info i', $dashboardCss);

        foreach ([$attendanceMutationService, $attendanceCardsService, $attendanceIpService] as $attendanceService) {
            $this->assertStringNotContainsString('Illuminate\Http\Request', $attendanceService);
            $this->assertStringNotContainsString('Request $request', $attendanceService);
            $this->assertStringNotContainsString('->input(', $attendanceService);
            $this->assertStringNotContainsString('->query(', $attendanceService);
        }
    }

    public function test_attendance_calendar_events_are_mapped_to_existing_modals(): void
    {
        $attendanceView = File::get(resource_path('views/staff_attendance/attendance/index.blade.php'));

        foreach (['onTime', 'late', 'deviation', 'annualLeave', 'specialLeave', 'unpaidLeave', 'sick', 'trip'] as $modalId) {
            $this->assertStringContainsString('<div class="modal fade" id="'.$modalId.'"', $attendanceView);
        }

        $this->assertStringContainsString("var attendanceModalIds = ['onTime', 'late', 'deviation'];", $attendanceView);
        $this->assertStringContainsString("var calendarLabelModalIds = ['annualLeave', 'specialLeave', 'unpaidLeave', 'sick', 'trip'];", $attendanceView);
        $this->assertStringContainsString('var calendarLabelEvents = @json($calendarLabelEvents ?? []);', $attendanceView);
        $this->assertStringContainsString('function fillAttendanceModal(modalId, props)', $attendanceView);
        $this->assertStringContainsString('function fillLeaveCalendarModal(modalId, props)', $attendanceView);
        $this->assertStringContainsString('function fillTripCalendarModal(props)', $attendanceView);
        $this->assertStringContainsString('window.attendanceCalendar = calendar;', $attendanceView);
        $this->assertStringContainsString('window.upsertAttendanceHistoryEvent = function (eventItem)', $attendanceView);
        $this->assertStringContainsString('calendar.refetchEvents();', $attendanceView);
        $this->assertStringContainsString('openAttendanceModal(props.attendanceModalId, props);', $attendanceView);
        $this->assertStringContainsString('openCalendarLabelModal(props.calendarModalId, props);', $attendanceView);
        $this->assertStringContainsString('.app-fullcalendar .fc-button-primary {', $attendanceView);
        $this->assertStringContainsString('.app-fullcalendar .fc-button-primary:not(:disabled).fc-button-active', $attendanceView);
        $this->assertStringContainsString("right: 'dayGridMonth,dayGridWeek,dayGridDay'", $attendanceView);
        $this->assertStringNotContainsString("right: 'dayGridMonth,dayGridWeek,dayGridDay,listSchedule'", $attendanceView);
        $this->assertStringContainsString("month: 'Month'", $attendanceView);
        $this->assertStringContainsString('firstDay: 1,', $attendanceView);
        $this->assertStringContainsString('.app-fullcalendar .fc-prev-button', $attendanceView);
        $this->assertStringContainsString('.app-fullcalendar .fc-today-button', $attendanceView);

        $activityScheduleView = File::get(resource_path('views/activity_schedule/index.blade.php'));
        $fullCalendarInit = File::get(public_path('assets/js/plugins-init/fullcalendar-init.js'));

        $this->assertStringContainsString('.app-fullcalendar .fc-button-primary {', $activityScheduleView);
        $this->assertStringContainsString('.app-fullcalendar .fc-button-primary:not(:disabled).fc-button-active', $activityScheduleView);
        $this->assertStringContainsString('.app-fullcalendar .fc-prev-button', $activityScheduleView);
        $this->assertStringContainsString('.app-fullcalendar .fc-today-button', $activityScheduleView);
        $this->assertStringContainsString('Activity Calendar', $activityScheduleView);
        $this->assertStringNotContainsString('Google Calendar', $activityScheduleView);
        $this->assertStringNotContainsString('calendar-date-filter', $activityScheduleView);
        $this->assertStringContainsString("right: 'dayGridMonth,dayGridWeek,dayGridDay,listSchedule'", $fullCalendarInit);
        $this->assertStringContainsString('firstDay: 1,', $fullCalendarInit);
        $this->assertStringContainsString("dayHeaderFormat: { weekday: 'short' }", $fullCalendarInit);
        $this->assertStringContainsString("today: 'Today'", $fullCalendarInit);
        $this->assertStringContainsString("month: 'Month'", $fullCalendarInit);
        $this->assertStringContainsString("week: 'Week'", $fullCalendarInit);
        $this->assertStringContainsString("day: 'Day'", $fullCalendarInit);
        $this->assertStringNotContainsString('mountDateFilterInToolbar', $fullCalendarInit);
        $this->assertStringNotContainsString('bindDateFilter', $fullCalendarInit);
    }

    public function test_attendance_calendar_late_boundary_and_modal_label_use_hours_and_minutes(): void
    {
        $calendarEventService = app(AttendanceCalendarEventService::class);
        $lateMinutesMethod = new \ReflectionMethod(AttendanceCalendarEventService::class, 'calculateClockInLateMinutes');
        $lateMinutesMethod->setAccessible(true);

        $this->assertSame(0, $lateMinutesMethod->invoke($calendarEventService, '2026-07-09', '08:00', '08:00'));
        $this->assertSame(1, $lateMinutesMethod->invoke($calendarEventService, '2026-07-09', '08:01', '08:00'));
        $this->assertSame(375, $lateMinutesMethod->invoke($calendarEventService, '2026-07-09', '14:15', '08:00'));

        $presenter = app(AttendanceCalendarPresenter::class);

        $this->assertSame(
            'Late 6 Hours 15 Minutes',
            $presenter->attendanceStatusLabel('late', 'Late 6 Hours 15 Minutes', 375)
        );
    }
}
