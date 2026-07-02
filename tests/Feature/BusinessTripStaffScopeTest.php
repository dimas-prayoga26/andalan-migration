<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class BusinessTripStaffScopeTest extends TestCase
{
    public function test_business_trip_index_and_detail_are_scoped_for_staff_users(): void
    {
        $controller = File::get(app_path('Http/Controllers/StaffAttendance/AttendanceBusinessTripController.php'));

        $this->assertStringContainsString('private function businessTripIndexQuery(?User $authenticatedUser): Builder', $controller);
        $this->assertStringContainsString('$this->businessTripIndexQuery($authenticatedUser instanceof User ? $authenticatedUser : null)', $controller);
        $this->assertStringContainsString("return \$authenticatedEmployeeId !== ''", $controller);
        $this->assertStringContainsString("? \$businessTripQuery->where('employee_id', \$authenticatedEmployeeId)", $controller);
        $this->assertStringContainsString('abort_unless($this->canAccessBusinessTrip($authenticatedUser instanceof User ? $authenticatedUser : null, $businessTrip), 403);', $controller);
        $this->assertStringContainsString('private function canAccessBusinessTrip(?User $authenticatedUser, BusinessTrip $businessTrip): bool', $controller);
        $this->assertStringContainsString('private function isStaffUser(?User $user): bool', $controller);
        $this->assertStringContainsString('->contains(\'staff\')', $controller);
    }
}
