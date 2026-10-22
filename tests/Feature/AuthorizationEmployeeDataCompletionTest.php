<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Employee;
use App\Models\EmployeeDeployment;
use App\Models\EmployeeEducation;
use App\Models\EmployeeIdentity;
use App\Models\EmployeeProfile;
use App\Models\Position;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class AuthorizationEmployeeDataCompletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_detail_lays_out_employee_data_values_and_completion(): void
    {
        $company = Company::query()->create(['name' => 'KMA']);
        $administrator = $this->createEmployeeUser('Main Superuser', 'main.superuser', $company, Position::query()->create(['name' => 'Super Administrator']));
        $staff = $this->createEmployeeUser('Ahmad Fahmil Ulumi', 'fahmil', $company, Position::query()->create(['name' => 'Staff']));
        $staffEmployee = $staff->employee;

        $staffEmployee->profile->update(['place_of_birth' => 'Sleman', 'height' => 170]);
        EmployeeIdentity::query()->create(['employee_id' => $staffEmployee->id, 'nik' => '011830']);
        EmployeeEducation::query()->create([
            'employee_id' => $staffEmployee->id,
            'institution_name' => 'Universitas Gadjah Mada',
            'major' => 'Teknik Informatika',
            'start_year' => 2017,
            'graduation_year' => 2021,
        ]);
        DB::table('employee_families')->insert([
            'id' => (string) Str::uuid(),
            'employee_id' => $staffEmployee->id,
            'name' => 'Siti Aminah',
            'relationship' => 'Ibu',
            'phone_number' => '081200000000',
            'is_emergency_contact' => true,
        ]);

        $this->travelTo(Carbon::parse('2026-10-21 10:00', 'Asia/Jakarta'));

        $response = $this->actingAs($administrator)->get(route('authorization.show', ['employee' => $staffEmployee]));

        $response->assertOk();
        $response->assertSeeInOrder([
            'Ahmad Fahmil Ulumi',
            'Staff',
            'Attendance (October)', '0/22',
            'Report (October)', '0',
            'KPI', '-',
            'KMA',
            '>Personal</button>',
            '>Employment</button>',
            '>Financial</button>',
            '>Family</button>',
            '>Education &amp; Experience</button>',
            '>Company Assets</button>',
            '>Attachments</button>',
            'Personal Information',
            'Full Name', 'Ahmad Fahmil Ulumi',
            'Place &amp; Date of Birth', 'Sleman',
            'Gender', 'Not filled yet',
            'NIK KTP', '011830',
            'Family Card Number (KK)', 'Not filled yet',
            'Physical &amp; Medical Information',
            'Height', '170 cm',
            'Employment &amp; Organizational Data',
            'Company', 'KMA',
            'Job Title', 'Staff',
            'Employment Type', 'PKWTT (Permanent)',
            'Contract End Date', 'No End Date',
            'Core Staff', 'No',
            'Financial &amp; Tax Information (Payroll)',
            'Employment BPJS',
            'Healthcare BPJS',
            'Bank Account',
            'Account Number', 'Not filled yet',
            'Family &amp; Emergency Contacts',
            'Emergency Contact 1', 'Siti Aminah (Ibu) - 081200000000',
            'Family Members', 'No data available.',
            'Education &amp; Professional Experience',
            'Educational Background', 'Universitas Gadjah Mada', 'Teknik Informatika', '2017 – 2021',
            'Certifications', 'No data available.',
            'Company Assets &amp; Facilities',
            'Hardware', 'Brand', 'Spec', 'PIN', 'No data available.',
            'Dokumen Digital', '0/7 Dokumen Wajib Terunggah',
            'Dokumen Identitas &amp; Legalitas Pribadi', 'Scan KTP', 'Wajib', 'Klik atau seret file ke sini',
            'Dokumen Perusahaan', 'Khusus HR', 'Scan Perjanjian Kerja (PKWT/PKWTT)', 'Klik atau seret file ke sini',
        ], false);
        $response->assertDontSee('Data Completion');
        $response->assertDontSee('role="progressbar"', false);
        $response->assertDontSee(route('authorization.edit', ['employee' => $staffEmployee]), false);
        $response->assertDontSee('btn btn-light w-100 mt-2">Back</a>', false);
    }

    private function createEmployeeUser(string $name, string $username, Company $company, Position $position): User
    {
        $user = User::query()->create([
            'email' => $username.'@example.test',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $employee = Employee::query()->create([
            'user_id' => $user->id,
            'status' => 'Active',
        ]);
        EmployeeProfile::query()->create([
            'employee_id' => $employee->id,
            'name' => $name,
        ]);
        EmployeeDeployment::query()->create([
            'employee_id' => $employee->id,
            'current_company_id' => $company->id,
            'current_position_id' => $position->id,
            'status' => 'Active',
        ]);

        return $user->load('employee.profile');
    }
}
