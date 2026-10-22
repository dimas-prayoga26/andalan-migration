<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\EmployeeDeployment;
use App\Models\EmployeeIdentity;
use App\Models\EmployeeProfile;
use App\Models\Position;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AuthorizationEmployeeStoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_created_employee_user_is_assigned_staff_role(): void
    {
        $manager = $this->createAuthorizationManager();

        $response = $this->actingAs($manager)->post(route('authorization.store'), [
            'is_active' => '1',
            'employee_code' => 'EMP-011830',
            'nickname' => 'Shiddiq',
            'email' => 'mnshiddiq.01@example.test',
        ]);

        $createdUser = User::query()
            ->where('email', 'mnshiddiq.01@example.test')
            ->firstOrFail();
        $staffRole = Role::query()
            ->where('name', 'Staff')
            ->where('guard_name', 'web')
            ->firstOrFail();

        $response->assertRedirect(route('authorization.edit', ['employee' => $createdUser->employee]).'#employee-data');
        $this->assertTrue(Hash::check('password', $createdUser->password));
        $this->assertTrue($createdUser->hasRole('Staff'));
        $this->assertDatabaseHas('model_has_roles', [
            'role_id' => $staffRole->uuid,
            'model_type' => User::class,
            'model_uuid' => $createdUser->id,
        ]);
        $this->assertSame('EMP-011830', $createdUser->employee->employee_code);
        $this->assertSame('Shiddiq', $createdUser->employee->profile->nickname);
        $this->assertNull($createdUser->employee->profile->name);
        $this->assertFalse(Schema::hasColumn('users', 'username'));
    }

    public function test_add_employee_form_splits_account_and_employee_data_tabs(): void
    {
        $manager = $this->createAuthorizationManager();

        $response = $this->actingAs($manager)->get(route('authorization.create'));

        $response->assertOk();
        $response->assertSee('<button class="nav-link active" id="employeeFormAccountTab"', false);
        $response->assertSeeInOrder([
            '1. Account &amp; Placement', 'Required',
            '2. Employee Data', 'Optional',
            'Login Account',
            'name="employee_code"', 'name="nickname"', 'name="email"', 'name="phone"',
            'Placement',
            'name="current_company_id"', 'name="current_office_location_id"', 'name="current_department_id"', 'name="current_position_ids[]"', 'name="pic_employee_id"',
            '</form>',
            'id="employeeFormData"',
            'Employee data can be completed after the account is created.',
        ], false);
        $response->assertDontSee('id="employeeDataWizard"', false);
        $response->assertSee('<form method="POST" id="employeeForm" action="'.route('authorization.store').'">', false);
        $response->assertSee('<input type="hidden" name="is_active" value="0" form="employeeForm">', false);
        $response->assertSee('<button type="submit" id="employeeFormSubmitButton" class="btn btn-success" form="employeeForm">', false);
        $response->assertSee("submitButton.classList.toggle('d-none', event.target !== accountTab);", false);
        $response->assertDontSee('id="employeeFormDocumentsTab"', false);
        $response->assertDontSee('js-document-dropzone mb-2', false);
        $response->assertDontSee('name="place_of_birth"', false);
        $response->assertDontSee('name="nik"', false);
    }

    public function test_admin_update_keeps_personal_and_identity_data_untouched(): void
    {
        $manager = $this->createAuthorizationManager();
        $staffUser = User::query()->create([
            'email' => 'staff.identity@example.test',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $staffEmployee = Employee::query()->create([
            'user_id' => $staffUser->id,
            'employee_code' => 'EMP-IDENTITY',
            'status' => 'Active',
        ]);
        EmployeeProfile::query()->create([
            'employee_id' => $staffEmployee->id,
            'nickname' => 'Identity',
            'gender' => 'Male',
            'place_of_birth' => 'Sleman',
            'date_of_birth' => '1999-05-10',
        ]);
        EmployeeIdentity::query()->create([
            'employee_id' => $staffEmployee->id,
            'nik' => '3301000000000001',
            'npwp' => '12.345.678.9-012.345',
        ]);

        $this->actingAs($manager)
            ->put(route('authorization.update', ['employee' => $staffEmployee]), [
                'is_active' => '1',
                'employee_code' => 'EMP-IDENTITY',
                'nickname' => 'Identity',
                'email' => 'staff.identity@example.test',
                'nik' => '9999999999999999',
                'gender' => 'Female',
            ])
            ->assertRedirect(route('authorization.show', ['employee' => $staffEmployee]));

        $this->assertDatabaseHas('employee_identities', [
            'employee_id' => $staffEmployee->id,
            'nik' => '3301000000000001',
            'npwp' => '12.345.678.9-012.345',
        ]);
        $this->assertDatabaseHas('employee_profiles', [
            'employee_id' => $staffEmployee->id,
            'gender' => 'Male',
            'place_of_birth' => 'Sleman',
        ]);
    }

    public function test_employee_code_and_nickname_are_required_and_employee_code_is_unique(): void
    {
        $manager = $this->createAuthorizationManager();

        $this->actingAs($manager)
            ->post(route('authorization.store'), [
                'is_active' => '1',
                'email' => 'missing.fields@example.test',
            ])
            ->assertSessionHasErrors(['employee_code', 'nickname']);

        $this->actingAs($manager)
            ->post(route('authorization.store'), [
                'is_active' => '1',
                'employee_code' => 'EMP-ADMIN',
                'nickname' => 'Duplicate',
                'email' => 'duplicate.code@example.test',
            ])
            ->assertSessionHasErrors(['employee_code']);

        $this->assertDatabaseMissing('users', ['email' => 'missing.fields@example.test']);
        $this->assertDatabaseMissing('users', ['email' => 'duplicate.code@example.test']);
    }

    public function test_updating_employee_keeps_full_name_filled_by_the_employee(): void
    {
        $manager = $this->createAuthorizationManager();
        $staffUser = User::query()->create([
            'email' => 'staff.wizard@example.test',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $staffEmployee = Employee::query()->create([
            'user_id' => $staffUser->id,
            'employee_code' => 'EMP-OLD',
            'status' => 'Active',
        ]);
        EmployeeProfile::query()->create([
            'employee_id' => $staffEmployee->id,
            'name' => 'Nama Lengkap Dari Wizard',
            'nickname' => 'Wizard',
        ]);

        $this->actingAs($manager)
            ->put(route('authorization.update', ['employee' => $staffEmployee]), [
                'is_active' => '1',
                'employee_code' => 'EMP-NEW',
                'nickname' => 'Wiz',
                'email' => 'staff.wizard@example.test',
            ])
            ->assertRedirect(route('authorization.show', ['employee' => $staffEmployee]));

        $staffEmployee->refresh()->load('profile');

        $this->assertSame('EMP-NEW', $staffEmployee->employee_code);
        $this->assertSame('Wiz', $staffEmployee->profile->nickname);
        $this->assertSame('Nama Lengkap Dari Wizard', $staffEmployee->profile->name);
    }

    public function test_update_employee_form_has_documents_tab_for_hr_uploads(): void
    {
        $manager = $this->createAuthorizationManager();
        $staffUser = User::query()->create([
            'email' => 'staff.documents@example.test',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $staffEmployee = Employee::query()->create([
            'user_id' => $staffUser->id,
            'employee_code' => 'EMP-DOCS',
            'status' => 'Active',
        ]);

        $response = $this->actingAs($manager)->get(route('authorization.edit', ['employee' => $staffEmployee]));

        $response->assertOk();
        $response->assertSeeInOrder([
            '1. Account &amp; Placement',
            '2. Employee Data',
            'id="employeeFormDocumentsTab"', '3. Documents', 'Optional',
            'id="employeeFormDocuments"',
            'Dokumen Digital',
            'Dokumen Perusahaan', 'Khusus HR', 'Scan Perjanjian Kerja (PKWT/PKWTT)', 'Klik atau seret file ke sini',
        ], false);
        $this->assertSame(14, substr_count($response->getContent(), 'js-document-dropzone mb-2'));
        $response->assertSee('data-upload-url="'.route('authorization.employee-documents.store', ['employee' => $staffEmployee]).'"', false);
        $response->assertSee('function renderFiles', false);
    }

    private function createAuthorizationManager(): User
    {
        $manager = User::query()->create([
            'email' => 'superadmin@example.test',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);

        $staffRole = Role::query()->firstOrCreate([
            'name' => 'Staff',
            'guard_name' => 'web',
        ]);

        $manager->assignRole($staffRole);
        $position = Position::query()->create([
            'name' => 'Super Administrator',
        ]);

        $employee = Employee::query()->create([
            'user_id' => $manager->id,
            'employee_code' => 'EMP-ADMIN',
            'status' => 'Active',
        ]);

        EmployeeProfile::query()->create([
            'employee_id' => $employee->id,
            'name' => 'Super Administrator',
        ]);

        EmployeeDeployment::query()->create([
            'employee_id' => $employee->id,
            'current_position_id' => $position->id,
            'status' => 'Active',
        ]);

        return $manager;
    }
}
