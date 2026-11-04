<?php

namespace App\Services;

use App\Models\Applicant;
use App\Models\ApplicantStatus;
use App\Models\Position;
use App\Models\User;

class ApplicantAssessmentAccessService
{
    public function canEditHrInterview(?User $user, Applicant $applicant): bool
    {
        return $this->isSuperAdministrator($user)
            || ($this->isAdministrator($user) && $this->statusValue($applicant) === ApplicantStatus::VALUE_HR_INTERVIEW);
    }

    public function canManageAssessmentTest(?User $user, Applicant $applicant): bool
    {
        return $this->canEditHrInterview($user, $applicant);
    }

    public function canEditTechnicalTest(?User $user, Applicant $applicant): bool
    {
        return $this->isSuperAdministrator($user)
            || ($this->isPic($user) && $this->statusValue($applicant) === ApplicantStatus::VALUE_TECHNICAL_TEST);
    }

    public function canEditUserInterview(?User $user, Applicant $applicant): bool
    {
        return $this->isSuperAdministrator($user)
            || ($this->isPic($user) && $this->statusValue($applicant) === ApplicantStatus::VALUE_USER_INTERVIEW);
    }

    public function canEditFinalAssessment(?User $user, Applicant $applicant): bool
    {
        return $this->canEditUserInterview($user, $applicant);
    }

    /**
     * @return array{hr_interview: bool, assessment_test: bool, technical_test: bool, user_interview: bool, final_assessment: bool}
     */
    public function permissionsFor(?User $user, Applicant $applicant): array
    {
        return [
            'hr_interview' => $this->canEditHrInterview($user, $applicant),
            'assessment_test' => $this->canManageAssessmentTest($user, $applicant),
            'technical_test' => $this->canEditTechnicalTest($user, $applicant),
            'user_interview' => $this->canEditUserInterview($user, $applicant),
            'final_assessment' => $this->canEditFinalAssessment($user, $applicant),
        ];
    }

    private function isSuperAdministrator(?User $user): bool
    {
        return $user instanceof User && $user->isSuperAdministrator();
    }

    private function isAdministrator(?User $user): bool
    {
        return $user instanceof User
            && (
                $user->isSuperAdministrator()
                || ($user->employee?->hasAnyPositionSystemKey([Position::KEY_ADMINISTRATOR]) ?? false)
            );
    }

    private function isPic(?User $user): bool
    {
        return $user instanceof User
            && ! $this->isAdministrator($user)
            && $user->hasAnyPositionPermission(['view-talent-acquisition']);
    }

    private function statusValue(Applicant $applicant): int
    {
        $applicant->loadMissing('applicantStatus:id,value');

        return (int) ($applicant->applicantStatus?->value ?? ApplicantStatus::VALUE_SUBMITTED);
    }
}
