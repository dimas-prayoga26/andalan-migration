<?php

namespace App\View\Composers;

use App\Models\AppNotification;
use App\Models\HrMeeting;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class HeaderProfileComposer
{
    public function compose(View $view): void
    {
        $headerData = [
            'headerUserName' => '-',
            'headerUserPositionLabel' => '-',
            'headerUserAvatarUrl' => asset('assets/default_user.jpg'),
            'headerNotifications' => collect(),
            'headerUnreadNotificationsCount' => 0,
        ];

        $authenticatedUserId = Auth::id();
        $authenticatedUser = is_string($authenticatedUserId) || is_int($authenticatedUserId)
            ? User::query()
                ->select(['id', 'username', 'email'])
                ->with([
                    'employee:id,user_id',
                    'employee.profile:id,employee_id,name,profile_picture_path',
                    'employee.deployment:id,employee_id,current_position_id',
                    'employee.deployment.position:id,name',
                ])
                ->find($authenticatedUserId)
            : null;

        if ($authenticatedUser instanceof User) {
            $employeeName = trim((string) ($authenticatedUser->employee?->profile?->name ?? ''));
            $username = trim((string) $authenticatedUser->username);
            $emailName = trim((string) explode('@', (string) $authenticatedUser->email)[0]);

            $headerData['headerUserName'] = $employeeName !== ''
                ? $employeeName
                : ($username !== '' ? $username : ($emailName !== '' ? $emailName : '-'));

            $primaryPositionName = trim((string) ($authenticatedUser->employee?->deployment?->position?->name ?? ''));
            $headerData['headerUserPositionLabel'] = $primaryPositionName !== '' ? $primaryPositionName : '-';
            $headerData['headerUserAvatarUrl'] = $this->avatarUrl(
                $authenticatedUser->employee?->profile?->profile_picture_path,
            );
            $authenticatedEmployeeId = $authenticatedUser->employee?->id;
            $visibleUnreadNotifications = $this->visibleUnreadNotifications($authenticatedUser, $authenticatedEmployeeId);

            $headerData['headerNotifications'] = $visibleUnreadNotifications->take(8);
            $headerData['headerUnreadNotificationsCount'] = $visibleUnreadNotifications->count();
        }

        $view->with($headerData);
    }

    /**
     * @return Collection<int, AppNotification>
     */
    private function visibleUnreadNotifications(User $authenticatedUser, mixed $authenticatedEmployeeId): Collection
    {
        $notifications = AppNotification::query()
            ->where(function (Builder $query) use ($authenticatedUser, $authenticatedEmployeeId): void {
                $query->where('user_id', $authenticatedUser->id);

                if (is_string($authenticatedEmployeeId) && $authenticatedEmployeeId !== '') {
                    $query->orWhere('employee_id', $authenticatedEmployeeId);
                }
            })
            ->whereNull('read_at')
            ->orderByDesc('created_at')
            ->get();

        $scheduledMeetingIds = $this->scheduledMeetingNotificationIds($notifications);

        return $notifications
            ->filter(fn (AppNotification $notification): bool => $this->notificationStillVisible($notification, $scheduledMeetingIds))
            ->map(function (AppNotification $notification): AppNotification {
                if ($notification->type === 'hr_meeting_scheduled') {
                    $notification->setAttribute('meeting_id', $this->meetingIdFromNotificationUrl($notification->url));
                }

                return $notification;
            })
            ->values();
    }

    /**
     * @param  Collection<int, AppNotification>  $notifications
     * @return Collection<int, string>
     */
    private function scheduledMeetingNotificationIds(Collection $notifications): Collection
    {
        $meetingIds = $notifications
            ->filter(fn (AppNotification $notification): bool => $notification->type === 'hr_meeting_scheduled')
            ->map(fn (AppNotification $notification): ?string => $this->meetingIdFromNotificationUrl($notification->url))
            ->filter()
            ->unique()
            ->values();

        if ($meetingIds->isEmpty()) {
            return collect();
        }

        return HrMeeting::query()
            ->whereIn('id', $meetingIds->all())
            ->where('status', 'scheduled')
            ->pluck('id')
            ->map(fn (mixed $meetingId): string => (string) $meetingId)
            ->values();
    }

    /**
     * @param  Collection<int, string>  $scheduledMeetingIds
     */
    private function notificationStillVisible(AppNotification $notification, Collection $scheduledMeetingIds): bool
    {
        if ($notification->type !== 'hr_meeting_scheduled') {
            return true;
        }

        $meetingId = $this->meetingIdFromNotificationUrl($notification->url);

        return is_string($meetingId) && $scheduledMeetingIds->contains($meetingId);
    }

    private function meetingIdFromNotificationUrl(?string $url): ?string
    {
        if (! is_string($url) || trim($url) === '') {
            return null;
        }

        $path = (string) parse_url($url, PHP_URL_PATH);
        $segments = array_reverse(array_filter(explode('/', trim($path, '/'))));

        foreach ($segments as $segment) {
            if (! preg_match('/^[A-Za-z0-9-]{16,}$/', $segment)) {
                continue;
            }

            return $segment;
        }

        return null;
    }

    private function avatarUrl(mixed $profilePicturePath): string
    {
        $defaultAvatarUrl = asset('assets/default_user.jpg');
        $profilePicturePath = trim((string) $profilePicturePath);

        if ($profilePicturePath === '') {
            return $defaultAvatarUrl;
        }

        if (Str::startsWith($profilePicturePath, ['http://', 'https://'])) {
            return $profilePicturePath;
        }

        $publicPath = ltrim($profilePicturePath, '/');
        $storagePath = Str::startsWith($publicPath, 'storage/')
            ? Str::after($publicPath, 'storage/')
            : $publicPath;

        if (Storage::disk('public')->exists($storagePath)) {
            return asset('storage/'.$storagePath);
        }

        return File::exists(public_path($publicPath)) ? asset($publicPath) : $defaultAvatarUrl;
    }
}
