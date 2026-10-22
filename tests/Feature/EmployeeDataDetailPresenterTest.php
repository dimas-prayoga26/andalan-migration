<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\CompanyAsset;
use App\Models\Employee;
use App\Models\EmployeeAddress;
use App\Models\EmployeeCertification;
use App\Models\EmployeeDeployment;
use App\Models\EmployeeLicense;
use App\Models\EmployeeProfile;
use App\Models\User;
use App\Support\Employee\EmployeeDataCompletionCalculator;
use App\Support\Employee\EmployeeDataDetailPresenter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class EmployeeDataDetailPresenterTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_formats_values_into_template_tabs_and_flags_required_fields(): void
    {
        $this->travelTo(Carbon::parse('2026-10-21 10:00', 'Asia/Jakarta'));

        $user = User::query()->create([
            'email' => 'dimas@example.com',
            'phone' => '081234567890',
            'password' => 'password',
            'is_active' => true,
        ]);
        $employee = Employee::query()->create(['user_id' => $user->id, 'employee_code' => '162537', 'status' => 'Active']);
        EmployeeProfile::query()->create([
            'employee_id' => $employee->id,
            'name' => 'Dimas Prayoga',
            'place_of_birth' => 'Semarang',
            'date_of_birth' => '2000-06-10',
            'weight' => 65,
            'hard_skills' => ['Laravel', 'MySQL'],
        ]);
        EmployeeDeployment::query()->create([
            'employee_id' => $employee->id,
            'current_company_id' => Company::query()->create(['name' => 'RNB Management'])->id,
            'join_date' => '2025-06-12',
            'is_contract' => true,
            'contract_end_date' => '2027-06-01',
            'status' => 'Active',
        ]);
        EmployeeAddress::query()->create([
            'employee_id' => $employee->id,
            'type' => EmployeeDataCompletionCalculator::KTP_ADDRESS_TYPE,
            'address_line' => 'Jl. Magelang KM 5',
            'regency' => 'Sleman',
            'postal_code' => '55581',
        ]);
        EmployeeAddress::query()->create(['employee_id' => $employee->id, 'address_line' => 'Jl. Kaliurang No. 10']);
        DB::table('employee_bank_accounts')->insert([
            ['id' => (string) Str::uuid(), 'employee_id' => $employee->id, 'bank_name' => 'Mandiri', 'account_number' => '999', 'is_primary' => false],
            ['id' => (string) Str::uuid(), 'employee_id' => $employee->id, 'bank_name' => 'BCA', 'account_number' => '123', 'is_primary' => true],
        ]);
        DB::table('employee_families')->insert([
            ['id' => (string) Str::uuid(), 'employee_id' => $employee->id, 'name' => 'Siti', 'relationship' => 'Ibu', 'phone_number' => '0811', 'is_emergency_contact' => true, 'is_dependents' => false],
            ['id' => (string) Str::uuid(), 'employee_id' => $employee->id, 'name' => 'Rina', 'relationship' => 'Adik', 'phone_number' => null, 'is_emergency_contact' => false, 'is_dependents' => true],
        ]);

        $sections = collect(app(EmployeeDataDetailPresenter::class)->forEmployee($employee))->keyBy('key');
        $groupOf = fn (string $sectionKey, string $groupTitle): array => collect($sections[$sectionKey]['groups'])->firstWhere('title', $groupTitle);
        $fieldOf = fn (string $sectionKey, string $groupTitle, string $label): array => collect($groupOf($sectionKey, $groupTitle)['fields'])->firstWhere('label', $label);
        $valueOf = fn (string $sectionKey, string $groupTitle, string $label): ?string => $fieldOf($sectionKey, $groupTitle, $label)['value'];
        $tableOf = fn (string $sectionKey, string $groupTitle, string $title): array => collect($groupOf($sectionKey, $groupTitle)['tables'])->firstWhere('title', $title);

        $this->assertSame(['personal', 'employment', 'financial', 'family', 'experience', 'assets', 'attachments'], $sections->keys()->all());
        $this->assertSame(['Personal', 'Employment', 'Financial', 'Family', 'Education & Experience', 'Company Assets', 'Attachments'], $sections->pluck('title')->all());

        $this->assertSame('Semarang, 10 Jun 2000 (26 y.o)', $valueOf('personal', 'Personal Information', 'Place & Date of Birth'));
        $this->assertNull($valueOf('personal', 'Personal Information', 'Religion'));
        $this->assertTrue($fieldOf('personal', 'Personal Information', 'Religion')['is_required']);
        $this->assertFalse($fieldOf('personal', 'Personal Information', 'Nickname')['is_required']);
        $this->assertSame('Jl. Magelang KM 5, Sleman 55581', $valueOf('personal', 'Personal Information', 'Permanent Address'));
        $this->assertSame('Jl. Kaliurang No. 10', $valueOf('personal', 'Personal Information', 'Current Residential Address'));
        $this->assertSame('081234567890', $valueOf('personal', 'Personal Information', 'Phone Number'));
        $this->assertSame('65 kg', $valueOf('personal', 'Physical & Medical Information', 'Weight'));

        $this->assertSame('162537', $valueOf('employment', 'Employment & Organizational Data', 'Employee ID'));
        $this->assertSame('RNB Management', $valueOf('employment', 'Employment & Organizational Data', 'Company'));
        $this->assertSame('PKWT (Contract)', $valueOf('employment', 'Employment & Organizational Data', 'Employment Type'));
        $this->assertSame('12 Jun 2025 (1 year, 4 months)', $valueOf('employment', 'Employment & Organizational Data', 'Join Date'));
        $this->assertSame('01 Jun 2027', $valueOf('employment', 'Employment & Organizational Data', 'Contract End Date'));
        $this->assertNull($valueOf('employment', 'Employment & Organizational Data', 'Probation End Date'));
        $this->assertSame('dimas@example.com', $valueOf('employment', 'Employment & Organizational Data', 'Work Email'));
        $this->assertSame('No', $valueOf('employment', 'Employment & Organizational Data', 'Core Staff'));

        $this->assertSame('1', $valueOf('financial', 'Financial & Tax Information (Payroll)', 'Number of Dependents'));
        $this->assertSame('BCA', $valueOf('financial', 'Bank Account', 'Bank Name'));
        $this->assertSame('123', $valueOf('financial', 'Bank Account', 'Account Number'));

        $this->assertSame('Siti (Ibu) - 0811', $valueOf('family', 'Family & Emergency Contacts', 'Emergency Contact 1'));
        $this->assertSame([['Rina', '-', '-', 'Adik', '-', '-']], $tableOf('family', 'Family & Emergency Contacts', 'Family Members')['rows']);

        $this->assertTrue($tableOf('experience', 'Education & Professional Experience', 'Educational Background')['is_required']);
        $this->assertSame([], $tableOf('experience', 'Education & Professional Experience', 'Educational Background')['rows']);
        $this->assertSame('Laravel, MySQL', $valueOf('experience', 'Skills', 'Hard Skills'));
    }

    public function test_company_assets_tab_lists_only_assets_held_by_the_employee(): void
    {
        $employee = Employee::factory()->create();
        $company = Company::factory()->create();
        CompanyAsset::factory()->for($company)->assignedTo($employee)->create([
            'name' => 'Laptop',
            'specification' => 'Lenovo ThinkPad E14',
            'serial_number' => 'SN-001',
            'handover_date' => '2026-06-01',
        ]);
        CompanyAsset::factory()->for($company)->assignedTo($employee)->create([
            'name' => 'ID Card',
            'specification' => null,
            'serial_number' => null,
            'handover_date' => '2026-05-01',
        ]);
        CompanyAsset::factory()->for($company)->assignedTo(Employee::factory()->create())->create(['name' => 'Monitor']);
        CompanyAsset::factory()->for($company)->create(['name' => 'Spare Laptop']);

        $sections = collect(app(EmployeeDataDetailPresenter::class)->forEmployee($employee))->keyBy('key');
        $assetTable = $sections['assets']['groups'][0]['tables'][0];

        $this->assertSame('Assets Held', $assetTable['title']);
        $this->assertSame([
            ['ID Card', '-', '-', '01 May 2026'],
            ['Laptop', 'Lenovo ThinkPad E14', 'SN-001', '01 Jun 2026'],
        ], $assetTable['rows']);
    }

    public function test_attachments_tab_lists_certification_and_license_files_by_document_key(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('employee-documents/bnsp.pdf', str_repeat('a', 2048));

        $user = User::query()->create([
            'email' => 'documented.staff@example.com',
            'password' => 'password',
            'is_active' => true,
        ]);
        $employee = Employee::query()->create(['user_id' => $user->id, 'status' => 'Active']);
        EmployeeCertification::query()->create([
            'employee_id' => $employee->id,
            'certificate_name' => 'Web Programmer',
            'issuer' => 'BNSP',
            'document_path' => 'storage/employee-documents/bnsp.pdf',
            'sequence' => 1,
        ]);
        EmployeeLicense::query()->create([
            'employee_id' => $employee->id,
            'license_type' => 'SIM A',
            'document_path' => 'employee-documents/missing.jpg',
        ]);
        EmployeeCertification::query()->create([
            'employee_id' => $employee->id,
            'certificate_name' => 'Without File',
            'sequence' => 2,
        ]);

        $sections = collect(app(EmployeeDataDetailPresenter::class)->forEmployee($employee))->keyBy('key');
        $documentFiles = $sections['attachments']['document_files'];
        $assetTable = $sections['assets']['groups'][0]['tables'][0];

        $this->assertSame(['Asset Name', 'Specification', 'Serial Number', 'Handover Date'], $assetTable['columns']);
        $this->assertSame([], $assetTable['rows']);
        $this->assertSame(['certificates', 'licenses'], array_keys($documentFiles));
        $this->assertSame([
            [
                'id' => null,
                'name' => 'Web Programmer',
                'url' => asset('storage/employee-documents/bnsp.pdf'),
                'download_url' => asset('storage/employee-documents/bnsp.pdf'),
                'delete_url' => null,
                'size' => '2.0 KB',
                'icon' => 'assets/images/files/pdf.avif',
                'is_image' => false,
                'uploaded_at' => null,
            ],
        ], $documentFiles['certificates']);
        $this->assertSame([
            [
                'id' => null,
                'name' => 'SIM A',
                'url' => null,
                'download_url' => null,
                'delete_url' => null,
                'size' => null,
                'icon' => 'assets/images/files/folder.avif',
                'is_image' => false,
                'uploaded_at' => null,
            ],
        ], $documentFiles['licenses']);
    }

    public function test_permanent_employee_without_emergency_contact_is_flagged(): void
    {
        $user = User::query()->create([
            'email' => 'permanent.staff@example.com',
            'password' => 'password',
            'is_active' => true,
        ]);
        $employee = Employee::query()->create(['user_id' => $user->id, 'status' => 'Active']);
        EmployeeDeployment::query()->create([
            'employee_id' => $employee->id,
            'join_date' => now()->subMonth()->toDateString(),
            'probation_end_date' => now()->addMonths(2)->toDateString(),
            'status' => 'Active',
        ]);

        $sections = collect(app(EmployeeDataDetailPresenter::class)->forEmployee($employee))->keyBy('key');
        $employmentFields = collect($sections['employment']['groups'][0]['fields'])->keyBy('label');
        $familyFields = collect($sections['family']['groups'][0]['fields']);

        $this->assertSame('PKWTT (Permanent)', $employmentFields['Employment Type']['value']);
        $this->assertSame('No End Date', $employmentFields['Contract End Date']['value']);
        $this->assertSame(now()->addMonths(2)->format('d M Y'), $employmentFields['Probation End Date']['value']);
        $this->assertSame([['label' => 'Emergency Contact', 'value' => null, 'is_required' => true]], $familyFields->all());
    }
}
