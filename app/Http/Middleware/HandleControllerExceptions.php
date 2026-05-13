<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class HandleControllerExceptions
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            return $next($request);
        } catch (Throwable $throwable) {
            if (
                $throwable instanceof ValidationException
                || $throwable instanceof AuthenticationException
                || $throwable instanceof AuthorizationException
                || $throwable instanceof HttpExceptionInterface
            ) {
                throw $throwable;
            }

            report($throwable);

            if ($request->expectsJson() || $request->ajax()) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Terjadi kesalahan pada proses permintaan.',
                ], 500);
            }

            abort(500, 'Terjadi kesalahan pada proses permintaan.');
        }
    }
}
