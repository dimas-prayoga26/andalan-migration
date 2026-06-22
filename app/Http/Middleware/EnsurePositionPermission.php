<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePositionPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$permissionNames): Response
    {
        $user = $request->user();

        abort_unless(
            $user instanceof User && $user->hasAnyPositionPermission($permissionNames),
            403
        );

        return $next($request);
    }
}
