<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\User;
use App\Models\UserActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserActivityLogger
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function log(
        string $event,
        ?User $user = null,
        ?Request $request = null,
        ?Attendance $attendance = null,
        array $metadata = []
    ): UserActivityLog {
        $request ??= request();
        $user ??= Auth::user() instanceof User ? Auth::user() : null;

        return UserActivityLog::query()->create([
            'user_id' => $user?->id,
            'attendance_id' => $attendance?->id,
            'session_id' => $this->sessionId($request),
            'event' => $event,
            'route_name' => $request->route()?->getName(),
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'metadata' => array_filter($metadata, static fn (mixed $value): bool => $value !== null),
        ]);
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    public function loggedIn(User $user, Request $request, array $metadata = []): UserActivityLog
    {
        return $this->log(UserActivityLog::LoggedIn, $user, $request, null, $metadata);
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    public function loggedOut(User $user, Request $request, array $metadata = []): UserActivityLog
    {
        return $this->log(UserActivityLog::LoggedOut, $user, $request, null, $metadata);
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    public function pageVisited(Request $request, User $user, array $metadata = []): UserActivityLog
    {
        return $this->log(UserActivityLog::PageVisited, $user, $request, null, $metadata);
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    public function clockInSubmitted(Request $request, ?User $user, ?Attendance $attendance, array $metadata = []): UserActivityLog
    {
        return $this->log(UserActivityLog::ClockInSubmitted, $user, $request, $attendance, $metadata);
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    public function clockOutSubmitted(Request $request, ?User $user, Attendance $attendance, array $metadata = []): UserActivityLog
    {
        return $this->log(UserActivityLog::ClockOutSubmitted, $user, $request, $attendance, $metadata);
    }

    private function sessionId(Request $request): ?string
    {
        if (! $request->hasSession()) {
            return null;
        }

        try {
            return $request->session()->getId();
        } catch (\Throwable) {
            return null;
        }
    }
}
