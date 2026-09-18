<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Models\User;
use App\Services\UserActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request, UserActivityLogger $activityLogger): JsonResponse|RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $authenticatedUser = $request->user();
        if ($authenticatedUser instanceof User) {
            $activityLogger->loggedIn($authenticatedUser, $request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Login berhasil.',
                'redirect' => route('dashboard'),
            ]);
        }

        return redirect()->intended('/');
    }

    public function destroy(Request $request, UserActivityLogger $activityLogger): RedirectResponse
    {
        $authenticatedUser = $request->user();
        if ($authenticatedUser instanceof User) {
            $activityLogger->loggedOut($authenticatedUser, $request);
        }

        auth()->guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
