<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class HeaderProfileComposerTest extends TestCase
{
    public function test_header_uses_employee_picture_and_primary_position(): void
    {
        $header = File::get(resource_path('views/layouts/header.blade.php'));
        $composer = File::get(app_path('View/Composers/HeaderProfileComposer.php'));
        $provider = File::get(app_path('Providers/AppServiceProvider.php'));

        $this->assertStringContainsString("View::composer('layouts.header', HeaderProfileComposer::class);", $provider);
        $this->assertStringContainsString('employee.profile:id,employee_id,name,profile_picture_path', $composer);
        $this->assertStringContainsString('employee.deployment.position:id,name', $composer);
        $this->assertStringContainsString("asset('assets/default_user.jpg')", $composer);
        $this->assertStringNotContainsString('getRoleNames()', $composer);
        $this->assertStringNotContainsString('DB::table', $header);
        $this->assertSame(2, substr_count($header, '{{ $headerUserAvatarUrl }}'));
        $this->assertSame(2, substr_count($header, '{{ $headerUserPositionLabel }}'));
    }
}
