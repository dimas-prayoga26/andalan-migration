<?php

namespace App\Services;

use App\Models\Applicant;
use App\Models\ApplicantFinalAssessment;
use App\Models\User;

class ApplicantFinalAssessmentService
{
    /**
     * @return array{skill_note: string, communication_note: string, experience_note: string, tools_note: string, final_note: string, final_decision: string}
     */
    public function finalAssessmentForView(Applicant $applicant): array
    {
        $finalAssessment = ApplicantFinalAssessment::query()
            ->whereBelongsTo($applicant)
            ->first();

        return [
            'skill_note' => (string) ($finalAssessment?->skill_note ?? ''),
            'communication_note' => (string) ($finalAssessment?->communication_note ?? ''),
            'experience_note' => (string) ($finalAssessment?->experience_note ?? ''),
            'tools_note' => (string) ($finalAssessment?->tools_note ?? ''),
            'final_note' => (string) ($finalAssessment?->final_note ?? ''),
            'final_decision' => (string) ($finalAssessment?->final_decision ?? ''),
        ];
    }

    /**
     * @param  array{skill_note: string, communication_note: string, experience_note: string, tools_note: string, final_note: string, final_decision: string}  $data
     */
    public function store(Applicant $applicant, array $data, ?User $assessor): ApplicantFinalAssessment
    {
        return ApplicantFinalAssessment::query()->updateOrCreate(
            [
                'applicant_id' => $applicant->id,
            ],
            [
                'skill_note' => trim($data['skill_note']),
                'communication_note' => trim($data['communication_note']),
                'experience_note' => trim($data['experience_note']),
                'tools_note' => trim($data['tools_note']),
                'final_note' => trim($data['final_note']),
                'final_decision' => $data['final_decision'],
                'assessed_by' => $assessor?->id,
                'submitted_at' => now(),
            ]
        );
    }
}
