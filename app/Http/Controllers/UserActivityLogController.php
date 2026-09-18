<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserActivityLog;
use App\Services\UserActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserActivityLogController extends Controller
{
    public function store(Request $request, UserActivityLogger $activityLogger): JsonResponse
    {
        $validated = $request->validate([
            'event' => [
                'required',
                'string',
                Rule::in([
                    UserActivityLog::ClockInClicked,
                    UserActivityLog::ClockInVerified,
                    UserActivityLog::ClockOutClicked,
                    UserActivityLog::ClockOutVerified,
                ]),
            ],
            'metadata' => ['sometimes', 'array'],
        ]);

        $user = $request->user();
        if ($user instanceof User) {
            $activityLogger->log(
                $validated['event'],
                $user,
                $request,
                null,
                $validated['metadata'] ?? []
            );
        }

        return response()->json(['success' => true]);
    }
}
