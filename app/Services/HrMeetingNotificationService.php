<?php

namespace App\Services;

use App\Models\AppNotification;
use App\Models\DeviceSubscription;
use App\Models\Employee;
use App\Models\HrMeeting;
use App\Models\User;
use App\Support\Branding\HostBrandingResolver;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class HrMeetingNotificationService
{
    public function __construct(private readonly WebPushNotificationService $webPushNotificationService)
    {
    }

    public function notifyScheduled(HrMeeting $meeting): void
    {
        if ($meeting->status !== 'scheduled') {
            return;
        }

        $users = $this->recipientUsers($meeting);
        if ($users->isEmpty()) {
            Log::warning('HR meeting notification skipped because no recipient users were found.', [
                'hr_meeting_id' => $meeting->id,
            ]);

            return;
        }

        $meetingUrl = route('zoom-meeting.join', $meeting);
        $dateLabel = $meeting->meeting_date?->timezone('Asia/Jakarta')->format('d M Y') ?? '-';
        $timeLabel = substr((string) $meeting->meeting_time, 0, 5).' WIB';
        $title = 'Meeting Baru: '.$meeting->title;
        $body = "Jadwal meeting {$dateLabel} pukul {$timeLabel}.";
        $brand = app(HostBrandingResolver::class)->resolve();
        $iconUrl = (string) ($brand['logo_url'] ?? asset('images/images.png'));

        $users->each(function (User $user) use ($meeting, $meetingUrl, $title, $body, $iconUrl): void {
            AppNotification::query()->create([
                'user_id' => $user->id,
                'employee_id' => $user->employee?->id,
                'brand_key' => config('app.brand_key'),
                'type' => 'hr_meeting_scheduled',
                'title' => $title,
                'body' => $body,
                'url' => $meetingUrl,
                'icon' => $iconUrl,
            ]);
        });

        $deviceSubscriptions = DeviceSubscription::query()
            ->whereIn('user_id', $users->pluck('id')->all())
            ->get();

        if ($deviceSubscriptions->isEmpty()) {
            Log::info('HR meeting push notification skipped because recipients have no device subscriptions.', [
                'hr_meeting_id' => $meeting->id,
                'recipient_user_ids' => $users->pluck('id')->values()->all(),
            ]);
        }

        $this->webPushNotificationService->sendToSubscriptions($deviceSubscriptions, [
            'title' => $title,
            'body' => $body,
            'url' => $meetingUrl,
            'icon' => $iconUrl,
            'badge' => $iconUrl,
        ]);
    }

    /**
     * @return Collection<int, User>
     */
    private function recipientUsers(HrMeeting $meeting): Collection
    {
        $meeting->loadMissing('participants');
        $participants = $meeting->participants;
        $employeeIds = $participants
            ->pluck('employee_id')
            ->filter()
            ->unique()
            ->values();

        if ($employeeIds->isNotEmpty()) {
            return Employee::query()
                ->with('user')
                ->where('status', 'Active')
                ->whereIn('id', $employeeIds->all())
                ->whereHas('user', fn ($query) => $query->where('is_active', true))
                ->get()
                ->pluck('user')
                ->filter()
                ->unique('id')
                ->values();
        }

        if ($participants->contains('participant_type', 'all_staff')) {
            return $this->activeEmployeeUserQuery()->get()
                ->pluck('user')
                ->filter()
                ->unique('id')
                ->values();
        }

        if ($participants->contains('participant_type', 'bod')) {
            return $this->bodEmployees()
                ->pluck('user')
                ->filter()
                ->unique('id')
                ->values();
        }

        return collect();
    }

    private function activeEmployeeUserQuery()
    {
        return Employee::query()
            ->with('user.roles')
            ->where('status', 'Active')
            ->whereHas('user', fn ($query) => $query->where('is_active', true))
            ->whereHas('user.roles', function ($query): void {
                $query->whereRaw('LOWER(name) = ?', ['staff']);
            })
            ->whereDoesntHave('user.roles', function ($query): void {
                $query->whereRaw('LOWER(name) = ?', ['superuser']);
            });
    }

    /**
     * @return Collection<int, Employee>
     */
    private function bodEmployees(): Collection
    {
        return $this->activeEmployeeUserQuery()
            ->whereHas('deployment', function ($query): void {
                $query->whereHas('position', fn ($positionQuery) => $this->bodPositionFilter($positionQuery))
                    ->orWhereHas('positions', fn ($positionQuery) => $this->bodPositionFilter($positionQuery));
            })
            ->get();
    }

    private function bodPositionFilter($query): void
    {
        $query->where('name', 'like', '%Supervisor%');
    }
}
