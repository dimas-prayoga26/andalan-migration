<?php

use App\Http\Middleware\EnsurePositionPermission;
use App\Http\Middleware\HandleControllerExceptions;
use App\Http\Middleware\LogAuthenticatedPageVisit;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');
        $middleware->web(append: [
            HandleControllerExceptions::class,
            LogAuthenticatedPageVisit::class,
        ]);
        $middleware->alias([
            'position.permission' => EnsurePositionPermission::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
