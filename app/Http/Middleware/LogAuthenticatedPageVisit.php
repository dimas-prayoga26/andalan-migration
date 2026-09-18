<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\UserActivityLogger;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogAuthenticatedPageVisit
{
    /**
     * @var array<int, string>
     */
    private const LOGGABLE_ROUTE_NAMES = [
        'dashboard',
        'attendance',
        'attendance.today',
    ];

    public function __construct(private UserActivityLogger $activityLogger) {}

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($this->shouldLog($request, $response)) {
            $user = $request->user();

            if ($user instanceof User) {
                $this->activityLogger->pageVisited($request, $user);
            }
        }

        return $response;
    }

    private function shouldLog(Request $request, Response $response): bool
    {
        $routeName = $request->route()?->getName();

        return $request->isMethod('GET')
            && $response->isSuccessful()
            && ! $request->expectsJson()
            && ! $request->ajax()
            && ! $request->is('up', 'assets/*', 'build/*', 'favicon.ico')
            && is_string($routeName)
            && in_array($routeName, self::LOGGABLE_ROUTE_NAMES, true);
    }
}
