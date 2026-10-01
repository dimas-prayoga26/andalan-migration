<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class AuthorizationEmployeeStatusUpdateTest extends TestCase
{
    public function test_status_switch_submits_false_when_unchecked(): void
    {
        $view = File::get(resource_path('views/authorization/form.blade.php'));

        $this->assertStringContainsString('<input type="hidden" name="is_active" value="0">', $view);
        $this->assertStringContainsString('id="is_active" name="is_active" value="1"', $view);
        $this->assertStringContainsString('<input type="hidden" name="is_event_project_admin" value="0">', $view);
        $this->assertStringContainsString('id="is_event_project_admin" name="is_event_project_admin" value="1"', $view);
        $this->assertStringContainsString('Event Project Admin', $view);
        $this->assertStringContainsString('@if ($isEdit)', $view);
        $this->assertStringContainsString('<input type="hidden" name="is_core_staff" value="0">', $view);
        $this->assertStringContainsString('id="is_core_staff" name="is_core_staff" value="1"', $view);
        $this->assertStringContainsString('Core Staff', $view);
    }

    public function test_status_value_is_normalized_and_persisted_without_true_fallback(): void
    {
        $controller = File::get(app_path('Http/Controllers/AuthorizationController.php'));

        $this->assertStringContainsString("'is_active' => \$request->boolean('is_active')", $controller);
        $this->assertStringContainsString("'is_event_project_admin' => \$request->boolean('is_event_project_admin')", $controller);
        $this->assertStringContainsString("'is_core_staff' => \$request->boolean('is_core_staff')", $controller);
        $this->assertStringContainsString("'employee_status' => \$request->boolean('is_active') ? 'Active' : 'Inactive'", $controller);
        $this->assertStringContainsString("'is_active' => ['required', 'boolean']", $controller);
        $this->assertStringContainsString("'is_event_project_admin' => ['required', 'boolean']", $controller);
        $this->assertStringContainsString("'is_core_staff' => ['required', 'boolean']", $controller);
        $this->assertSame(2, substr_count($controller, "'is_active' => (bool) \$validated['is_active']"));
        $this->assertSame(2, substr_count($controller, "'is_event_project_admin' => (bool) \$validated['is_event_project_admin']"));
        $this->assertSame(2, substr_count($controller, "'is_core_staff' => (bool) \$validated['is_core_staff']"));
        $this->assertStringNotContainsString("\$validated['is_active'] ?? true", $controller);
        $this->assertStringNotContainsString("\$validated['is_event_project_admin'] ?? true", $controller);
        $this->assertStringNotContainsString("\$validated['is_core_staff'] ?? true", $controller);
    }
}
