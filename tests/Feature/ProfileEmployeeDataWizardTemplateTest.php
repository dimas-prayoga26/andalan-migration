<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Employee;
use App\Models\EmployeeDeployment;
use App\Models\EmployeePicAssignment;
use App\Models\EmployeeProfile;
use App\Models\Position;
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
        $response->assertDontSee('Kelengkapan data');
        $response->assertDontSee('role="progressbar"', false);
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
        $response->assertSee('href="#wizard_Payment"', false);
        $response->assertDontSee('href="#wizard_Finish"', false);
        $response->assertDontSee('id="wizard_Finish"', false);
        $response->assertDontSee('<span>5</span>', false);
        $response->assertDontSee('Employment &amp; Contract Details', false);
        $response->assertDontSee('Status Kepegawaian');
        $response->assertDontSee('Gaji Pokok');
        $response->assertDontSee('Riwayat MCU');
        $response->assertDontSee('Aset Perusahaan yang Dipegang');
        $response->assertDontSee('Simpan Step 5');
        $response->assertDontSee('Pilih status');
        $response->assertDontSee('NIK Karyawan');
        $response->assertDontSee('Penempatan diatur pada tab');
        $response->assertDontSee('Simpan Data');
        $response->assertDontSee('toolbarExtraButtons', false);
    }

    public function test_profile_summary_shows_position_and_pic_labels_with_the_assigned_pic(): void
    {
        $user = User::query()->create([
            'email' => 'staff.pic@example.test',
            'password' => 'password',
            'is_active' => true,
        ]);
        $employee = Employee::query()->create(['user_id' => $user->id, 'employee_code' => 'EMP-PIC', 'status' => 'Active']);
        EmployeeDeployment::query()->create([
            'employee_id' => $employee->id,
            'current_position_id' => Position::query()->create(['name' => 'Web Developer'])->id,
            'status' => 'Active',
        ]);
        $supervisorUser = User::query()->create([
            'email' => 'supervisor.pic@example.test',
            'password' => 'password',
            'is_active' => true,
        ]);
        $supervisor = Employee::query()->create(['user_id' => $supervisorUser->id, 'employee_code' => 'EMP-SPV', 'status' => 'Active']);
        EmployeeProfile::query()->create(['employee_id' => $supervisor->id, 'name' => 'Erlin Supervisor']);
        EmployeePicAssignment::query()->create([
            'staff_employee_id' => $employee->id,
            'supervisor_employee_id' => $supervisor->id,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get(route('profile'));

        $response->assertOk();
        $response->assertSeeInOrder([
            '<span class="profile-summary-label">Position</span><span class="profile-summary-value">Web Developer</span>',
            '<span class="profile-summary-label">PIC</span><span class="profile-summary-value">Erlin Supervisor</span>',
        ], false);
        $response->assertDontSee('Job Title');
        $response->assertDontSee('Atasan Langsung');
    }

    public function test_wizard_date_fields_use_single_date_picker_with_hidden_iso_value(): void
    {
        $user = User::query()->create([
            'email' => 'staff.date@example.test',
            'password' => 'password',
            'is_active' => true,
        ]);
        $employee = Employee::query()->create(['user_id' => $user->id, 'employee_code' => 'EMP-DATE', 'status' => 'Active']);
        EmployeeDeployment::query()->create(['employee_id' => $employee->id, 'status' => 'Active']);
        EmployeeProfile::query()->create(['employee_id' => $employee->id, 'name' => 'Staff Date', 'date_of_birth' => '2003-04-26']);

        $response = $this->actingAs($user)->get(route('profile'));

        $response->assertOk();
        $response->assertSee('<input type="text" class="form-control js-employee-data-date-picker" value="26/04/2003" placeholder="DD/MM/YYYY" autocomplete="off" readonly', false);
        $response->assertSee('<input type="hidden" name="date_of_birth" value="2003-04-26" data-date-value', false);
        $response->assertSee('data-row-field="date_of_birth"', false);
        $response->assertDontSee('type="date"', false);
        $response->assertSee('singleDatePicker: true', false);
        $response->assertSee('initDatePickers(newRow);', false);
        $response->assertSee('field = dateDisplayFor(field) || field;', false);
    }

    public function test_wizard_date_field_renders_empty_display_without_a_value(): void
    {
        $field = view('employee_data.wizard.field', [
            'name' => 'expired_at',
            'label' => 'Masa Berlaku',
            'type' => 'date',
            'value' => null,
            'disabled' => true,
        ])->render();

        $this->assertStringContainsString('class="form-control js-employee-data-date-picker" value="" placeholder="DD/MM/YYYY"', $field);
        $this->assertStringContainsString('<input type="hidden" name="expired_at" value="" data-date-value disabled', $field);
        $this->assertSame(2, substr_count($field, 'disabled'));
    }

    public function test_wizard_step_partials_exist(): void
    {
        foreach (['step-1-personal', 'step-2-legal-tax', 'step-3-education', 'step-4-family', 'step-5-employment'] as $stepView) {
            $this->assertTrue(View::exists('employee_data.wizard.'.$stepView), $stepView.' partial is missing.');
        }
    }

    public function test_repeatable_rows_use_compact_icon_action_buttons(): void
    {
        $expectedRepeatableGroups = [
            'step-2-legal-tax' => 1,
            'step-3-education' => 4,
            'step-4-family' => 2,
            'step-5-employment' => 2,
        ];

        foreach ($expectedRepeatableGroups as $stepView => $expectedCount) {
            $view = (string) file_get_contents(resource_path('views/employee_data/wizard/'.$stepView.'.blade.php'));

            $this->assertSame($expectedCount, substr_count($view, "@include('employee_data.wizard.repeatable'"), $stepView);
            $this->assertStringNotContainsString('>Add</a>', $view);
            $this->assertStringNotContainsString('>Remove</a>', $view);
        }

        $rowView = (string) file_get_contents(resource_path('views/employee_data/wizard/repeatable-row.blade.php'));

        $this->assertSame(1, substr_count($rowView, '<div class="employee-data-row-actions">'));
        $this->assertSame(1, substr_count($rowView, '<i class="fa fa-plus"></i>'));
        $this->assertSame(1, substr_count($rowView, '<i class="fa fa-trash"></i>'));

        $profile = (string) file_get_contents(resource_path('views/profile.blade.php'));
        $wizardStyles = (string) file_get_contents(resource_path('views/employee_data/wizard/styles.blade.php'));

        $this->assertStringContainsString("@include('employee_data.wizard.wizard', ['hasEmploymentStep' => false])", $profile);
        $this->assertStringContainsString("@include('employee_data.wizard.styles')", $profile);
        $this->assertStringContainsString('.employee-data-row-actions {', $wizardStyles);
        $this->assertStringContainsString('flex-wrap: nowrap;', $wizardStyles);
        $this->assertStringContainsString('overflow: visible !important;', $wizardStyles, 'SmartWizard must not clip open dropdown menus.');
        $this->assertStringContainsString('data-size="8"', (string) file_get_contents(resource_path('views/employee_data/wizard/field.blade.php')));
    }
}
