<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminContext
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $authenticatedUser = $request->user();

        if ($authenticatedUser === null) {
            abort(403);
        }

        if (! $authenticatedUser->hasAnyRole(['admin', 'superuser'])) {
            $companyName = $authenticatedUser->company?->name;

            abort_if($companyName === null, 403);

            return redirect()->route('dashboard', ['tenant' => $companyName]);
        }

        return $next($request);
    }
}
