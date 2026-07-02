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
}
