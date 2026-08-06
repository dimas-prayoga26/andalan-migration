<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class DashboardStructureTest extends TestCase
{
    public function test_dashboard_menu_carousel_is_hidden_without_deleting_markup(): void
    {
        $dashboardView = File::get(resource_path('views/dashboard.blade.php'));

        $this->assertStringContainsString('$showDashboardMenuCarousel = false;', $dashboardView);
        $this->assertStringContainsString('@if ($showDashboardMenuCarousel)', $dashboardView);
        $this->assertStringContainsString('dashboard-menu-carousel owl-carousel', $dashboardView);
        $this->assertStringContainsString('Data Pelamar', $dashboardView);
        $this->assertStringContainsString('Data Karyawan', $dashboardView);
        $this->assertStringContainsString('Blog Management', $dashboardView);
        $this->assertStringContainsString('Laporan Pekerjaan', $dashboardView);
    }

    public function test_dashboard_shortcut_menu_only_shows_active_links_and_keeps_inactive_markup_hidden(): void
    {
        $dashboardView = File::get(resource_path('views/dashboard.blade.php'));

        $this->assertStringContainsString('$showDashboardInactiveShortcuts = false;', $dashboardView);
        $this->assertStringContainsString("href=\"{{ route('attendance.today') }}\"", $dashboardView);
        $this->assertStringContainsString("href=\"{{ route('activity-schadule') }}\"", $dashboardView);
        $this->assertStringContainsString("href=\"{{ route('project_management.projects') }}\"", $dashboardView);
        $this->assertStringContainsString("href=\"{{ route('project_management.task_list') }}\"", $dashboardView);
        $this->assertStringContainsString("href=\"{{ route('employee_data') }}\"", $dashboardView);
        $this->assertStringContainsString('$canViewDashboardEmployeeShortcut', $dashboardView);
        $this->assertStringContainsString("'view-authorization'", $dashboardView);
        $this->assertStringContainsString("'view-employee-database'", $dashboardView);
        $this->assertStringNotContainsString("\$dashboardPositionNames->contains('Administrator')", $dashboardView);
        $this->assertGreaterThanOrEqual(4, substr_count($dashboardView, '@if ($showDashboardInactiveShortcuts)'));
        $this->assertStringContainsString('dashboard-shortcut-item--blog', $dashboardView);
        $this->assertStringContainsString('dashboard-shortcut-item--meeting', $dashboardView);
        $this->assertStringContainsString('dashboard-shortcut-item--finance', $dashboardView);
        $this->assertStringContainsString('dashboard-shortcut-item--profile', $dashboardView);
        $this->assertStringContainsString('dashboard-shortcut-item--pelamar', $dashboardView);
        $this->assertStringContainsString('dashboard-shortcut-item--setting', $dashboardView);
        $this->assertStringContainsString('dashboard-shortcut-item--administration', $dashboardView);
    }

    public function test_dashboard_attendance_cards_show_date_time_without_distance(): void
    {
        $dashboardView = File::get(resource_path('views/dashboard.blade.php'));

        $this->assertStringContainsString('Date &amp; Time', $dashboardView);
        $this->assertStringContainsString("format('d M Y | H:i:s')", $dashboardView);
        $this->assertStringContainsString("month: 'short'", $dashboardView);
        $this->assertStringContainsString("var formattedCardMonth = String(cardDateMap.month || '').replace('.', '');", $dashboardView);
        $this->assertStringContainsString("var formattedDateTime = cardDateMap.day + ' ' + formattedCardMonth + ' ' + cardDateMap.year + ' | ' + formattedTime;", $dashboardView);
        $this->assertStringContainsString('var lateThresholdTotalMinutes = officeStartTotalMinutes + lateGraceMinutes;', $dashboardView);
        $this->assertStringContainsString("attendanceSummaryTimeElement.classList.add(totalMinutes <= lateThresholdTotalMinutes ? 'text-success' : 'text-danger');", $dashboardView);
        $this->assertStringContainsString("attendanceClockOutSummaryTimeElement.classList.add(isWithinWorkRange ? 'text-warning' : 'text-black');", $dashboardView);
        $this->assertStringContainsString("var modalDateTime = modalDate + ' - ' + formattedTime;", $dashboardView);
        $this->assertStringContainsString('clockInCurrentDateElement.textContent = modalDateTime;', $dashboardView);
        $this->assertStringContainsString("clockInCurrentDateElement.classList.add(totalMinutes <= lateThresholdTotalMinutes ? 'text-success' : 'text-danger');", $dashboardView);
        $this->assertStringContainsString('clockOutCurrentDateElement.textContent = modalDateTime;', $dashboardView);
        $this->assertStringContainsString("clockOutCurrentDateElement.classList.add(isWithinWorkRange ? 'text-warning' : 'text-black');", $dashboardView);
        $this->assertStringNotContainsString('<p class="fs-14 mb-2">Distance</p>', $dashboardView);
    }

    public function test_dashboard_attendance_confirmation_matches_attendance_menu_radius_behavior(): void
    {
        $dashboardView = File::get(resource_path('views/dashboard.blade.php'));

        $this->assertStringContainsString('context.hasVerifiedOnsite = true;', $dashboardView);
        $this->assertStringContainsString('id="dashboardClockInStatusText">Please wait</p>', $dashboardView);
        $this->assertStringContainsString('id="dashboardClockOutStatusText">Please wait</p>', $dashboardView);
        $this->assertStringContainsString('id="dashboardClockInSubmitBtn" disabled>Clock In</button>', $dashboardView);
        $this->assertStringContainsString('id="dashboardClockOutSubmitBtn" disabled>Clock Out</button>', $dashboardView);
        $this->assertStringContainsString('assets/vendor/sweetalert2/sweetalert2.min.js', $dashboardView);
        $this->assertStringContainsString('data-clock-out-too-early="true"', $dashboardView);
        $this->assertStringContainsString("showSwalAlert('warning', 'Clock Out Belum Tersedia', message);", $dashboardView);
        $this->assertStringContainsString("clockOutCardButtonElement.addEventListener('click', function (event)", $dashboardView);
        $this->assertStringContainsString("setVerificationMessage(context, 'Verification successful', 'success');", $dashboardView);
        $this->assertStringContainsString('checkOnsiteLocation(context);', $dashboardView);
        $this->assertStringNotContainsString('Mulai Verifikasi', $dashboardView);
        $this->assertStringNotContainsString('context.isWithinOfficeRadius = inRadius;', $dashboardView);
        $this->assertStringNotContainsString('if (context.isWithinOfficeRadius) {', $dashboardView);
        $this->assertStringNotContainsString("setVerificationMessage(context, 'Lokasi berada di luar radius kantor.', 'warning');", $dashboardView);
        $this->assertStringNotContainsString('context.hasVerifiedOnsite = inRadius;', $dashboardView);
    }
}
