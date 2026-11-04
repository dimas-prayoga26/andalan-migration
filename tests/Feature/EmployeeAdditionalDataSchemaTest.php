<?php

namespace Tests\Feature;

use App\Models\EducationLevel;
use App\Models\Employee;
use App\Models\EmployeeCertification;
use App\Models\EmployeeEducation;
use App\Models\EmployeeLicense;
use App\Models\EmployeeProfile;
use App\Models\EmployeeReference;
use App\Models\EmployeeWorkExperience;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EmployeeAdditionalDataSchemaTest extends TestCase
{
    #[Test]
    public function employee_additional_data_migrations_define_expected_tables_and_columns(): void
    {
        $migrationContents = collect(File::files(database_path('migrations')))
            ->filter(fn ($file): bool => str_contains($file->getFilename(), 'employee_'))
            ->map(fn ($file): string => File::get($file->getPathname()))
            ->implode("\n");

        foreach ([
            "Schema::create('employee_licenses'",
            "Schema::create('employee_educations'",
            "Schema::create('employee_certifications'",
            "Schema::create('employee_work_experiences'",
            "Schema::create('employee_references'",
            "Schema::table('employee_profiles'",
            "Schema::table('employee_identities'",
            "foreignUuid('employee_id')->constrained('employees', 'id')->cascadeOnDelete()",
            "foreignUuid('education_level_id')->nullable()->constrained('education_levels', 'id')->nullOnDelete()",
            "string('mother_name')->nullable()",
            "json('languages')->nullable()",
            "json('hard_skills')->nullable()",
            "json('soft_skills')->nullable()",
        ] as $expectedSchemaFragment) {
            $this->assertStringContainsString($expectedSchemaFragment, $migrationContents);
        }
    }

    #[Test]
    public function employee_additional_data_models_define_expected_relationships_and_casts(): void
    {
        $employee = new Employee;

        $this->assertInstanceOf(HasMany::class, $employee->licenses());
        $this->assertInstanceOf(HasMany::class, $employee->educations());
        $this->assertInstanceOf(HasMany::class, $employee->certifications());
        $this->assertInstanceOf(HasMany::class, $employee->workExperiences());
        $this->assertInstanceOf(HasMany::class, $employee->references());

        $this->assertInstanceOf(BelongsTo::class, (new EmployeeLicense)->employee());
        $this->assertInstanceOf(BelongsTo::class, (new EmployeeEducation)->employee());
        $this->assertInstanceOf(BelongsTo::class, (new EmployeeEducation)->educationLevel());
        $this->assertInstanceOf(BelongsTo::class, (new EmployeeCertification)->employee());
        $this->assertInstanceOf(BelongsTo::class, (new EmployeeWorkExperience)->employee());
        $this->assertInstanceOf(BelongsTo::class, (new EmployeeReference)->employee());
        $this->assertInstanceOf(HasMany::class, (new EducationLevel)->employeeEducations());

        $this->assertSame('array', (new EmployeeProfile)->getCasts()['languages']);
        $this->assertSame('array', (new EmployeeProfile)->getCasts()['hard_skills']);
        $this->assertSame('array', (new EmployeeProfile)->getCasts()['soft_skills']);
    }
}
