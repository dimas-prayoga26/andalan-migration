<?php

namespace App\Support\Attendance;

use App\Models\LeaveRequest;
use App\Models\LeaveRequestHistory;
use Illuminate\Support\Collection;

/**
 * Resolves where a leave request sits in the leave lifecycle (step 2 Supervisor Review,
 * step 3 HR Verification, step 4 Final Decision) for the leave cards.
 *
 * Expects the leave request's `histories` relation to be loaded.
 */
class LeaveCardStepResolver
{
    /**
     * @return array{title: string, status: string, color: string}
     */
    public function currentStep(LeaveRequest $leaveRequest): array
    {
        $leaveStatus = $this->normalizedStatus($leaveRequest->status);
        $supervisorReviewStatuses = $this->supervisorReviewStatuses($leaveRequest);

        if ($leaveStatus === 'approved') {
            return ['title' => 'Final Decision', 'status' => 'Approved', 'color' => 'success'];
        }

        if (in_array($leaveStatus, ['rejected', 'refused'], true)) {
            return $supervisorReviewStatuses->contains('rejected')
                ? ['title' => 'Supervisor Review', 'status' => 'Rejected', 'color' => 'danger']
                : ['title' => 'Final Decision', 'status' => 'Rejected', 'color' => 'danger'];
        }

        return $supervisorReviewStatuses->contains('approved')
            ? ['title' => 'HR Verification', 'status' => 'Pending', 'color' => 'warning']
            : ['title' => 'Supervisor Review', 'status' => 'Pending', 'color' => 'warning'];
    }

    /**
     * Notice shown when HR opens a card that has not reached HR Verification yet, or null when it has.
     *
     * @return array{title: string, message: string}|null
     */
    public function hrLockedNotice(LeaveRequest $leaveRequest): ?array
    {
        $supervisorReviewStatuses = $this->supervisorReviewStatuses($leaveRequest);

        if ($supervisorReviewStatuses->contains('approved')) {
            return null;
        }

        if ($supervisorReviewStatuses->contains('rejected')) {
            return [
                'title' => 'Ditolak PIC',
                'message' => 'Pengajuan cuti ini ditolak oleh PIC / Supervisor, sehingga tidak diteruskan.',
            ];
        }

        return [
            'title' => 'Belum Disetujui PIC',
            'message' => 'Pengajuan cuti ini belum disetujui oleh PIC / Supervisor, sehingga belum bisa diproses.',
        ];
    }

    /**
     * @return Collection<int, string>
     */
    private function supervisorReviewStatuses(LeaveRequest $leaveRequest): Collection
    {
        return $leaveRequest->histories
            ->filter(fn (LeaveRequestHistory $history): bool => $this->normalizedStatus($history->event_type) === 'supervisor_review')
            ->map(fn (LeaveRequestHistory $history): string => $this->normalizedStatus($history->to_status))
            ->values();
    }

    private function normalizedStatus(mixed $value): string
    {
        return strtolower(trim((string) $value));
    }
}
