<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\CompanyAsset;
use App\Models\Employee;
use App\Models\EmployeeBankAccount;
use App\Models\EmployeeCompensation;
use App\Models\EmployeeDocument;
use App\Models\EmployeeFamily;
use App\Models\EmployeeMedicalCheckup;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class EmployeeHrRecordModelsTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_has_bank_account_family_document_medical_checkup_compensation_and_asset_records(): void
    {
        $employee = Employee::factory()->create();
        EmployeeBankAccount::factory()->for($employee)->create(['bank_name' => 'BCA']);
        EmployeeFamily::factory()->for($employee)->emergencyContact()->create(['name' => 'Siti']);
        EmployeeFamily::factory()->for($employee)->create(['name' => 'Rina']);
        EmployeeDocument::factory()->for($employee)->create(['document_type' => 'npwp']);
        EmployeeMedicalCheckup::factory()->for($employee)->create();
        EmployeeCompensation::factory()->for($employee)->create();
        CompanyAsset::factory()->assignedTo($employee)->create(['name' => 'Laptop']);

        $employee->refresh();

        $this->assertSame(['BCA'], $employee->bankAccounts->pluck('bank_name')->all());
        $this->assertTrue($employee->bankAccounts->first()->is_primary);
        $this->assertSame(['Siti'], $employee->families()->emergencyContact()->pluck('name')->all());
        $this->assertCount(2, $employee->families);
        $this->assertSame(['npwp'], $employee->documents->pluck('document_type')->all());
        $this->assertCount(1, $employee->medicalCheckups);
        $this->assertInstanceOf(EmployeeCompensation::class, $employee->compensation);
        $this->assertSame(['Laptop'], $employee->assets->pluck('name')->all());
        $this->assertTrue($employee->assets->first()->holder->is($employee));
    }

    public function test_compensation_amounts_are_encrypted_at_rest_and_one_per_employee(): void
    {
        $employee = Employee::factory()->create();
        $compensation = EmployeeCompensation::factory()->for($employee)->create([
            'base_salary' => '8500000',
            'allowance' => '1250000',
        ]);

        $storedCompensation = DB::table('employee_compensations')->where('id', $compensation->id)->first();

        $this->assertNotSame('8500000', $storedCompensation->base_salary);
        $this->assertNotSame('1250000', $storedCompensation->allowance);
        $this->assertSame('8500000', $compensation->fresh()->base_salary);
        $this->assertSame('1250000', $compensation->fresh()->allowance);

        $this->expectException(QueryException::class);

        EmployeeCompensation::factory()->for($employee)->create();
    }

    public function test_company_owns_assets_that_stay_when_the_holder_is_deleted(): void
    {
        $company = Company::factory()->create();
        $employee = Employee::factory()->create();
        $heldAsset = CompanyAsset::factory()->for($company)->assignedTo($employee)->create();
        $availableAsset = CompanyAsset::factory()->for($company)->create();

        $this->assertSame(CompanyAsset::STATUS_ASSIGNED, $heldAsset->status);
        $this->assertSame(CompanyAsset::STATUS_AVAILABLE, $availableAsset->status);
        $this->assertEqualsCanonicalizing([$heldAsset->id, $availableAsset->id], $company->assets()->pluck('id')->all());
        $this->assertTrue($heldAsset->company->is($company));

        $employee->forceDelete();

        $this->assertNull($heldAsset->fresh()->employee_id);
        $this->assertCount(2, $company->assets()->get());
    }

    public function test_employee_document_is_soft_deleted_and_keeps_its_uploader(): void
    {
        $uploader = User::factory()->create();
        $document = EmployeeDocument::factory()->create(['uploaded_by' => $uploader->id]);

        $this->assertTrue($document->uploader->is($uploader));

        $document->delete();

        $this->assertSoftDeleted($document);
        $this->assertCount(0, $document->employee->documents);
        $this->assertCount(1, $document->employee->documents()->withTrashed()->get());
    }

    public function test_medical_checkup_result_options_have_labels(): void
    {
        $this->assertSame([
            EmployeeMedicalCheckup::RESULT_FIT => 'Fit',
            EmployeeMedicalCheckup::RESULT_FIT_WITH_NOTE => 'Fit with Note',
            EmployeeMedicalCheckup::RESULT_UNFIT => 'Unfit',
        ], EmployeeMedicalCheckup::resultOptions());
    }
}
