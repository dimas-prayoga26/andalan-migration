<?php

namespace App\Http\Middleware;

use App\Models\Company;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class EnsureCompanyContext
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $authenticatedUser = $request->user();
        $tenant = (string) $request->route('tenant');

        if ($authenticatedUser === null) {
            abort(403);
        }

        if ($authenticatedUser->hasAnyRole(['admin', 'superuser'])) {
            if ($tenant !== 'admin') {
                return redirect()->route('dashboard', ['tenant' => 'admin']);
            }

            URL::defaults(['tenant' => 'admin']);

            return $next($request);
        }

        if ($authenticatedUser->company === null) {
            abort(403);
        }

        $expectedTenant = $authenticatedUser->company->name;

        if ($tenant !== $expectedTenant) {
            return redirect()->route('dashboard', ['tenant' => $expectedTenant]);
        }

        $routeCompany = Company::query()->where('name', $tenant)->first();

        if ($routeCompany === null) {
            abort(404);
        }

        URL::defaults(['tenant' => $expectedTenant]);

        return $next($request);
    }
}
