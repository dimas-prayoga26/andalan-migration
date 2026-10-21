<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Employee;
use App\Models\EmployeeDeployment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

class ProfileEmployeeDataWizardTemplateTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_renders_employee_data_wizard_template(): void
    {
        $user = User::query()->create([
            'email' => 'staff.profile@example.com',
            'password' => 'password',
            'is_active' => true,
        ]);
        $employee = Employee::query()->create([
            'user_id' => $user->id,
            'employee_code' => 'EMP-0001',
            'status' => 'Active',
        ]);
        EmployeeDeployment::query()->create([
            'employee_id' => $employee->id,
            'current_company_id' => Company::query()->create(['name' => 'RNB'])->id,
            'join_date' => '2026-01-01',
            'status' => 'Active',
        ]);

        $response = $this->actingAs($user)->get(route('profile'));

        $response->assertOk();
        $response->assertSee('Data karyawan belum lengkap');
        $response->assertSee('Silakan lengkapi data diri, identitas, pendidikan, dan keluarga pada tab');
        $response->assertSee('Kelengkapan data: 0%');
        $response->assertDontSee('Step 1–4');
        $response->assertDontSee('Data kepegawaian akan diisi oleh HR');
        $response->assertSee('enableURLhash: false', false);
        $response->assertSee('id="employee-data-tab"', false);
        $response->assertSee('id="employee-data-pane"', false);
        $response->assertSee('id="employeeDataWizard"', false);
        $response->assertSeeInOrder([
            'Personal &amp; Physical Profile',
            'Legal, Tax &amp; Compliance',
            'Education, Experience &amp; Skills',
            'Family &amp; Emergency Contacts',
        ], false);
        $response->assertSeeInOrder(['Data Kepegawaian', 'Employee ID', 'EMP-0001', 'Company', 'RNB', 'Work Email', 'staff.profile@example.com']);
        $response->assertSee('jquery.smartWizard.min.js', false);
        $response->assertDontSee('Employment Type');
        $response->assertDontSee('Contract End');
        $response->assertDontSee('Job Level');
        $response->assertDontSee('id="wizard_Finish"', false);
        $response->assertDontSee('Simpan Data');
        $response->assertDontSee('toolbarExtraButtons', false);
    }

    public function test_wizard_step_partials_exist(): void
    {
        foreach (['step-1-personal', 'step-2-legal-tax', 'step-3-education', 'step-4-family'] as $stepView) {
            $this->assertTrue(View::exists('employee_data.wizard.'.$stepView), $stepView.' partial is missing.');
        }
    }

    public function test_repeatable_rows_use_compact_icon_action_buttons(): void
    {
        $expectedActionGroups = [
            'step-2-legal-tax' => 1,
            'step-3-education' => 4,
            'step-4-family' => 2,
        ];

        foreach ($expectedActionGroups as $stepView => $expectedCount) {
            $view = (string) file_get_contents(resource_path('views/employee_data/wizard/'.$stepView.'.blade.php'));

            $this->assertSame($expectedCount, substr_count($view, '<div class="employee-data-row-actions">'), $stepView);
            $this->assertSame($expectedCount, substr_count($view, '<i class="fa fa-plus"></i>'), $stepView);
            $this->assertSame($expectedCount, substr_count($view, '<i class="fa fa-trash"></i>'), $stepView);
            $this->assertStringNotContainsString('>Add</a>', $view);
            $this->assertStringNotContainsString('>Remove</a>', $view);
        }

        $profile = (string) file_get_contents(resource_path('views/profile.blade.php'));
        $wizardStyles = (string) file_get_contents(resource_path('views/employee_data/wizard/styles.blade.php'));

        $this->assertStringContainsString("@include('employee_data.wizard.wizard')", $profile);
        $this->assertStringContainsString("@include('employee_data.wizard.styles')", $profile);
        $this->assertStringContainsString('.employee-data-row-actions {', $wizardStyles);
        $this->assertStringContainsString('flex-wrap: nowrap;', $wizardStyles);
    }
}
