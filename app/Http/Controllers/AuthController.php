<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function create(): View
    {
        $logoPaths = collect(File::files(public_path('assets/logo_perusahaan')))
            ->map(static fn ($file): string => asset('assets/logo_perusahaan/'.rawurlencode($file->getFilename())))
            ->values()
            ->all();

        return view('auth.login', [
            'logoPaths' => $logoPaths,
        ]);
    }

    public function store(LoginRequest $request): JsonResponse|RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();
        $authenticatedUser = $request->user();
        $tenant = 'admin';

        if (! $authenticatedUser?->hasAnyRole(['superuser'])) {
            $companyName = $authenticatedUser?->company?->name;
            abort_if($companyName === null, 403);
            $tenant = $companyName;
        }

        $dashboardUrl = route('dashboard', ['tenant' => $tenant]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Login berhasil.',
                'redirect' => $dashboardUrl,
            ]);
        }

        return redirect()->intended($dashboardUrl);
    }

    public function destroy(Request $request): RedirectResponse
    {
        auth()->guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
