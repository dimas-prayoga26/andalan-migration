<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class DatePickerWeekStartConfigurationTest extends TestCase
{
    public function test_shared_date_picker_configuration_starts_week_on_monday(): void
    {
        $commonJs = File::get(resource_path('views/layouts/commonjs.blade.php'));
        $customJs = File::get(public_path('assets/js/custom.js'));
        $materialDatePickerInit = File::get(public_path('assets/js/plugins-init/material-date-picker-init.js'));
        $pickadateInit = File::get(public_path('assets/js/plugins-init/pickadate-init.js'));
        $picOvertimeIndex = File::get(resource_path('views/pic_attendance/overtime/index.blade.php'));
        $projectTaskList = File::get(resource_path('views/project_management/task_list/index.blade.php'));
        $projectDetail = File::get(resource_path('views/project_management/projects/detail.blade.php'));

        $this->assertStringContainsString('week: { dow: 1 }', $commonJs);
        $this->assertStringContainsString('weekStart: 1', $customJs);
        $this->assertStringContainsString('weekStart: 1', $materialDatePickerInit);
        $this->assertStringContainsString('firstDay: 1', $pickadateInit);
        $this->assertStringContainsString('weekStart: 1', $picOvertimeIndex);
        $this->assertStringContainsString('week: { dow: 1 }', $projectTaskList);
        $this->assertStringContainsString('week: { dow: 1 }', $projectDetail);
        $this->assertStringNotContainsString('weekStart: 0', $materialDatePickerInit);
    }
}
