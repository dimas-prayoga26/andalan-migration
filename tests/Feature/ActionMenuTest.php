<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ActionMenuTest extends TestCase
{
    /**
     * Views whose row actions are grouped in the three-dot menu, keyed by view path => how the menu is built.
     *
     * @var array<string, string>
     */
    private const ACTION_MENU_VIEWS = [
        'applicant_data/index.blade.php' => 'actionMenu([',
        'applicant_data/job_vancancies.blade.php' => 'actionMenu([',
        'employee_data/index.blade.php' => 'actionMenu([',
        'employee_data/authorization.blade.php' => 'actionMenu([',
        'settings/index.blade.php' => 'actionMenu([',
        'settings/companies/index.blade.php' => 'actionMenu([',
        'settings/office-locations/index.blade.php' => 'actionMenu([',
        'settings/attendance-rules/index.blade.php' => 'actionMenu([',
        'admin_attendance/leave/index.blade.php' => 'actionMenu([',
        'pic_attendance/leave/index.blade.php' => 'actionMenu([',
        'admin_attendance/recap_attendance/index.blade.php' => 'actionMenu([',
        'director_attendance/attendance/index.blade.php' => 'actionMenu([',
        'pic_attendance/attendance/index.blade.php' => 'actionMenu([',
        'meetings/admin/index.blade.php' => 'actionMenu([',
        'authorization/index.blade.php' => '<x-action-menu>',
        'authorization/assign-event-divisions.blade.php' => '<x-action-menu>',
        'email-management/index.blade.php' => '<x-action-menu>',
        'admin_attendance/overtime/index.blade.php' => '<x-action-menu>',
        'director_attendance/overtime/index.blade.php' => '<x-action-menu>',
        'pic_attendance/overtime/index.blade.php' => '<x-action-menu>',
    ];

    public function test_action_menu_component_renders_three_dot_toggle_with_items(): void
    {
        $html = Blade::render('<x-action-menu><li><a class="dropdown-item" href="/detail">Detail</a></li></x-action-menu>');

        $this->assertStringContainsString('<div class="dropdown action-menu">', $html);
        $this->assertStringContainsString('class="btn btn-light action-menu-toggle" data-bs-toggle="dropdown"', $html);
        $this->assertStringContainsString('data-bs-popper-config=\'{"strategy":"fixed"}\'', $html);
        $this->assertStringContainsString('<i class="fa-solid fa-ellipsis-vertical" aria-hidden="true"></i>', $html);
        $this->assertStringContainsString('<ul class="dropdown-menu dropdown-menu-end">', $html);
        $this->assertStringContainsString('<li><a class="dropdown-item" href="/detail">Detail</a></li>', $html);
    }

    public function test_action_menu_component_merges_extra_classes(): void
    {
        $html = Blade::render('<x-action-menu class="ms-auto"></x-action-menu>');

        $this->assertStringContainsString('<div class="dropdown action-menu ms-auto">', $html);
    }

    public function test_layout_provides_js_helper_and_styles_matching_the_component(): void
    {
        $commonJs = File::get(resource_path('views/layouts/commonjs.blade.php'));
        $mainHead = File::get(resource_path('views/layouts/mainhead.blade.php'));

        $this->assertStringContainsString('window.actionMenu = function (items)', $commonJs);
        $this->assertStringContainsString('class="btn btn-light action-menu-toggle" data-bs-toggle="dropdown"', $commonJs);
        $this->assertStringContainsString('fa-solid fa-ellipsis-vertical', $commonJs);
        $this->assertStringContainsString("'<ul class=\"dropdown-menu dropdown-menu-end\">' + items.join('') + '</ul>'", $commonJs);
        $this->assertLessThan(
            strpos($commonJs, 'window.actionMenu'),
            strpos($commonJs, 'bootstrap.bundle.min.js'),
            'The helper is defined after Bootstrap is loaded.'
        );
        $this->assertStringContainsString('.action-menu-toggle {', $mainHead);
        $this->assertStringContainsString('.action-menu .dropdown-item {', $mainHead);
    }

    public function test_layout_scripts_render_with_the_action_menu_helper(): void
    {
        $html = view('layouts.commonjs')->render();

        $this->assertStringContainsString('window.actionMenu = function (items)', $html);
        $this->assertStringNotContainsString('<x-action-menu', File::get(resource_path('views/layouts/commonjs.blade.php')));
    }

    public function test_table_row_actions_are_grouped_in_the_three_dot_menu(): void
    {
        foreach (self::ACTION_MENU_VIEWS as $viewPath => $menuMarker) {
            $view = File::get(resource_path('views/'.$viewPath));

            $this->assertStringContainsString($menuMarker, $view, $viewPath.' does not use the three-dot action menu.');
            $this->assertStringNotContainsString('light btn-sm">Update</', $view, $viewPath);
            $this->assertStringNotContainsString('class="dropdown dropdown-xs"', $view, $viewPath);
            $this->assertStringNotContainsString('talent-action-btn', $view, $viewPath);
            $this->assertStringNotContainsString('talent-vacancy-action-btn', $view, $viewPath);
            $this->assertStringNotContainsString('applicant-action-btn', $view, $viewPath);
        }
    }

    public function test_row_actions_keep_their_behaviour_inside_the_menu(): void
    {
        $applicants = File::get(resource_path('views/applicant_data/index.blade.php'));
        $settings = File::get(resource_path('views/settings/index.blade.php'));
        $meetings = File::get(resource_path('views/meetings/admin/index.blade.php'));
        $eventDivisions = File::get(resource_path('views/authorization/assign-event-divisions.blade.php'));
        $emailManagement = File::get(resource_path('views/email-management/index.blade.php'));

        $this->assertStringContainsString('class="dropdown-item text-danger" onclick="deleteData(\\\'\' + escapeHtml(applicant.id) + \'\\\')"', $applicants);
        $this->assertStringContainsString('<button type="button" class="dropdown-item text-danger" disabled><i class="bi bi-trash"></i>Delete</button>', $settings);
        $this->assertStringContainsString('class="js-meeting-delete-form"', $meetings);
        $this->assertStringContainsString('<button type="submit" class="dropdown-item text-danger"><i class="bi bi-trash"></i>Delete</button>', $meetings);
        $this->assertStringContainsString('class="dropdown-item js-edit-event-division"', $eventDivisions);
        $this->assertStringContainsString('form="deleteEventDivisionForm-{{ $eventDivisionAssignment[\'id\'] }}"', $eventDivisions);
        $this->assertStringContainsString('data-bs-target="#deleteMailAccountModal{{ $account->id }}"><i class="bi bi-trash"></i>Delete</button>', $emailManagement);
        $this->assertStringContainsString('data-bs-target="#deleteTakeoverModal{{ $takeover->id }}"><i class="bi bi-trash"></i>Delete</button>', $emailManagement);
    }
}
