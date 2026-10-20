<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

abstract class Controller
{
    /**
     * Answer a table action with JSON for AJAX calls, or flash the message onto the redirect for regular form submissions.
     */
    protected function tableActionResponse(Request $request, RedirectResponse $redirect, string $message, bool $isSuccessful = true): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => $isSuccessful,
                'message' => $message,
            ], $isSuccessful ? 200 : 422);
        }

        return $redirect->with($isSuccessful ? 'status' : 'error', $message);
    }
}
