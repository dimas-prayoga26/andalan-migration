<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class TestingPageStructureTest extends TestCase
{
    public function test_testing_route_uses_view_without_position_permission(): void
    {
        $route = Route::getRoutes()->getByName('testing');

        $this->assertNotNull($route);
        $this->assertSame('testing.index', $route->defaults['view']);
        $this->assertNotContains('position.permission:view-testing', $route->gatherMiddleware());
    }

    public function test_testing_view_uses_main_layout_and_sidebar_links_to_route(): void
    {
        $testingView = file_get_contents(resource_path('views/testing/index.blade.php'));
        $sidebar = file_get_contents(resource_path('views/layouts/sidebar.blade.php'));

        $this->assertStringContainsString("@extends('layouts.main')", $testingView);
        $this->assertStringContainsString("route('testing')", $sidebar);
    }
}
