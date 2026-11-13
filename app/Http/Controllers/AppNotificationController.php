<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use App\Models\HrMeeting;
use Illuminate\Http\RedirectResponse;

class AppNotificationController extends Controller
{
    public function open(AppNotification $appNotification): RedirectResponse
    {
        $user = auth()->user();
        $employeeId = $user?->employee?->id;

        abort_unless(
            (string) $appNotification->user_id === (string) $user?->id
            || (
                is_string($employeeId)
                && $employeeId !== ''
                && (string) $appNotification->employee_id === $employeeId
            ),
            403,
        );

        $meeting = $appNotification->type === 'hr_meeting_scheduled'
            ? $this->meetingFromNotificationUrl($appNotification->url)
            : null;
        $isMeetingNotYetJoinable = $meeting instanceof HrMeeting && ! $meeting->isJoinable();

        if ($appNotification->read_at === null && ! $isMeetingNotYetJoinable) {
            $appNotification->forceFill(['read_at' => now()])->save();
        }

        return redirect()->to($this->notificationTargetUrl($appNotification, $meeting));
    }

    private function notificationTargetUrl(AppNotification $notification, ?HrMeeting $meeting): string
    {
        if ($meeting instanceof HrMeeting) {
            return route('zoom-meeting.join', $meeting);
        }

        return $notification->url ?: route('dashboard');
    }

    private function meetingFromNotificationUrl(?string $url): ?HrMeeting
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

            $meeting = HrMeeting::query()->whereKey($segment)->first();

            if ($meeting instanceof HrMeeting) {
                return $meeting;
            }
        }

        return null;
    }
}
