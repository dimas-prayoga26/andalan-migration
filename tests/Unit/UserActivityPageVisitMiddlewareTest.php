<?php

namespace Tests\Unit;

use App\Http\Middleware\LogAuthenticatedPageVisit;
use App\Services\UserActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use Symfony\Component\HttpFoundation\Response;

class UserActivityPageVisitMiddlewareTest extends TestCase
{
    public function test_page_visit_log_is_limited_to_attendance_surfaces(): void
    {
        $middleware = new LogAuthenticatedPageVisit(new UserActivityLogger);
        $shouldLog = new ReflectionMethod($middleware, 'shouldLog');

        $this->assertTrue($shouldLog->invoke($middleware, $this->requestForRoute('dashboard'), new Response('', 200)));
        $this->assertTrue($shouldLog->invoke($middleware, $this->requestForRoute('attendance'), new Response('', 200)));
        $this->assertTrue($shouldLog->invoke($middleware, $this->requestForRoute('attendance.today'), new Response('', 200)));

        $this->assertFalse($shouldLog->invoke($middleware, $this->requestForRoute('authorization'), new Response('', 200)));
        $this->assertFalse($shouldLog->invoke($middleware, $this->requestForRoute('project_management.task_list'), new Response('', 200)));
        $this->assertFalse($shouldLog->invoke($middleware, $this->requestForRoute('dashboard', 'POST'), new Response('', 200)));
        $this->assertFalse($shouldLog->invoke($middleware, $this->requestForRoute('dashboard'), new Response('', 500)));
    }

    private function requestForRoute(string $routeName, string $method = 'GET'): Request
    {
        $request = Request::create('/', $method);
        $route = new Route([$method], '/', []);
        $route->name($routeName);
        $request->setRouteResolver(fn (): Route => $route);

        return $request;
    }
}
