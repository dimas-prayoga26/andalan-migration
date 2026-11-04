<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\EmployeeAddress;
use App\Models\EmployeeEducation;
use App\Models\EmployeeIdentity;
use App\Models\EmployeeProfile;
use App\Models\User;
use App\Support\Employee\EmployeeDataCompletionCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class EmployeeDataCompletionCalculatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_without_data_is_zero_percent_with_every_required_item_missing(): void
    {
        $completion = $this->calculate($this->createEmployee());

        $this->assertSame(0, $completion['percent']);
        $this->assertSame(0, $completion['completed_items']);
        $this->assertSame(15, $completion['total_items']);
        $this->assertFalse($completion['is_complete']);
        $this->assertSame(['personal', 'identity', 'education', 'family'], array_column($completion['steps'], 'key'));
        $this->assertSame(['Kontak Darurat'], $completion['steps'][3]['missing_items']);
        $this->assertSame(
            ['Nama Lengkap', 'Tempat Lahir', 'Tanggal Lahir', 'Jenis Kelamin', 'Agama', 'Status Pernikahan', 'Nomor Handphone', 'Alamat KTP', 'Alamat Domisili'],
            array_column($completion['steps'][0]['items'], 'label'),
        );
    }

    public function test_fully_filled_employee_is_complete(): void
    {
        $employee = $this->createEmployee(phone: '081234567890');
        $this->fillPersonalData($employee);
        $this->fillIdentityData($employee);
        EmployeeEducation::query()->create(['employee_id' => $employee->id, 'institution_name' => 'Universitas Gadjah Mada']);
        $this->insertEmergencyContact($employee, '081200000000');

        $completion = $this->calculate($employee);

        $this->assertSame(100, $completion['percent']);
        $this->assertTrue($completion['is_complete']);
        $this->assertSame([[], [], [], []], array_column($completion['steps'], 'missing_items'));
    }

    public function test_partial_data_lists_missing_items_per_step(): void
    {
        $employee = $this->createEmployee(phone: '081234567890');
        EmployeeProfile::query()->create([
            'employee_id' => $employee->id,
            'name' => 'Dimas Prayoga',
            'gender' => 'Male',
        ]);
        EmployeeAddress::query()->create(['employee_id' => $employee->id, 'address_line' => 'Jl. Kaliurang No. 10']);
        EmployeeIdentity::query()->create(['employee_id' => $employee->id, 'nik' => '3404000000000001']);
        DB::table('employee_bank_accounts')->insert([
            'id' => (string) Str::uuid(),
            'employee_id' => $employee->id,
            'bank_name' => 'BCA',
            'account_number' => '',
        ]);
        DB::table('employee_families')->insert([
            'id' => (string) Str::uuid(),
            'employee_id' => $employee->id,
            'name' => 'Sibling Without Emergency Flag',
            'phone_number' => '081200000000',
            'is_emergency_contact' => false,
        ]);

        $completion = $this->calculate($employee);
        $stepsByKey = collect($completion['steps'])->keyBy('key');

        $this->assertSame(5, $completion['completed_items']);
        $this->assertSame(33, $completion['percent']);
        $this->assertSame(
            ['Tempat Lahir', 'Tanggal Lahir', 'Agama', 'Status Pernikahan', 'Alamat KTP'],
            $stepsByKey['personal']['missing_items'],
        );
        $this->assertSame(['Nomor Kartu Keluarga', 'Nama Ibu Kandung', 'Rekening Bank'], $stepsByKey['identity']['missing_items']);
        $this->assertSame(
            [
                ['label' => 'NIK (No KTP)', 'is_filled' => true],
                ['label' => 'Nomor Kartu Keluarga', 'is_filled' => false],
                ['label' => 'Nama Ibu Kandung', 'is_filled' => false],
                ['label' => 'Rekening Bank', 'is_filled' => false],
            ],
            $stepsByKey['identity']['items'],
        );
        $this->assertFalse($stepsByKey['family']['is_complete']);
    }

    private function calculate(Employee $employee): array
    {
        return app(EmployeeDataCompletionCalculator::class)->forEmployee($employee);
    }

    private function createEmployee(?string $phone = null): Employee
    {
        $user = User::query()->create([
            'email' => uniqid('user_', true).'@example.com',
            'phone' => $phone,
            'password' => 'password',
            'is_active' => true,
        ]);

        return Employee::query()->create([
            'user_id' => $user->id,
            'status' => 'Active',
        ]);
    }

    private function fillPersonalData(Employee $employee): void
    {
        EmployeeProfile::query()->create([
            'employee_id' => $employee->id,
            'name' => 'Dimas Prayoga',
            'place_of_birth' => 'Sleman',
            'date_of_birth' => '1999-05-10',
            'gender' => 'Male',
            'religion' => 'Islam',
            'marital_status' => 'Lajang',
        ]);

        foreach ([EmployeeDataCompletionCalculator::KTP_ADDRESS_TYPE, EmployeeDataCompletionCalculator::DOMICILE_ADDRESS_TYPE] as $addressType) {
            EmployeeAddress::query()->create([
                'employee_id' => $employee->id,
                'type' => $addressType,
                'address_line' => 'Jl. Kaliurang No. 10',
            ]);
        }
    }

    private function fillIdentityData(Employee $employee): void
    {
        EmployeeIdentity::query()->create([
            'employee_id' => $employee->id,
            'nik' => '3404000000000001',
            'kk' => '3404000000000002',
            'mother_name' => 'Siti Aminah',
        ]);

        DB::table('employee_bank_accounts')->insert([
            'id' => (string) Str::uuid(),
            'employee_id' => $employee->id,
            'bank_name' => 'BCA',
            'account_number' => '1234567890',
            'account_holder_name' => 'Dimas Prayoga',
            'is_primary' => true,
        ]);
    }

    private function insertEmergencyContact(Employee $employee, string $phoneNumber): void
    {
        DB::table('employee_families')->insert([
            'id' => (string) Str::uuid(),
            'employee_id' => $employee->id,
            'name' => 'Ibu',
            'relationship' => 'Ibu',
            'phone_number' => $phoneNumber,
            'is_emergency_contact' => true,
        ]);
    }
}
