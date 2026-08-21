<?php

namespace Tests\Feature;

use App\Http\Controllers\AuthorizationController;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AuthorizationEventDivisionManagementTest extends TestCase
{
    public function test_event_division_management_routes_are_registered(): void
    {
        $storeRoute = Route::getRoutes()->getByName('authorization.event-divisions.divisions.store');
        $updateRoute = Route::getRoutes()->getByName('authorization.event-divisions.divisions.update');
        $destroyRoute = Route::getRoutes()->getByName('authorization.event-divisions.divisions.destroy');

        $this->assertNotNull($storeRoute);
        $this->assertSame('authorization/event-divisions/divisions', $storeRoute?->uri());
        $this->assertContains('POST', $storeRoute?->methods() ?? []);
        $this->assertSame(AuthorizationController::class.'@storeEventDivision', $storeRoute?->getActionName());

        $this->assertNotNull($updateRoute);
        $this->assertSame('authorization/event-divisions/divisions/{eventDivision}', $updateRoute?->uri());
        $this->assertContains('PATCH', $updateRoute?->methods() ?? []);
        $this->assertSame(AuthorizationController::class.'@updateEventDivision', $updateRoute?->getActionName());

        $this->assertNotNull($destroyRoute);
        $this->assertSame('authorization/event-divisions/divisions/{eventDivision}', $destroyRoute?->uri());
        $this->assertContains('DELETE', $destroyRoute?->methods() ?? []);
        $this->assertSame(AuthorizationController::class.'@destroyEventDivision', $destroyRoute?->getActionName());
    }

    public function test_assign_event_division_page_exposes_add_update_and_delete_controls(): void
    {
        $view = File::get(resource_path('views/authorization/assign-event-divisions.blade.php'));

        $this->assertStringContainsString('Add Event Division', $view);
        $this->assertStringContainsString('id="addEventDivisionModal"', $view);
        $this->assertStringContainsString("route('authorization.event-divisions.divisions.store')", $view);
        $this->assertStringContainsString('js-edit-event-division', $view);
        $this->assertStringContainsString('id="editEventDivisionModal"', $view);
        $this->assertStringContainsString("route('authorization.event-divisions.divisions.update'", $view);
        $this->assertStringContainsString('deleteEventDivisionForm-', $view);
        $this->assertStringContainsString("route('authorization.event-divisions.divisions.destroy'", $view);
        $this->assertStringContainsString('initializeEventDivisionEditor', $view);
    }

    public function test_event_division_controller_validates_and_soft_deletes_divisions(): void
    {
        $controller = File::get(app_path('Http/Controllers/AuthorizationController.php'));
        $eventDivisionModel = File::get(app_path('Models/EventDivision.php'));

        $this->assertStringContainsString('public function storeEventDivision(Request $request): RedirectResponse', $controller);
        $this->assertStringContainsString('public function updateEventDivision(Request $request, EventDivision $eventDivision): RedirectResponse', $controller);
        $this->assertStringContainsString('public function destroyEventDivision(Request $request, EventDivision $eventDivision): RedirectResponse', $controller);
        $this->assertStringContainsString('private function validatedEventDivisionData(Request $request, ?EventDivision $eventDivision = null): array', $controller);
        $this->assertStringContainsString("Rule::unique('event_divisions', 'title')", $controller);
        $this->assertStringContainsString("\$eventDivision->update(['status' => 'inactive']);", $controller);
        $this->assertStringContainsString("->where('current_event_division_id', \$eventDivision->id)", $controller);
        $this->assertStringContainsString("->update(['current_event_division_id' => null]);", $controller);
        $this->assertStringContainsString('ProjectDivisionEvent::query()', $controller);
        $this->assertStringContainsString('use GeneratesCustomSequenceUuid;', $eventDivisionModel);
        $this->assertStringContainsString("'status' => 'active'", $eventDivisionModel);
    }
}
