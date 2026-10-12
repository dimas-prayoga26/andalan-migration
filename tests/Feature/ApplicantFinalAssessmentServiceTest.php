<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ApplicantFinalAssessmentServiceTest extends TestCase
{
    public function test_final_assessment_service_stores_one_record_per_applicant(): void
    {
        $service = File::get(app_path('Services/ApplicantFinalAssessmentService.php'));

        $this->assertStringContainsString('public function finalAssessmentForView(Applicant $applicant): array', $service);
        $this->assertStringContainsString('public function store(Applicant $applicant, array $data, ?User $assessor): ApplicantFinalAssessment', $service);
        $this->assertStringContainsString('updateOrCreate', $service);
        $this->assertStringContainsString("'applicant_id' => \$applicant->id", $service);
        $this->assertStringContainsString("'skill_note' => trim(\$data['skill_note'])", $service);
        $this->assertStringContainsString("'communication_note' => trim(\$data['communication_note'])", $service);
        $this->assertStringContainsString("'experience_note' => trim(\$data['experience_note'])", $service);
        $this->assertStringContainsString("'tools_note' => trim(\$data['tools_note'])", $service);
        $this->assertStringContainsString("'final_note' => trim(\$data['final_note'])", $service);
        $this->assertStringContainsString("'final_decision' => \$data['final_decision']", $service);
        $this->assertStringContainsString("'assessed_by' => \$assessor?->id", $service);
        $this->assertStringContainsString("'submitted_at' => now()", $service);
    }
}
