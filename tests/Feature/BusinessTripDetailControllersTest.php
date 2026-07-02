<?php

namespace Tests\Feature;

use App\Http\Controllers\StaffAttendance\AttendanceBusinessTripCashAdvanceController;
use App\Http\Controllers\StaffAttendance\AttendanceBusinessTripLifecycleLogController;
use App\Http\Controllers\StaffAttendance\AttendanceBusinessTripReimbursementController;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class BusinessTripDetailControllersTest extends TestCase
{
    public function test_business_trip_detail_resource_controllers_exist_in_staff_attendance_controller_folder(): void
    {
        $this->assertFileDoesNotExist(app_path('Http/Controllers/BusinessTripExpenseItemController.php'));

        foreach ([
            AttendanceBusinessTripCashAdvanceController::class => 'AttendanceBusinessTripCashAdvanceController.php',
            AttendanceBusinessTripReimbursementController::class => 'AttendanceBusinessTripReimbursementController.php',
            AttendanceBusinessTripLifecycleLogController::class => 'AttendanceBusinessTripLifecycleLogController.php',
        ] as $controllerClass => $fileName) {
            $controllerSource = File::get(app_path('Http/Controllers/StaffAttendance/'.$fileName));

            $this->assertTrue(class_exists($controllerClass));
            $this->assertStringContainsString('namespace App\Http\Controllers\StaffAttendance;', $controllerSource);

            foreach ([
                'index',
                'create',
                'store',
                'show',
                'edit',
                'update',
                'destroy',
            ] as $resourceMethod) {
                $this->assertTrue(method_exists($controllerClass, $resourceMethod), $controllerClass.'::'.$resourceMethod);
            }
        }
    }
}
