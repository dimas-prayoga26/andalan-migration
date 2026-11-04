<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\CompanyAsset;
use App\Models\EducationLevel;
use App\Models\Employee;
use App\Models\EmployeeAddress;
use App\Models\EmployeeBankAccount;
use App\Models\EmployeeCertification;
use App\Models\EmployeeCompensation;
use App\Models\EmployeeDeployment;
use App\Models\EmployeeEducation;
use App\Models\EmployeeFamily;
use App\Models\EmployeeIdentity;
use App\Models\EmployeeLicense;
use App\Models\EmployeeMedicalCheckup;
use App\Models\EmployeeProfile;
use App\Models\Position;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class EmployeeDataWizardSaveTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_saves_personal_step_with_ktp_and_domicile_addresses(): void
    {
        [$staffUser, $staffEmployee] = $this->createStaff('personal.staff');
        $legacyAddress = EmployeeAddress::query()->create([
            'employee_id' => $staffEmployee->id,
            'address_line' => 'Alamat lama tanpa tipe',
        ]);

        $response = $this->actingAs($staffUser)->putJson(route('profile.employee-data.update', ['employeeDataStep' => 'personal']), [
            'name' => 'Dimas Prayoga',
            'nickname' => 'Dimas',
            'place_of_birth' => 'Sleman',
            'date_of_birth' => '2000-06-10',
            'gender' => 'Male',
            'religion' => 'Islam',
            'marital_status' => 'Lajang',
            'nationality' => 'WNI',
            'height' => '170',
            'weight' => '65.5',
            'blood_type' => 'O',
            'phone' => '081234567890',
            'personal_email' => 'dimas.pribadi@example.test',
            'linkedin_url' => 'https://www.linkedin.com/in/dimas',
            'addresses' => [
                'ktp' => [
                    'province' => 'DAERAH ISTIMEWA YOGYAKARTA',
                    'regency' => 'KABUPATEN SLEMAN',
                    'subdistrict' => 'DEPOK',
                    'village' => 'CONDONGCATUR',
                    'address_line' => 'Jl. Kaliurang KM 5 RT 01/RW 02',
                ],
            ],
            'domicile_same_as_ktp' => '1',
        ]);

        $response->assertOk()
            ->assertJsonPath('message', 'Data berhasil disimpan.')
            ->assertJsonPath('is_employee_data_complete', false);

        $profile = EmployeeProfile::query()->where('employee_id', $staffEmployee->id)->firstOrFail();

        $this->assertSame('Dimas Prayoga', $profile->name);
        $this->assertSame('Sleman', $profile->place_of_birth);
        $this->assertSame('2000-06-10', $profile->date_of_birth);
        $this->assertSame('Male', $profile->gender);
        $this->assertSame('Lajang', $profile->marital_status);
        $this->assertSame(170, (int) $profile->height);
        $this->assertSame('081234567890', $staffUser->fresh()->phone);
        $this->assertDatabaseHas('employee_addresses', [
            'employee_id' => $staffEmployee->id,
            'type' => 'ktp',
            'regency' => 'KABUPATEN SLEMAN',
            'address_line' => 'Jl. Kaliurang KM 5 RT 01/RW 02',
        ]);
        $this->assertDatabaseHas('employee_addresses', [
            'id' => $legacyAddress->id,
            'type' => 'domicile',
            'village' => 'CONDONGCATUR',
            'address_line' => 'Jl. Kaliurang KM 5 RT 01/RW 02',
        ]);
        $this->assertSame(2, EmployeeAddress::query()->where('employee_id', $staffEmployee->id)->count());
    }

    public function test_personal_step_rejects_invalid_values_with_indonesian_messages(): void
    {
        [$staffUser] = $this->createStaff('invalid.staff');

        $this->actingAs($staffUser)
            ->putJson(route('profile.employee-data.update', ['employeeDataStep' => 'personal']), [
                'date_of_birth' => now()->addDay()->toDateString(),
                'gender' => 'Unknown',
                'personal_email' => 'bukan-email',
                'linkedin_url' => 'linkedin.com/in/dimas',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['date_of_birth', 'gender', 'personal_email', 'linkedin_url'])
            ->assertJsonPath('errors.personal_email.0', 'Email Pribadi harus berupa alamat email yang valid.');
    }

    public function test_staff_saves_legal_step_and_syncs_license_rows_by_id(): void
    {
        [$staffUser, $staffEmployee] = $this->createStaff('legal.staff');
        $keptLicense = EmployeeLicense::query()->create([
            'employee_id' => $staffEmployee->id,
            'license_type' => 'SIM C',
            'license_number' => 'OLD-1',
            'document_path' => 'employee-documents/sim-c.pdf',
        ]);
        $removedLicense = EmployeeLicense::query()->create([
            'employee_id' => $staffEmployee->id,
            'license_type' => 'SIO',
        ]);

        $response = $this->actingAs($staffUser)->putJson(route('profile.employee-data.update', ['employeeDataStep' => 'legal']), [
            'nik' => '3404010101000001',
            'kk' => '3404010101000002',
            'mother_name' => 'Siti Aminah',
            'npwp' => '12.345.678.9-012.345',
            'bpjs_kesehatan' => '0001234567890',
            'bank' => ['bank_name' => 'BCA', 'account_number' => '1234567890', 'account_holder_name' => 'Dimas Prayoga'],
            'licenses' => [
                ['id' => (string) $keptLicense->id, 'license_type' => 'SIM C', 'license_number' => 'NEW-1', 'expired_at' => '2030-01-01'],
                ['id' => '', 'license_type' => 'SIM A', 'license_number' => 'SIM-A-1', 'expired_at' => ''],
                ['id' => '', 'license_type' => '', 'license_number' => '', 'expired_at' => ''],
            ],
        ]);

        $response->assertOk();

        $licenseIds = $response->json('row_ids.licenses');

        $this->assertCount(2, $licenseIds);
        $this->assertSame((string) $keptLicense->id, $licenseIds[0]);
        $this->assertDatabaseHas('employee_licenses', [
            'id' => $keptLicense->id,
            'license_number' => 'NEW-1',
            'document_path' => 'employee-documents/sim-c.pdf',
        ]);
        $this->assertDatabaseHas('employee_licenses', ['id' => $licenseIds[1], 'license_type' => 'SIM A']);
        $this->assertDatabaseMissing('employee_licenses', ['id' => $removedLicense->id]);
        $this->assertDatabaseHas('employee_identities', [
            'employee_id' => $staffEmployee->id,
            'nik' => '3404010101000001',
            'mother_name' => 'Siti Aminah',
        ]);
        $this->assertDatabaseHas('employee_bank_accounts', [
            'employee_id' => $staffEmployee->id,
            'bank_name' => 'BCA',
            'account_number' => '1234567890',
            'is_primary' => true,
        ]);

        $this->actingAs($staffUser)
            ->putJson(route('profile.employee-data.update', ['employeeDataStep' => 'legal']), [
                'nik' => '123',
                'bank' => ['account_number' => '12-34'],
                'licenses' => [['license_number' => 'Tanpa Jenis']],
            ])
            ->assertUnprocessable()
            ->assertJsonPath('errors.nik.0', 'NIK harus 16 digit angka.')
            ->assertJsonValidationErrors(['bank.account_number', 'licenses.0.license_type']);

        $this->assertSame(1, EmployeeBankAccount::query()->where('employee_id', $staffEmployee->id)->count());
    }

    public function test_staff_saves_education_step_rows_and_skills(): void
    {
        [$staffUser, $staffEmployee] = $this->createStaff('education.staff');
        $educationLevel = EducationLevel::query()->create(['name' => 'Strata 1']);
        $certification = EmployeeCertification::query()->create([
            'employee_id' => $staffEmployee->id,
            'certificate_name' => 'K3 Umum',
            'document_path' => 'storage/employee-documents/k3.pdf',
            'sequence' => 1,
        ]);

        $this->actingAs($staffUser)->putJson(route('profile.employee-data.update', ['employeeDataStep' => 'education']), [
            'educations' => [
                ['id' => '', 'education_level_id' => (string) $educationLevel->id, 'institution_name' => 'Universitas Gadjah Mada', 'major' => 'Teknik Informatika', 'city' => 'Yogyakarta', 'start_year' => '2017', 'graduation_year' => '2021', 'gpa' => '3.65'],
                ['id' => '', 'institution_name' => 'SMA N 1 Sleman', 'graduation_year' => '2017'],
            ],
            'certifications' => [
                ['id' => (string) $certification->id, 'certificate_name' => 'K3 Umum', 'issuer' => 'Kemnaker', 'effective_date' => '2024-01-01', 'expired_at' => '2027-01-01', 'city' => 'Jakarta'],
            ],
            'work_experiences' => [
                ['company_name' => 'PT Contoh', 'last_position' => 'Web Developer', 'duration_text' => '2 tahun', 'resignation_reason' => 'Kontrak selesai'],
            ],
            'references' => [
                ['name' => 'Budi', 'position' => 'Manager', 'company_name' => 'PT Contoh', 'phone_number' => '0812 3456 7890'],
            ],
            'languages' => 'Indonesia, Inggris, Indonesia',
            'hard_skills' => 'Laravel, MySQL',
            'soft_skills' => ['Teamwork', 'Leadership'],
        ])->assertOk();

        $this->assertSame(
            ['Universitas Gadjah Mada', 'SMA N 1 Sleman'],
            EmployeeEducation::query()->where('employee_id', $staffEmployee->id)->orderBy('sequence')->pluck('institution_name')->all()
        );
        $this->assertDatabaseHas('employee_educations', ['institution_name' => 'Universitas Gadjah Mada', 'education_level_id' => $educationLevel->id, 'sequence' => 1]);
        $this->assertDatabaseHas('employee_certifications', [
            'id' => $certification->id,
            'issuer' => 'Kemnaker',
            'document_path' => 'storage/employee-documents/k3.pdf',
        ]);
        $this->assertDatabaseHas('employee_work_experiences', ['employee_id' => $staffEmployee->id, 'company_name' => 'PT Contoh', 'sequence' => 1]);
        $this->assertDatabaseHas('employee_references', ['employee_id' => $staffEmployee->id, 'name' => 'Budi']);

        $profile = EmployeeProfile::query()->where('employee_id', $staffEmployee->id)->firstOrFail();

        $this->assertSame(['Indonesia', 'Inggris'], $profile->languages);
        $this->assertSame(['Laravel', 'MySQL'], $profile->hard_skills);
        $this->assertSame(['Teamwork', 'Leadership'], $profile->soft_skills);
    }

    public function test_family_step_splits_family_members_and_emergency_contacts_and_completes_the_data(): void
    {
        [$staffUser, $staffEmployee] = $this->createStaff('family.staff');
        $this->fillRequiredDataExceptFamily($staffUser, $staffEmployee);

        $response = $this->actingAs($staffUser)->putJson(route('profile.employee-data.update', ['employeeDataStep' => 'family']), [
            'families' => [
                ['name' => 'Rina', 'date_of_birth' => '2020-01-01', 'relationship' => 'Anak'],
                ['name' => 'Siti Aminah', 'occupation' => 'Guru', 'relationship' => 'Ibu'],
            ],
            'emergency_contacts' => [
                ['name' => 'Siti Aminah', 'phone_number' => '081200000000', 'relationship' => 'Ibu'],
            ],
        ]);

        $response->assertOk()->assertJsonPath('is_employee_data_complete', true);
        $this->assertCount(2, $response->json('row_ids.families'));
        $this->assertDatabaseHas('employee_families', ['employee_id' => $staffEmployee->id, 'name' => 'Rina', 'is_dependents' => true, 'is_emergency_contact' => false]);
        $this->assertDatabaseHas('employee_families', ['employee_id' => $staffEmployee->id, 'name' => 'Siti Aminah', 'gender' => 'Female', 'is_dependents' => false, 'is_emergency_contact' => false]);
        $this->assertDatabaseHas('employee_families', ['employee_id' => $staffEmployee->id, 'phone_number' => '081200000000', 'is_emergency_contact' => true]);

        $this->actingAs($staffUser)
            ->putJson(route('profile.employee-data.update', ['employeeDataStep' => 'family']), [
                'families' => [],
                'emergency_contacts' => [['name' => 'Tanpa Nomor', 'relationship' => 'Ayah']],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['emergency_contacts.0.phone_number']);

        $this->actingAs($staffUser)
            ->putJson(route('profile.employee-data.update', ['employeeDataStep' => 'family']), [])
            ->assertOk()
            ->assertJsonPath('is_employee_data_complete', false);

        $this->assertSame(0, EmployeeFamily::query()->where('employee_id', $staffEmployee->id)->count());
    }

    public function test_staff_cannot_save_the_employment_step_from_the_profile(): void
    {
        [$staffUser, $staffEmployee] = $this->createStaff('employment.staff');

        $this->actingAs($staffUser)
            ->putJson('/profile/employee-data/employment', ['employment_status' => 'PKWT', 'end_date' => '2030-01-01'])
            ->assertNotFound();

        $this->assertFalse(EmployeeDeployment::query()->where('employee_id', $staffEmployee->id)->value('is_contract'));
    }

    public function test_hr_saves_employee_data_steps_without_touching_account_fields(): void
    {
        $manager = $this->createAuthorizationManager();
        [$staffUser, $staffEmployee] = $this->createStaff('managed.staff');
        EmployeeProfile::query()->create(['employee_id' => $staffEmployee->id, 'nickname' => 'Managed']);
        $staffUser->forceFill(['phone' => '080000000000'])->save();

        $this->actingAs($manager)
            ->putJson(route('authorization.employee-data.update', ['employee' => $staffEmployee, 'employeeDataStep' => 'personal']), [
                'name' => 'Nama Dari HR',
                'religion' => 'Hindu',
            ])
            ->assertOk();

        $profile = EmployeeProfile::query()->where('employee_id', $staffEmployee->id)->firstOrFail();

        $this->assertSame('Nama Dari HR', $profile->name);
        $this->assertSame('Managed', $profile->nickname);
        $this->assertSame('080000000000', $staffUser->fresh()->phone);
    }

    public function test_hr_saves_employment_step_contract_compensation_mcu_and_assets(): void
    {
        $manager = $this->createAuthorizationManager();
        [, $staffEmployee] = $this->createStaff('contract.staff');
        $company = Company::factory()->create(['name' => 'RNB Management']);
        EmployeeDeployment::query()->where('employee_id', $staffEmployee->id)->update([
            'current_company_id' => $company->id,
            'join_date' => '2025-01-01',
        ]);
        $returnedAsset = CompanyAsset::factory()->for($company)->assignedTo($staffEmployee)->create(['name' => 'Monitor Lama']);
        $employmentUrl = route('authorization.employee-data.update', ['employee' => $staffEmployee, 'employeeDataStep' => 'employment']);

        $response = $this->actingAs($manager)->putJson($employmentUrl, [
            'employment_status' => 'PKWT',
            'join_date' => '2026-01-05',
            'end_date' => '2027-01-04',
            'base_salary' => '8.500.000',
            'allowance' => 'Rp 1.250.000',
            'medical_checkups' => [
                ['checkup_date' => '2026-01-02', 'clinic_name' => 'Klinik Pratama', 'result_status' => 'fit'],
            ],
            'assets' => [
                ['name' => 'Laptop', 'specification' => 'ThinkPad E14', 'serial_number' => 'SN-001', 'handover_date' => '2026-01-05'],
            ],
        ]);

        $response->assertOk();

        $deployment = EmployeeDeployment::query()->where('employee_id', $staffEmployee->id)->firstOrFail();
        $compensation = EmployeeCompensation::query()->where('employee_id', $staffEmployee->id)->firstOrFail();

        $this->assertTrue($deployment->is_contract);
        $this->assertSame('2026-01-05', $deployment->join_date->toDateString());
        $this->assertSame('2027-01-04', $deployment->contract_end_date->toDateString());
        $this->assertSame('8500000', $compensation->base_salary);
        $this->assertSame('1250000', $compensation->allowance);
        $this->assertNotSame('8500000', DB::table('employee_compensations')->where('id', $compensation->id)->value('base_salary'));
        $this->assertDatabaseHas('employee_medical_checkups', ['employee_id' => $staffEmployee->id, 'clinic_name' => 'Klinik Pratama', 'result_status' => 'fit']);
        $this->assertDatabaseHas('company_assets', [
            'id' => $response->json('row_ids.assets.0'),
            'company_id' => $company->id,
            'employee_id' => $staffEmployee->id,
            'status' => CompanyAsset::STATUS_ASSIGNED,
        ]);
        $this->assertDatabaseHas('company_assets', [
            'id' => $returnedAsset->id,
            'employee_id' => null,
            'status' => CompanyAsset::STATUS_AVAILABLE,
        ]);

        $this->actingAs($manager)->putJson($employmentUrl, [
            'employment_status' => 'Probation',
            'join_date' => '2026-01-05',
            'end_date' => '2026-04-05',
            'medical_checkups' => [],
            'assets' => [],
        ])->assertOk();

        $deployment->refresh();

        $this->assertFalse($deployment->is_contract);
        $this->assertNull($deployment->contract_end_date);
        $this->assertSame('2026-04-05', $deployment->probation_end_date->toDateString());
        $this->assertSame(0, EmployeeMedicalCheckup::query()->where('employee_id', $staffEmployee->id)->count());

        $this->actingAs($manager)
            ->putJson($employmentUrl, ['employment_status' => 'PKWT', 'join_date' => '2026-01-05', 'end_date' => '2025-12-31'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['end_date']);
        $this->actingAs($manager)
            ->putJson($employmentUrl, ['employment_status' => 'Probation'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['end_date']);
    }

    public function test_hr_cannot_add_assets_before_the_employee_has_a_company(): void
    {
        $manager = $this->createAuthorizationManager();
        [, $staffEmployee] = $this->createStaff('no.company.staff');

        $this->actingAs($manager)
            ->putJson(route('authorization.employee-data.update', ['employee' => $staffEmployee, 'employeeDataStep' => 'employment']), [
                'assets' => [['name' => 'Laptop']],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['assets.0.name']);

        $this->assertSame(0, CompanyAsset::query()->count());
    }

    public function test_staff_without_authorization_access_cannot_save_other_employee_data(): void
    {
        [$staffUser] = $this->createStaff('regular.staff');
        [, $otherEmployee] = $this->createStaff('other.staff');

        $response = $this->actingAs($staffUser)
            ->putJson(route('authorization.employee-data.update', ['employee' => $otherEmployee, 'employeeDataStep' => 'personal']), [
                'name' => 'Diubah Orang Lain',
            ]);

        $this->assertContains($response->status(), [302, 403]);
        $this->assertDatabaseMissing('employee_profiles', ['name' => 'Diubah Orang Lain']);
    }

    public function test_profile_and_edit_pages_prefill_the_wizard_with_saved_data(): void
    {
        $manager = $this->createAuthorizationManager();
        [$staffUser, $staffEmployee] = $this->createStaff('prefill.staff');
        EmployeeProfile::query()->create(['employee_id' => $staffEmployee->id, 'name' => 'Nama Prefill', 'marital_status' => 'Married']);
        EmployeeIdentity::query()->create(['employee_id' => $staffEmployee->id, 'nik' => '3404010101000009']);
        EmployeeEducation::query()->create(['employee_id' => $staffEmployee->id, 'institution_name' => 'Politeknik Prefill', 'sequence' => 1]);

        $profileResponse = $this->actingAs($staffUser)->get(route('profile'));

        $profileResponse->assertOk()
            ->assertSee('data-save-url="'.route('profile.employee-data.update', ['employeeDataStep' => 'personal']).'"', false)
            ->assertSee('name="name" class="form-control" value="Nama Prefill"', false)
            ->assertSee('<option value="Menikah" selected>Menikah</option>', false)
            ->assertSee('name="nik" class="form-control" value="3404010101000009"', false)
            ->assertSee('value="Politeknik Prefill"', false)
            ->assertDontSee('data-employee-data-step="employment"', false);

        $this->actingAs($manager)->get(route('authorization.edit', ['employee' => $staffEmployee]))
            ->assertOk()
            ->assertSee('data-save-url="'.route('authorization.employee-data.update', ['employee' => $staffEmployee, 'employeeDataStep' => 'employment']).'"', false)
            ->assertSee('Diatur pada tab 1. Account &amp; Placement.', false)
            ->assertSee('Simpan Step 5');
    }

    /**
     * @return array{0: User, 1: Employee}
     */
    private function createStaff(string $emailName): array
    {
        $staffUser = User::query()->create([
            'email' => $emailName.'@example.test',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $staffEmployee = Employee::query()->create([
            'user_id' => $staffUser->id,
            'employee_code' => strtoupper(str_replace('.', '-', $emailName)),
            'status' => 'Active',
        ]);
        EmployeeDeployment::query()->create([
            'employee_id' => $staffEmployee->id,
            'status' => 'Active',
        ]);

        return [$staffUser, $staffEmployee];
    }

    private function fillRequiredDataExceptFamily(User $staffUser, Employee $staffEmployee): void
    {
        $staffUser->forceFill(['phone' => '081234567890'])->save();
        EmployeeProfile::query()->create([
            'employee_id' => $staffEmployee->id,
            'name' => 'Lengkap',
            'place_of_birth' => 'Sleman',
            'date_of_birth' => '2000-01-01',
            'gender' => 'Male',
            'religion' => 'Islam',
            'marital_status' => 'Lajang',
        ]);
        EmployeeAddress::query()->create(['employee_id' => $staffEmployee->id, 'type' => 'ktp', 'address_line' => 'Alamat KTP']);
        EmployeeAddress::query()->create(['employee_id' => $staffEmployee->id, 'type' => 'domicile', 'address_line' => 'Alamat Domisili']);
        EmployeeIdentity::query()->create(['employee_id' => $staffEmployee->id, 'nik' => '3404010101000001', 'kk' => '3404010101000002', 'mother_name' => 'Ibu']);
        EmployeeBankAccount::factory()->for($staffEmployee)->create(['account_number' => '1234567890']);
        EmployeeEducation::query()->create(['employee_id' => $staffEmployee->id, 'institution_name' => 'UGM', 'sequence' => 1]);
    }

    private function createAuthorizationManager(): User
    {
        $manager = User::query()->create([
            'email' => 'superadmin@example.test',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $manager->assignRole(Role::query()->firstOrCreate(['name' => 'Staff', 'guard_name' => 'web']));
        $managerEmployee = Employee::query()->create([
            'user_id' => $manager->id,
            'employee_code' => 'EMP-ADMIN',
            'status' => 'Active',
        ]);
        EmployeeProfile::query()->create(['employee_id' => $managerEmployee->id, 'name' => 'Super Administrator']);
        EmployeeDeployment::query()->create([
            'employee_id' => $managerEmployee->id,
            'current_position_id' => Position::query()->create(['name' => 'Super Administrator'])->id,
            'status' => 'Active',
        ]);

        return $manager;
    }
}
