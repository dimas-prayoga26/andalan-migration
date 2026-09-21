<?php

namespace Tests\Unit;

use App\Console\Commands\Support\LegacyApplicantMapper;
use Illuminate\Support\Collection;
use Tests\TestCase;

class LegacyApplicantMapperTest extends TestCase
{
    public function test_split_piped_values_normalizes_and_drops_blank_entries(): void
    {
        $this->assertSame(
            ['2024-10', '2025-01', '2025-08'],
            LegacyApplicantMapper::splitPipedValues('2024-10||2025-01||2025-08')
        );

        $this->assertSame([], LegacyApplicantMapper::splitPipedValues(null));
        $this->assertSame([], LegacyApplicantMapper::splitPipedValues(''));
    }

    public function test_combine_rows_zips_columns_by_index(): void
    {
        $rows = LegacyApplicantMapper::combineRows([
            'company_name' => 'PT A||PT B',
            'role' => 'Staff||Manager',
            'start_period' => '2020-01||2021-01',
        ]);

        $this->assertSame([
            ['company_name' => 'PT A', 'role' => 'Staff', 'start_period' => '2020-01'],
            ['company_name' => 'PT B', 'role' => 'Manager', 'start_period' => '2021-01'],
        ], $rows);
    }

    public function test_combine_rows_drops_rows_where_every_column_is_blank_or_a_dash(): void
    {
        $rows = LegacyApplicantMapper::combineRows([
            'company_name' => 'PT A||-',
            'role' => 'Staff||-',
            'start_period' => '2020-01||-',
        ]);

        $this->assertSame([
            ['company_name' => 'PT A', 'role' => 'Staff', 'start_period' => '2020-01'],
        ], $rows);
    }

    public function test_combine_rows_keeps_a_row_when_at_least_one_column_has_a_real_value(): void
    {
        $rows = LegacyApplicantMapper::combineRows([
            'company_name' => 'PT A||-',
            'role' => 'Staff||Manager',
            'start_period' => '2020-01||2022-01',
        ]);

        $this->assertSame([
            ['company_name' => 'PT A', 'role' => 'Staff', 'start_period' => '2020-01'],
            ['company_name' => '-', 'role' => 'Manager', 'start_period' => '2022-01'],
        ], $rows);
    }

    public function test_map_applicant_attributes_resolves_lookups_and_falls_back_gracefully(): void
    {
        $mapper = new LegacyApplicantMapper(
            genderIdsByName: new Collection(['male' => '1', 'female' => '2']),
            maritalStatusIdsByName: new Collection(['single' => '10', 'married' => '11']),
            educationLevelIdsByName: new Collection(['strata 1' => 'edu-uuid-1']),
            applicantStatusIdsByValue: new Collection([0 => 'status-uuid-0', 2 => 'status-uuid-2']),
            jobVacancyIdsByName: new Collection(['backend engineer' => 'vacancy-uuid-1']),
            legacyVacancyNamesByValue: new Collection([5 => 'Backend Engineer']),
        );

        $attributes = $mapper->mapApplicantAttributes([
            'full_name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'gender' => '1',
            'marital_status' => '2',
            'nb' => '2',
            'job_applied' => '5',
            'created_at' => '2026-09-21 08:00:00',
        ], 'andalanku');

        $this->assertSame('Jane Doe', $attributes['full_name']);
        $this->assertSame('andalanku', $attributes['brand_key']);
        $this->assertSame('1', $attributes['gender_id']);
        $this->assertSame('11', $attributes['marital_status_id']);
        $this->assertSame('status-uuid-2', $attributes['applicant_status_id']);
        $this->assertSame('vacancy-uuid-1', $attributes['job_vacancy_id']);
        $this->assertSame('2026-09-21 08:00:00', $attributes['created_at']->format('Y-m-d H:i:s'));
    }

    public function test_map_applicant_attributes_defaults_to_submitted_status_when_legacy_status_is_unknown(): void
    {
        $mapper = new LegacyApplicantMapper(
            genderIdsByName: new Collection,
            maritalStatusIdsByName: new Collection,
            educationLevelIdsByName: new Collection,
            applicantStatusIdsByValue: new Collection([0 => 'status-uuid-submitted']),
            jobVacancyIdsByName: new Collection,
            legacyVacancyNamesByValue: new Collection,
        );

        $attributes = $mapper->mapApplicantAttributes([
            'full_name' => 'John Doe',
            'nb' => null,
            'job_applied' => null,
        ], null);

        $this->assertSame('status-uuid-submitted', $attributes['applicant_status_id']);
        $this->assertNull($attributes['job_vacancy_id']);
        $this->assertNull($attributes['brand_key']);
    }

    public function test_map_education_rows_splits_multiple_entries_and_resolves_education_level(): void
    {
        $mapper = new LegacyApplicantMapper(
            genderIdsByName: new Collection,
            maritalStatusIdsByName: new Collection,
            educationLevelIdsByName: new Collection(['sma' => 'edu-1', 'strata 1' => 'edu-2']),
            applicantStatusIdsByValue: new Collection,
            jobVacancyIdsByName: new Collection,
            legacyVacancyNamesByValue: new Collection,
        );

        $rows = $mapper->mapEducationRows([
            'educational_level' => '1||3',
            'educational_institution' => 'SMA 1||Universitas A',
            'gpa' => '-||3.5',
            'department' => '-||Informatika',
            'start_education' => '2010||2016',
            'graduate_education' => '2013||2020',
        ]);

        $this->assertCount(2, $rows);
        $this->assertSame('edu-1', $rows[0]['education_level_id']);
        $this->assertSame('SMA 1', $rows[0]['institution']);
        $this->assertSame('-', $rows[0]['gpa']);
        $this->assertSame('edu-2', $rows[1]['education_level_id']);
        $this->assertSame('Universitas A', $rows[1]['institution']);
        $this->assertSame('3.5', $rows[1]['gpa']);
    }

    public function test_map_work_experience_rows_splits_multiple_entries(): void
    {
        $mapper = new LegacyApplicantMapper(
            genderIdsByName: new Collection,
            maritalStatusIdsByName: new Collection,
            educationLevelIdsByName: new Collection,
            applicantStatusIdsByValue: new Collection,
            jobVacancyIdsByName: new Collection,
            legacyVacancyNamesByValue: new Collection,
        );

        $rows = $mapper->mapWorkExperienceRows([
            'company_name' => 'PT A||PT B',
            'role' => 'Staff||Manager',
            'company_location' => 'Jakarta||Bandung',
            'start_date' => '2020-01||2022-01',
            'end_date' => '2021-12||2023-12',
        ]);

        $this->assertSame([
            ['company_name' => 'PT A', 'role' => 'Staff', 'company_location' => 'Jakarta', 'start_period' => '2020-01', 'end_period' => '2021-12'],
            ['company_name' => 'PT B', 'role' => 'Manager', 'company_location' => 'Bandung', 'start_period' => '2022-01', 'end_period' => '2023-12'],
        ], $rows);
    }
}
